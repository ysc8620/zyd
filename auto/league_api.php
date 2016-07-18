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
    global $mongo;
    $curr = $mongo->zyd->league;
    $postStr = file_get_contents("http://interface.win007.com/zq/League_XML.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $info = [
            'league_id' => $item['id'],
            'color'    => $item['color'],
            'cn_short' => $item['gb_short'],
            'tw_short' => trim($item['big_short']),
            'en_short' => trim($item['en_short']),
            'cn_name' => trim($item['gb']),
            'tw_name' => $item['big'],
            'en_name' => $item['en'],
            'type' => $item['type'],
            'sum_round' => $item['sum_round'],
            'curr_round' => $item['curr_round'],
            'curr_match_season' => $item['Curr_matchSeason'],
            'country_id' => $item['countryID'],
            'country_name' => $item['country'],
            'area_id' => $item['areaID'],
        ];
        $team = $curr->findOne(array('league_id'=>$info['league_id']));
        if($team){
            $curr->update(array('league_id'=>$info['league_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);