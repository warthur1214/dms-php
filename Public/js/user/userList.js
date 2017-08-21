$(function() {
    $('#fileOut').click(function() {
        var user_phone = $('#user_phone').val();
        var car_no = $('#car_no').val();
        var device_id = $('#device_id').val();
        var organ_id = $('[name="organ_id"]').val();
        var url = $(".page .current").html();
        $(this).attr('href', "/Home/User/getInfo/p/" + url + "?fileStatus=1&user_phone=" + user_phone + "&device_id=" + device_id + "&organ_id=" + organ_id);
        return;
    }); 

    var mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: '/Home/User/getInfo', //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "tel": { title: "用户手机号" }, //不需要排序的列定义 orderable: false
                "nickname": { title: "用户名" },
                "car_no": { title: "车牌号" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "car_vin": { title: "车架号" },
                "motor_no": { title: "发动机号" },
                "device_no": { title: "设备号" },
                "organ_name": { title: "所属企业" },
                "create_time": { title: "注册时间" } 
            } 
        }
    }); 

    $('.selectInfo').click(function() {
        var postdata = getSearchData(); 
        mytable.reloadByParam( postdata );
    });
});
 