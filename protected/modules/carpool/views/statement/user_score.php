<!DOCTYPE html>
<html lang="en" >
<head>
  <?php
    $this->render('block_head');
   ?>


</head>
<body >
  <div class="cp-all cp-page-statement"  >


      <div class="cp-date-bar">
        <div class="container-fluid">
        <h3 id="datetimepicker" class="date form_datetime" ><?php echo $month; ?></h3>
        </div>
      </div>
      <div class="cp-user-bar">
        <div class="container-fluid">
        <h5 class="pull-left"><?php echo $user['name']; ?> <small><?php echo $user['loginname']; ?></small></h5>
        <h5 class="pull-right"><?php echo $user['companyname']; ?> / <?php echo $user['Department']; ?></h5>
        </div>
      </div>
      <div class="cp-score-content">
        <div class="container">
          <dl class="cp-score-total">
            <dt>当月得分：</dt>
            <dd>
              <b class="cp-score"><?php echo $scores['total'];?></b>
            </dd>
          </dl>

          <div class="cp-statement-section cp-statement-section-carowner  col-md-6">
            <div id="chart-carowner" style="height:300px" ></div>
            <div class="row">

              <dl class=" col-xs-6">
                <dt>共</dt>
                <dd>
                  <b class="cp-score"><?php echo $totals['carowner_all'];?></b>
                  <span class="cp-unit">次</span>
                </dd>
              </dl>
              <dl class=" col-xs-6">
                <dt>乘客数</dt>
                <dd>
                  <b class="cp-score"><?php echo $totals['carowner_passengers'];?></b>
                  <span class="cp-unit">人次</span>
                </dd>
              </dl>
            </div>
          </div>

          <div class="cp-statement-section col-md-6">
            <div id="chart-passenger" style="height:300px" ></div>
            <div class="row">
              <dl class=" col-xs-6">
                <dt>共</dt>
                <dd>
                  <b class="cp-score"><?php echo $totals['passenger_all'];?></b>
                  <span class="cp-unit">次</span>
                </dd>
              </dl>
              <dl class=" col-xs-6">
                <dt>成功</dt>
                <dd>
                  <b class="cp-score"><?php echo $totals['passenger_picked'];?></b>
                  <span class="cp-unit">次</span>
                </dd>
              </dl>
            </div>

          </div>

        </div>

        <!-- 拼车清单  -->
        <div class="container cp-myRouteDatas-list" >
          <h3>任司机情况</h3>
          <div class="table-responsive">
            <table class="table table-hover table-striped table-responsive">
              <thead>
                <tr>
                  <th>司机</th>
                  <th>乘客</th>
                  <th>出发时间 </th>
                  <th>起点终点</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="J-carowner-all" >
                <?php foreach ($datas['info_carowner_all'] as $key => $value) { ?>
                  <tr>
                    <td><?php echo $value['co_name'] ;?></td>
                    <td><?php echo $value['pa_name'] ;?></td>
                    <td><?php echo $value['time'] ;?></td>
                    <td><?php echo $value['s_addressname'] ;?><br /><?php echo $value['e_addressname'] ;?></td>
                    <td><?php echo $value['love_wall_ID'] ? $value['love_wall_ID'] : '<i class="fa fa-user"></i>';?></td>

                  </tr>
                <?php }?>
                <?php
                  foreach ($datas['wall_all'] as $key => $value) {
                    if($value['pa_num']==0){ ?>
                  <tr>
                    <td><?php echo $value['co_name'] ;?></td>
                    <td>-</td>
                    <td><?php echo $value['time'] ;?></td>
                    <td><?php echo $value['s_addressname'] ;?><br /><?php echo $value['e_addressname'] ;?></td>
                    <td><?php echo $value['love_wall_ID'] ? $value['love_wall_ID'] : '<i class="fa fa-user"></i>';?></td>
                  </tr>
                <?php }}?>
              </tbody>
            </table>
          </div>
          <h3>任乘客情况</h3>
          <div class="table-responsive">
            <table class="table table-hover table-striped ">
              <thead>
                <tr>
                  <th>司机</th>
                  <th>乘客</th>
                  <th>出发时间 </th>
                  <th>起点终点</th>
                  <th></th>
                </tr>
              </thead>
              <tbody  >
                <?php foreach ($datas['info_passenger_all'] as $key => $value) { ?>
                  <tr>
                    <td><?php echo $value['co_name'] ;?></td>
                    <td><?php echo $value['pa_name'] ;?></td>
                    <td><?php echo $value['time'] ;?></td>
                    <td><?php echo $value['s_addressname'] ;?><br /><?php echo $value['e_addressname'] ;?></td>
                    <td><?php echo $value['love_wall_ID'] ? $value['love_wall_ID'] : '<i class="fa fa-user"></i>';?></td>

                  </tr>
                <?php }?>
              </tbody>
            </table>
          </div>

        </div>
        <!-- /拼车清单  -->

      </div>

  </div>

</body>

  <script>
  var totals = <?php echo json_encode($totals)?>;
  var myChart_carowner = echarts.init(document.getElementById('chart-carowner'));
  var isAdmin = '<?php echo $admin ?>'
  var uid    = '<?php echo $user["uid"];?>'
  function redirect(url,win) {
      var lct = typeof(win)!="undefined" ? win.location : location;
      //console.log(lct);
      lct.href = url;
  }
  function appendZero(obj){
        if(obj<10) return "0" +""+ obj;
        else return obj;
    }
  $('#datetimepicker').datetimepicker({
    format: 'yyyy-mm',
    autoclose: true,
    language:'zh-CN',
    // todayBtn: true,
    minView: 3,
    startView:3,
    initialDate:'<?php echo $month;?>',
  }).on('changeDate', function(ev){
    var month = ev.date.getFullYear() + "-" + appendZero(ev.date.getMonth()+1);
    redirect('?uid='+uid+'&month='+month+'&admin='+isAdmin)
  });


  // 使用刚指定的配置项和数据显示图表。
  myChart_carowner.setOption({
    title : {
        text: '本月当司机情况',
        // subtext: '单位：次',
        x:'center'
    },
    legend: {
        orient: 'vertical',
        left: 'left',
        top: '50px',
        data: ['没乘客','有乘客']
    },
    tooltip: { //弹窗组件
                formatter: "<br/> {c} 次{b}"
            },

    series : [
        {
            // name: '空座位情况',
            type: 'pie',
            radius: ['26%','56%'],
            label: {normal: {
               show: true,
               formatter: '{b}:{c}次'
           }},
           center: ['50%', '60%'],
            data:[
                {value:totals.carowner_passenger_empty,
                name:'没乘客',
                itemStyle:{
                    normal:{color:"rgba(106, 86, 146, 0.6)"}
                }},
                {value:totals.carowner_all -  totals.carowner_passenger_empty,
                name:'有乘客',
                itemStyle:{
                    normal:{color:"rgba(106, 86, 146, 1)"}
                }},
            ]
        }
    ]
    }
  );

  var myChart_passenger = echarts.init(document.getElementById('chart-passenger'));
  // 使用刚指定的配置项和数据显示图表。
  myChart_passenger.setOption({
    title : {
        text: '本月约车情况',
        // subtext: '单位：次',
        x:'center'
    },
    legend: {
        orient: 'vertical',
        left: 'left',
        top: '50px',
        data: ['失败','成功']
    },
    tooltip: { //弹窗组件
                formatter: "{a}:<br/> {c} 次{b}"
            },

    series : [
        {
            name: '约车',
            type: 'pie',
            radius: ['26%','56%'],
            label: {normal: {
               show: true,
               formatter: '{b}:{c}次'
           }},
           center: ['50%', '60%'],
            data:[
                {value:totals.passenger_unpicked,
                name:'失败',
                itemStyle:{
                    normal:{color:"rgba(106, 86, 146, 0.6)"}
                }},
                {value:totals.passenger_picked,
                name:'成功',
                itemStyle:{
                    normal:{color:"rgba(106, 86, 146, 1)"}
                }},
            ]
        }
    ]
    }
  );



  $("#J-carowner-all tr").click(function(){
    var beClicked = $(this).attr('data-beclicked');
    if(beClicked==1){
      $(this).attr('data-beclicked',0).removeClass("cp-active")
    }else{
      $(this).attr('data-beclicked',1).addClass("cp-active")
    }
    $trs = $("#J-carowner-all").find(".cp-active");
    console.log($trs.length)

  })


  </script>

</html>
