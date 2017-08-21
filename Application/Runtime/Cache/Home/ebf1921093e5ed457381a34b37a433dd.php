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
        查看设备 
      </h1>
    </section>
    <!-- general form elements -->
    <div class="box box-cus box-cus-form">  
        <div class="box-body">
            <form role="form" id="info_form">
                <table class="table table-bordered" id="tableVue">
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
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> 设备信息</td>
                        </tr>
                        <tr>
                            <th>编号</th>
                            <td>{{id}}</td>
                            <th>设备号</th>
                            <td>{{device_no}}</td>
                            <th>设备状态</th>
                            <td>{{active_status}}</td>
                        </tr>
                        <tr>
                            <th>激活时间</th>
                            <td> {{active_time}}</td>
                            <th>绑定状态</th>
                            <td> {{bind_status}} </td>
                            <th>设备公司</th>
                            <td> {{device_com}} </td>
                        </tr>
                        <tr>
                            <th>设备类型</th>
                            <td> {{ device_type }} </td>
                            <th>设备型号</th>
                            <td> {{ device_model }} </td>
                            <th>入库日期</th>
                            <td> {{ create_time }} </td>
                        </tr>
                        <tr>
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> sim卡信息</td>
                        </tr>
                        <tr>
                            <th>IMSI</th>
                            <td>{{ imsi }}</td>
                            <th>ICCID</th>
                            <td>{{ sim_iccid }}</td>
                            <th>MSISDN</th>
                            <td>{{ msisdn }}</td>
                        </tr>
                        <tr>
                            <th>套餐月份</th>
                            <td>{{ package_month }}</td>
                            <th>总流量</th>
                            <td>{{ total_flow }}</td>
                        </tr> 
                        <tr>
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> 归属信息</td>
                        </tr>
                        <tr>
                            <th>归属企业</th>
                            <td>{{ first_name }}</td>
                            <th>归属公司</th>
                            <td>{{ company_name }}</td>
                            <th>归属机构</th>
                            <td>{{ son_name }}</td>
                        </tr>
                        <tr>
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> 用户信息</td>
                        </tr>
                        <tr>
                            <th>用户手机号</th>
                            <td>{{ tel }}</td>
                            <th>用户名</th>
                            <td>{{ nickname }}</td>
                            <th>注册时间</th>
                            <td>{{ user_create_time }}</td>
                        </tr>
                        <tr>
                            <td class="title" colspan="6"><i class="fa fa fa-info-circle"></i> 车辆信息</td>
                        </tr>
                        <tr>
                            <th>车辆品牌</th>
                            <td>{{ car_band }}</td>
                            <th>车型</th>
                            <td>{{ car_serious }}</td>
                            <th>车牌号</th>
                            <td>{{ car_no }}</td> 
                        </tr>
                        <tr> 
                            <th>车架号</th>
                            <td>{{ v_code }}</td>
                            <th>发动机号</th>
                            <td>{{ e_code }}</td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div> 
        <div class="box-footer clearfix text-center">  
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

  
    <script src="/Public/js/public/vue.js"></script> 
    <script src="/Public/js/vehicle/editVehicle.js"></script> 
</body>

</html>