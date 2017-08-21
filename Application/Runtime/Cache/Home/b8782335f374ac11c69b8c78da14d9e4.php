<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>

<head>
      <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>硬件管理平台</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
      <!-- Bootstrap 3.3.6 --> 
  <link rel="stylesheet" href="/Public/css/public/bootstrap.min.css"> 
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/Public/css/public/font-awesome.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/Public/css/public/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="/Public/css/public/skins/_all-skins.min.css">

  <link rel="stylesheet" href="/Public/css/public/bootstrap.min.btn.css">
  <link rel="stylesheet" href="/Public/css/style/style.css"> 
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <!-- DataTables -->
    <link rel="stylesheet" href="/Public/plugins/datatables/dataTables.bootstrap.css">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="/Public/plugins/iCheck/all.css">
    <style type="text/css">
    .alert_page_success {
        color: #468847;
        background-color: #dff0d8;
        border-color: #d6e9c6
    }
    
    .alert_page_error {
        color: #b94a48;
        background-color: #f2dede;
        border-color: #eed3d7
    }
    </style>
</head>

<body>
    <section class="content-header">
        <h1>
        添加设备 
      </h1>
    </section>
    <!-- general form elements -->
    <div class="box box-cus box-cus-form"> 
        <div class="alert alert-error" style="display:none;">
            <span>信息不能为空，请输入</span>
        </div>
        <div class="box-body">
            <form role="form" id="info_form">
                <table class="table table-bordered">
                    <colgroup>
                        <col>
                        <col width="20%">
                        <col>
                        <col width="20%">
                        <col>
                        <col width="20%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> 添加设备信息</td>
                        </tr>
                        <tr>
                            <th>设备号</th>
                            <td>
                                <input type="text" class="form-control" id="device_id" name="device_id" placeholder="设备号">
                            </td>
                            <th>设备公司</th>
                            <td>
                                <select class="form-control" id="vender_id" name="vender_id">
                                    <option value="">请选择</option>
                                    <?php if(is_array($vender)): $i = 0; $__LIST__ = $vender;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val['vender_id']); ?>">
                                            <?php echo ($val['vender_name']); ?>
                                        </option><?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </td>
                            <th>设备类型</th>
                            <td>
                                <select class="form-control" id="device_type" name="device_type">
                                </select>
                            </td>
                            <!-- <th>IMSI手机号</th>
                            <td>
                                <input type="text" class="form-control" id="card_num" name="card_num" placeholder="IMSI手机号">
                            </td>
                            <th>IMSI</th>
                            <td>
                                <input type="text" class="form-control" id="imsi" name="imsi" placeholder="IMSI">
                            </td> -->
                        </tr>
                        <tr>
                            <th>设备型号</th>
                            <td>
                                <select class="form-control" id="device_model" name="device_model">
                                </select>
                            </td>
                            <th>设备归属</th>
                            <td>
                                <input type="hidden" name="organ_id">
                                <input type="text" class="form-control" id="organ_id">   
                            </td>
                            <th></th>
                            <td> 
                            </td>
                            <!-- <th>套餐年限</th>
                            <td>
                                <input type="text" class="form-control" id="card_term" name="card_term" placeholder="套餐年限">
                            </td>
                            <th>总流量</th>
                            <td>
                                <input type="text" class="form-control" id="total_flow" name="total_flow" placeholder="总流量">
                            </td> -->
                            
                        </tr>
                        <tr> 
                        </tr>
                    </tbody>
                </table>
            </form>
        </div> 
        <div class="box-footer clearfix text-center"> 
            <button type="submit" class="btn btn-info" id="submit"><i class="fa fa-save"></i>  提交</button>
            <a href="/Home/Vehicle/vehicleList" class="btn btn-default"><i class="fa fa-arrow-left"></i> 返回</a>
        </div>
    </div>
    <!-- /.box -->
    <!-- jQuery 2.2.0 -->
<script src="/Public/plugins/jQuery/jQuery-2.2.0.min.js"></script>
<script src="/Public/plugins/jQuery/jquery-ui.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="/Public/js/public/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="/Public/js/public/app.min.js"></script>

<!-- radio checkbox 美化-->
<link href="/Public/plugins/iCheck/minimal/_all.css" rel="stylesheet">
<script src="/Public/plugins/iCheck/icheck.min.js"></script>

<!-- modal.js 封装过的提示框组件 -->
<script src="/Public/js/public/modal.js"></script>

<script src="/Public/js/public/public.js"></script>


    <!-- DataTables -->
    <script src="/Public/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/Public/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="/Public/plugins/jQuery/jquery.validate.min.js"></script> 
    <script src="/Public/js/public/carGroupPopup.js"></script>
    <script src="/Public/js/vehicle/addVehicle.js"></script>
    <script type="text/javascript">
    $(function() {
        $('input[type="radio"].flat-red').iCheck({
            radioClass: 'iradio_flat-green'
        });
        $('#vender_id').change(function() {
            $.ajax({
                url: '/Home/Vehicle/getDevice/act/type/id/' + $(this).val(),
                type: "post",
                dataType: "json",
                success: function(result) {
                    var html = "";
                    html += "<option value=''>请选择</option>";
                    for (var i = 0; i < result.length; i++) {
                        html += "<option value='" + result[i]['device_type_id'] + "'>" + result[i]['device_type_name'] + "</option>";
                    };
                    $('#device_type').html(html);
                    $('#device_model').html("<option value=''>请选择</option>");
                }
            });
        })
        $('#device_type').change(function() {
            $.ajax({
                url: '/Home/Vehicle/getDevice/act/model/id/' + $(this).val(),
                type: "post",
                dataType: "json",
                success: function(result) {
                    var html = "";
                    html += "<option value=''>请选择</option>";
                    for (var i = 0; i < result.length; i++) {
                        html += "<option value='" + result[i]['device_model_id'] + "'>" + result[i]['device_model_name'] + "</option>";
                    };
                    $('#device_model').html(html);
                }
            });
        })
    })
    </script>
</body>

</html>