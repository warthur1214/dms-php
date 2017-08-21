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
      user_name:{
          required: true,
          namePRC:true
      },
      user_age:{
          required: true,
          digits:true,
          maxlength:2
      },
      user_phone:{
          required: true,
          phone:true
      },
      user_card:{
          required: true,
          minlength:6
      },
      user_email:{
          required: true,
          email:true
      },
      car_number:{
          required: true
      },
      car_type:{
          required: true
      },
      device_number:{
          required: true
      },
      organ_id:{
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
      url : "/Home/User/addUserAjax",
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
          setTimeout(function(){window.location.href = '/Home/User/userList'},2000);
        }

      }
    });
  });
});