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
    body {
        background-color: #1e282c;
    }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini padding0">
    <div class="wrapper">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="treeview">
                    <a id="mainHref" href="/Home/Index/main" target="myFrame">
                        <span>首页</span>
                    </a>
                </li>
                <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vals): $mod = ($i % 2 );++$i; if(count($vals['menu_two']) != 0): ?><li class="treeview">
                            <a href="<?php echo ($vals['module_url']); ?>">
                                <span><?php echo ($vals['module_name']); ?></span> <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <?php if(is_array($vals['menu_two'])): $i = 0; $__LIST__ = $vals['menu_two'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><li>
                                        <a href="<?php echo ($val['module_url']); ?>" target="myFrame">
                                            <i class="fa fa-circle-o"></i>
                                            <?php echo ($val['module_name']); ?>
                                        </a>
                                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                        </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </ul>
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


    <script type="text/javascript">
    $(function() {

        var $firstMenu = $('.sidebar-menu').children('li');
        var menuIco = ["fa-dashboard","fa-automobile","fa-location-arrow","fa-credit-card","fa-search","fa-wrench","fa-users","fa-database","fa-edit","fa-gears"];

        $firstMenu.each(function(i) {
            var $this = $(this);
            $this.children('a').prepend('<i class="fa '+ menuIco[ i ] +'"></i> ');  
        });
        $('.treeview-menu').find('a').click(function() {
            var li = $(this).parent();
            li.addClass('active');
            li.siblings().removeClass('active');
        });
        $('#mainHref').click(function() { 
            $('.sidebar-menu > .active .fa-angle-left').trigger('click');  
            $(this).parent().addClass('active'); 
        });
    });
    </script>
</body>

</html>