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
      car_type:{
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
    var user_name = $('#user_name').val();
    var user_sex = $('input[name="user_sex"]:checked').val();
    var car_type = $('#car_type').val();
    var user_id = $('#user_id').val();

    var requestData = {
            "user_name":user_name,
            "user_sex":user_sex,
            "car_type":car_type,
            "user_id":user_id
          };
    $.ajax({
      url : "/Home/User/editUserAjax",
      type : "post",
      data : requestData,
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