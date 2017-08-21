
//表单序列化后转成对象
function conveterParamsToJson(paramsAndValues) {
    var jsonObj = {};
    var param = paramsAndValues.split("&");
    for ( var i = 0; param != null && i < param.length; i++) {
        var para = param[i].split("=");
        jsonObj[para[0]] = para[1];
    }
    return jsonObj;
}


$(function(){
    /*获取sim列表*/
    mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: "/Home/SIM/simListAjax", //表格列表数据
        ajaxdata: {},
        scrollX: true, //是否显示横向滚动条
        tableOpts: {
            data: {//初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "id": { title: "编号"},
                "imsi": { title: "IMSI号"}, //不需要显示的列定义visible： false
                "sim_iccid": { title: "ICCID号"},
                "device_no": { title: "sim卡和设备绑定状态" }, 
                "tel": { title: "用户手机号" }, 
              /*  "uni_real_name": { title: "实名制认证状态" },*/
                "uni_real_name": {
                    title: "实名制认证状态",
                    render: function(data, type, row, meta) {
                        var _data = JSON.stringify(row);
                        // console.log(_data);
                        if(data=="未通过"){
                            var _text = '<span data-obj="' + _data + '" errorMsg="' + row.uni_error_info + '" class="text-blue" onclick="showSendeePhone( this )">' + data + '</span>';
                            return _text;
                        }
                        else{
                            var _text = '<span data-obj="' + _data + '" errorMsg="' + row.uni_error_info + '">' + data + '</span>';
                            return _text;
                        }

                    }
                },
                "reg_time": { title: "认证状态时间" }, 
                "total_flow": { title: "总流量(Mb)" },
                "package_month": { title: "套餐月份" }, 
                "organ_name": { title: "SIM卡归属" }, 
                "vender_name": { title: "应用硬件厂家" }
            },
            operate: {
                "title": '操作', //自定义操作列
                render: function(data, type, row, meta) {
                    var str = '';
                    if(row.device_no=="未绑定"){
                        str += '<span class="btn-group">' +
                            '<a href="#" checkID="'+row.id+'" onclick="checkMsg(this)" class="btn btn-xs btn-info">查看</a> ' +
                            '</span>';
                        return str;
                    }
                    else{
                        str += '<span class="btn-group">' +
                            '<a href="#" checkID="'+row.id+'" onclick="checkMsg(this)" class="btn btn-xs btn-info">查看</a> ' +
                            '<a href="#" checkID="'+row.id+'" onclick="free(this)" class="btn btn-xs btn-default">解绑</a> ' +
                            '</span>';
                        return str;
                    }
                }
            }
        }
    });

    /*点击搜索*/
    var status = false;
    $('#searchBtn').bind('click', function(){
        status = true;
        var _data = $('#submit_form').serialize();
        data= decodeURIComponent(_data,true);
        var paramsData = conveterParamsToJson(data);
        mytable.reloadByParam(paramsData);
    });



    /*导出数据*/
    
    $('#fileOut').bind('click',function () {
        var _href = "/Home/SIM/simListAjax";
        var _data = $('#submit_form').serialize();
        _href = _href + '?' + 'fileOut=1&'+_data;
        $(this).attr('href', _href);
    });

    var $ajaxHtml = $('<div class="modal fade in modal-ajax" style="display: block;"><i class="fa fa-refresh fa-spin"></i></div>'); 
    /*批量导入按钮*/
    $('#upload').uploadify({
        'preventCaching': false,
        'fileSizeLimit': '50MB', //上传大小限制
        'fileTypeExts': '*.xls;*.xlsx', //格式限制
        'queueSizeLimit': 1, //数量限制
        'buttonText': '批量导入',
        'swf': '/Public/plugins/uploadify/flash/uploadify.swf',
        'uploader': '/Home/MyUpload/Index/import/sim',
        'onSelect': function(){
            $ajaxHtml.appendTo( top.document.body ); 
        },
        'onUploadSuccess': function(file, res, response) { //成功回调
            $ajaxHtml.remove();
            var data = JSON.parse(res);
            var success = $('.alert_page_success');
            var error = $('.alert_page_error');
            Alert(data.msg, function() {
                    if (data.status == '1') {
                        mytable.refresh();
                    };
                });
        }
    });
});

function showSendeePhone(el) {
    if($(el).text()=="未通过"){
        Alert($(el).attr('errorMsg'));
    }
}

function checkMsg(el) {
    var _id = $(el).attr("checkID");//获取行内信息
    getCard(_id);
}
//点击查看获取详细信息
function getCard(id)
{
    $.ajax({
        url:'/Home/SIM/getInfoById/id/'+id,
        dataType:'json',
        type:'post',
        success:function(result)
        {
            var content = "<div style='width:250px;margin:0 auto;margin-top:10px;'>";
            content += "MSISDN号 ：<strong>"+result.msisdn+"</strong><br />";
            content += "归属企业：<strong>"+result.organ_name+"</strong><br />";
            content += "归属公司：<strong>"+result.company_name+"</strong><br />";
            content += "归属机构：<strong>"+result.son_name+"</strong><br />";
            content += "绑定时间：<strong>"+result.active_time+"</strong><br />";
            content += "到期时间：<strong>"+result.expire_time+"</strong><br />";
            content += "</div>";
            layer.open({
                type: 1,
                title: false,
                shadeClose: true,
                shade: 0.2,
                area: ['400px', '150px'],
                content: content
            });
        }
    });
}
function free(el) {
    var _id = $(el).attr("checkID");//获取行内信息
    Confirm('确实要解绑吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/SIM/unbindSIM/id/'+_id, function(res) {

                AlertHide(res.msg, function() {
                    if (res.status == '1') {
                    window.location.href = '/Home/SIM/simList';
                    };
                });
            });
        };
    });

}







