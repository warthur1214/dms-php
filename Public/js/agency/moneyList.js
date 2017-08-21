$(function() {
    var mytable;
    var buyerd = '';
    var company_branchd = '';
    var agency_full_named ='';
    var pj_status = '0';//状态
    var balance_status = '';
    var email_status ='';
    var flagNk1 = 1;
    var flagNk2 = 1;
    var flagNk3 = 1;
    $("#buyer_id").on('focus',function(){
        if($(this).val() =='' && flagNk1 == 1){
            AjaxJson("/Home/Agency/getCompanyBranchList",{buyer:''}, function(res) {

                InitAutocomplete({
                    dataSource: res.data,
                    $el: $("#buyer_id"),
                    valueKey: 'id',
                    labelKey: 'buyer'
                });
                if(res.status == 1){
                    flagNk1 = 0;
                }
            });
        }   
    });
    $("#company_branch_id").on('focus',function(){
    if($(this).val()=='' && flagNk2 == 1){
        AjaxJson("/Home/Agency/getCompanyBranchList",{company_branch:''}, function(res) {
            InitAutocomplete({
                dataSource: res.data,
                $el: $("#company_branch_id"),
                valueKey: 'id',
                labelKey: 'company_branch'
            });
            if(res.status == 1){
                flagNk2 = 0;
            }
        });        
    }
    });
    $("#agency_full_name_id").on('focus',function(){
        if($(this).val()=='' && flagNk3 == 1){
        AjaxJson("/Home/Agency/getCompanyBranchList",{agency_full_name:''}, function(res) {

            InitAutocomplete({
                dataSource: res.data,
                $el: $("#agency_full_name_id"),
                valueKey: 'id',
                labelKey: 'agency_full_name'
            });
            if(res.status == 1){
                flagNk3 = 0;
            }
        });
    }
    });

    $('#fileOut').click(function() {//导出数据
        var arr = mytable.getSelected();  
        var _href = "/Home/Excel/agencyInfoLoadExcel";
        var postdata = $.extend({}, getSearchData());
        var _searchArry = [];
        if (arr.length != 0) {
            _href = _href + '?id=' + arr;
            $(this).attr('href', _href); 
            return;
        } else {
            postdata.start_pay_date = postdata.pay_business_date.split(' - ')[0];
            postdata.end_pay_date = postdata.pay_business_date.split(' - ')[1];
            postdata.pj_status = pj_status;
            postdata.balance_status = balance_status;
            postdata.email_status = email_status;
            delete postdata['pay_business_date'];
           for (var i in postdata) {
                var _data = (i + '=' + postdata[i]);
                _searchArry.push(_data);
            }

            _href = _href + '?' + _searchArry.join('&'); 
            $(this).attr('href', _href); 
            return;
        }
    });
    var $ajaxHtml = $('<div class="modal fade in modal-ajax" style="display: block;"><i class="fa fa-refresh fa-spin"></i></div>'); 
    /*批量导入按钮*/
    $('#upload').uploadify({
        'preventCaching': false,
        'fileSizeLimit': '50MB', //上传大小限制
        'fileTypeExts': '*.xls;*.xlsx', //格式限制
        'queueSizeLimit': 1, //数量限制
        'buttonText': '批量导入',
        'successTimeout': 0,
        'swf': '/Public/plugins/uploadify/flash/uploadify.swf',
        'uploader': '/Home/MyUpload/index?import=moneyList',
        'onSelect': function() {
            $ajaxHtml.appendTo(top.document.body);
        },
        'onUploadSuccess': function(file, res, response) { //成功回调
            $ajaxHtml.remove();
            var data = JSON.parse(res);
            var success = $('.alert_page_success');
            var error = $('.alert_page_error');
            if (data.status == '1') {
                $('.alert-box>h3').text('此次导入数据的结果如下：');
                $('.box-alert-hide').parents('body').css('overflow','hidden');
                $('.box-alert-hide').show();
                $('.alert-cont').append('<p>'+data.msg+'</p>');
                $('.sure-move').on('click',function(){
                    $(this).parent().prev().html('');
                    $('.box-alert-hide').parents('body').css('overflow','auto');
                    $(this).closest('.box-alert-hide').hide();
                    mytable.refresh();
                    return;
                });
            }else{
                Alert(data.msg);
            }
            // Alert(data.msg, function() {
            //     if (data.status == '1') {
            //         mytable.refresh();
            //     };
            // });
        }
    });
    /*批量修改数据*/
    $('#batchPlay').on('click',function(){
        var arr = mytable.getSelected();
        var thisbatch = $(this);
        var _html = thisbatch.text();
        var pjStatus = thisbatch.attr('data-pj-status');
        var _data =  {vins:arr,pj_status:pjStatus}; 
        batchPlayData(arr,pjStatus,_data,_html);
    });
    function batchPlayData(arr,pjStatus,_data,_html){
        if (arr.length != 0) {
        AjaxJson('/Home/Agency/updateMultiStatus', _data, function(res) { 
                if(pjStatus == 2){
                    $('.alert-box>h3').text('此次批量核对的结果如下：');
                    $('.box-alert-hide').show();
                    $('.box-alert-hide').parents('body').css('overflow','hidden');
                    $('.alert-cont').append('<p>'+res.msg+'</p>');
                    $('.sure-move').on('click',function(){
                        $(this).parent().prev().html('');
                        $('.box-alert-hide').parents('body').css('overflow','auto');
                        $(this).closest('.box-alert-hide').hide();
                        mytable.refresh();
                        return;
                    });

                }else{
                    AlertHide( res.msg );
                }
                if( res.status == 0 ){
                    return;
                };
                mytable.refresh();
            });        
        return;
        } else {
            if(pjStatus == '1'){
                alert('请选择要打款的数据');
            }
            if(pjStatus == '2'){
                alert('请选择要核对的数据');
            }
            if(pjStatus == '3'){
                alert('请选择要开票的数据');
            }
            if(pjStatus == '4'){
                alert('请选择要结算的数据');
            }
        }
    }
    $('.nav-pills li').on('click',function(){
        var thisl = $(this);
        thisl.addClass('active').siblings().removeClass('active');
        thisl.attr('lmk','true').siblings().attr('lmk','');
        if($('[role="presentation1"]').attr('lmk') == 'true'){
            $('#batchPlay').show().attr('data-pj-status',1).text('批量已打款');
            pj_status = 0;
            $('#fileIn').show();
            $('#fileOut').hide();
            $('#upload-form').show();
        }
        if($('[role="presentation2"]').attr('lmk') == 'true'){
            $('#batchPlay').show().attr('data-pj-status',2).text('批量已核对');
            $('.radio-inp').show();
            $('#fileIn').hide();
            $('#fileOut').show();
            $('#upload-form').hide();
            $('.radio-inp').find('li').first().addClass('active').siblings().removeClass('active');
            pj_status = 1;
            balance_status = 1;
        }else{
            $('.radio-inp').hide();
            $('.radio-inp').find('li').removeClass('active');
            balance_status = '';
            email_status = '';
        }
        if($('[role="presentation3"]').attr('lmk') == 'true'){
            $('#batchPlay').show().attr('data-pj-status',3).text('批量已开票');
            pj_status = 2;
            $('#fileIn').hide();
            $('#fileOut').show();
            $('#upload-form').hide();
        }
        if($('[role="presentation4"]').attr('lmk') == 'true'){
            $('#batchPlay').show().attr('data-pj-status',4).text('批量已结算');
            pj_status = 3;
            $('#fileIn').hide();
            $('#fileOut').show();
            $('#upload-form').hide();
        }
        if($('[role="presentation5"]').attr('lmk') == 'true'){
            $('#batchPlay').hide();
            pj_status = 4;
            $('#fileIn').hide();
            $('#fileOut').show();
            $('#upload-form').hide();
        }
        $('.selectInfo').trigger('click');
    });
    $('.radios-box li').on('click',function(event){
        event.stopPropagation();
        var thiskl = $(this);
        thiskl.addClass('active').siblings().removeClass('active');
        pj_status = 1;
        if(thiskl.attr('data-radio')=='1'){
            email_status='';
            balance_status = 1;
            //console.log(121);
            //$('.selectInfo').trigger('click');
        }
        if(thiskl.attr('data-radio')=='2'){
            email_status='';
            balance_status = 0;
            $('#batchPlay').hide();
        }else{
            $('#batchPlay').show();
        }
        if(thiskl.attr('data-radio')=='3'){
            balance_status = '';
            email_status = 0;
            //$('.selectInfo').trigger('click');
        }
        $('.selectInfo').trigger('click');
    });
    loadDatatable();

    /*==========================================
     * 加载图表数据
     */
    function loadDatatable() {
        var postdata = getSearchData();

        if (mytable) {
            postdata.start_pay_date = postdata.pay_business_date.split(' - ')[0] || null;
            postdata.end_pay_date = postdata.pay_business_date.split(' - ')[1] || null;
            postdata.pj_status = pj_status;
            postdata.balance_status = balance_status;
            postdata.email_status = email_status;
            delete postdata['pay_business_date'];
            postdata = $.extend({}, postdata);
            mytable.reloadByParam( postdata );
        } else {
            mytable = InitDataTable({
                $el: $('#list'), //表格dom选择器
                url: '/Home/Agency/moneyListAjax', //表格列表数据 
                ajaxdata: '',
                checkbox: true,
                valKey: 'id',
                tableOpts: {
                    data: {
                        "company_branch": { title: "国寿省份名称" }, //不需要排序的列定义 orderable: false
                        "company_department": { title: "国寿财中支名称" },
                        "agency_full_name": { title: "一级商全称" }, //初始化表格的时候，指定列的排序规则 "aaSorting": asc | desc
                        "buyer": {title: "购车客户名"},
                        "vin": {title: "VIN码"},
                        "insurance_money": { title: "结算费用" },
                        "sales_status": { title: "有无实销" },
                        "active_status": { title: "车辆激活状态" },
                        "pay_business_date": { title: "保单收付日期" },
                    }
                }
            });
            mytable.reloadByParam( {'pj_status':0} );
        }
    }
    $('#pay_business_date').daterangepicker();
    $('.fa-calendar-times-o').click(function() {
        $('#pay_business_date').val('');
    });

    $('.selectInfo').click(function() {
        var postdata = getSearchData(); 
        postdata.start_pay_date = postdata.pay_business_date.split(' - ')[0] || null;
        postdata.end_pay_date = postdata.pay_business_date.split(' - ')[1] || null;
        postdata.pj_status = pj_status;
        postdata.balance_status = balance_status;
        postdata.email_status = email_status;
        delete postdata['pay_business_date'];
        postdata = $.extend({}, postdata);
        mytable.reloadByParam( postdata );
    });
    $('#clearSearch').click(function(){
        $(':input','#submit_form').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');
    });
}); 
