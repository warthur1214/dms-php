<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

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

<body class="hold-transition skin-blue sidebar-mini padding0" style="overflow: hidden;">
    <div class="wrapper">
        <header class="main-header">
    <!-- Logo -->
    <a href="/" class="logo" target="_top">
        <span class="logo-mini"><b>硬件管理</b></span>
        <span class="logo-lg"><b>硬件管理平台</b></span>
    </a>
    <nav class="navbar navbar-static-top">
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li>
                    <a href="javascript:void(0);" target="_top">
                    <i class="fa fa-sitemap"></i> <span id="orgText"></span>
                    </a>
                    <div id="orgTree" class="dropdown-topnav hide"></div>
                </li>
                <li class="dropdown user user-menu">
                    <a href="/Home/Login/loginOut" target="_top">
                        <i class="fa fa-user"></i>
                        <span class="hidden-xs">退出</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header> 

        <aside class="main-sidebar">
            <iframe src="/Home/Index/menu" width="100%" height="100%" scrolling="no" frameborder="0"></iframe>
        </aside>
        <!-- Content Wrapper. Contains page content -->
        <div id="content" class="content-wrapper">
            <iframe src="/Home/Index/main" width="100%" height="100%" name="myFrame" scrolling="no" frameborder="0"></iframe>
        </div>
        <div class="control-sidebar-bg"></div>
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


    <script src="/Public/js/public/roleTree.js"></script>
    <script type="text/javascript">
    $(function() {
        function calculateHeight(){
            var _h = $('#content').outerHeight();
            $('iframe').outerHeight(_h); 
        };
        calculateHeight();
        $(window).on('resize', function(){
            setTimeout(function(){
                calculateHeight();
            },1000);
        });

        var $orgTree = $('#orgTree');
        var $orgText = $('#orgText');

        function showOrgTree() {
            $orgTree.removeClass('hide');
        };

        function hideOrgTree() {
            $orgTree.addClass('hide');
        };

        $orgText.parent('a').bind('click', function(e) {
            showOrgTree();
            e.stopPropagation();
        });
        AjaxJson('/Home/Index/sonParent', function(res) { 
            var orgTree = InitRoleTree({
                $el: $orgTree,
                dataSource: res.data,
                textKey: 'organ_name',
                valKey: 'organ_id',
                childrenKey: 'son'
            });

            orgTree.onClickNode = function(data) {
                $('#orgText').text(data.organ_name); 
                setDefaultOrginId(data.organ_id);
                hideOrgTree();
            };

            var $node = $orgTree.find('li[data-id="' + res.organ_id + '"] > span');
            $node.addClass('active');
            $orgText.text($node.text());
        });

        $('#orgTree').parent('li').bind('mouseleave', function(e) {
            hideOrgTree();
        });
    });

    function setDefaultOrginId(id) {
        AjaxJson('/Home/Index/saveChannel', {
            "organ_id": id
        },function( res ){ 
          top.location.reload(); 
        });
    };
    </script>
</body>

</html>