$(function() {
    var validForm = InitValidateForm($("#info_form"));

    AjaxJson("/Home/Cost/getCarNo", function(res) {

        InitAutocomplete({
            dataSource: res.data,
            $el: $("#car_id"),
            valueKey: 'car_id',
            labelKey: 'car_no'
        });
    });


    AjaxJson("/Home/Cost/getDriver", function(res) {

        InitAutocomplete({
            dataSource: res.data,
            $el: $("#driver_id"),
            valueKey: 'driver_id',
            labelKey: 'driver_name'
        });
    });

    $('#submit').bind('click', function() {

        //解决司机名称不校验的问题
        // $("#driver_id").trigger('focus');
        // setTimeout(function() {
        //     $("#driver_id").trigger('blur')
        // }, 0);
        var express = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
        var _val = $.trim($('#car_id').val());
        var _flag = express.test(_val);
        if(_flag){
        }else{
            $('#car_id').parents('.form-group').append('<label for="car_id" class="text-red">请输入正确的格式</label>');
            return;
        }
        if (validForm.validnew()) {
            var postdata = validForm.serializeObject();

            AjaxJson("/Home/Cost/addCostAjax", postdata, function(res) {
                
                AlertHide(res.msg, function() {
                    if (res.status == '1') {
                        HrefTo('/Home/Cost/costList');
                    };
                });

            });
        };
    });
});
