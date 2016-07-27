<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 17:03
 */

use Helpers\Presenter;
use Redis\MyRedis;

function getAccessToken($city_id)
{
    $appId = D('city')->where(['id' => $city_id])->getField('appid');
    $key = 'wechat_access_token' . $appId;
    $access_token = MyRedis::getTokenInstance()->new_get($key);
    return $access_token;
}

/**
 * 时间戳转日期
 *
 * created by 胡倍玮
 *
 * @param int $timestamp 时间戳
 * @param string $format 默认Y-m-d H:i:s
 * @return null|string 时间戳为空或0返回null，不填返回当前日期
 */
function timestampToDate($timestamp = -1, $format = 'Y-m-d H:i:s')
{
    if (!$timestamp) {
        return null;
    }
    if ($timestamp == -1) {
        return date($format, time());
    } else {
        return date($format, $timestamp);
    }
}

/**
 * http请求
 *
 * @param $url
 * @param mixed $data
 * @return mixed
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * 获取城市数组或名称
 * 因为本方法会获取所有城市，不建议在视图的循环里调用，在控制器调用后再传递到视图
 *
 * @param int|null $city_id
 * @return array|string 数组形如['城市id1' => '城市名称1', ...]，如果传了城市id则返回对应城市名称
 */
function cityMap($city_id = null)
{
    $city_list = D('City')->select();
    $city_map = [];
    foreach ($city_list as $key => $value) {
        $city_map[$value['id']] = $value['city_name'];
    }
    if ($city_id) {
        return $city_map[$city_id];
    }
    return $city_map;
}

