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
</head>

<body class="hold-transition sidebar-mini padding0">
    <div class="wrapper">
        <section class="content-header hide">
            <h1>
        欢迎<strong><?php echo ($display_name); ?></strong>，现在是<?php echo ($date); ?>！
      </h1>
        </section>
        <section class="content">
            <div class="row">
                <!--车辆数据-->
                <div class="col-md-6">
                    <!--box begin-->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                              <span class="text-bold">平台中相应的车辆方面数据</span> 
                            </h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <ul id="listIndexCarinfo" class="list-index-carinfo list-unstyled clearfix height300"> 
                                <li>
                                <a href="/Home/Car/carList">
                                    <div class="small-box bg-aqua">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['carNo']); ?> 辆
                                            </h3>
                                            <p>平台总车辆数</p>
                                        </div>
                                    </div>
                                </a>
                                </li> 
                                <li>
                                <a href="/Home/TravelCount/travelCount">
                                    <div class="small-box bg-maroon">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['travelNum']); ?>W km
                                            </h3>
                                            <p>平台车辆总里程</p>
                                        </div>
                                    </div>
                                </a>
                                </li> 
                                <li>
                                <a href="/Home/CostCount/costCount">
                                    <div class="small-box bg-teal">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['cost']); ?> 
                                            </h3>
                                            <p>平台车辆总费用</p>
                                        </div>
                                    </div>
                                </a>
                                </li> 
                                <li class="widthP25">
                                <a href="/Home/Car/carPlace?status=on">
                                    <div class="small-box bg-orange">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['on']); ?> 辆
                                            </h3>
                                            <p>行驶车辆</p>
                                        </div>
                                    </div>
                                </a>
                                </li>
                                <li class="widthP25">
                                <a href="/Home/Car/carPlace?status=stop">
                                    <div class="small-box bg-fuchsia">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['stop']); ?> 辆 
                                            </h3>
                                            <p>静止车辆</p>
                                        </div>
                                    </div>
                                </a>
                                </li>
                                <li class="widthP25">
                                <a href="/Home/Car/carPlace?status=off">
                                    <div class="small-box bg-purple">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['off']); ?> 辆 
                                            </h3>
                                            <p>离线车辆</p>
                                        </div>
                                    </div>
                                </a>
                                </li>
                                <li class="widthP25">
                                <a href="/Home/Car/carList?active_status=2">
                                    <div class="small-box bg-purple">
                                        <div class="inner">
                                            <h3>
                                            <?php echo ($info['wrong']); ?> 辆 
                                            </h3>
                                            <p>问题车辆</p>
                                        </div>
                                    </div>
                                </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!--box end-->
                </div>
                <!--车辆数据 end-->
                <!--驾驶评分-->
                <div class="col-md-6">
                    <!--box begin-->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                              <span class="text-bold">驾驶评分</span> 
                              <span class="badge label-index-chart">当前月</span> 
                            </h3>
                            <div class="box-tools pull-right">
                                <a href="/Home/ScoreCount/scoreCount" class="label label-primary">详情</a>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div id="pingfen" class="height300"></div>
                        </div>
                    </div>
                    <!--box end-->
                </div>
                <!--驾驶评分 end-->
            </div>
            <!--车辆费用-->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                      <span class="text-bold">车辆费用（Km）</span> 
                      <span class="badge label-index-chart">本年</span> 
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="/Home/CostCount/costCount" class="label label-success">详情</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="feiyong" class="height300"></div>
                        </div>
                        <div class="col-md-4">
                            <div id="feiyongdetail" class="height300"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--车辆费用 end-->
            <!--行驶数据-->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                      <span class="text-bold">行驶数据（Km）</span> 
                      <span class="badge label-index-chart">本月</span> 
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="/Home/TravelCount/travelCount" class="label label-warning">详情</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="shuju" class="height300"></div>
                        </div>
                        <!-- <div class="col-md-5">
                            <div id="shujudetail" class="height300"></div>
                        </div> -->
                    </div>
                </div>
            </div>
            <!--行驶数据 end-->
        </section>
    </div>
    <!-- ./wrapper -->
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


    <script src="/Public/js/public/echarts-all-3.js"></script>
    <script type="text/javascript" src="/Public/js/index/indexBarChart.js"></script>
    <script type="text/javascript" src="/Public/js/index/indexPieChart.js"></script>
    <script type="text/javascript" src="/Public/js/index/indexLineChart.js"></script>
    <script type="text/javascript" src="/Public/js/index/main.js"></script>
</body>

</html>