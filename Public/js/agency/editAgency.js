$(function() {
    var validForm = InitValidateForm($("#info_form"));  
    

    $('#submit').bind('click', function() {

        if (validForm.validnew()) {
            var postdata = $.extend({}, validForm.serializeObject() );
            var agency_id = $("#info_form").attr('data-att');
            var _posinew = {};
            var _htmlkl= ''; 
            for(var i in postdata){
                var datanew = $('[name='+i+']').parents('td').attr('data-att');
                var datanewtr = $('[name='+i+']').text();
                if(datanew!=postdata[i]){
                    _posinew[i] = postdata[i];
                    _htmlkl += '<div>'+datanewtr+'<span>&nbsp;&nbsp;'+datanew+'</span>&nbsp;&nbsp;变更为&nbsp;&nbsp;<span>'+_posinew[i]+'</span></div>';
                }
                postdata.agency_id = agency_id;
            }
            if(!$.isEmptyObject(_posinew)){
                $('.box-alert-hide').parents('body').css('overflow','hidden');
                $('.box-alert-hide').show();
                $('.alert-cont').append(_htmlkl);
            }else{
                dataAjaxAuto(postdata);
            }
            $('.none-move').on('click',function(){
                //var $el = $(this);
                $('.box-alert-hide').parents('body').css('overflow','auto');
                $('.box-alert-hide').hide();
                $('.alert-cont').html('');
            });
            $('.sure-move').on('click',function(){
                //var $el = $(this);
                dataAjaxAuto(postdata);
            });
        }
    });
    //二次封装
    function dataAjaxAuto(opt) {
        var ajsc = {
            _init : function(opt){
                var me = this;
                $.extend(me,opt);
                AjaxJson("/Home/Agency/editAgencyAjax", opt, function( res ) {  
                    AlertHide( res.msg, function(){
                        if( res.status == '1' ){
                            HrefTo( '/Home/Agency/agencyList');
                        }
                    }); 
                });
                me.domAlert();
            },
            domAlert : function(){
                $('.box-alert-hide').parents('body').css('overflow','auto');
                $('.box-alert-hide').hide();
            }
        };
        ajsc._init(opt);
        return ajsc;
    }
}); 
