<?php /*a:1:{s:44:"/www/wwwroot/php8.run/app/view/index/tj.html";i:1583720060;}*/ ?>
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
	<div id="main" class="main"></div>
	<div class="footer">
		<span>
			<span>在线人数：</span>
			<span id="online"><?php echo htmlentities($online); ?></span>
			<span>，已采集数据：</span>
			<span id="tradeNum"><?php echo htmlentities($tradeNum); ?></span>
			<div class="source">
				<span>Github开源：</span>
				<a href="https://github.com/tianheng2017/okex-btc-usdt" target="_blank">简易OKEX BTC/USDT现货统计系统</a>
			</div>
		</span>
	</div>
	<script>
        window.setInterval(function() {
            $.get('<?php echo url("index/getTjJson"); ?>').done(function (data) {
                myChart.setOption({
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: data.date,
                    },
                    series: [{
                        name:'数量(条)',
                        type:'line',
                        stack: '条',
                        data: data.count,
                        itemStyle : { normal: {label : {show: true}}}
                    }]
                });
            });
        },3000);
	    var myChart = echarts.init(document.getElementById('main'));
	    option = {
	        title: {
	            text: '<?php echo htmlentities($title); ?>',
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
	                name:'数量(条)',
	                type:'line',
	                stack: '个',
	                data: <?php echo $count; ?>,
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