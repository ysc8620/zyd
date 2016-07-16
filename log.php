<?php
/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/7/6
 * Time: 18:57
 */

//$postStr = file_get_contents("http://interface.win007.com/zq/today.aspx");
//$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
//$data = json_decode(json_encode($obj), true);
//foreach($data as $item){
//
//}


$postStr = file_get_contents('./odds.log');
$list = explode('$', $postStr);

$league_list = explode(';', $list[0]);
// 联赛部分
foreach($league_list as $league){
    echo $league."\r\n";
    break;
}

$match_list = explode(';',$list[1]);
// 赛程部分
foreach($match_list as $match){
echo $match."\r\n";
    break;
}

$yapei_list = explode(';', $list[2]);
// 亚赔让球
foreach($yapei_list as $yapei_rangqiu){
echo $yapei_rangqiu."\r\n";
    break;
}

$oupei_list = explode(';', $list[3]);
// 欧赔标准
foreach($oupei_list as $oupei_biaozhun){
echo $oupei_biaozhun."\r\n";
    break;
}

$daxiaoqiu_list = explode(';', $list[4]);
// 大小球
foreach($daxiaoqiu_list as $daxiaoqiu){
echo $daxiaoqiu."\r\n";
    break;
}

$riqi_list = explode(';', $list[5]);
// 日期
foreach($riqi_list as $riqi){
    echo $riqi."\r\n";
    break;
}

$banchang_list = explode(';', $list[6]);
// 半场让球
foreach($banchang_list as $banchang){
echo $banchang."\r\n";
    break;
}

$banchang_daxiaoqiu_list = explode(';',$list[7]);
// 半场大小球
foreach($banchang_daxiaoqiu_list as $banchang_daxiaoqiu){
echo $banchang_daxiaoqiu."\r\n";break;
}