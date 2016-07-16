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

$data = file_get_contents('./log.log');
$data = explode("\r\n", $data);
foreach ($data as $item) {

    if(strpos($item,'rq[') !== false){
        preg_match_all("/\"(.*?)\"/i", $item, $res);
        if($res[0]){
            if($res[1][0]){
                $row = explode('^', $res[1][0]);
                // print_r($row);
            }
        }
    }
}