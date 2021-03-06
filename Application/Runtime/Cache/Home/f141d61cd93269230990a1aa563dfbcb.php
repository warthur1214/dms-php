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
    <link rel="stylesheet" href="/Public/plugins/select2/select2_metro.css">
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
        .uploadify{
            background: #ff5500;
            color:#fff;
            border-radius:4px;
            position: relative;
        }
        #SWFUpload_0{
            left:0;
        }
        #upload-form {
            padding: 5px 0;
            background: transparent;
            border: none;
        }
        #upload-form{
            padding:5px 0;
        }
        #cooperateWrap{
            position: relative;
        }
        #sonWrapper{
            position: absolute;
            top:34px;
            height:auto;
            width:100%;
            background: #fff;
            overflow-y: auto;
            padding:0;
            margin:0;
            display: none;
            z-index: 900;
        }
        #sonWrapper li:hover{

            cursor: pointer;
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

            <a href="addSIM" class="btn btn-group btn-sm btn-info"> <i class="fa fa-plus"></i> 添加sim</a>
      </span>

    <h1>
        sim卡列表
    </h1>
</section>
<div class="box">
    <form class="form-horizontal paddingTB10 selectForm" id="submit_form" style="overflow: visible;">
        <div class="form-group">
            <label for="simName" class="col-sm-1 control-label">IMSI号</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" placeholder="请输入IMSI号" name="imsi" id="simName" />
            </div>
            <label for="sim_iccid" class="col-sm-1 control-label">ICCID号</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" placeholder="请输入ICCID号" name="sim_iccid" id="sim_iccid" />
            </div>
            <label for="device_id" class="col-sm-1 control-label">设备号</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" placeholder="请输入设备号" name="device_id" id="device_id" />
            </div>
            <label for="tel" class="col-sm-1 control-label">用户手机号</label>
            <div class="col-sm-2">
                <input type="text" class="form-control" placeholder="请输入手机号" name="tel" id="tel" />
            </div>
        </div>
        <div class="form-group">
            <label for="cooperateSort" class="col-sm-1 control-label">绑定状态</label>
            <div class="col-sm-2">
                <select class="form-control" id="cooperateSort" name="bind_status">
                    <option value="">全部</option>
                    <option value="1">已绑定</option>
                    <option value="0">未绑定</option>
                </select>
            </div>
            <label class="col-sm-1 control-label">SIM卡归属</label>
            <div class="col-sm-2" id="cooperateWrap">
                <select type="text" class="form-control" id="chooseOrgan" name="organ_id" >
                    <option id="selectOrgan" style="display:none;height: 0;" value="">全部</option>
                </select>
                <ul id="sonWrapper"></ul>
            </div>
            <label for="uni_real_name" class="col-sm-1 control-label">实名制认证状态</label>
            <div class="col-sm-2">
                <select class="form-control" id="uni_real_name" name="uni_real_name">
                    <option value="">全部</option>
                    <option value="4">未申请</option>
                    <option value="0">未认证</option>
                    <option value="1">认证中</option>
                    <option value="2">已通过</option>
                    <option value="3">未通过</option>
                </select>
            </div>
            <label for="sim_vender" class="col-sm-1 control-label">应用硬件厂家</label>
            <div class="col-sm-2">
                <select class="form-control" id="sim_vender" name="sim_vender">
                    <option value="">全部</option>
                    <?php if(is_array($vender)): $i = 0; $__LIST__ = $vender;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val['vender_id']); ?>"><?php echo ($val['vender_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </div>
        </div>
        <div class="form-group text-center">
                <a href="javascript:;" id="searchBtn" class="btn  btn-sm btn-info"><i class="fa fa-search"></i> 搜索</a>
        </div>

    </form>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- alert start -->
        <div class="alert alert_page_success" style="display:none;">
        </div>
        <div class="alert alert_page_error" style="display:none;">
        </div>
        <div class="clearfix">
            <div class="pull-left">
                <div id="upload-form" class="btn btn-sm">
                    <div id="queue"></div>
                    <input type="file" id="upload" name="upload" class="upload_class bg-red">
                </div>
                <span id="downModel" class="btn btn-group btn-sm btn-info"><i class="fa fa-file-excel-o"></i>  sim卡模板</span>
                <!-- <a class="btn btn-sm btn-info" id="fileOut"><i class="fa fa-download"></i> 导出数据 </a> -->
            </div>
        </div>
        <table id="list" class="table table-bordered"> 
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


<!-- DataTables -->
<script src="/Public/js/public/dataTableCusV2.js"></script>
<script src="/Public/plugins/daterangepicker/moment.js"></script>
<script src="/Public/plugins/daterangepicker/daterangepicker-dob.js"></script>
<script src="/Public/plugins/select2/select2.min.js"></script>
<script src="/Public/plugins/select2/underscore-min.js"></script>
<!--layer-->
<script src="/Public/layer/layer.js"></script>

<script src="/Public/plugins/uploadify/jquery.uploadify.min.js"></script>

<!--自定义插件-->

<script src="/Public/js/sim/simList.js"></script>
<script>
    /*导出信息模板*/
    $("#downModel").on('click',function () {
        alertMsg = {
            width: 250,
            title: '提示信息'
        };
        Confirm('批量导入时，请将Excel格式另存为97-2003兼容版本',alertMsg,function (res) {
            if(res){
                window.location.href = '/Public/file/sim.xlsx';
            }
        });
    });

    //sim卡归属
    $(function () {
        bornTree($("#sonWrapper"),'/Home/Public/organTree');
        $("#cooperateWrap").click(function(evt){
            $("#sonWrapper").show();
            evt.stopPropagation();
        });
        $(document).bind('click', function() {
            $("#sonWrapper").hide();
        })
    })

    // 生成树状函数
    function bornTree($el,url){
        init(url);
        var _html = '';

        function init(url) {
            AjaxJson(url,function (res) {
                if (res){
                    renderList(res);
                    $el.append('<li class="fillText" organ_id="" name="'+'全部'+'">' + '全部' + '</li>');
                    $el.append(_html);
                }
            })
        }
        function renderList(res) {

            for(var i=0,len=res.length;i<len;i++){
                var data = res[i];
                if(data.son){
                    _html += '<li class="fillText" organ_id="'+data.organ_id+'" name="'+data.organ_name+'">'+data.organ_name+'<ul>';
                    renderList(data.son);
                    _html += '</ul></li>';
                }
                else {
                    _html += '<li class="fillText" organ_id="'+data.organ_id+'" name="'+data.organ_name+'">' + data.organ_name + '</li>';
                }
            }
            $("#sonWrapper").on("click",".fillText",function (evt) {
                evt.stopPropagation();
                var valText = $(this).attr('name');
                var valId = $(this).attr('organ_id');
                $("#selectOrgan").text(valText).attr('selected','true').val(valId);
                $("#sonWrapper").hide();

            })

        }
    }
    $("#chooseOrgan").click(function (evt) {
        $("#sonWrapper").toggle();
        evt.stopPropagation();
    })
</script>
</body>

</html>