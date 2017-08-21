var mytable;
$(function() {
    CarGroupPopup({
        $textEl: $('#organ_id'),
        $valueEl: $('[name="organ_id"]'),
        showGroup: false,
        showAll: true
    });

    mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: '/Home/Vehicle/getInfo', //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "id": { title: "编号" }, //不需要排序的列定义 orderable: false
                "device_no": { title: "设备号" },
                "active_status": { title: "设备状态" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "bind_status": { title: "绑定状态" },
                "device_com": { title: "设备公司" },
                "device_type": { title: "设备类型" },
                "active_time": { title: "激活时间" },
                "organ_name": { title: "设备归属" } 
            },
            operate: {
                "title": '操作', //自定义操作列
                render: function(data, type, row, meta) { 
                    var _btnHtml = '<a href="javascript:;" onclick="unbundingTel(\'' + row.id + '\')" class="btn btn-xs btn-info">手机解绑</a> <a href="javascript:;" onclick="getCard(\'' + row.id + '\')" class="btn btn-xs btn-primary">查看</a> <a href="javascript:;" onclick="delVehicle(\'' + row.id + '\',this)" class="btn btn-xs btn-danger">删除</a>';
                    return _btnHtml;
                }
            } 
        }
    });    
    $('#active_time').daterangepicker();
    $('.fa-calendar-times-o').click(function() {
        $('#active_time').val('');
    })
    $('#vender_id').change(function() {
        $.ajax({
            url: '/Home/Vehicle/getDevice/act/type/id/' + $(this).val(),
            type: "post",
            dataType: "json",
            success: function(result) {
                var html = "";
                html += "<option value=''>请选择</option>";
                for (var i = 0; i < result.length; i++) {
                    html += "<option value='" + result[i]['device_type_id'] + "'>" + result[i]['device_type_name'] + "</option>";
                };
                $('#device_type').html(html);
            }
        });
        $('#device_model').html('');
    })
    $('#device_type').change(function() {
        $.ajax({
            url: '/Home/Vehicle/getDevice/act/model/id/' + $(this).val(),
            type: "post",
            dataType: "json",
            success: function(result) {
                var html = "";
                html += "<option value=''>请选择</option>";
                for (var i = 0; i < result.length; i++) {
                    html += "<option value='" + result[i]['device_model_id'] + "'>" + result[i]['device_model_name'] + "</option>";
                };
                $('#device_model').html(html);
            }
        });
    });

    $('.selectInfo').click(function() {
        var postdata = getSearchData(); 
        mytable.reloadByParam( postdata );
    });

    var $ajaxHtml = $('<div class="modal fade in modal-ajax" style="display: block;"><i class="fa fa-refresh fa-spin"></i></div>');

    /*导出数据按钮*/
    $('#btnExport').bind('click', function() {
        $ajaxHtml.appendTo(top.document.body);
        var _href = "/Home/Vehicle/getInfo";
        var postdata = $.extend({ "fileOut": "1" }, getSearchData());
        var _searchArry = [];

        for (var i in postdata) {
            var _data = (i + '=' + postdata[i]);
            _searchArry.push(_data);
        };

        _href = _href + '?' + _searchArry.join('&'); 
        $(this).attr('href', _href); 

        var t = setInterval(function(){

            if( document.readyState=="complete" ){ 
                setTimeout(function(){
                    $ajaxHtml.remove();
                },1000);
                clearInterval(t);
            };
        },500);
    });



    /*批量导入按钮*/
    $('#upload').uploadify({
        'preventCaching': false,
        'fileSizeLimit': '50MB', //上传大小限制
        'fileTypeExts': '*.xls;*.xlsx', //格式限制
        'queueSizeLimit': 1, //数量限制
        'buttonText': '批量导入',
        'successTimeout': 0,
        'swf': '/Public/plugins/uploadify/flash/uploadify.swf',
        'uploader': '/Home/MyUpload/Index/import/device',
        'onSelect': function() {
            $ajaxHtml.appendTo(top.document.body);
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
/*==================================================
 * 返回搜索表单值
 */
function getSearchData() {
    var _formdata = $('#submit_form').serializeArray();
    var _postdata = {};

    for (var i = 0, l = _formdata.length; i < l; i++) {
        var _d = _formdata[i];
        var _name = _d.name;
        _postdata[_name] = _d.value;
    };
    return _postdata;
}; 
//获取详细信息
function getCard(id) {
    HrefTo('/Home/Vehicle/editVehicle?id=' + id);
};
//手机解绑
function unbundingTel(id)
{
    Confirm('确定要解绑手机吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/Vehicle/unbundingTel/id/' + id, function(res) {
                AlertHide(res.msg);
                if (res.status == '1') {
                    mytable.refresh();
                }
            });
        };
    });
}
//删除设备
function delVehicle(id, el) {

    Confirm('确实要删除该设备吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/Vehicle/delVehicle/id/' + id, function(res) {
                AlertHide(res.msg);
                if (res.status == '1') {
                    $(el).parents('tr').remove();
                }
            });
        };
    });
};
