$(function() 
{
	var form = $('#info_form');
  form.find('.form-control').not('[nowrap="nowrap"]').wrap('<div class="form-group"></div>'); 
  form.validate({
    errorElement: 'label', //default input error message container
    errorClass: 'text-red', // default input error message class
    focusInvalid: false, // do not focus the last invalid input
    rules:
    {
      driver_name:{
          required: true
      },
      driver_phone:{
          required: true
      },
      driver_id_card:{
          required: true,
          IDcard:true
      },
      driver_address:{
          required: true
      },
      work_time:{
          required: true
      },
      drive_card:{
          required: true
      },
      organ_id:{
          required: true
      },
      drive_year:{
          required: true,
          digits:true
      },
      card_use_time:{
          required: true
      },
      drive_type:{
          required: true
      },
      drive_card_location:{
          required: true
      }
    },
    invalidHandler: function (event, validator) { //display error alert on form submit   
        $('.alert-error').show();
    },

    highlight: function (element) { // hightlight error inputs
        $(element).closest('.form-group').addClass('has-error'); // set error class to the control group
    },

    success: function (label) {
        $('.alert-error').hide();
        label.prev('.form-group').removeClass('has-error');
        label.remove();
    },

    errorPlacement: function (error, element) {
        error.addClass('text-red').insertAfter(element.closest('.form-group'));
    },

    submitHandler: function (form) {
      $('.alert-error').hide();
      $('.alert-success').show();
    }
  });

  $('#submit').click(function()
  {
    if (form.valid() == false)
    {
      return false;
    }
    $.ajax({
      url : "/Home/Driver/editDriverAjax",
      type : "post",
      data : form.serialize(),
      dataType :"json",
      success: function(result)
      {
        $('.alert').html(result.msg);
        if(result.status == 0)
        {
          $('.alert').show();
        }
        else
        {
          $('.alert').show().removeClass('alert-error').addClass('alert-success');
          setTimeout(function(){window.location.href = '/Home/Driver/driverList'},2000);
        }

      }
    });
  });

});


CarGroupPopup({
    $textEl: $('#organ_id'),
    $valueEl: $('[name="organ_id"]'),
    showGroup: false,
    url: '/Home/Public/organTree'
});

