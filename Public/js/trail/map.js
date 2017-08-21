$(function() {
    var pointArry = [];

    /*获取纬度值*/
    var geolatitude = $('.geolatitude');
    /*获取经度值*/
    var geolongitude = $('.geolongitude');
    /*获取速度值*/
    var speed = $('.speed');
    /*获取定位时间*/
    var createtime = $('.createtime');
    /*获取方位*/
    var gpsorientation = $('.gpsorientation');

    geolatitude.each(function(i)
    {
        var position = gpsorientation.eq(i).html();
        var pos = "";
        if(position % 90 == 0)
        {
            switch(position)
            {
                case 0:
                    pos = "正北";
                    break;
                case 90:
                    pos = "正东";
                    break;
                case 180:
                    pos = "正南";
                    break;
                default:
                    pos = "正西";
                    break; 
            }                      
        }
        else
        {
            if(position < 90)
            {
                pos = "东北";
            }
            else if(position < 180)
            {
                pos = "东南";
            }
            else if(position < 270)
            {
                pos = "西南";
            }
            else
            {
                pos = "西北";
            }

        };
        pointArry.push({
            geolatitude:$(this).html(),
            geolongitude:geolongitude.eq(i).html(),
            speed:speed.eq(i).html(),
            createtime:createtime.eq(i).html(),
            pos:pos,
        });
    })

    /*防止初始化的时候地图组件没有加载报错*/
    var myInterval = setInterval(function() {
        if (BMap) {
            initHistoryPath();
            clearInterval(myInterval);
        };
    }, 1000);

    function initHistoryPath() {
        var map = new BMap.Map('container');
        var geoc = new BMap.Geocoder();
        var LineColor = ["red", "blue", "green"]; //轨迹线条颜色
        var pointData = initPoint(pointArry);
        var lushu;
 
        //绑定事件
        $("#run").bind('click',function() { 

            $("#run").find('.dropdown-menu').show(); 

            startLushu();
        });

        $("#run li").bind('click', function( e ){
            var $this = $(this);

            $("#run input").val( $this.attr('data-value') );
            $this.siblings('.active').removeClass('active');
            $this.addClass('active');
            $(this).parent('.dropdown-menu').hide();

            
            startLushu();
            e.stopPropagation();
        });
        $("#pause").bind('click',function() { 
            lushu.pause();
        }); 
        $("#stop").bind('click',function() { 
            lushu.stop();
        });  

        initMap(pointArry); //实例化地图

        /*=========================================开始运动轨迹*/
        function startLushu(){
            var _speed = parseInt($("#run input").val()) * 500; 
            lushu._opts.speed = _speed;
            lushu.start();
        };
        /*========================================初始化地图*/
        function initMap(data) {
            map.enableScrollWheelZoom();


            map.centerAndZoom(pointData[0], 15);

            initPolyLineGroup(data);
            initLushu(pointData, 500);

            var beginIcon = new BMap.Icon("/Public/image/dest_markers.png", new BMap.Size(30, 35), {
                imageOffset: new BMap.Size(0, 0),
                anchor: new BMap.Size(15, 35)
            });
            var endIcon = new BMap.Icon("/Public/image/dest_markers.png", new BMap.Size(45, 35), {
                imageOffset: new BMap.Size(0, -30),
                anchor: new BMap.Size(15, 35)
            });
            setPointIcon(pointData[0], beginIcon);
            setPointIcon(pointData[pointData.length - 1], endIcon);
        };

        /*======================================初始化点坐标,返回所有点坐标集合*/
        function initPoint(data) {
            var _arry = [];

            for (var i = 0, l = data.length; i < l; i++) {
                var _d = data[i];
                var _point = new BMap.Point(_d.geolongitude, _d.geolatitude);

                _arry.push(_point);
            };
            return _arry;
        };

        /*===================================初始化点坐标之间的连线*/
        function initPolyLineGroup(data) {   

            for (var i = 1, l = data.length; i < l; i++) {
                var _d = data[i];
                var _dPrev = data[i - 1];

                initPolyLine(_d, _dPrev);  
            };
        };
        
        /*======================初始化折线*/
        function initPolyLine( data, prevdata ){
            var _d = data;
            var _dPrev = prevdata;
            var _lineColor = LineColor[1];  
            var _point = new BMap.Point(_d.geolongitude, _d.geolatitude);
            var _pointPrev = new BMap.Point(_dPrev.geolongitude, _dPrev.geolatitude); 

            if (_d.speed <= 10) {
                _lineColor = LineColor[0];
            } else if (_d.speed >= 30) {
                _lineColor = LineColor[2];
            } else {
                _lineColor = LineColor[1];
            };

            //创建折线
            var polyline = new BMap.Polyline([
                _pointPrev, _point
            ], {
                strokeColor: _lineColor,
                strokeWeight: 5,
                strokeOpacity: 0.8
            });

            polyline.addEventListener("click", function( e ){ 
                var pt = e.point;  //获取marker的位置  

                onClickPolyLine( pt, _d ); 
                 
            }); 
            map.addOverlay(polyline); //增加折线  
        };
        /*======================点击折线的回调事件*/
        function onClickPolyLine(pos, data) {
            var _point = data;
            var pt = pos;
            var opts = {
                width: 250, // 信息窗口宽度
                title: "" // 信息窗口标题 
            }; 
            geoc.getLocation(pt, function(rs){
                var addComp = rs.addressComponents;
                console.log(addComp);
                var content = addComp.province + addComp.city + addComp.district + addComp.street + addComp.streetNumber;
                
                _point.addr = content;

                var infoWindow = new BMap.InfoWindow( template('carInfo', _point ), opts);  // 创建信息窗口对象 
                map.openInfoWindow( infoWindow, pt ); //开启信息窗口 
            });
        };

        /*====================================================初始化行车轨迹*/
        function initLushu(arrPois, speed) {
            map.setViewport(arrPois);
            lushu = new BMapLib.LuShu(map, arrPois, {
                autoView: true, //是否开启自动视野调整，如果开启那么路书在运动过程中会根据视野自动调整
                icon: new BMap.Icon('http://developer.baidu.com/map/jsdemo/img/car.png', new BMap.Size(52, 26), {
                    anchor: new BMap.Size(27, 13)
                }),
                speed: speed,
                enableRotation: true, //是否设置marker随着道路的走向进行旋转
                landmarkPois: []
            });
        };

        /*======================================================设置坐标点的图标*/
        function setPointIcon(point, myIcon) {
            var pt = point;
            var marker = new BMap.Marker(pt, {
                icon: myIcon
            }); // 创建标注
            map.addOverlay(marker); // 将标注添加到地图中
        };
    };
})
