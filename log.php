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
    $param = [
        'appVersion'=> '1.0',
        'appid' =>'zq8bfc58935bf37o2e',
        'time' => '1456356854',
        'system' => 'IOS',
        'systemVersion' => '9.3.2',
        'model' => 'iPhone 6S Plus',
        'imei' => '3580865021934706',
        'ssid'=>'1a02be1c9d62843b8bf973b98c2180a9'//1a02be1c9d62843b8bf973b98c2180a9

    ];
    $param['sign'] = sign($param,'b8e586b6eb3530f1c5efad7ea3f1359e');

    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
        'system: '.$param['system'],
        'systemVersion:  '.$param['system'],
        'appVersion: '.$param['appVersion'],
        'model: '.$param['model'],
        'imei: '.$param['imei'],
        'sign: '.$param['sign'],
        'time:'.$param['time'],
        'appid:'.$param['appid'],
        'ssid:'.$param['ssid']
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
    return md5($data['appVersion'].$data['appid'].$data['time'].$appsecret);
}


echo "=\r\n";

$result = httpPost("https://api.zydzuqiu.com/user/check_user.html", $data);
echo ($result);
print_r(json_decode($result, true));

