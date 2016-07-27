<?php
/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/7/6
 * Time: 18:57
 */

echo MD5(microtime(true));
die();
$postStr = '<?xml  version="1.0" encoding="utf-8"?><c><a><h>1212236,14,-0.25,0.89,0.94,False,False</h><h>1212237,14,0.75,0.86,0.97,False,False</h><h>1212238,14,0,0.97,0.86,False,False</h><h>1212240,14,0,0.78,1.07,False,False</h><h>1252366,14,0,0.83,1.08,False,False</h><h>1252370,14,0,0.89,1.01,False,False</h><h>1252371,14,0,0.90,1.00,False,False</h><h>1261335,17,0.5,0.78,1.06,False,True</h><h>1261335,42,0.5,0.80,1.04,False,True</h><h>1268634,42,1,0.99,0.93,False,False</h><h>1268658,42,0,0.99,0.93,False,False</h></a><o><h>1212238,14,2.75,3.10,2.55</h><h>1212240,14,2.45,3.10,2.88</h><h>1266693,4,5.75,4.33,1.53</h><h>1268634,42,1.54,3.55,6.60</h><h>1268658,42,2.64,3.20,2.57</h></o><d><h>1212236,14,2.5,1.20,0.65</h><h>1252366,14,2.5,0.87,0.95</h><h>1252370,14,2.5,0.98,0.83</h></d><a></a><d><h>1268634,42,1,1.05,0.83</h></d></c>';
$postStr = str_replace(array('<o>','<d>'),'<a>',$postStr);
$postStr = str_replace(array('</o>','</d>'),'</a>',$postStr);
$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($obj), true);

if(count($data['a']) == 5){
    foreach($data['a'][0]['h'] as $asia){
        $info = explode(',', $asia);
        print_r($info);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'change_rate' => floatval($info[2]),
            'change_home_rate' => floatval($info[3]),
            'change_away_rate' => floatval($info[4]),
            'is_inclose' => strval($info[5]),
            'is_walk' => strval($info[6]),
            'update_time' => time()
        ];
        print_r($data);
        continue;
        $match = M('asia_yapei')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
        if($match){
            M('asia_yapei')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_yapei')->add($data);
        }
    }

    print_r($data['a'][3]);

}
//foreach($data as $item){
//
//}