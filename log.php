<?php
/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/7/20
 * Time: 11:57
 */

error_reporting(0);
header("Content-type:text/html;charset=utf-8");
// 发送请求
function httpPost($url, $data = null)
{
    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
        'X-ptype: like me','X-ptyped: like me2'
    ) );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $temp=curl_exec ($ch);
    curl_close ($ch);
    return $temp;
}
// 生成加密串
function sign($data,$appsecret)
{
    return md5($data['version'].$data['appid'].$data['time'].$appsecret);
}
//
$data = [
    'version'=> '1.0',
    'appid' =>'zq8bfc58935bf37o2e',
    'time' => '1456356854'
];
$data['sign'] = sign($data,'b8e586b6eb3530f1c5efad7ea3f1359e');
$data['remark'] = 'test';
$data['date'] = date("Y-m-d H:i:s");
$result = httpPost("http://w.zyd.cn/test/test.html", $data);
print_r(json_decode($result));

