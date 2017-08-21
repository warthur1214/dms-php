var mytable;
$(function() {
    CarGroupPopup({
        $textEl: $('#car_group'),
        $valueEl: $('[name="car_group"]'),
        showAll: true
    });
    var Mydate = DateRangeCus($('#dateCus'));
    var _currentDate = $('#currentDate').val().split('-');
    Mydate.setDate(_currentDate[0], _currentDate[1], _currentDate[2] );

    Mydate.onClickPrev = function() {
        loadChartData();
    };
    Mydate.onClickNext = function() {
        loadChartData();
    };

    loadChartData(); //加载图标数据 

    $('#boxTools .btn').bind('click', function() { //切换周月年
        var $this = $(this);
        var _val = $this.attr('data-role');

        $('#datestate').val(_val);
        $this.siblings('.btn-success').removeClass('btn-success');
        $this.addClass('btn-success');

        Mydate.setDateState(_val);
        loadChartData();
    });

    $('#searchBtn').bind('click', function() { //点击搜索按钮
        loadChartData();
    });
    $('#btnExport').bind('click', function() {
    	var _href = "/Home/TravelCount/travelListAjax";
    	var postdata = $.extend({ "fileOut": "1" }, getSearchData());
    	var _searchArry = [];

    	for( var i in postdata ){
    		var _data = (i + '=' + postdata[i]);
    		_searchArry.push( _data );
    	};
    	_href = _href + '?' + _searchArry.join('&');

    	$(this).attr('href', _href);  
    }); 
});

/*==========================================
 * 加载图表数据
 */
function loadChartData() {
    var postdata = getSearchData();
    var ajaxtravel = $.ajax({
        url: '/Home/TravelCount/travelData',
        dataType: 'json',
        type: 'POST',
        data: postdata
    });
    var ajaxtravelitem = $.ajax({
        url: '/Home/TravelCount/travelItemData',
        dataType: 'json',
        type: 'POST',
        data: postdata
    });

    $.when(ajaxtravel, ajaxtravelitem).done(function(r1, r2) {
        renderTravelData(r1[0]);
        renderTravelItemChart(r2[0]);
    });

    if( mytable ){
    	mytable.reloadByParam( postdata );
    }else{
    	mytable = InitDataTable({
	        $el: $('#myTable'), //表格dom选择器
	        url: "/Home/TravelCount/travelListAjax", //表格列表数据 
	        ajaxdata: {
	        	"timeStatus": postdata.timeStatus,
	            "timeVal": postdata.timeVal
	        },
            scrollX: true,
	        tableOpts: {
	            data: {
	                "id": { title: "id", visible: false }, //不需要显示的列定义visible： false
	                "start_time": { title: "开始时间" }, //不需要排序的列定义 orderable: false
	                "end_time": { title: "结束时间" },
	                "car_no": { title: "车牌号" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
	                "device_no": { title: "设备号" },
	                "driver_name": { title: "司机姓名" },
	                "duration": { title: "驾驶时长" },
	                "avg_speed": { title: "平均速度（km/h）" },
	                "max_speed": { title: "最大速度（km/h）" },
	                "accel_count": { title: "急加速（次）" },
	                "decel_count": { title: "急减速（次）" },
	                "oil_wear": { title: "油耗（L）" }
	            }
	        }
	    });
    }; 
};

/*==========================================
 * 渲染各项行驶数据统计
 */
function renderTravelData(res) {
    var _data = res[0];
    var $totalChart = $('#totalChart');

    for (var i in _data) {
        $('[data-role="' + i + '"]', $totalChart).html(_data[i]);
    };
};


/*=================================================
 * 渲染数据项图表
 */
function renderTravelItemChart(data) {
    var myChartData = transferData(data);
    var dom = document.getElementById("container");
    var myChart = echarts.init(dom);

    var baseOption = {
        title: {
            text: '行驶数据项',
            textStyle: {
                fontWeight: 'normal'
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {},
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            boundaryGap: false
        },
        yAxis: {
            type: 'value'
        }
    };

    baseOption.legend.data = myChartData.legendData;
    baseOption.xAxis.data = myChartData.xAxisData;
    baseOption.series = myChartData.seriesData;

    if (baseOption && typeof baseOption === "object") { 
        myChart.setOption(baseOption, true);
    };
};

/*========================================================
 * 转换数据格式；将后台服务器传过来的数据转换成图表需要的数据格式
 */
function transferData(data) {
    var _data = data;
    var _xaxisKey = "timeval"; //时间坐标
    var _legendMap = {
        "avg_speed": "平均速度",
        "distance_travelled": "累计里程数",
        "accel_count": "急加速",
        "decel_count": "急减速",
        "oil_wear": "累计油耗",
        "max_speed": "最大速度",
        "duration": "驾驶时长"
    };
    var _legendData = [];
    var _xAxisData = [];
    var _yAxisData = [];
    var _seriesData = [];

    for (var i = 0, l = _data.length; i < l; i++) {
        var _d = _data[i];

        for (var j in _d) {
            var _val = _d[j];

            if (j == _xaxisKey) {
                _xAxisData.push(_val);
            } else {

                if (!_yAxisData[j]) {
                    _yAxisData[j] = [_val];
                } else {
                    _yAxisData[j].push(_val);
                };
            };
        };
    };

    for (var i in _yAxisData) {
        var _val = _yAxisData[i];
        var _legenddata = _legendMap[i];
        var _seriesdata = {
            name: _legenddata,
            type: 'line',
            data: _val
        };

        _seriesData.push(_seriesdata);
        _legendData.push(_legenddata);
    };
    return { 'legendData': _legendData, 'xAxisData': _xAxisData, 'seriesData': _seriesData };
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

    var _timeval = $('#dateCus .text-date').html().split(' — ');
    if (_timeval.length == 2) {
        _timeval = _timeval[0].replace(/\./g, '/') + '-' + _timeval[1].replace(/\./g, '/');
    } else {
        _timeval = _timeval[0].replace(/\./g, '/');
    };
    _postdata.timeVal = _timeval;
    return _postdata;
};
