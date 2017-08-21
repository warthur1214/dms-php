function IndexLineChart(opts) {
    var _defaults = {
        domId: '', // 图表元素id
        title: '', //图表标题
        url: '',
        xkey: '',
        ykey: '',
        xdata: [],
        ydata: []
    };
    var _IndexLineChart = {
        sourceData: {},
        myChart: null,
        _init: function(opts) {
            var me = this;
            for (var i in opts) {
                me[i] = opts[i];
            };
            me.myChart = echarts.init(document.getElementById(me.domId));
            me.setOption({
                title: {
                    text: me.title
                },
                xAxis: [{
                    data: me.xdata
                }],
                series: [{
                    data: me.ydata
                }]
            });
            me.loadData( me.url );
            me.bindEvent();
        },
        /*请求数据源*/
        loadData: function(url) {
            var me = this;
            if( url == ""){
                return;
            };
            AjaxJson( url, function( res ){
                var _data = me.transferLineBarChartData( res, me.xkey, me. ykey );
                me.sourceData = _data; 
                me.setOption({ 
                    xAxis: [{
                        data: _data.xdata
                    }],
                    series: [{
                        data: _data.ydata
                    }]
                });
                me.successLoadData();
            });
        },
        successLoadData: function( ){ 
        },
        /*将服务器端返回的数据转换成echart需要的数据格式*/
        transferLineBarChartData: function(data, xkey, ykey) {
            var _x = xkey;
            var _y = ykey;
            var _data = data;
            var tempdata = {
                xdata: [],
                ydata: []
            };
            if( !data ){
                return tempdata;
            };
            for (var i = 0, l = _data.length; i < l; i++) {
                var _d = _data[i];
                var _xval = _d[_x];
                var _yval = _d[_y];
                tempdata.xdata.push(_xval);
                tempdata.ydata.push(_yval);
            };
            return tempdata;
        },
        /*获取图表默认配置项*/
        getDefaultOption: function() {
            var option = {
                title: {
                    show: false,
                    left: 'center',
                    textStyle: {
                        fontWeight: 'normal',
                        fontSize: 20
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    formatter: "{b}: <br/>{c}"
                },
                grid: {
                    left: 20,
                    right: 20,
                    bottom: 20,
                    top: 20,
                    containLabel: true
                },
                xAxis: [{
                    type: 'category'
                }],
                yAxis: [{
                    type: 'value'
                }],
                series: [{
                    type: 'line',
                    itemStyle: {
                        normal: {
                            color: '#09afd8'
                        }
                    },
                    lineStyle: {
                        normal: {
                            color: '#09afd8'
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: '#00c0ef'
                        }
                    },
                    label: {
                        normal: {
                            show: true,
                            position: 'top'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontSize: '20',
                                fontWeight: 'bold'
                            }
                        }
                    }
                }]
            };
            return option;
        },
        /*设置图表配置项*/
        setOption: function(opts) {
            var me = this;
            var _newOption = $.extend(true, me.getDefaultOption(), opts);

            if( _newOption.series[0].data.length == 0 ){
                _newOption.tooltip.show = false;
            }else{
                _newOption.tooltip.show = true;
            };
            me.myChart.setOption(_newOption);
        },
        /*事件处理器*/
        bindEvent: function() {
            var me = this;
            me.myChart.on('click', function(params) {
                me.onClick(params);
            });
        },
        /*点击折线节点的回调方法*/
        onClick: function(data) {

        }
    };
    var _options = $.extend({}, _defaults, opts);
    _IndexLineChart._init(_options);
    return _IndexLineChart;
};
