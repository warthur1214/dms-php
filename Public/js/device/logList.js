/**
 * Created by hongxun.wang on 2016/11/9.
 */

var $searchForm = $("#searchForm");
$("checkDetail").show();
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
        url: "/Home/DeviceLog/logListAjax", //表格列表数据
        ajaxdata: {},
        tableOpts: {
            data: {
                "organ_id": { title: "设备号",orderable: true,"aaSorting": "desc"}, //不需要显示的列定义visible： false
                "organ_name": { title: "手机号码" }, //不需要排序的列定义 orderable: false
                "abbreviated_name": { title: "操作行为" },
                "organ_type": { title: "行为产生时间" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "cooperate_type": { title: "归属企业" }
            }
        }
    });

    /*导出数据*/

    $('#outBtn').bind('click',function () {
        var _href = "/Home/SIM/simListAjax";
        var _data = $('#searchForm').serialize();
        _href = _href + '?' + 'fileOut=1&'+_data;
        $(this).attr('href', _href);
    });
});

//监听设备号改变
$('#deviceName').change(function() {
    var paramObj = conveterParamsToJson($searchForm.serialize());
    mytable.reloadByParam(paramObj);
    console.log(paramObj);
});
//监听手机号改变
$('#phoneNum').change(function() {
    var paramObj = conveterParamsToJson($searchForm.serialize());
    console.log(paramObj);
    mytable.reloadByParam(paramObj);

});
//监听操作行为改变
$('#operateBehavior').change(function() {
    var paramObj = conveterParamsToJson($searchForm.serialize());
    console.log(paramObj);
    mytable.reloadByParam(paramObj);

});




