function IndexPieChart(opts) {
    var _defaults = {
        domId: '', // 图表元素id
        title: '', //图表标题 
        sdata: []
    };
    var _IndexLineChart = {
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
                series: [{
                    data: me.sdata
                }]
            });
        },
        /*获取图表默认配置项*/
        getDefaultOption: function() {
            var option = {
                color: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
                title: {
                    x: 'center',
                    y: 'middle',
                    textStyle: {
                        fontWeight: 'normal',
                        fontSize: 20
                    }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{b}: <br/>{c}"
                },
                series: [{
                    type: 'pie',
                    radius: ['30%', '55%'],
                    avoidLabelOverlap: false,
                    label: {
                        normal: { 
                            formatter: '{b}\n\n{d}%'
                        },
                        emphasis: {
                            show: true,
                            textStyle: {
                                fontWeight: 'bold'
                            }
                        }
                    },
                    itemStyle: {
                        normal: {
                            borderColor: '#fff',
                            borderWidth: 3,
                            borderType: 'solid'
                        }
                    },
                    data: []
                }]
            };
            return option;
        },
        /*设置图表配置项*/
        setOption: function(opts) {
            var me = this;
            var _newOption = $.extend(true, me.getDefaultOption(), opts); 
            
            me.myChart.setOption(_newOption);
        },
        redrawByData: function( newdata ) { 
            
            this.setOption({
                title: {
                    text: newdata.title
                },
                series: [{
                    data: newdata.sdata
                }]
            });
        }
    };
    var _options = $.extend({}, _defaults, opts);
    _IndexLineChart._init(_options);
    return _IndexLineChart;
};
