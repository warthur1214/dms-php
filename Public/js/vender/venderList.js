$(function() {
    var venderListCache = {};
    var mytable = InitDataTable({
        $el: $('#example1'), //表格dom选择器
        url: "/Home/Vender/venderListAjax", //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "vender_id": { title: "编号", "aaSorting": "desc", "orderable": true }, //不需要显示的列定义visible： false
                "vender_name": { title: "硬件厂家" } //不需要排序的列定义 orderable: false
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) { 

                    var _btnfh = ('<a href="/Home/Vender/editVender/id/' + row.vender_id + '" class="btn btn-xs btn-success">修改</a>');
                    var _text = (_btnfh + ' <span data-id="' + row.vender_id + '" onclick="removeRecord( this )" class="btn btn-xs btn-default">删除</span> ');

                    return _text;
                }
            }
        }
    });
});

/*删除列表记录*/
function removeRecord(el) {
    var _id = $(el).attr('data-id');

    Confirm('确实要删除该硬件厂家吗?', function(flag) {
        if (flag) {
            AjaxJson('/Home/Vender/delVender/id/' + _id, function(res) {
                
                AlertHide( res.msg );
                if( res.status == 0 ){
                    return;
                };
                
                $(el).parents('tr').remove();
            });
        };
    });
};
