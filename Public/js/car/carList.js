$(function() {
    var mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: '/Home/Car/getInfo', //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "car_no": { title: "车牌号" }, //不需要排序的列定义 orderable: false
                "car_band": { title: "车辆品牌" },
                "driver_name": { title: "司机名称" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "car_status": { title: "车辆状态" },
                "device_no": { title: "设备号" },
                "active_status": { title: "设备状态" },
                "bind_status": { title: "绑定状态" },
                "device_type_name": { title: "设备类型" },
                "group_name": { title: "所属车组" }
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) {

                    var _btnfh = ("<a href='/Home/Car/editCar/id/" + row.id + "' class='btn btn-xs btn-info'>查看</a>");
                    return _btnfh;
                }
            }
        }
    });
    CarGroupPopup({
        $textEl: $('#car_group'),
        $valueEl: $('[name="car_group"]'),
        showAll: true
    });
    $('.selectInfo').click(function() {
        var postdata = getSearchData(); 
        mytable.reloadByParam( postdata );
    });
    var _status = location.search.split('status=')[1];
    if( _status ){
        mytable.reloadByParam({ "active_status": _status}); 
        $('#active_status').val( _status );　
    }; 
});

