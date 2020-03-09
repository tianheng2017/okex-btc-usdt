<?php
// 应用公共文件

function get_url($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	$output = json_decode($output, true);
	return $output;
}

function jsons($count, $res){
	$data = [
		'code' => $count ? 0 : 1,
		'msg' => !$count ? '暂无数据' : '',
		'count' => $count,
		'data' => $res,
	];
	return json($data);
}