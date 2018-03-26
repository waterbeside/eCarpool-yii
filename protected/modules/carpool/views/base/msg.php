<?php
  $classNameOfAlert = $success ? 'alert-success' : 'alert-warning';
 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="x5-fullscreen" content="true">
    <!-- UC强制全屏 -->
    <meta name="full-screen" content="yes">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <title><?php if($success):?>操作成功！<?php else:?>操作出错啦！<?php endif;?></title>
      <link href="/c/css/bootstrap.cp.min.css" rel="stylesheet">
      <style>
      .n_icon {  font-size: 50px; padding: 20px 15px 10px;}
      @media (max-width:768px) {
        .btn-wrap a.btn { display: block; }
      }
      </style>
  </head>
  <body>
    <div class="container">

        <div class="alert <?php echo $classNameOfAlert; ?> " style="margin-top:100px">
          <div class="row">
            <?php if($success):?>
              <!-- <span class="glyphicon glyphicon-ok-sign text-success"></span> -->
              <div class=" text-success col-sm-3 col-md-2 n_icon text-center" ><span class="glyphicon glyphicon-ok "></span></div>
            <?php else:?>
              <!-- <span class="glyphicon glyphicon-remove-sign text-danger"></span> -->
              <div class=" text-warning col-sm-3 col-md-2 n_icon text-center"><span class="glyphicon glyphicon-warning-sign"></span></div>
            <?php endif;?>
            <div class="col-sm-9 col-md-10">
              <?php echo '<h4>'.$msg.'</h4>';?>
              <p class="text-muted" style="font-size:12px">该页将在3秒后自动跳转!</p>
              <p class="btn-wrap">
                  <?php if(!empty($gotoUrl)):?>
                      <a class="btn btn-default " href="<?php echo $gotoUrl?>">立即跳转</a>
                  <?php else:?>
                      <a  class="btn btn-default "  href="javascript:void(0)" onclick="history.go(-1)">返回上一页</a>
                  <?php endif;?>
              </p>
            </div>

          </div>
        </div>

    </div>

    <script>
    <?php if(empty($gotoUrl)):?>
       setInterval("history.go(-1);",<?php echo $sec;?>000);
    <?php else:?>
       setInterval("window.location.href='<?php echo  $gotoUrl;?>'",<?php echo $sec;?>000);
    <?php endif;?>

    </script>

  </body>
</html>
