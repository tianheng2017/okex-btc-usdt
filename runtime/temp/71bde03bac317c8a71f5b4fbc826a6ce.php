<?php /*a:1:{s:47:"/www/wwwroot/php8.run/app/view/index/index.html";i:1583283955;}*/ ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlentities($title); ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=no, minimum-scale=1, initial-scale=1" />
    <script type="text/javascript" src="/js/jquery.min.js"></script><script type="text/javascript" src=" /layui/layui.js"></script><script type="text/javascript" src=" /js/echarts.min.js"></script><link rel="stylesheet" type="text/css" href=" /layui/css/layui.css" /><link rel="stylesheet" type="text/css" href=" /css/style.css" />
</head>
<body>
	<h1><?php echo htmlentities($title); ?></h1>
	<div class="table">
	    <table id="demo"></table>
	</div>
	<div id="main" class="main"></div>
	<div class="footer">
		<span>
			<span>在线人数：</span>
			<span id="online"><?php echo htmlentities($online); ?></span>
			<span>，已采集数据：</span>
			<a id="tradeNum" href="<?php echo url('/tj'); ?>"><?php echo htmlentities($tradeNum); ?></a>
		</span>
	</div>
	<script>
	    layui.use(['table','layer'], function(){
	        var table = layui.table
	            ,layer = layui.layer;
	        var data = table.render({
	            elem: '#demo'
	            ,url: '<?php echo url("index/index"); ?>'
	            ,page: false
	            ,autoSort: false
	            ,even: true
	            ,title: '<?php echo htmlentities($title); ?>'
	            ,cols: [[
	                {field: 'time', title: '日期', align:'center'}
	                ,{field: 'buy', title: '总买入', align:'center'}
	                ,{field: 'sell', title: '总卖出', align:'center'}
	                ,{field: 'nbuy', title: '净买入', align:'center'}
	                ,{field: 'buyMax', title: '最大买单', align:'center'}
	                ,{field: 'sellMax', title: '最大卖单', align:'center'}
	            ]]
	            ,done: function (res, curr, count) {
	                layer.msg('数据已刷新');
	            }
	        });
	        window.setInterval(function() {
	            data.reload('demo');
	            $.get('<?php echo url("index/getChartJson"); ?>').done(function (data) {
	                myChart.setOption({
	                    xAxis: {
	                        type: 'category',
	                        boundaryGap: false,
	                        data: data.date,
	                    },
	                    series: [{
	                        name:'净买入(个)',
	                        type:'line',
	                        stack: '个',
	                        data: data.will,
	                        itemStyle : { normal: {label : {show: true}}}
	                    }]
	                });
	            });
	        },60000);
	    });
	    var myChart = echarts.init(document.getElementById('main'));
	    option = {
	        title: {
	            text: '今日净买入分时图',
	            x:'center',
	            y:'top',
	            textAlign:'center'
	        },
	        tooltip: {
	            trigger: 'axis'
	        },
	        grid: {
	            containLabel: true
	        },
	        toolbox: {
	            feature: {
	                saveAsImage: {}
	            }
	        },
	        yAxis: {
	            type: 'value'
	        },
	        xAxis: {
	            type: 'category',
	            boundaryGap: false,
	            data: <?php echo $date; ?>,
	        },
	        series: [
	            {
	                name:'净买入(个)',
	                type:'line',
	                stack: '个',
	                data: <?php echo $will; ?>,
	                itemStyle : { normal: {label : {show: true}}}
	            },
	        ]
	    };
	    myChart.setOption(option);
	    window.addEventListener("resize", function () {
	        myChart.resize();
	    });
		ws = new WebSocket("wss://php8.run:7373");
		ws.onmessage = function(e) {
			var data = JSON.parse(e.data);
		    $('#online').text(data.online + ' 人');
		    $('#tradeNum').text(data.tradesNum + ' 条');
		};
	</script>
</body>
</html>