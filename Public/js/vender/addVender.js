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
      vender_name:
      {
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
      url : "/Home/Vender/addVenderAjax",
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
          setTimeout(function(){window.location.href = '/Home/Vender/venderList'},2000);
        }

      }
    });
  });
});