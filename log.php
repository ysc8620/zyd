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

$data = ['1'=>2];
$data1 = ['2'=>3,'1'=>5];
print_r(array_merge($data, $data1));
die();
$postStr = file_get_contents('./baiou.log');
$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($obj), true);



foreach($data['h'] as $item){
    // print_r($item);;
    foreach($item['odds'] as $odds){

    }
}
