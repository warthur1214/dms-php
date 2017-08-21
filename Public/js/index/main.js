$(function() {
    var TravelInfo = {
        "duration": "驾驶时长",
        "avg_speed": "平均速度",
        "max_speed": "最大速度",
        "accel_count": "急加速",
        "decel_count": "急减速"
    };
    var TravelInfoCache = {};
    var CostInfoCache = {};
    /*======== 初始化费用明细饼图 ========*/
    var pieChart = IndexPieChart({
        domId: "feiyongdetail" 
    });

    /*======== 初始化行驶数据柱状图 ========*/
    /*var barChart = IndexBarChart({
        domId: "shujudetail" 
    });*/
    
    /*======== 初始化驾驶评分折线图 ========*/
    IndexLineChart({
        domId: "pingfen", 
        url: '/Home/Index/score',
        xkey: 'create_time',
        ykey: 'score_cnt'
    });
    /*======== 初始车辆费用折线图 ========*/
    IndexLineChart({
        domId: "feiyong", 
        url: '/Home/Index/cost',
        xkey: 'cost_time',
        ykey: 'cost_sum',
        onClick: function(data) {
            onClickCostChart(data);
        },
        successLoadData: function() {
            var _data = this.sourceData.xdata;

            if( _data && _data[0] ){
                getCostInfoData( this.sourceData.xdata[0] );
            }; 
        }
    });
    /*======== 初始车辆费用形式数据折线图 ========*/
    IndexLineChart({
        domId: "shuju", 
        url: '/Home/Index/travel',
        xkey: 'timeVal',
        ykey: 'distance_travelled',
        // onClick: function(data) {
        //     onClickTravelChart(data);
        // },
        successLoadData: function() { 
            var _data = this.sourceData.xdata;

            // if( _data && _data[0] ){
            //     getTravelInfoData( this.sourceData.xdata[0] );
            // }; 
        }
    });

    

    $('#listIndexCarinfo a').bind('click', function(){
        var _href = $(this).attr('href').split('?')[0];
        var $sidebarMenu = $($(top.document.body).find('aside iframe')[0].contentWindow.document.body).find('.sidebar-menu');
        var $activeA =  $sidebarMenu.find('a[href="'+ _href +'"]');

        $sidebarMenu.find('li').removeClass('active');
        $activeA.parents('.treeview').addClass('active');
        $activeA.parent('li').addClass('active');
    });
    /*======== 点击费用折线图，刷新费用明细饼图 ========*/
    function onClickCostChart(data) {
        getCostInfoData( data.name ); 
    }; 

    /*======== 点击行驶数据折线图，刷新行驶数据柱状图 ========*/
    // function onClickTravelChart(data) {
    //     getTravelInfoData( data.name ); 
    // };

    /*======== 获取费用信息数据 ========*/
    function getCostInfoData( month ){
        if( CostInfoCache[month] ){
            RenderCostInfoChart( CostInfoCache[month], month );
            return;
        };
        var _url = '/Home/Index/getCostInfo/cost_time/' + month;
        AjaxJson( _url, function( res ){ 
            CostInfoCache[month] = res;
            RenderCostInfoChart( res, month );
        }); 
    };

    /*======== 获取行驶信息数据 ========*/
    // function getTravelInfoData( month ){ 
    //     if( TravelInfoCache[month] ){
    //         RenderTravelInfoChart( TravelInfoCache[month], month );
    //         return;
    //     };
    //     var _url = '/Home/Index/getTravelInfo/timeval/' + month;
    //     AjaxJson( _url, function( res ){ 
    //         TravelInfoCache[month] = res;
    //         RenderTravelInfoChart( res, month );
    //     }); 
    // };

    /*======== 绘制行驶数据统计图表 ========*/
    function RenderTravelInfoChart( data, date ){
        var _data = data[0];
        if( !_data || _data.length < 0){
            return 
        }; 
        var _xdata = [];
        var _ydata = [];

        for( var i in TravelInfo){
            var _d = _data[i];
            var _key = TravelInfo[i];
            _xdata.push( _key );
            _ydata.push( _d );
        };  
        /*barChart.redrawByData({
            title: date,
            xdata: _xdata,
            ydata: _ydata
        });*/
    };

    /*======== 绘制费用数据统计图表 ========*/
    function RenderCostInfoChart( data, date ){
        var _data = data[0];
        if( !_data || _data.length < 0){
            return 
        }; 
        var _sdata = $.map( data, function( n, i ){
            return { value: n.cost_sum, name: n.cost_type };  
        }); 

        pieChart.redrawByData({
            title: date,
            sdata: _sdata
        });

    };  
});

function TransferLineBarChartData(data, xkey, ykey) {
    var _x = xkey;
    var _y = ykey;
    var _data = data;
    var tempdata = {
        xdata: [],
        ydata: []
    };

    for (var i = 0, l = _data.length; i < l; i++) {
        var _d = _data[i];
        var _xval = _d[_x];
        var _yval = _d[_y];
        tempdata.xdata.push(_xval);
        tempdata.ydata.push(_yval);
    };
    return tempdata;
};
