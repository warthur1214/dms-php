var mytable;
$(function() { 
    /** 多选下拉列表*/
    $("#driver_phone").select2({
        placeholder: "请输入手机号",
        allowClear: true,
        data: mData
    });

    mytable = InitDataTable({
        $el: $('#list'), //表格dom选择器
        url: '/Home/Driver/getDriver', //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "driver_id": { title: "编号" }, //不需要排序的列定义 orderable: false
                "name": { title: "司机姓名" },
                // "gender": { title: "性别" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "gender": {
                    title: "性别",
                    render: function(data, type, row, meta) { 
                        return row.gender == 1 ? '女' : '男';
                    }
                },
                "phone": { title: "手机号" },
                "license_no": { title: "驾驶证号" },
                "license_start_time": {
                    title: "驾驶证有效期",
                    render: function(data, type, row, meta) { 
                         return row.license_start_time + ' -- ' + row.license_end_time;
                    }
                } 
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) {

                    var _btnfh = ("<a href='/Home/Driver/editDriver/id/" + row.driver_id + "' class='btn btn-xs btn-success'>查看详情</a> <a href='javascript:;' class='btn btn-xs btn-default' onclick='delDriver(" + row.driver_id + ")'>删除</a>");
                    return _btnfh;
                }
            }
        }
    });
    $('.selectInfo').click(function() {
        var postdata = getSearchData();
        mytable.reloadByParam(postdata);
    });
});

function delDriver(id) {
    //处理删除事件
    Confirm('确实要删除该司机信息吗?', function(res) {
        if (res) {
            AjaxJson('/Home/Driver/delDriver/id/' + id, function(result) {
                if (result.status == 0) {
                    AlertHide(result.msg);
                    return;
                };
                AlertHide(result.msg, function() {
                    mytable.refresh();
                });
            });
        };
    });
};
