$(function() {

    var pointArry = [];   
    var all = [];
    var stop = [];
    var on = [];
    var off = []; 
    var carGroupNo = {}; 
    var cacheCarListData = {};
    var initPointData; //缓存原始数据转换成点坐标
    var markerData = {}; // 缓存标注对象
    var geoc; //逆地址解析对象 
    var map; //地图对象 
    
    /*=============================请求车辆数据信息 */
    $.ajax({
        url: "getCarData", 
        dataType: 'json',
        success: function(res) {      
            var obj = transferCarData( res );
            initPage( obj.data );  
            initCarList( obj.html );
        },
        error: function( err ) { 
        }
    });   

    /*验证数组存在，且值不为空*/
    function validateArry( data ){
        var _flag = ( data && data.length > 0 ); 
        return _flag;
    };
    /*========转换成车辆信息数据========*/
    function transferCarData( data ){
        var _data = [];
        var _html = '';

        getCarData( data ); 
        return {data: _data, html: _html};

        /*====获取公司下的车辆信息====*/
        function getCarData( data ){ 
            for( var i = 0, l = data.length; i < l; i++){
                var _d = data[i];   

                _html += ('<li id="org-'+ _d.organ_id +'">'+
                '<i role="expand" class="fa fa-caret-down text-default"></i>'+
                ' <input type="checkbox"><i class="fa fa-sitemap"></i> '+ _d.organ_name +'<ul id="ul-org-'+ _d.organ_id +'">'); //渲染公司html

                if( validateArry( _d.car ) ){ //公司下有车辆

                    _html += renderCarHtml( _d.car ); //渲染车辆html

                    _data = _data.concat( _d.car );
                }; 

                if( validateArry( _d.group ) ){ //公司下有车组
                    var _groupdata = getGroupCar( _d.group ); 
                    
                    _html += renderGroupHtml( _d.group ); //渲染车组html
                    _data = _data.concat( _groupdata );
                }; 

                if( validateArry( _d.son ) ){ //公司下有子公司  
                    getCarData( _d.son );
                };
                _html += '</ul></li>';
            };
        };

        /*==== 获取车组中的车辆信息 ====*/
        function getGroupCar( data ){
            var _cardata = [];

            for(var i = 0, l = data.length; i < l; i++){
                var _d = data[i];

                if( validateArry( _d.car ) ){ //公司下有车辆
                    _cardata = _cardata.concat( _d.car );
                };
            };
            return _cardata;
        }; 

        /*渲染车辆列表*/
        function renderCarHtml( data ){ 
            var _html = '';
            if( !data ){
                return _html;
            };
            
            for(var i = 0, l = data.length; i < l; i++){ 
                var _d = data[i];
                var _iconHtml = '';

                if( _d ){ 
                    switch( _d.status ){
                        case 'stop':
                        _iconHtml += '<i class="fa fa-circle-o text-red"></i> ';
                        break;
                        case 'on':
                        _iconHtml += '<i class="fa fa-circle-o text-green"></i> ';
                        break;
                        default:
                        _iconHtml += '<i class="fa fa-circle-o text-default"></i> ';
                        break;
                    };
                    var _carNo = ( _d.car_no ? _d.car_no :　_d.device_id );
                    var _deviceNo = _d.device_id;
                    _html += '<li data-no="'+ _carNo +'" data-device="'+ _deviceNo +'" id="'+ _d.id +'">'+
                    '<input type="checkbox" value="'+ _d.id +'" name="carNo">'+ 
                    _iconHtml + _carNo +'</li>';
                };
                
            };
            return _html;
        };
        /*渲染车组列表*/
        function renderGroupHtml( data ){
            var _html = '';
            for(var i = 0, l = data.length; i < l; i++){ 
                var _d = data[i];
                if( _d ){
                    _html += '<li id="org-'+ _d.organ_id +'-group-'+ _d.group_id +'">'+
                    '<i role="expand" class="fa fa-caret-down text-default"></i>'+
                    ' <input type="checkbox"><i class="fa fa-car"></i> '+ _d.group_name +
                    '<ul id="ul-org-'+ _d.organ_id +'-group-'+ _d.group_id +'">';
                    _html += renderCarHtml( _d.car ); 
                    _html += '</ul></li>';
                }; 
            };
            return _html;
        }; 
    };
    /*=============================初始化页面*/
    function initPage(res) {  
        var groupObj = {};

        for (var i = 0, l = res.length; i < l; i++) {
            var _d = res[i];
            if( !_d ){
                continue;
            };
            var _newd = $.extend( {}, _d );
            var _id = _d.id + ""; 

            all.push(_id); 
            switch (_d.status) {
                case "off":
                    off.push(_id);
                    break;
                case "on":
                    on.push(_id);
                    break;
                case "stop":
                    stop.push(_id);
                    break;
            };

            //车辆按公司分组
            var _orgid = 'org-' + _d.organ_id;
            if( !groupObj[_orgid] ){ 
                groupObj[_orgid] = [];
            };
            groupObj[_orgid].push( _id );
            
            //车辆按公司下的小组分组

            if( _d.group_id ){ 
               var _groupid = 'org-'+ _d.organ_id + '-group-' + _d.group_id; 

               if( !groupObj[ _groupid ] ){ 
                    groupObj[ _groupid ] = [];
                };
               groupObj[ _groupid ].push( _id );
            };

            _newd.speed = _d.gpsSpeed;
            _newd.carno = _d.car_no;
            pointArry.push( _newd );
            cacheCarListData[_id] = _newd;
        };  

        carGroupNo = $.extend({}, groupObj);
        carGroupNo["all"] = all;
        carGroupNo["stop"] = stop;
        carGroupNo["on"] = on;
        carGroupNo["off"] = off; 

        initMapLocation(pointArry); 

        $("#carStatusBox, #carListBox").removeClass('hide');  
    };  
    /*==========================初始化车辆列表*/
    function initCarList( html ){   
        var $el = $('#carList'); 
        $el.html( html );
        carListEvent(); 
    };
    /*==========================初始化地图*/
    function initMapLocation( data ) {
        map = new BMap.Map("container", { enableMapClick: false }); //创建地图对象  
        initPointData = initPoint(data); //将原始数据转换成点坐标 
 
        geoc = new BMap.Geocoder(); //逆地址解析 
        map.enableScrollWheelZoom(); 

        if( initPointData.length == 0 ){   
            map.centerAndZoom( new BMap.Point(116.404, 39.915), 6);  //如果没有车辆信息，直接显示地图
        }else{
            map.centerAndZoom(initPointData[0], 17); // 初始化地图   
            map.setViewport( initPointData );
        };

        initCarMarkerList( initPointData ); // 创建所有车辆标注  
        addOverlay( carGroupNo["all"] );
        initCarStatusNum(); //初始化不同状态车辆个数 

        $('#all').unbind('click').bind('click', function() {
            addOverlay( carGroupNo["all"]);
            selectAll();
        });
        $('#off').unbind('click').bind('click', function() {  
            showCarsByStatus( "off" );
        });
        $('#on').unbind('click').bind('click', function() { 
            showCarsByStatus( "on" );
        });

        $('#stop').unbind('click').bind('click', function() { 
            showCarsByStatus( "stop" );
        });  
        
        if( location.href.split('?status=')[1] ){
            showCarsByStatus( location.href.split('?status=')[1] ); 
        };
        
    };

    function showCarsByStatus( status ){

        var $li = $('#' + status ).parent('li');
        $li.siblings('.active').removeClass('active');
        $li.addClass('active');  

        map.clearOverlays(); 
        addOverlay( carGroupNo[ status ] );
        selectCarList( carGroupNo[ status ] );
    };
    /*===========================不同状态车辆个数设置=*/
    function initCarStatusNum() {
        $("#all .badge").text(carGroupNo["all"].length);
        $("#stop .badge").text(carGroupNo["stop"].length);
        $("#on .badge").text(carGroupNo["on"].length);
        $("#off .badge").text(carGroupNo["off"].length);
    };
    /*======================================初始化点坐标,返回所有点坐标集合*/
    function initPoint(data) {
        var _arry = [];

        for (var i = 0, l = data.length; i < l; i++) {
            var _d = data[i];
            var _point = new BMap.Point(_d.geolongitude, _d.geolatitude);

            _d._status = _d.status;
            _point.data = _d;
            _arry.push(_point);
        };
        return _arry;
    };

    /*======================================初始化所有车辆标注*/
    function initCarMarkerList(data) {

        for (var i = 0, l = data.length; i < l; i++) {
            initCarMarker(data[i]);
        };
    };

    /*======================================初始化点坐标对应车辆标注*/
    function initCarMarker(data) {

        var status = data.data._status;
        var _labelClass = '';
        var currentIcon = null;
        var _id = data.data.id;

        switch (status) {
            case 'off':
                //创建离线状态车辆图标
                currentIcon = new BMap.Icon("/Public/image/car_status.png", new BMap.Size(50, 30), {
                    imageOffset: new BMap.Size(0, 0),
                    anchor: new BMap.Size(20, 15)
                });
                _labelClass = 'bg-default';
                break;
            case 'on':
                //创建行驶状态车辆图标
                currentIcon = new BMap.Icon("/Public/image/car_status.png", new BMap.Size(50, 30), {
                    imageOffset: new BMap.Size(0, -30),
                    anchor: new BMap.Size(20, 15)
                });
                _labelClass = 'bg-green';
                break;
            case 'stop':
                //创建静止状态车辆图标
                currentIcon = new BMap.Icon("/Public/image/car_status.png", new BMap.Size(50, 30), {
                    imageOffset: new BMap.Size(0, -60),
                    anchor: new BMap.Size(20, 15)
                });
                _labelClass = 'bg-red';
                break;
        };
        var marker = new BMap.Marker(data, { icon: currentIcon }); // 创建标注

        /*var label = new BMap.Label(); //创建标签
        label.setStyle({
            "border": "none"
        });
        label.setContent( '<span class="badge-carno badge '+ _labelClass +'">' + data.data.carno + '</span>' );
        marker.setLabel(label); //设置标注的标签 */

        marker.addEventListener("click", function(e) { //给标注添加点击事件

            var pt = marker.getPosition(); //获取marker的位置    
            onClickMarker(pt, data.data);
        });

        markerData[_id] = marker; //缓存标注
    };

    /*======================按指定类型删除标注*/
    function removeOverlay( data ){
        var carnoArry = data; 
        if(typeof data == "string"){
            carnoArry = [];
            carnoArry.push( data );
        };

        for (var i = 0, l = carnoArry.length; i < l; i++) { 
            var _idx = carnoArry[i];
            var _currentMarker = markerData[_idx]; 

            map.removeOverlay( _currentMarker ); 
        };
    };
    /*======================按指定类型添加标注*/
    function addOverlay( data ) {   
        var carnoArry = data; 

        if(typeof data == "string"){ 
            
            var $checkbox = $("#carList :checked[value]");
            carnoArry = [];

            for(var i = 0, l = $checkbox.length; i < l; i++){
                var _val = $checkbox[i].value;
                carnoArry.push( _val );
            };  
        };

        var _newPointArry = []; 
        for (var i = 0, l = carnoArry.length; i < l; i++) {

            var _idx = carnoArry[i];
            var _currentMarker = markerData[_idx];

            _newPointArry.push( _currentMarker.point ); 
            map.addOverlay(_currentMarker);
        }; 
        map.setViewport( _newPointArry );
    };

    /*====================判断标注是否在可视区域内*/
    function isVisibleMarker(point) {

        var bound = map.getBounds(); //地图可视区域
        var _flag = bound.containsPoint(point);

        if (bound.containsPoint(point) == true) {
            alert(point.data.carno + "在可视区域内")

        } else {
            alert(point.data.carno + "不在可视区域内")
        }
        return _flag;
    };
    /*======================点击标注的回调事件*/
    function onClickMarker(pos, data) {
        var _point = data;
        var pt = pos;
        var opts = {
            width: 250, // 信息窗口宽度
            title: "" // 信息窗口标题 
        }; 
        geoc.getLocation(pt, function(rs) {
            var addComp = rs.addressComponents;
            var content = addComp.province + addComp.city + addComp.district + addComp.street + addComp.streetNumber;

            _point.addr = content;
 
            var infoWindow = new BMap.InfoWindow(template('carInfo', _point), opts); // 创建信息窗口对象  
            map.openInfoWindow(infoWindow, pt); //开启信息窗口   
        });
    };

    /*=======================勾选所有checkbox*/
    function selectCarList( checkbox ){

        $("#carList input[type='checkbox']").prop("checked", false);

        for(var i = 0, l = checkbox.length; i < l; i++){
            var $check = $('#' + checkbox[i]).children('[type="checkbox"]');
            $check.prop("checked", true); 
        };
    }; 
    /*=====================选中所有复选框*/
    function selectAll(){
        $("#carList input[type='checkbox']").prop("checked", true); //默认显示所有的节点 
    };
    /*======================车辆列表事件*/
    function carListEvent() { 
        selectAll();

        /*搜索框绑定 enter 搜索*/
        $('#car_no').keydown(function( e ){
            if( e.keyCode == 13 ){ //按下回车键
                $('#selectCar').trigger('click');
                return false;
            };  
        });
        /*点击搜索按钮*/
        $('#selectCar').bind('click', function(){
            var _carno = $('#car_no').val(); 
            var _selectStr = 'data-no';
            var $car = $("#carList").find('[data-no="'+ _carno +'"]');

            if( $car.length == 0 ){ 
                _selectStr = 'data-device';
                $car = $("#carList").find('[data-device="'+ _carno +'"]'); 
            };
            var uls = $car.parents('ul');
            var $rootul = $( uls[uls.length - 2] );  

            if($car.length > 0){  
                var _newli = $rootul.parent().clone( true );  
                var _currentli = $.extend({}, _newli);
                
                for(var i = 0, l = _currentli.find('li').length; i < l; i++){  

                    var $li = _newli.find( _currentli.find('li')[i] ); 

                    if( $li.find('['+ _selectStr +'="'+ _carno +'"]').length == 0 ){ 

                        if( $li.attr( _selectStr ) == _carno ){
                            $li.siblings('li').addClass('remove-flag');
                        }else{ 
                            _newli.find( _currentli.find('li')[i] ).addClass('remove-flag');
                        }; 
                    }; 
                };  

                _newli.find('.remove-flag').remove();
                $('#carListResult').html( _newli ).removeClass('hide'); 
                $('#carList').hide();
                $car.trigger('click');  
            }else{

                $('#car_no').val('');
                $('#car_no').focus();

                $('#carListResult').html( '' ).addClass('hide'); 
                $('#carList').show(); 
            }; 
            
        });
        /*复选框勾选操作*/
        $("#carList input[type='checkbox']").bind('click', function(e) {
            var $this = $(this);
            var uls = $this.parents("ul");
            var $rootul = $( uls [uls.length - 2] ); 
            var $parentLi = $this.parent("li"); 
            var _id = $parentLi.attr('id');
            var $node = $parentLi.children('ul');

            if ($this.is(":checked")) { //选中 

                if ($node.length > 0) { //非叶子节点 

                    $node.find('input[type="checkbox"]').prop("checked", true);
 
                    addOverlay( carGroupNo[ _id ] );

                } else { //选中的是叶子节点   

                    if( $parentLi.siblings('li').length == 0 || $parentLi.siblings('li').children('[type="checkbox"]').not(':checked').length == 0 ){
                        
                        $parentLi.parent('ul').parent('li').children('[type="checkbox"]').prop("checked", true);
                    };
                    addOverlay( _id ); 
                };

                if($rootul.find('[type="checkbox"]').not(':checked').length == 0){
                    $rootul.siblings('[type="checkbox"]').prop("checked", true);
                };
            } else { // 取消选中

                var $node = $this.parent("li").children('ul');
                if ($node.length > 0) { //非叶子节点 

                    $node.find('input[type="checkbox"]').prop("checked", false); 
                    $($node.parents('ul')[0]).parent('li').children('[type="checkbox"]').prop("checked", false);
 
                    removeOverlay( carGroupNo[ _id ] ); 
                } else { //叶子节点 

                    $this.parent("li").parent('ul').parent('li').children('[type="checkbox"]').prop("checked", false);
                   
                    removeOverlay( $this.val()  );  
                };
                $rootul.siblings('[type="checkbox"]').prop("checked", false);
            };
            e.stopPropagation();
        });

        /*点击车辆节点操作*/
        $("#carList li").not(":has(ul)").bind('click', function() {
            var $this = $(this);
            var _id = $this.attr('id');

            if (!$this.children('[type="checkbox"]').prop("checked")) {
                $this.children('[type="checkbox"]').trigger('click');
            };
            var listData = cacheCarListData[_id];
            var _point = new BMap.Point( listData.geolongitude, listData.geolatitude );

            $("#carList li").removeClass("active");
            $this.addClass('active');   

            map.setCenter( _point );  
            onClickMarker( _point, listData );

        });

        /*点击收起|展开子节点*/
        $('#carList [role="expand"]').bind('click', function() {
            var $ul = $(this).siblings("ul");
            $ul.slideToggle();
            $(this).parent('li').toggleClass("car-collapse");
        });

        /*点击收起展开车辆列表面板*/
        $('#collapseBox').bind('click', function() {

            var $icon = $(this).find('i');

            if ($(this).attr("data-expand") == "true") { //收起面板

                $("#carListBox").animate({
                    right: "-260px"
                });

                $icon.attr({
                    "class": "fa fa-angle-double-left",
                    "title": $icon.attr("data-expand")
                });
                $(this).attr("data-expand", "false");

            } else { //展开面板

                $("#carListBox").animate({
                    right: "0px"
                });

                $icon.attr({
                    "class": "fa fa-angle-double-right",
                    "title": $icon.attr("data-collapse")
                });
                $(this).attr("data-expand", "true");
            };
        });
    }; 
});
