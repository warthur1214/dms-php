var mytable;
var myTree = InitCarTree(); //选择车辆
var myCartable = InitCarTable({ $el: $('#myTablePopup') });

$(function() {
    mytable = InitDataTable({
        $el: $('#myTable'), //表格dom选择器
        url: "/Home/Fence/fenceListAjax", //表格列表数据 
        ajaxdata: {},
        tableOpts: {
            data: {
                "fence_id": { title: "cost_id", visible: false }, //不需要显示的列定义visible： false
                "fence_name": { title: "围栏名称" }, //不需要排序的列定义 orderable: false
                "work_term": { title: "报警条件" },
                "open_time": { title: "开启日期" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                "end_time": { title: "结束日期" },
                "work_day": { title: "星期" },
                "work_stime": {
                    title: "每天时间段",
                    render: function(data, type, row, meta) {
                        return row.work_stime + ' — ' + row.work_etime;
                    }
                },
                "sendee_con": {
                    title: "报警人数",
                    render: function(data, type, row, meta) {
                        var _data = JSON.stringify(row);
                        var _text = '<span data-obj=' + _data + ' class="text-blue" onclick="showSendeePhone( this )">' + data + '</span>';
                        return _text;
                    }
                },
                "is_use": {
                    title: "围栏状态",
                    render: function(data, type, row, meta) {
                        var _data = JSON.stringify(row);
                        var _textoption = '';
                        var _text = '';
                        var readonlyhtml = '';

                        if (data == 1) {
                            _textoption = '<option value="1" selected="selected">有效</option><option value="0">无效</option>';
                        } else {
                            readonlyhtml = 'disabled="disabled"';
                            _textoption = '<option value="1">有效</option><option value="0" selected="selected">无效</option>';
                        };
                        _text = '<select '+ readonlyhtml +' data-obj=' + _data + ' onchange="changeStatus( this )">' + _textoption + '</select>';
                        return _text;
                    }
                },
                "car_con": {
                    title: "已添加车辆",
                    render: function(data, type, row, meta) {
                        var _data = JSON.stringify(row);
                        var _text = '<span data-obj=' + _data + ' class="text-blue" onclick="showCarInfo( this )">' + data + '</span>';
                        return _text;
                    }
                }
            },
            operate: {
                "title": '操作', //自定义操作列 
                render: function(data, type, row, meta) {

                    var _data = JSON.stringify( row );
                    var _btnfh = ('<span data-obj=' + _data + ' onclick="manageCar( this )" class="btn btn-xs btn-default">增减车辆</span>');
                    var _text = (_btnfh + ' <a href="/Home/Fence/editFence?id='+ row.fence_id +'" class="btn btn-xs btn-success">修改</span> ');

                    return _text;
                }
            }
        }
    });

    $('#closeFencetable').bind('click', function(){ 
       myCartable.hide(); 
    });

    $('#searchBtn').bind('click', function(){
        var _data = getSearchData(); 
        mytable.reloadByParam( _data );
    });
});

/**=======================================================
 * 展示报警人信息
 */
function showSendeePhone( el ) {
    var _val = JSON.parse( $(el).attr('data-obj') ).sendee_phone;
    Alert( _val ); 
};

/**=======================================================
 * 展示已添加车辆信息
 */
function showCarInfo( el ) { 
    var _data = JSON.parse( $(el).attr('data-obj') );  
    myCartable.load( '/Home/Fence/getCarById/id/' + _data.fence_id );
};

/**============================= ==========================
 * 修改围栏状态
 */
function changeStatus( el ) { 
    var _data = JSON.parse( $(el).attr('data-obj') );
    var _val = $(el).val();

    AjaxJson('/Home/Fence/editStatus/id/' + _data.fence_id, {"is_use": _val}, function( res ){
        AlertHide( res.msg );

        if( res.status == "1"){ 
            $(el).prop('disabled', 'disabled');

        }else{ 
        };
    }); 
};

/**============================= ==========================
 * 增减车辆
 */
function manageCar( el ){
    var _data = JSON.parse( $(el).attr('data-obj') );
    myTree.load( '/Home/Fence/getCar/id/' + _data.fence_id, function( res ){

        var postdata = {
            "fence_id": _data.fence_id,
            "carStr": res,
            "listAdd": "1"
        };

        AjaxJson('/Home/Fence/addCar', postdata, function( data ){
            AlertHide( data.msg );
            if( data.status == "1"){
                mytable.refresh();
            }; 
        }); 
    }); 
};
