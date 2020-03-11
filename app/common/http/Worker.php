<?php

namespace app\common\http;

use think\cache\driver\Redis;
use think\worker\Server;
use Workerman\Lib\Timer;
use think\facade\Cache;

class Worker extends Server
{
    protected $socket = 'http://0.0.0.0:7373';
    protected $protocol = 'https';
    protected $connection_count = 0;
    protected $redis;
    protected $context = [
        'ssl' => [
            'local_cert'  => '/www/wwwroot/php8.run/app/common/http/php8.run.crt',
            'local_pk'    => '/www/wwwroot/php8.run/app/common/http/php8.run.key',
            'verify_peer' => false,
        ],
    ];
    protected $transport = 'ssl';
    
    public function __construct(){
    	parent::__construct();
    	$this->redis = new Redis();
    }

    public function onConnect($connection)
    {
        ++$this->connection_count;
    }
 
    public function onWorkerStart($worker)
    {
        Timer::add(1, function() use($worker){
            $data = json_encode([
                'online'    => $this->connection_count,
                'tradesNum' => $this->redis->scard('trades'),
            ]);
            foreach($worker->connections as $connection){
                $connection->send($data);
            }
            Cache::set('online', $this->connection_count);
        });
    }

    public function onClose($connection)
    {
        $this->connection_count--;
    }

    public function onError($connection, $code, $msg){}
}