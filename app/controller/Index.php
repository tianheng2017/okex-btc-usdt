<?php
namespace app\controller;

use app\common\model\Trade;
use think\facade\{Cache, Log, View};

class Index extends Base
{
	private function getDataAndDate()
	{
		try {
	        $len = ($this->redis->LLEN('will') < 30) ? $this->redis->LLEN('will') : 30;
	        $will = $this->redis->LRANGE('will', 0, $len);
	        $date = [];
	        $arr = range(0, $len*1, 1);
	        foreach (range(0, $len-1) as $k => $vo){
	            $date[] = $k ? date('H:i', strtotime('-'.$arr[$k].' minute')) : date('H:i');
	        }
	        $res = [
	        	'will'  => array_reverse($will),
	        	'date'  => array_reverse($date),
	        ];
	        return $res;
		}catch(\Exception $e){
			Log::error($e->getMessage());
		}
	}
	
	private function getCount()
	{
		try {
	        $len = ($this->redis->LLEN('count') < 30) ? $this->redis->LLEN('count') : 30;
	        $count = $this->redis->LRANGE('count', 0, $len);
	        $date = [];
	        $arr = range(0, $len*3, 3);
	        foreach (range(0, $len-1) as $k => $vo){
	            $date[] = $k ? date('i:s', strtotime('-'.$arr[$k].' second')) : date('i:s');
	        }
	        $res = [
	        	'count' => array_reverse($count),
	        	'date' => array_reverse($date),
	        ];
	        return $res;
		}catch(\Exception $e){
			Log::error($e->getMessage());
		}
	}
	
	private function getCache($vo)
	{
		$start_time = $vo ? strtotime(date("Y-m-d", strtotime("-".$vo." day"))) : strtotime(date("Y-m-d"));
        $end_time = $start_time + 60*60*24 - 1;
        $buy_arr = $this->redis->zrangebyscore('buy', $start_time, $end_time);
        $sell_arr = $this->redis->zrangebyscore('sell', $start_time, $end_time);
        $data['start_time'] = $start_time;
		if ($vo){
			$data['buy']	 =  Cache::remember('buy_'.$start_time, round(array_sum($buy_arr), 2));
			$data['sell']	 =  Cache::remember('sell_'.$start_time, round(array_sum($sell_arr), 2));
			$data['nbuy']	 =  Cache::remember('nbuy_'.$start_time, round($data['buy']-$data['sell'], 2));
			$data['buyMax']  =  Cache::remember('buyMax_'.$start_time, round(@max($buy_arr), 2));
			$data['sellMax'] =  Cache::remember('sellMax_'.$start_time, round(@max($sell_arr), 2));
		}else{
			$data['buy']	 =  round(array_sum($buy_arr), 2);
			$data['sell']	 =  round(array_sum($sell_arr), 2);
			$data['nbuy']	 =  round($data['buy']-$data['sell'], 2);
			$data['buyMax']  =  round(@max($buy_arr), 2);
			$data['sellMax'] =  round(@max($sell_arr), 2);
		}
		return $data;
	}
	
	
	public function index()
	{
        if ($this->request->IsAjax()) {
            $data = [];
            foreach (range(0, 6) as $k => $vo) {
                $res = $this->getCache($vo);
                $data[] = [
                    'time'      =>  date('Y-m-d', $res['start_time']),
                    'buy'       =>  $res['buy'],
                    'sell'      =>  $res['sell'],
                    'nbuy'		=>	$res['nbuy'],
                    'buyMax'    =>  $res['buyMax'],
                    'sellMax'   =>  $res['sellMax'],
                ];
            }
            return jsons(count($data), $data);
        } else {
			$res = $this->getDataAndDate();
            View::assign([
                'title'    => 'OKEX BTC/USDT现货统计系统',
                'date'     => @json_encode($res['date']),
                'will'     => @json_encode($res['will']),
                'online'   => (Cache::get('online')??0) . ' 人',
                'tradeNum' => ($this->redis->scard('trades')?:0) . ' 条',
            ]);
            return View::fetch();
        }
	}


    public function getChartJson()
    {
    	return json($this->getDataAndDate());
    }
    
    public function tj()
    {
    	$count = $this->getCount();
	    View::assign([
            'title'  => '交易记录入库统计（条）',
            'count'   => json_encode($count['count']),
            'date'   => json_encode($count['date']),
            'online' => (Cache::get('online')??0) . ' 人',
            'tradeNum' => ($this->redis->scard('trades')?:0) . ' 条',
        ]);
        return View::fetch();
    }
    
    public function getTjJson()
    {
    	return json($this->getCount());
    }
}
