$(function() {
    var validForm = InitValidateForm($("#info_form"));  
    
    AjaxJson("/Home/Cost/getInfo/id/" + self.location.search.split('id=')[1], function( res ) { 
        validForm.assignForm( res );  
    });
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

        if (validForm.validnew()) {
            var postdata = $.extend({}, validForm.serializeObject() );

            delete postdata.car_no;
            delete postdata.driver_name; 

            AjaxJson("/Home/Cost/editCostAjax", postdata, function( res ) {  
                
            	AlertHide( res.msg, function(){
                    if( res.status == '1' ){
                        HrefTo( '/Home/Cost/costList');
                    };
                }); 
		    });
        };
    });
}); 
