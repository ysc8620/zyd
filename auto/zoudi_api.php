<?php
/**
 * 获取球队信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=zoudi_api=\r\n";
do{
    $postStr = file_get_contents("http://interface.win007.com/zq/Odds_Running.aspx");

    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $list = json_decode(json_encode($obj), true);
    foreach($list['h'] as $zoudi){
        $item = explode(',',$zoudi);
        if(count($item) == 13){
            if(!in_array($item[8], [3,24,31,8])){
                continue;
            }
            $data = [
                'zoudi_id' => $item[0],
                'match_id' => $item[1],
                'time' => $item[2],
                'home_score' => $item[3],
                'away_score' => $item[4],
                'home_yellow' => $item[5],
                'away_yellow' => $item[6],
                'type' => $item[7],
                'company_id' => $item[8],
                'rate_1' => $item[9],
                'rate_2' => $item[10],
                'rate_3' => $item[11],
                'change_date' => $item[12],
                'update_time' => time(),
            ];
            if($data['type'] == 1){
                $data['rate_2'] = -$data['rate_2'];
            }
            $data['time'] = trim(str_replace('分','',$data['time']));
            $has = M('zoudi')->where(array('zoudi_id'=>$data['zoudi_id']))->field('id')->find();
            if(!$has){
                M('zoudi')->add($data);
            }
        }
    }

}while(false);