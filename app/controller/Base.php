<?php
declare (strict_types = 1);

namespace app\controller;

use think\App;
use think\Cache;
use think\cache\driver\Redis;

/**
 * 控制器基础类
 */
abstract class Base{
    protected $request;
    protected $app;
    protected $redis;
    protected $middleware = [];

    public function __construct(App $app, Cache $cache) {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->redis = new Redis();
        $this->initialize();
    }

    protected function initialize(){}

    /**
     * 获取锁
    */
    public function lock($key, $random, $expire = 20, $num = 5) {
        $is_lock = $this->redis->set($key, $random, ['nx', 'ex' => $expire]);
        if (!$is_lock) {
            for ($i = 0; $i < $num; $i++) {
                $is_lock = $this->redis->set($key, $random, ['nx', 'ex' => $expire]);
                if ($is_lock) {
                    break;
                }
                sleep(1);
            }
        }
        if (!$is_lock) {
            $this->unlock($key, $random);
            $is_lock = $this->redis->set($key, $random, ['nx', 'ex' => $expire]);
        }
        return $is_lock ? true : false;
    }
    
    /**
     * 释放锁
     */
    public function unlock($key, $random) {
        $script = <<<EOF
            local key = KEYS[1]
            local random = ARGV[1]
            if (redis.call("EXISTS", key) == 1 and redis.call("GET", key) == random)
            then
                return redis.call("DEL", key)
            end
            return 0
EOF;
        return $this->redis->eval($script, [$key, $random],1);
    }
 
}
