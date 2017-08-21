function InitCarTree() {
    var _defaults = {};
    var _initCarTree = {
        _init: function(opts) {},
        load: function(url, call) {
            var me = this;

            AjaxJson(url, function(res) {
                var _html = me.renderTreeList(res.data);
                me.$el = $('<div class="clearfix fencelist-tree-box"><div class="tree-box car-list-box">' + 
                    '<ul class="list-unstyled">'+
                    _html + '</ul></div><div class="car-box"></div></div>');

                Confirm(me.$el, {
                    width: '475px'
                }, function(res) {
                    if (res) {
                        var _carArry = me.getCarSelected();

                        if (call) {
                            call(_carArry);
                        };
                    };
                });
                me.renderCarBox();
                me._bindEvent();

            });
        },
        /*绑定事件*/
        _bindEvent: function() {
            var me = this;
            var $treelist = me.$el.find('.tree-box li');
            var $carlist = me.$el.find('.car-box li');

            /*点击收起|展开图标*/
            $treelist.find('[role="expand"]').bind('click', function() {
                var $this = $(this);

                if ($this.attr('role') == "expand") {

                    $this.attr('role', 'collapse');
                    $this.removeClass('fa-caret-down').addClass('fa-caret-right');
                } else {
                    $this.attr('role', 'expand');
                    $this.removeClass('fa-caret-right').addClass('fa-caret-down');
                };

                $this.siblings('ul').slideToggle();

            });

            /*点击树结构车辆列表项新增车辆*/
            $treelist.filter('[role="car"]').bind('click', function() {
                var $this = $(this);
                var $carIco = $this.children('.fa-circle-o');

                if (!$carIco.attr('role')) {

                    $carIco.attr('role', 'selected');
                    $carIco.removeClass('text-default').addClass('text-red');

                    me.appendListToCarbox($this);
                };
            });
        },
        appendListToCarbox: function($el) {
            var me = this;
            var $treelist = me.$el.find('.tree-box li');
            var $carlist = this.$el.find('.car-box');
            $el.clone().appendTo($carlist.children('ul'));


            /*点击已新增的车辆列表*/
            $carlist.find('li').unbind('click').bind('click', function() {
                var $this = $(this);
                var _id = $(this).attr('data-id');
                var $ico = $treelist.filter('[data-id="' + _id + '"]').children('.fa-circle-o');

                $ico.removeClass('text-red').addClass('text-default');
                $ico.removeAttr('role');

                $this.remove();
            });
        },
        getCarSelected: function() {
            var me = this;
            var $carlist = me.$el.find('.car-box li');
            var _data = [];

            for (var i = 0, l = $carlist.length; i < l; i++) {
                var _id = $($carlist[i]).attr('data-id');
                _data.push(_id);
            };
            return _data.join(',');
        },
        /*渲染车辆列表*/
        renderCarBox: function() {
            var me = this;
            var $treelist = me.$el.find('.tree-box [role="selected"]').parent('li');
            var $carlist = me.$el.find('.car-box');

            $carlist.html('<ul class="list-unstyled"></ul>');

            me.appendListToCarbox($treelist);
        },
        validateArry: function(data) {
            var _flag = (data && data.length > 0);
            return _flag;
        },
        /*渲染树列表*/
        renderTreeList: function(data) {
            var me = this;
            var _organhtml = '';

            getCarData(data);
            /*====获取公司下的车辆信息====*/
            function getCarData(_sdata) {
                for (var i = 0, l = _sdata.length; i < l; i++) {
                    var _d = _sdata[i];
                    _organhtml += '<li><i role="expand" class="fa fa-caret-down text-default"></i> ' + _d.organ_name + '<ul>';

                    if (me.validateArry(_d.car)) {
                        _organhtml += me.renderCarList(_d.car);
                    };

                    if (me.validateArry(_d.group)) {
                        _organhtml += me.renderGroupList(_d.group);
                    };
                    if( me.validateArry( _d.son ) ){ //公司下有子公司  
                        getCarData( _d.son );
                    };
                    _organhtml += '</ul></li>';
                };
            };

            return _organhtml;
        },
        renderGroupList: function(data) {
            var me = this;
            var _sdata = data;
            var _grouphtml = '';

            for (var i = 0, l = _sdata.length; i < l; i++) {
                var _d = _sdata[i];

                _grouphtml += '<li><i role="expand" class="fa fa-caret-down text-default"></i> <i class="fa fa-car"></i> ' + _d.group_name +'<ul>';

                if (me.validateArry(_d.car)) {
                    _grouphtml += me.renderCarList(_d.car);
                };

                _grouphtml += '</ul></li>';
            };
            return  _grouphtml;
        },
        renderCarList: function(data) {
            var me = this;
            var _sdata = data;
            var _carhtml = '';

            for (var i = 0, l = _sdata.length; i < l; i++) {
                var _d = _sdata[i]; 
                if(_d)
                {
                    if (_d.is_click == "1") { //被选中的 
                        _carhtml += '<li role="car" data-id="' + _d.id + '"><i role="selected" class="fa fa-circle-o text-red"></i> ' + _d.device_id + ' - ' + _d.car_no + '</li>';
                    } else {
                        _carhtml += '<li role="car" data-id="' + _d.id + '"><i class="fa fa-circle-o text-default"></i> ' + _d.device_id + ' - ' +  _d.car_no + '</li>';
                    };
                }
            };
            return _carhtml;
        }
    };
    _initCarTree._init(_defaults);
    return _initCarTree;
};
