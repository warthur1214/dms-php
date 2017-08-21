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
        <span class="pull-right">
        <a href="/Home/Device" class="btn btn-sm btn-info"> <i class="fa fa-plus"></i> 添加硬件</a>
      </span>
        <h1>
        企业硬件列表 
      </h1>
    </section>
    <div class="box marginB0">
        <div class="box-body">
            <div class="alert alert_page_success" style="display:none;">
            </div>
            <div class="alert alert_page_error" style="display:none;">
            </div>
            <table id="example1" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>设备编号</th>
                        <th>硬件厂家</th>
                        <th>设备类型</th>
                        <th>设备型号</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
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
     <link rel="stylesheet" href="/Public/plugins/datatables/dataTables.bootstrap.css"> 
 <script src="/Public/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="/Public/js/public/dataTableCus.js"></script>  
    
    <script src="/Public/js/device/deviceList.js"></script>
</body>

</html>