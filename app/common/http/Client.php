<?php

namespace app\common\http;

use think\cache\driver\Redis;
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use think\facade\Cache;

class Client
{
    public function __construct() {
        $worker = new Worker();
        $worker->onWorkerStart = function(){

            $wss = new AsyncTcpConnection("wss://real.okex.me:8443/ws/v3");

            $wss->onConnect = function($connection){
                echo 'connection success';
            };

            $wss->onMessage = function($connection, $data){
                echo "recv: $data\n";
            };

            $wss->onError = function($connection, $code, $msg)
            {
                echo "error: $msg\n";
            };

            $wss->onClose = function($connection){
                echo "connection closed\n";
            };

            $wss->connect();
        };
        Worker::runAll();
    }
}