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
print_r($data);