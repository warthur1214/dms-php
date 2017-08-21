$(function() {
    var mytable;
    CarGroupPopup({
        $textEl: $('#car_group'),
        $valueEl: $('[name="car_group"]'),
        showAll: true
    });

    $('#fileOut').click(function() {
        var arr = mytable.getSelected();  
        if (arr.length != 0) {
            var car_no = $('#car_no').val();
            var trailtime = $('#trailtime').val();
            var car_group = $('[name="car_group"]').val();
            var url = $(".page .current").html();
            $(this).attr('href', "/Home/Trail/getTrail/p/" + url + "?fileStatus=1&car_no=" + car_no + "&trailtime=" + trailtime + "&car_group=" + car_group + "&checkArr=" + arr);
            return;
        } else {
            alert('请选择要导出的数据!');
        }
    });

    mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: '/Home/Trail/getTrail', //表格列表数据 
        ajaxdata: {},
        checkbox: true,
        valKey: 'id',
        tableOpts: {
            data: {
                "id": { title: "行程编号" }, //不需要排序的列定义 orderable: false
                "device_no": { title: "设备号" },
                "car_no": { title: "车牌号" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "start_time": {title: "点火时间"},
                "end_time": {title: "熄火时间"},
                "distance_travelled": { title: "本次行驶距离（km）" },
                "duration": { title: "本次行驶时长" },
                "oil_wear": {
                    title: "本次行驶油耗（L）",
                    render: function(data, type, row, meta) {
                        return data / 1000;
                    }
                },
                "start_address": {
                    title: "本次行程",
                    render: function(data, type, row, meta) {
                        var start = row.start_address;
                        var end = row.end_address;
                        var str = start + ' — ' + end;
                       // / var str = start ? start.substr(0, 6) + "... — " + end.substr(0, 6) + "..." : '';
                        var _html = ("<span title='" + start + " — " + end + "'>" + str + "</span>");
                        return _html;
                    }
                }
            },
            operate: {
                "title": '操作', //自定义操作列 
                width: '100px',
                render: function(data, type, row, meta) {

                    var _btnfh = ("<a href='/Home/Trail/checkInfo/id/" + row.id + "/user_id/" 
                        + row.user_id + "/jny_id/" + row.jny_id + "/start_time/" + row.start_time + "' class='btn btn-xs btn-success'>行程</a> <a href='/Home/Trail/checkTrail/id/" 
                        + row.id + "/user_id/" + row.user_id + "/jny_id/" + row.jny_id + "/start_time/" + row.start_time + "' class='btn btn-xs btn-default'>轨迹</a>");
                    return _btnfh;
                }
            }
        }
    });
 
    $('#trailtime').daterangepicker();
    $('.fa-calendar-times-o').click(function() {
        $('#trailtime').val('');
    });

    $('.selectInfo').click(function() {
        var postdata = getSearchData();  
        mytable.reloadByParam( postdata );
    });
}); 
