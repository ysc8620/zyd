<?php
/**
 * 获取联赛信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=league_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->league;
    $postStr = file_get_contents("http://interface.win007.com/zq/League_XML.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $info = [
            'league_id' => intval($item['id']),
            'color'    => getValue($item['color']),
            'cn_short' => getValue($item['gb_short']),
            'tw_short' => getValue($item['big_short']),
            'en_short' => getValue($item['en_short']),
            'cn_name' => getValue($item['gb']),
            'tw_name' => getValue($item['big']),
            'en_name' => getValue($item['en']),
            'type' => intval($item['type']),
            'sum_round' => getValue($item['sum_round']),
            'curr_round' => getValue($item['curr_round']),
            'curr_match_season' => getValue($item['Curr_matchSeason']),
            'country_id' => intval($item['countryID']),
            'country_name' => getValue($item['country']),
            'area_id' => intval($item['areaID']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s')
        ];
        $team = M('league')->where(array('league_id'=>$info['league_id']))->field('id,league_id')->find();
        if($team){
            M('league')->where(array('league_id'=>$info['league_id']))->save($info);
        }else{
            M('league')->add($info);
        }
    }
}while(false);