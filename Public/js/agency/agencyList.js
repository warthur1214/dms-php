$(function() {
    var mytable;
    var buyerd = '';
    var company_branchd = '';
    var agency_full_named ='';
    var pj_status = '';//状态
    var balance_status = '';
    var email_status ='';
    var flagnum1 = 1;
    var flagnum2 = 1;
    $('#agency_full_name').on('focus',function(){
        if($(this).val()=='' && flagnum1 == 1){
            AjaxJson("/Home/Agency/getCompanyBranchList",{agency_full_name:''}, function(res) {

                InitAutocomplete({
                    dataSource: res.data,
                    $el: $("#agency_full_name"),
                    valueKey: 'id',
                    labelKey: 'agency_full_name'
                });
                if(res.status == 1){
                   flagnum1 = 0; 
                }
            });
        }
    });
    $("#balance_person").on('focus',function(){
        if($(this).val()=='' && flagnum2 == 1){
            AjaxJson("/Home/Agency/getCompanyBranchList",{balance_person:''}, function(res) {

                InitAutocomplete({
                    dataSource: res.data,
                    $el: $("#balance_person"),
                    valueKey: 'id',
                    labelKey: 'balance_person'
                });
                if(res.status == 1){
                   flagnum2 = 0; 
                }
            });
        }
    });

    var $ajaxHtml = $('<div class="modal fade in modal-ajax" style="display: block;"><i class="fa fa-refresh fa-spin"></i></div>'); 
    /*批量导入数据*/
    $('#upload').uploadify({
        'preventCaching': false,
        'fileSizeLimit': '50MB', //上传大小限制
        'fileTypeExts': '*.xls;*.xlsx', //格式限制
        'queueSizeLimit': 1, //数量限制
        'buttonText': '批量导入',
        'swf': '/Public/plugins/uploadify/flash/uploadify.swf',
        'uploader': '/Home/MyUpload/index?import=agencyList',
        'onSelect': function(){
            $ajaxHtml.appendTo( top.document.body ); 
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
            //         if (data.status == '1') {
            //             mytable.refresh();
            //         };
            //     });
        }
    });
    /*=================================================
     * 修改记录
     */
    $('body').on('click','.editoBtn',function(){
        var _data = JSON.parse($(this).attr('data-obj'));
        HrefTo( '/Home/Agency/editAgency?id=' + _data.id ); 
    });



    loadDatatable();

    /*==========================================
     * 加载图表数据
     */
    function loadDatatable() {
        var postdata = getSearchData();

        if (mytable) {
            mytable.reloadByParam(postdata);
        } else {
            mytable = InitDataTable({
                $el: $('#list'), //表格dom选择器
                url: '/Home/Agency/agencyListAjax', //表格列表数据 
                ajaxdata: {},
                checkbox: false,
                valKey: 'id',
                tableOpts: {
                    data: {
                        "agency_full_name": { title: "一级商全称" }, //不需要排序的列定义 orderable: false
                        "balance_person": { title: "结算联系人" },
                        "telephone": { title: "联系人电话" }, //不需要排序的列定义 orderable: false
                        "email": { title: "邮箱" },
                        "bank_name": { title: "经销商开户行" }, //不需要排序的列定义 orderable: false
                        "bank_account": { title: "银行账户" },
                    },
                    operate: {
                        "title": '操作', //自定义操作列 
                        render: function(data, type, row, meta) {

                            var _data = JSON.stringify(row);
                            var _btnfh = ('<span data-obj=' + _data + ' class="btn btn-xs btn-default editoBtn">编辑</span>');
                            // var _text = (_btnfh + ' <span data-obj=' + _data + ' onclick="deleteRecord( this )" class="btn btn-xs btn-success">删除</span> ');

                            return _btnfh;
                        }
                    }
                }
            });
        }
    }
    // $('#pay_business_date').daterangepicker();
    // $('.fa-calendar-times-o').click(function() {
    //     $('#pay_business_date').val('');
    // });

    $('.selectInfo').click(function() {
        var postdata = getSearchData(); 
        postdata = $.extend({}, postdata);
        mytable.reloadByParam( postdata );
    });
    $('#clearSearch').click(function(){//清楚索引
        $('#submit_form')[0].reset();
    });
}); 
