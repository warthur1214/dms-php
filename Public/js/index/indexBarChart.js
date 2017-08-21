function IndexBarChart(opts) {
    var _defaults = {
        domId: '', // 图表元素id
        title: '行驶数据', //图表标题
        xdata: [],
        ydata: []
    };
    var _IndexBarChart = {
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
        },
        getDefaultOption: function() {
            var option = { 
                color: ['#f56954'],
                title: { 
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
                    top: 40,
                    containLabel: true 
                },
                xAxis: [{
                    type: 'category' 
                }],
                yAxis: [{
                    type: 'value'
                }],
                series: [{ 
                    type: 'bar',
                    barWidth: '40%',
                    label: {
                        normal: {
                            show: true,
                            position: 'top'
                        },
                        emphasis: {
                            show: true,
                            textStyle: { 
                                fontWeight: 'bold'
                            }
                        }
                    }  
                }]
            };
            return option;
        },
        setOption: function( opts ) {
            var me = this; 
            var _newOption = $.extend( true, me.getDefaultOption(), opts );

            if( _newOption.series[0].data.length == 0 ){
                _newOption.tooltip.show = false;
            }else{
                _newOption.tooltip.show = true;
            };
            

            me.myChart.setOption( _newOption ); 
        }, 
        redrawByData: function( newdata ){
            this.setOption({ 
                title: {
                    text: newdata.title
                },
                xAxis: [{
                    data: newdata.xdata
                }],
                series: [{
                    data: newdata.ydata
                }]
            }); 
        }
    };
    var _options = $.extend({}, _defaults, opts);
    _IndexBarChart._init(_options);
    return _IndexBarChart;
};
