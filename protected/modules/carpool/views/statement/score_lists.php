<!DOCTYPE html>
<html lang="en" >
<head>
  <?php
    $this->render('block_head');
   ?>
   <style>
    .cp-page-statement .cp-pagesize {
       float: right; padding-top: 5px;
    }

   </style>

</head>
<body >
  <div class="cp-all cp-page-statement"  >


      <div class="cp-date-bar ">
        <div class="container-fluid">
        <h3 id="datetimepicker" class="date form_datetime"><?php echo $month; ?></h3>
        <div class="cp-pagesize ">
          <form onsubmit="return changePageSize();">
            每页显示 <input id="J-input-pagesize" name="pagesize" type="text" class="form-control " style="width:100px; display:inline-block"  value=""/>条
          </form>
        </div>
        </div>
      </div>

      <div class="cp-activeUser-list">
          <div id="jsGrid"></div>
          <div id="cp-pagination">  </div>
          <!--<table class="table table-hover table-striped table-content">
            <colgroup>
              <col style="width: 60px;"/>
              <col style="min-width: 140px;  "/>
              <col style="max-width: 120px;"/>
              <col style="max-width: 120px;"/>
              <col class="hidden-xs" style="width: 160px;"/>
              <col style="max-width: 120px;"/>
              <col class="hidden-xs" style="width: 100px;"/>
              <col class="hidden-xs" style="width: 100px;"/>
              <col class="hidden-xs"style="width: 100px;"/>
              <col class="hidden-xs" style="width: 100px;"/>
              <col style="width: 100px;"/>
              <col style="max-width: 90px;"/>
            </colgroup>
            <thead class="">
              <tr>
                <th>#</th>
                <th>用户名&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th class="hidden-xs">工号</th>
                <th class="hidden-xs">电话</th>
                <th class="hidden-xs">部门</th>
                <th>公司名</th>
                <th class="hidden-xs">任司机<br />次数</th>
                <th class="hidden-xs">有客|无客</th>
                <th class="hidden-xs">搭客人次</th>
                <th class="hidden-xs">约车<br />成功/总</th>
                <th>得分</th>
                <th>@</th>
              </tr>
            </thead>

            <tbody id="J-list-wrapper">

            </tbody>
          </table>-->
        </div>
  </div>

</body>

  <script>
  var datas = <?php echo json_encode($lists);?>;
  var month = "<?php echo $month ;?>";
  var isAdmin = "<?php echo $admin ;?>";
  var pagesize = <?php echo $pagesize ? $pagesize : 2000 ; ?>;


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
    minView: 3,
    startView:3,
    initialDate:month,
  }).on('changeDate', function(ev){
    var month_new = ev.date.getFullYear() + "-" + appendZero(ev.date.getMonth()+1);
    redirect('?month='+month_new+'&pagesize=<?php echo $pagesize;?>'+'&admin='+isAdmin)
  }).on('show',function(ev){
    $('.datetimepicker').css({'position':"fixed","top":45});
  });


  var $listWrapper = $("#J-list-wrapper");
  /*$.each(datas,function(index, el) {
    // console.log(el);
    var link_isAdmin = isAdmin ? '&admin=1' : '';
    var item_html = '<tr>\
      <td>'+el.uid+'</td>\
      <td>'+el.name+'</td>\
      <td>'+el.loginname+'</td>\
      <td>'+el.phone+'</td>\
      <td class="hidden-xs">'+el.Department+'</td>\
      <td>'+el.companyname+' <span class="hidden-xs">/ '+el.companyname_format+' </span></td>\
      <td class="hidden-xs">'+el.monthStatement.carowner_all+'</td>\
      <td class="hidden-xs">'+el.monthStatement.carowner_passenger_has+' | '+el.monthStatement.carowner_passenger_empty+'</td>\
      <td class="hidden-xs">'+el.monthStatement.carowner_passengers+'</td>\
      <td class="hidden-xs">'+el.monthStatement.passenger_picked+' / '+el.monthStatement.passenger_all+'</td>\
      <td>'+el.monthScores.total+'</td>\
      <td><a target="_blank" href="/carpool/statement/user_score?uid='+el.uid+'&month='+month+link_isAdmin+'"><i class="fa fa-chevron-circle-right "></i><span class="hidden-xs"> view</span></a></td>\
    </tr>'
    $listWrapper.append(item_html);
  });*/


  $.each(datas,function(index, el) {
    // console.log(el);
    datas[index]['stm_carowner_all'] = el.monthStatement.carowner_all;
    datas[index]['stm_carowner_passenger_has'] = el.monthStatement.carowner_passenger_has;
    datas[index]['stm_carowner_passenger_empty'] = el.monthStatement.carowner_passenger_empty;
    datas[index]['stm_carowner_passengers'] = el.monthStatement.carowner_passengers;
    datas[index]['stm_passenger_picked'] = el.monthStatement.passenger_picked;
    datas[index]['stm_passenger_all'] = el.monthStatement.passenger_all;
    datas[index]['score'] = el.monthScores.total;
    delete datas[index].monthStatement;
    delete datas[index].monthScores;

  });


  function doGrid(){
    $("#jsGrid").jsGrid({
        width:    "100%",
        height:   document.documentElement.clientHeight - 50,
        classes:  "table table-hover",
        // inserting: true,
        // editing: true,
        sorting: true,
        paging: true,
        data: datas,

        pageSize: pagesize,
        pageButtonCount: 5,
        // pagerContainer: "#cp-pagination",
        pagerFormat: " {first} {prev} {pages} {next} {last}",
        pagePrevText: "<",
        pageNextText: ">",
        pageFirstText: "<<",
        pageLastText: ">>",
        pageNavigatorNextText: "&#8230;",
        pageNavigatorPrevText: "&#8230;",

        fields: [
            { name: "uid", type: "text", width: 50, validate: "required" , title:'#'},
            { name: "name", type: "text", title:"姓名" },
            { name: "loginname", type: "text", width: 100 ,title:"帐号"},
            { name: "phone", type: "text", width: 120 ,title:"电话" },
            { name: "Department", type: "text", width: 120 ,title:"部门" },
            { name: "companyname", type: "text", width: 90 ,title:"厂名",itemTemplate: function(value, item){ return value} },
            { name: "score", type: "number", width: 80 ,title:"分数" },

            { name: "stm_carowner_all", type: "number", width: 90 ,title:"任司机数" },
            // { name: "stm_carowner_passenger_has", type: "number", width: 80 ,title:"成功 " },
            // { name: "stm_carowner_passenger_empty", type: "number", width: 80 ,title:"失败" },
            { name: "stm_carowner_passengers", type: "number", width: 90 ,title:"乘客人次" },
            // { name: "stm_passenger_all", type: "number", width: 80 ,title:"约车总数" },
            { name: "stm_passenger_picked", type: "number", width: 80 ,title:"搭车" },
            { name: "uid", type: "text", width: 80 ,title:"@" ,itemTemplate: function(value, item){
              var link_isAdmin = isAdmin ? '&admin=1' : '';
              return '<a target="_blank" href="/carpool/statement/user_score?uid='+value+'&month='+month+link_isAdmin+'"><i class="fa fa-chevron-circle-right "></i><span class="hidden-xs"> view</span></a>';
            }},
            // { name: "monthStatement", type: "text", width: 200 ,title:"任司机数",itemTemplate: function(value, item){
            //     // return value.carowner_all;
            // } },
        ]
    });
  }

  function changePageSize(){
    pagesize =   $("#J-input-pagesize").val();
    doGrid();
  }

  $("#J-input-pagesize").val(pagesize);
  doGrid()



  </script>


</html>
