<?php

/*
 *  饿了么抢大红包脚本 PHP
 *  Author: Zmsky
 *  http://xloli.net
 *
 *  ======== 使用方法 ========
 *  打开终端输入以下命令执行
 *  php run.php 手机号
 *  ========================
 */
include_once('lib.php');

// 抢红包API
define("QIANG_HONG_BAO_API","http://101.132.113.122:3007/hongbao"); 
// 拼红包API
define("HONG_BAO_API","https://www.pinghongbao.com/eleme"); 

if (count($argv) < 2){
    die("[Error] 请输入手机号!");
}
// 获取手机号
$mobile = $argv[1];

// 利用DOM库请求红包API，获取红包URL
$html = new simple_html_dom();
$html->load_file(HONG_BAO_API);
$copyBtns = $html->find('.copybtn');

foreach ($copyBtns as $copyBtn){
    $url = $copyBtn->getAttribute('data-clipboard-text');
    $regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
    $response = get($url); // 获取真实的红包URL
    preg_match_all($regex, $response, $matches);
    $result = post(QIANG_HONG_BAO_API,["url"=>$matches[0][0],"mobile"=>$mobile]); // 请求抢红包
    echo $result . "\n";
}

echo "\n\n ✔ 红包领取完毕！";

// 获取Response的Header部分
function get($url) {
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL,$url);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($oCurl, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_HEADER, true);
	curl_setopt($oCurl, CURLOPT_NOBODY, true);
	$sContent = curl_exec($oCurl);
	$headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
	$header = substr($sContent, 0, $headerSize);
	curl_close($oCurl);
	return $header;
}

// Post请求
function post($url, $data){
    $postData = http_build_query(
        $data
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData
        )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    return $result;
}
