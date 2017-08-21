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
    <link rel="stylesheet" href="/Public/css/style/style.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="/Public/plugins/datatables/dataTables.bootstrap.css">
    <!-- daterange picker -->
    <link rel="stylesheet" href="/Public/plugins/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" type="text/css" href="/Public/plugins/uploadify/uploadify.css" />
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
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <style>
  </style>
</head>

<body>
    <section class="content-header">
        <span class="pull-right">
            <a href="/Home/Vehicle" class="btn btn-sm btn-info"> <i class="fa fa-plus"></i> 添加设备</a>
        </span>
        <h1>
        设备列表 
      </h1>
    </section>
    <div class="box">
        <form class="form-horizontal padding10" role="form" id="submit_form">
            <div class="form-group">
                <label for="device_id" class="col-sm-1 control-label">设备号</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="device_id" name="device_id" placeholder="设备号">
                </div>
                <label for="VIN" class="col-sm-1 control-label">VIN码</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="vin" name="vin" placeholder="VIN">
                </div>
                <label class="col-sm-1 control-label">设备归属</label>
                <div class="col-sm-2">
                    <input type="hidden" name="organ_id">
                    <input type="text" class="form-control" id="organ_id">  
                </div>
                <!-- <label for="card_num" class="col-sm-1 control-label">IMSI号</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="card_num" name="card_num" placeholder="IMSI手机号">
                </div> -->
            </div>
            <div class="form-group">
                <label for="active_status" class="col-sm-1 control-label">设备状态</label>
                <div class="col-sm-2">
                    <select class="form-control" id="active_status" name="active_status">
                        <option value="">请选择</option>
                        <option value="0">未激活</option>
                        <option value="1">已激活</option>
                        <option value="2">已损坏</option>
                    </select>
                </div>
                <label for="is_use" class="col-sm-1 control-label">绑定状态</label>
                <div class="col-sm-2">
                    <select class="form-control" id="is_use" name="is_use">
                        <option value="-1">请选择</option>
                        <option value="-2">未绑定</option>
                        <option value="1">已绑定手机</option>
                        <option value="2">已绑定车辆</option>
                        <option value="3">已绑定车辆&手机</option>
                    </select>
                </div>
                <label for="" class="col-sm-1 control-label">设备公司</label>
                <div class="col-sm-2">
                    <select class="form-control" id="vender_id" name="vender_id">
                        <option value="">请选择</option>
                        <?php if(is_array($vender)): $i = 0; $__LIST__ = $vender;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val['vender_id']); ?>">
                                <?php echo ($val['vender_name']); ?>
                            </option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div> 
            </div>
            <div class="form-group">
                
                <label for="device_type" class="col-sm-1 control-label">设备类型</label>
                <div class="col-sm-2">
                    <select class="form-control" id="device_type" name="device_type">
                    </select>
                </div>
                <label for="active_time" class="col-sm-1 control-label">激活时间</label>
                <div class="col-sm-3">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar-times-o"></i>
                        </div>
                        <input type="text" class="form-control pull-right" id="active_time" name="active_time" placeholder="激活时间区间">
                    </div>
                </div>
                <!-- <label for="device_model" class="col-sm-1 control-label">设备型号</label>
                <div class="col-sm-2">
                    <select class="form-control" id="device_model" name="device_model">
                    </select>
                </div> -->
                <div class="text-center">
                    <a href="javascript:;" class="btn btn-sm btn-info selectInfo"><i class="fa fa-search"></i> 搜索</a>
                </div>
            </div>
        </form>
        <div class="box-body with-borderT">
            <!-- alert start -->
            <div class="clearfix">
                <div class="alert alert_page_success" style="display:none;">
                </div>
                <div class="alert alert_page_error" style="display:none;">
                </div>
            </div>
            <div class="clearfix">
                <div id="upload-form" class="pull-left" style="margin: 0 5px 0 0;">
                    <div id="queue"></div>
                    <input type="file" id="upload" name="upload" class="upload_class">
                </div>
                <div class="pull-left">
                    <span id="downModel" class="btn btn-sm btn-info"><i class="fa fa-file-excel-o"></i> 设备信息模板 </span>
                    <!-- <a id="btnExport" class="btn btn-sm btn-info"><i class="fa fa-external-link"></i> 导出数据 </a > -->
                </div> 
            </div>
            <table id="list" class="table table-bordered table-hover table-box-cus">
                 
            </table>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix page">
        </div>
    </div>
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


<script src="/Public/js/public/dataTableCusV2.js"></script> 

<script src="/Public/plugins/uploadify/jquery.uploadify.min.js"></script>
<script src="/Public/plugins/daterangepicker/moment.js"></script>
<script src="/Public/plugins/daterangepicker/daterangepicker-dob.js"></script>
<script src="/Public/layer/layer.js"></script>
<script src="/Public/js/public/carGroupPopup.js"></script>
<script src="/Public/js/vehicle/vehicleList.js"></script>
    <script>
        /*导出信息模板*/
        $("#downModel").on('click',function () {
            alertMsg = {
                width: 250,
                title: '提示信息'
            };
            Confirm('批量导入时，请将Excel格式另存为97-2003兼容版本',alertMsg,function (res) {
                if(res){
                    window.location.href = '/Public/file/device.xlsx';
                }
            });
        });
    </script>
</body>

</html>