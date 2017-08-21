$(function() {
    var form = $('#info_form');
    form.find('.form-control').not('[nowrap="nowrap"]').wrap('<div class="form-group"></div>');
    form.validate({
        errorElement: 'label', //default input error message container
        errorClass: 'text-red', // default input error message class
        focusInvalid: false, // do not focus the last invalid input
        rules: {
            car_band: {
                required: true
            },
            car_serious: {
                required: true
            },
            car_no: {
                required: true
            },
            car_buy_time: {
                required: true
            },
            car_care: {
                required: true
            },
            car_status: {
                required: true
            }
        },
        invalidHandler: function(event, validator) { //display error alert on form submit   
            $('.alert-error').show();
        },

        highlight: function(element) { // hightlight error inputs
            $(element).closest('.form-group').addClass('has-error'); // set error class to the control group
        },

        success: function(label) {
            $('.alert-error').hide();
            label.parent('.form-group').removeClass('has-error');
            label.remove();
        },

        // errorPlacement: function (error, element) {
        //     error.addClass('text-red').insertAfter(element.closest('td'));
        // },

        submitHandler: function(form) {
            $('.alert-error').hide();
            $('.alert-success').show();
        }
    });

    $('#submit').click(function() {
        if (form.valid() == false) {
            return false;
        }

        $.ajax({
            url: "/Home/Car/editCarAjax",
            type: "post",
            data: form.serialize(),
            dataType: "json",
            success: function(result) {
                $('.alert').html(result.msg);
                if (result.status == 0) {
                    $('.alert').show();
                } else {
                    $('.alert').show().removeClass('alert-error').addClass('alert-success');
                    setTimeout(function() { window.location.href = '/Home/Car/carList' }, 2000);
                }

            }
        });
    });

    InitAutoComplete({
        $el: $('#carBand'),
        url: '/Home/Public/brand',
        text: 'car_brand',
        val: 'car_brand_id',
        onSelected: function($el) {

            $('#carSerious').val('');
            carSeriousComp.setUrl('/Home/Public/getCarName/id/' + $('[name="car_band"]').val());
            carSeriousComp.load();
        }
    });

    var carSeriousComp = InitAutoComplete({
        $el: $('#carSerious'),
        url: '/Home/Public/getCarName/id/' + $('[name="car_band"]').val(),
        text: 'car_series',
        val: 'car_series_id'
    });

});

function InitAutoComplete(opts) {
    var _defaults = {
        $el: '',
        url: '',
        text: '',
        val: ''
    };
    var _opts = $.extend({}, _defaults, opts);
    var _InitAutoComplete = {
        _init: function(opts) {
            var me = this;
            for (var i in opts) {
                me[i] = opts[i];
            };
            me.$el.parents('.form-group').addClass('autocomplete-box');
            me.$el.before('<i role="autocompleteIcon" class="fa fa-caret-down"></i>');
            me.$el.after('<div class="autolist-box hide" role="list"></div>');
            me.load();
            me.bindEvent();
        },
        load: function(param) {
            var me = this;
            var _newurl = me.url;

            if (param && param != "") {
                _newurl = (_newurl + '?key=' + param);
            };

            AjaxJson(_newurl, function(res) {
                me.renderSelectList(res);
            });

        },
        renderSelectList: function(data) {
            var me = this;
            var $list = me.$el.siblings('[role="list"]');
            var _html = '';
            var _text = me.text;
            var _val = me.val;
            var _default = me.$el.val();
            var $input = $('[name="'+ me.$el.attr('data-name') +'"]');

            if (data && data.length > 0) {

                for (var i = 0, l = data.length; i < l; i++) {

                    var _d = data[i];

                    if (_default == _d[_text]) {
                        _html += '<li class="active" data-val="' + _d[_val] + '">' + _d[_text] + '</li>';
                        $input.val( _d[_val] );
                        console.info( me.$el.attr('data-name') + '------' + _d[_val] + '------' + $input.val() );
                    } else {
                        _html += '<li data-val="' + _d[_val] + '">' + _d[_text] + '</li>';
                    };

                };
                _html = ('<ul>' + _html + '</ul>');
            } else {
                _html = '<p style="padding: 10px; margin: 0;">没有检索到结果,请自行填写！</p>';
            };
            $list.html(_html);
        },
        bindEvent: function() {
            var me = this;
            var $icon = me.$el.siblings('[role="autocompleteIcon"]');
            var $list = me.$el.siblings('[role="list"]'); 
            var $input = $('[name="'+ me.$el.attr('data-name') +'"]');

            $icon.bind('click', function(e) {
                $('.autolist-box').addClass('hide');
                me.showListBox();
                e.stopPropagation();
            });
            me.$el.bind('keyup', function(e) {
                me.onKeyup(e);
            });
            $list.on('click', 'li', function(e) {
                var $this = $(this);
                var _valText = $this.text(); 
                var _val = $this.attr('data-val'); 

                $this.siblings('.active').removeClass('active');
                $this.addClass('active');

                me.$el.val( _valText );
                $input.val( _val );
                me.onSelected($this);
            });
            $(document).bind('click', function() {
                me.hideListBox();
            });
        },
        onSelected: function($el) {

        },
        onKeyup: function(e) {
            var me = this;
            me.$el.unbind('keyup');

            setTimeout(function() {
                var _val = me.$el.val();

                me.load(_val);
                me.showListBox();
                me.$el.bind('keyup', function(e) {
                    me.onKeyup(e);
                });
            }, 800);
        },
        showListBox: function() {
            this.$el.siblings('[role="list"]').removeClass('hide');
        },
        hideListBox: function() {
            this.$el.siblings('[role="list"]').addClass('hide');
        },
        setUrl: function(url) {
            this.url = url;
        }
    };
    _InitAutoComplete._init(_opts);
    return _InitAutoComplete;
};
