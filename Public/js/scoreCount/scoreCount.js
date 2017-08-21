var mytable;  
$(function() {   
    CarGroupPopup({
        $textEl: $('#car_group'),
        $valueEl: $('[name="car_group"]'),
        showAll: true
    });
    $('#searchBtn').bind('click', function() { //点击搜索按钮
        loadDatatable();
    });

    $('#btnExport').bind('click', function() {
        var _href = "/Home/ScoreCount/scoreListAjax";
        var postdata = $.extend({ "fileOut": "1" }, getSearchData());
        var _searchArry = [];

        for (var i in postdata) {
            var _data = (i + '=' + postdata[i]);
            _searchArry.push(_data);
        };
        
        _href = _href + '?' + _searchArry.join('&'); 
        $(this).attr('href', _href);
    });

    /*==================================================
     * 日期模块相关配置
     */
    var _currentDate = new Date($('#currentDate').val());
    var _sDate = new Date( _currentDate.valueOf() - 3 * 24 * 60 * 60 * 1000); 
    var _eDate = new Date( _currentDate.valueOf() - 1 * 24 * 60 * 60 * 1000); 
    $('#sdate').datepicker({ //开始时间 
        maxDate: '-1d',
        onSelect: function() { 
            var $this = $(this);
            var $nextDate = $('#edate');
            var _val = $this.val(); 

            $nextDate.datepicker("option", "minDate", _val);
        }
    });
    $('#sdate').datepicker( "setDate", getDateString( _sDate ) ); 

    $('#edate').datepicker({ //结束时间 
        minDate: getDateString( _sDate ),
        maxDate: '-1d', 
        onSelect: function() { 
            var $this = $(this); 
            var _val = $this.val(); 
            var $prevDate = $('#sdate');

            $prevDate.datepicker("option", "maxDate", _val);
        }
    });
    $('#edate').datepicker( "setDate", getDateString( _eDate ) ); 


    loadDatatable(); 
});

/*==========================================
 * 加载图表数据
 */
function loadDatatable() { 
    var postdata = getSearchData();

    if (mytable) { 
        mytable.reloadByParam( postdata ); 
    } else {
        mytable = InitDataTable({
            $el: $('#myTable'), //表格dom选择器
            url: "/Home/ScoreCount/scoreListAjax", //表格列表数据 
            ajaxdata: { 
            },
            scrollX: true,
            showThead: false,
            tableOpts: {
                data: {
                    "tel": { title: "id", visible: false }, //不需要显示的列定义visible： false
                    "car_no": { title: "开始时间" }, //不需要排序的列定义 orderable: false
                    "device_no": { title: "结束时间" },
                    "risk_score": { title: "车牌号" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                    "accel_score": { title: "设备号" },
                    "decel_score": { title: "司机姓名" },
                    "speed_score": { title: "驾驶时长" },
                    "night_score": { title: "平均速度（km/h）" },
                    "area_score": { title: "最大速度（km/h）" },
                    "duration_score": { title: "急加速（次）" },
                    "distance_score": { title: "急减速（次）" },
                    "start_time": { title: "油耗（L）" },
                    "end_time": { title: "油耗（L）" }
                }
            }
        }); 
    };
};

function reloadByParam( ajaxparam, tableobj ) {
    var _tableobj = tableobj;

    _tableobj.settings()[0].ajax.data = ajaxparam;
    _tableobj.ajax.reload();
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
