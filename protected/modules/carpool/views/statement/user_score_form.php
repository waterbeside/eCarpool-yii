<!DOCTYPE html>
<html lang="en" >
<head>
  <?php
    $this->render('block_head');
   ?>


</head>
<body >
  <div class="cp-all" >
    <div class="container">
      <form class="form" method="get" action="/carpool/statement/user_score" >
        <div class="form-group">
          <label for="loginname">用户名</label>
          <input type="text" class="form-control" name="loginname" placeholder="用户名">
        </div>
        <div class="form-group">
          <label for="month">月份</label>
          <input type="text" class="form-control" name="month" placeholder="月份" value="<?php echo date('Y-m'); ?>" id="datetimepicker" data-date-format="yyyy-mm" readonly >
        </div>
        <button class="btn btn-success btn-block">查询</button>
      </form>

    </div>
  </div>

</body>

  <script>
  $('#datetimepicker').datetimepicker({
    format: 'yyyy-mm',
    autoclose: true,
    language:'zh-CN',
    // todayBtn: true,
    minView: 3,
    startView:3,
  });

  </script>

</html>
