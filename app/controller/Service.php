<?php
namespace app\controller;

use think\facade\Log;

class Service extends Base
{
	private function getWillName($type)
	{
	   switch ($type){
    		case 1:
    			$name = 'will';
    			break;
    		case 2:
    			$name = 'will5m';
    			break;
    		case 3:
    			$name = 'will30m';
    			break;
    		case 4:
    			$name = 'will1h';
    			break;
    		case 5:
    			$name = 'will4h';
    			break;
			default:
				$name = 'will';
    	}
    	return $name;
	}
	
	private function add($res, $random)
	{
		if (!$this->lock('lock', $random)) {
			exit('false:lock fail');
		}
        $i = 0;
	    foreach ($res as $k => $v){
            $status = $this->redis->sadd('trades', $v['trade_id']);
            if ($status){
                $this->redis->zadd(($v['side'] == 'sell') ? 'sell' : 'buy', strtotime($v['time']), $v['size']);
                $i++;
            }
        }
        return $i;
	}
	
	private function after($count, $random)
	{
	    $script = <<<EOF
            redis.call("LPUSH", KEYS[1], ARGV[1])
            if redis.call("LLEN", KEYS[1]) > 30 then
                redis.call("RPOP", KEYS[1])
            end
            if (redis.call("EXISTS", KEYS[2]) == 1 and redis.call("GET", KEYS[2]) == ARGV[2]) then
                return redis.call("DEL", KEYS[2])
            end
            return 0
EOF;
        if (!$this->redis->eval($script, ['count','lock',$count,$random], 2)){
        	exit('false:after');
        }
    	return true;
	}
	
	public function clear(){
		$last_time = strtotime(date('Y-m-d')) - 7*24*60*60;
		$this->redis->zremrangebyscore('buy', 0, $last_time);
		$this->redis->zremrangebyscore('sell', 0, $last_time);
		return 'success';
	}
	
	public function set()
    {
        try {
            $res = get_url('https://www.okex.me/api/spot/v3/instruments/BTC-USDT/trades');
            $random = time();
            $count = $this->add($res, $random);
            $this->after($count, $random);
            return 'success';
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'error：'.$e->getMessage();
        }
    }
    
    public function setChartData()
	{
        try {
            $type = $this->request->param('type/d');
            $name = $this->getWillName($type);
            $start_time = strtotime(date("Y-m-d"));
            $end_time = $start_time + 60*60*24 - 1;
            $buyAll = array_sum($this->redis->zrangebyscore('buy', $start_time, $end_time));
            $sellAll = array_sum($this->redis->zrangebyscore('sell', $start_time, $end_time));
            $will = round($buyAll - $sellAll, 2);
            $script = <<<EOF
                redis.call("LPUSH", KEYS[1], ARGV[1])
                if redis.call("LLEN", KEYS[1]) > 60 then
                    redis.call("RPOP", KEYS[1])
                end
EOF;
            $this->redis->eval($script, [$name,$will], 1);
            return 'success';
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'error：'.$e->getMessage();
        }
    }
}