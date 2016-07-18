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


$postStr = file_get_contents('./log.log');
$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($obj), true);


foreach($data['match'] as $item){
    $match_id = $item['id'];
    $match_info = true;//$curr->findOne(array('match_id'=>$match_id));
    if($match_info)
    {
        $technic = (array)$match_info['technic'];
        $info = explode(';', trim($item['TechnicCount']));
        foreach ($info as $row) {
            $list = explode(',', $row);
            if(count($list) != 3)continue;
            $technic['id' . $list[0]] = [
                'home' => $list[1],
                'away' => $list[2]
            ];
        }
        print_r($technic);
    }
}

