var mytable;
$(function() {
    mytable = InitDataTable({
        $el: $('#example1'), //表格dom选择器
        url: "/Home/CarGroup/groupListAjax", //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "group_id": { title: "编号", "aaSorting": "desc", "orderable": true }, //不需要显示的列定义visible： false
                "group_name": { title: "名称" }, //不需要排序的列定义 orderable: false
                "group_depict": { title: "描述" },
                "organ_name": { title: "企业名称" }
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) {

                    var _btnfh = ('<a href="/Home/CarGroup/editGroup/id/' + row.group_id + '" class="btn btn-xs btn-success">修改</a>');
                    var _text = (_btnfh + ' <span data-id="' + row.group_id + '" onclick="removeRecord( this )" class="btn btn-xs btn-default">删除</span> ');

                    return _text;
                }
            }
        }
    });
});

function removeRecord(el) {
    var _id = $(el).attr('data-id');

    Confirm('确实要删除该车辆分组吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/CarGroup/delGroup/id/' + _id, function(res) { 
                AlertHide( res.msg );
                if( res.status == 0 ){
                    return;
                };
                mytable.refresh();
            });
        };
    });
};
