var ItemStyle = ['item-box-aqua', 'item-box-yellow', 'item-box-blue', 'item-box-green', 'item-box-navy', 'item-box-teal', 'item-box-olive'];
$(function() {
    CarGroupPopup({
        $textEl: $('#car_group'),
        $valueEl: $('[name="car_group"]'),
        showAll: true
    });
    var Mydate = DateRangeCus($('#dateCus'));
    var _currentDate = $('#currentDate').val().split('-');
    Mydate.setDate(_currentDate[0], _currentDate[1], _currentDate[2]);

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
        var _href = "/Home/CostCount/costListOut";
        var postdata = $.extend({ "fileOut": "1" }, getSearchData());
        var _searchArry = [];

        for (var i in postdata) {
            var _data = (i + '=' + postdata[i]);
            _searchArry.push(_data);
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
        url: '/Home/CostCount/costData',
        dataType: 'json',
        type: 'POST',
        data: postdata
    });
    var ajaxtravelitem = $.ajax({
        url: '/Home/CostCount/costItemData',
        dataType: 'json',
        type: 'POST',
        data: postdata
    });

    $.when(ajaxtravel, ajaxtravelitem).done(function(r1, r2) { 
        
        
        if( r1[0] ){
            renderTravelData(r1[0]);    
        }else{
            $('#totalChart').html('<p class="paddingL20">没有统计数据</p>');
        }; 
        
        renderTravelItemChart(r2[0]);
        /*if( r2[0] ){
            
        }else{
            $('#container').html('<h4 class="box-title">费用数据项</h4><p>没有统计数据</p>');
        }; */
    });
};

/*==========================================
 * 渲染各项行驶数据统计
 */
function renderTravelData( res ) {
    var $totalChart = $('#totalChart');
    var _html = renderCostData( res );
    $totalChart.html( _html );   
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
            text: '费用数据项',
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
    var _xaxisKey = "cost_time"; //时间坐标
    var _legendMap = {
        "保险费用": "保险费用",
        "维修费用": "维修费用",
        "保养费用": "保养费用",
        "过路过桥费": "过路过桥费",
        "加油费": "加油费",
        "总费用": "总费用" 
    };
    var _legendData = [];
    var _xAxisData = [];
    var _yAxisData = {};
    var _seriesData = [];

    if( !_data ){
        return { 'legendData': _legendData, 'xAxisData': _xAxisData, 'seriesData': _seriesData };
    };
    for (var i = 0, l = _data.length; i < l; i++) {
        var _d = _data[i];

        for (var j in _d) {
            var _val = _d[j];

            if (j == _xaxisKey) { //缓存时间数据 

                if( _xAxisData.join(',').indexOf(_val) == -1 ){

                    _xAxisData.push(_val);
                    _yAxisData[ _val ] = _d;
                }else{
                    _yAxisData[_val ] = $.extend({}, _yAxisData[ _val ], _d );
                }; 

            }else{ 
                if( _legendData.join(',').indexOf( j ) == -1 ){

                    _legendData.push( j ); 
                };
            }; 
        };
    };

    var _sdata = {};
    for (var i in _yAxisData) {
        var _val = _yAxisData[i];  

        for(var j in _legendData){
            var _d = _legendData[j];
            var _sval = _val[_d] ? _val[_d] : 0;

            if( !_sdata[ _d ] ){
                _sdata[ _d ] = [];
                _sdata[ _d ].push( _sval );
            }else{
                _sdata[ _d ].push( _sval );
            }; 
        };
        
    };

    for(var i in _legendData){
        var _name = _legendData[i];
        var _seriesdata = {
            name: _name,
            type: 'line',
            data: _sdata[_name]
        }; 
        _seriesData.push(_seriesdata); 
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

/*==============================================
 * 渲染费用数据项
 */
function renderCostData( res ) { 
    var _data = res;
    var _html = '';
    if( !_data ){
        return _html;
    };
    for(var i = 0, l = _data.length; i < l; i++){
        var _style = ( ItemStyle[i] ? ItemStyle[i] : '' );
        var _d = _data[i];

        for(var j in _d){
            var _tmpl = '<div class="item-box '+ _style +'">' +
            '<span class="title-box">' +
            '<span data-role="distance_travelled">'+ _d[j] +'</span> 元' +
            '</span>' +
            '<p class="text-item">'+ j +'</p>' +
            '</div>';
            _html += _tmpl;
        }; 
    };
    return _html;
};
