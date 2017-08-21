function InitCarTable(opts) {
    var myTable = null;
    var _defaults = {
        $el: '',
        removeFlag: false
    };
    var _opts = $.extend({}, _defaults, opts);
    var _initCarTable = {
        _$table: null,
        _tableParam: null,
        _tableIds: '',
        _init: function(opts) {
            var me = this;
            for (var i in opts) {
                me[i] = opts[i];
            };
            me._$table = me.$el.find('table');
        },
        load: function( url, param ) {
            var me = this;
            var _param = param ? param : {}; 

            me.show(); 
            
            if( myTable ){
                myTable.destroy();
                myTable = null;
            };
            var _tableOption = {
                $el: me._$table, //表格dom选择器
                url: url, //表格列表数据 
                ajaxdata: _param,
                tableOpts: {
                    data: { 
                        "car_no": { title: "车牌号" }, //不需要排序的列定义 orderable: false 
                        "car_band": { title: "车辆品牌" },
                        "driver_name": { title: "司机名称" },
                        "driver_phone": { title: "联系方式" }
                    }
                }
            };
            if (me.getRemoveFlag()) {
                _tableOption.tableOpts.operate = {
                    "title": '操作', //自定义操作列 
                    render: function(data, type, row, meta) {
                        var _data = JSON.stringify(row);
                        var _text = ('<span data-obj=' + _data + ' onclick="deleteRecordInitCarTable( this )" class="label label-success">删除</span> ');

                        return _text;
                    }
                }
            };
            myTable = InitDataTable(_tableOption, function(){
                me.setTableParam(_param);
            });  
            
        },
        removeByValue: function( arr, val ) {   
            for (var i = 0; i < arr.length; i++) {    
                if ( arr[i] == val ) {       
                    arr.splice(i, 1);     
                    return arr.join(',');     
                };   
            };
            return arr.join(',');   
        },
        removeItem: function( el ) { 
            var me = this;
            var _param = me.getTableParam();
            var id = JSON.parse($(el).attr('data-obj')).car_id;
            var _tableIds = _param.carStr.split(',');

            _tableIds = me.removeByValue( _tableIds, id );   

            $(el).parent().parent().remove(); 
            me.setTableParam({ carStr: _tableIds }); 
        },
        show: function() {
            var me = this;
            me.$el.removeClass('hide');
        },
        hide: function() {
            var me = this; 
            me.$el.addClass('hide'); 
            
            myTable.destroy();
            myTable = null;
        },
        setRemoveFlag: function(flag) {
            this.removeFlag = flag;
        },
        getRemoveFlag: function() {
            return this.removeFlag;
        },
        setTableParam: function( param ) {
            var me = this;
            var _param;

            if( $.isEmptyObject( param ) ){ 
                var _tabledata = myTable.getDatatableSource();
                var _carids = [];

                for(var i = 0, l = _tabledata.length; i < l; i++){
                    var _carid = _tabledata[i].car_id;
                    _carids.push( _carid );
                };

                _param = {"carStr": _carids.join(',') };
                
            }else{
                _param = param;
            }; 
            this._tableParam = _param;  
            this.setTableIds( _param.carStr );
        },
        getTableParam: function() { 
            return this._tableParam;
        },
        setTableIds: function( ids ) {
            this._tableIds = ids;
        },
        getTableIds: function( ids ) { 
            return this._tableIds;
        }
    };
    _initCarTable._init(_opts);
    return _initCarTable;
};
