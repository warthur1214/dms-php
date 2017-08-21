var myTree = InitCarTree(); //选择车辆 
var myCartable = InitCarTable({ $el: $('#myTablePopup') });

$(function() {
    var validForm = InitValidateForm($("#info_form"));
    var drawmanage = drawingManagerCus($('#address').val(), 'map');
    drawmanage.drawingManager.close();

    $('#AreaStyle').bind('change', function() {
        var _val = $(this).val();

        if( _val == "" ){
            drawmanage.clearAll();
            drawmanage.drawingManager.close();
            return;
        };
        if (_val == "boundary") {
            $('#boundary').attr("type", "text");
            drawmanage.clearAllOverlay();
            return;
        } else {
            $('#boundary').attr("type", "hidden").val('');
        };
        drawmanage.clearAll();
        drawmanage.setDrawingMode(_val);
    });

    /*地址名称表单输入*/
    $('#address').bind('blur', function() {

        drawmanage.setCenter($(this).val());

    }).bind('keyup', function(e) {

        if (e.keyCode == '13') {
            $(this).trigger('blur');
        };
    });

    /*行政区域表单输入*/
    $('#boundary').bind('blur', function() {

        drawmanage.drawingBoundary($(this).val());

    }).bind('keyup', function(e) {

        if (e.keyCode == '13') {
            $(this).trigger('blur');
        };
    });
    //序列化checkbox
    function serializeBox(){
        var obj = $("#checkWeek :checked");
        check_val = [];
        for(k in obj){
            if(obj[k].checked)
                check_val.push(obj[k].value);
        }
        var dataStr = '';
        dataStr = check_val.join(',');
        $("#hiddenData").val(dataStr);
    }
    $('#submit').bind('click', function() {
        $('#area_val').val( drawmanage.getDrawingData() );  
        $('#carStr').val( myCartable.getTableIds() );
        serializeBox();
        if (validForm.validnew()) { 
            var postdata = validForm.serializeObject();

            if( postdata.area_val == "" ){
                Alert('请选择区域');
                return;
            };
            
            AjaxJson("/Home/Fence/addFenceAjax", postdata, function( res ){  
                
                    
                    AlertHide( res.msg , function(){
                        if( res.status == "1"){
                            HrefTo('/Home/Fence/fenceList');
                        }; 
                    });
            });
            //
        };
    });

    //点击添加车辆按钮
    $('#addCar').bind('click', function() {  
        myTree.load('/Home/Fence/getCar', function(res) {
            myCartable.setRemoveFlag(true); 
            myCartable.load('/Home/Fence/getCarById', { "carStr": res });
        });
    });

    //日期初始化
    $('#open_time').datepicker({ //开启日期  
        onSelect: function() {
            var $this = $(this);
            var $nextDate = $('#end_time');
            var _val = $this.val();

            $nextDate.datepicker("option", "minDate", _val);
        }
    });
    $('#end_time').datepicker({ //结束日期  
        onSelect: function() {
            var $this = $(this);
            var _val = $this.val();
            var $prevDate = $('#open_time');

            $prevDate.datepicker("option", "maxDate", _val);
        }
    });
    $('#work_stime').timepicker({
        showSecond: true, //显示秒
        timeFormat: "hh:mm:ss" //格式化时间  
    }); 
    $('#work_etime').timepicker({
        showSecond: true, //显示秒
        timeFormat: "hh:mm:ss" //格式化时间  
    }); 
});

function deleteRecordInitCarTable(el) {
    myCartable.removeItem(el);
};
