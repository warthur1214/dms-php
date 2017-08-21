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
        添加企业硬件
      </h1>
    </section>
    <!-- general form elements -->
    <div class="box box-cus box-cus-form">
        <div class="alert alert-error" style="display:none;">
            <span>信息不能为空，请输入</span>
        </div>
        <div class="box-body">
            <form id="info_form">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="title" colspan="4"><i class="fa fa fa-info-circle"></i> 企业硬件信息</td>
                        </tr>
                        <tr>
                            <th>硬件厂家</th>
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
                                <input type="text" class="form-control" name="device_type_name" id="device_type_name" placeholder="设备类型">
                            </td>
                        </tr>
                        <tr>
                            <th>设备型号</th>
                            <td colspan="3">
                                <div class="device_info">
                                    <div class="icon-box"><a href="javascript:;" class="add btn btn-info btn-xs">[+]</a></div> 
                                    <input type="text" class="form-control" name="device_model_name[]" placeholder="设备型号">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div> 
        <div class="box-footer clearfix text-center">
            <button type="button" class="btn btn-info" id="submit"><i class="fa fa-save"></i>  提交</button>
            <a href="/Home/Device/deviceList" class="btn btn-default" id="back"><i class="fa fa-arrow-left"></i>返回</a>
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
    <script src="/Public/plugins/jQuery/additional-methods.min.js"></script>
    <script src="/Public/js/device/addDevice.js"></script>
    <script type="text/javascript">
    $(function() {
        $('.add').click(function() {
            var parent = $(this).parents('.device_info');
            var html = parent.prop('outerHTML');
            var html = html.replace('add', 'remove').replace('新增', '').replace('[+]', '[-]');
            parent.after(html);
        })
        $('#info_form').on('click', '.remove', function() {
            $(this).parents('.device_info').remove();
        })
    })
    </script>
</body>

</html>