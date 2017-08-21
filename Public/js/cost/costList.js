var mytable;
$(function() {
    AjaxJson("/Home/Cost/getCarNo", function(res) {

        InitAutocomplete({
            dataSource: res.data,
            $el: $("#car_no"),
            valueKey: 'car_id',
            labelKey: 'car_no'
        });
    });


    AjaxJson("/Home/Cost/getDriver", function(res) {

        InitAutocomplete({
            dataSource: res.data,
            $el: $("#driver_id"),
            valueKey: 'driver_id',
            labelKey: 'driver_name'
        });
    });
    
    $('#sdate').datepicker({ //开始时间 
        maxDate: '',
        onSelect: function() {
            var $this = $(this);
            var $nextDate = $('#edate');
            var _val = $this.val();

            $nextDate.datepicker("option", "minDate", _val);
        }
    });

    $('#edate').datepicker({ //结束时间  
        maxDate: '',
        onSelect: function() {
            var $this = $(this);
            var _val = $this.val();
            var $prevDate = $('#sdate');

            $prevDate.datepicker("option", "maxDate", _val);
        }
    });

    $('#searchBtn').bind('click', function() { //点击搜索按钮
        loadDatatable();
    });
    loadDatatable();

    /*==========================================
     * 加载图表数据
     */
    function loadDatatable() {
        var postdata = getSearchData();

        if (mytable) {
            postdata.car_no = postdata.car_id;
            delete postdata.car_id;
            mytable.reloadByParam(postdata);
        } else {
            mytable = InitDataTable({
                $el: $('#myTable'), //表格dom选择器
                url: "/Home/Cost/costListAjax", //表格列表数据 
                ajaxdata: {},
                tableOpts: {
                    data: {
                        "cost_id": { title: "cost_id", visible: false }, //不需要显示的列定义visible： false
                        "car_no": { title: "车牌号" }, //不需要排序的列定义 orderable: false
                        "driver_name": { title: "司机姓名" },
                        "cost_type": { title: "费用类型" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                        "cost": { title: "费用金额（元）" },
                        "cost_time": { title: "费用产生日期" }
                    },
                    operate: {
                        "title": '操作', //自定义操作列 
                        render: function(data, type, row, meta) {

                            var _data = JSON.stringify(row);
                            var _btnfh = ('<span data-obj=' + _data + ' onclick="editRecord( this )" class="btn btn-xs btn-default">编辑</span>');
                            var _text = (_btnfh + ' <span data-obj=' + _data + ' onclick="deleteRecord( this )" class="btn btn-xs btn-success">删除</span> ');

                            return _text;
                        }
                    }
                }
            });
        };
    };
});

/*=================================================
 * 修改记录
 */
function editRecord(el) {
    var _data = JSON.parse($(el).attr('data-obj'));
    HrefTo( '/Home/Cost/editCost?id=' + _data.cost_id ); 
};

/*=================================================
 * 删除记录
 */
function deleteRecord(el) {
    Confirm('<p>确认删除？</p>', function(res) {
        if (res) {
            var _data = JSON.parse($(el).attr('data-obj'));

            AjaxJson('/Home/Cost/delCost/id/' + _data.cost_id, function(res) { 

                AlertHide( res.msg );
                if( res.status == 0 ){
                    return;
                };

                mytable.refresh();
            });
        };
    }); 
};
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
