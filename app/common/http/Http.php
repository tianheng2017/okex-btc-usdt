<?php
namespace app\common\http;

use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Http\Client;

class Http
{
	public function __construct() {
		$worker = new Worker();
		$worker->onWorkerStart = function(){
			$http = new Client();
			Timer::add(2, function() use($http){
		    	$http->get('https://php8.run/service/set', function($response){}, function($exception){});
		    });
            Timer::add(60, function() use($http){
                $http->get('https://php8.run/service/setChartData/type/1', function($response){}, function($exception){});
            });
            Timer::add(180, function() use($http){
                $http->get('https://php8.run/service/setChartData/type/3', function($response){}, function($exception){});
            });
            Timer::add(3600, function() use($http){
                $http->get('https://php8.run/service/setChartData/type/4', function($response){}, function($exception){});
            });
            Timer::add(14400, function() use($http){
                $http->get('https://php8.run/service/setChartData/type/5', function($response){}, function($exception){});
            });
            Timer::add(86400, function() use($http){
                $http->get('https://php8.run/service/clear', function($response){}, function($exception){});
            });
		};
		Worker::runAll();
	}
}
