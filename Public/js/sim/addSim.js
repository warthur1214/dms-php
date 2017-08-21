$(function() {
    var validForm = InitValidateForm($("#info_form")); 

    $('#submit').click(function() {
        if (validForm.validnew()) { 
            var postdata = validForm.serializeObject(); 

            AjaxJson("/Home/SIM/addSIMAjax", postdata, function( res ){   
                    
                    AlertHide( res.msg , function(){
                        if( res.status == "1"){
                            HrefTo('/Home/SIM/simList');
                        }; 
                    });
            }); 
             
        }; 
    });
});
