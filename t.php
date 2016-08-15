<?php
/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/7/6
 * Time: 18:57
 *
 * `zoudi_id`, `match_id`, `time`, `home_score`, `away_score`, `home_yellow`, `away_yellow`, `type`, `company_id`, `rate_1`, `rate_2`, `rate_3`, `change_date`, `update_time`
 */
echo base64_encode(("1000006"));
die();

$postStr = file_get_contents("F:/user/Odds_Running.aspx");
$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($obj), true);

print_r($data);die();

foreach($data['h'] as $match){
    print_r($match);die();
    if(is_array($match['odds']['o'])){
        foreach($match['odds']['o'] as $rate){
            echo $rate."\r\n";
        }
    }else{
        echo $match['odds']['o']."\r\n";
    }

}
die();
$json = '{
    "status": 100,
    "msg": "",
    "time": 1469319671,
    "data": {
        "league_list": [
            {
                "league_id": "1474",
                "color": "#74d174",
                "cn_short": "澳布女超",
                "cn_name": "澳洲布里斯班女子超级联赛",
                "type": "1",
                "sum_round": "10",
                "curr_round": "1",
                "curr_match_season": "2016",
                "country_name": "澳洲",
                "is_hot": "0",
                "total_match": "3"
            },
            {
                "league_id": "619",
                "color": "#8c9936",
                "cn_short": "澳维U21",
                "cn_name": "澳洲维多利亚超级联赛U21",
                "type": "1",
                "sum_round": "26",
                "curr_round": "22",
                "curr_match_season": "2016",
                "country_name": "澳洲",
                "is_hot": "0",
                "total_match": "1"
            }
        ],
        "list": [
             {
                "league_id": "140",
                "league_name": "墨西联",
                "league_ico": "http://api2.zydzuqiu.com/Public/static/noimg.png",
                "list": [
                    {
                        "match_id": "1257184",
                        "match_time": "2016-07-24 08:05:00",
                        "league_id": "140",
                        "league_name": "墨西联",
                        "kind": "1",
                        "level": "1",
                        "state": "1",
                        "home_id": "10085",
                        "home_name": "莱昂",
                        "home_score": "0",
                        "away_id": "15156",
                        "away_name": "拿加沙",
                        "away_score": "0",
                        "home_red": "0",
                        "away_red": "0",
                        "home_yellow": "0",
                        "away_yellow": "0",
                        "match_round": "2",
                        "address": "",
                        "weather_ico": "5",
                        "weather": "微雨",
                        "temperature": "16℃～17℃",
                        "is_neutral": "False",
                        "technic": {
                            "id0": {
                                "home": "",
                                "away": ""
                            },
                            "id1": {
                                "home": "",
                                "away": ""
                            },
                            "id2": {
                                "home": "",
                                "away": ""
                            },
                            "id3": {
                                "home": "1",
                                "away": "0"
                            },
                            "id4": {
                                "home": "",
                                "away": ""
                            },
                            "id5": {
                                "home": "3",
                                "away": "2"
                            },
                            "id6": {
                                "home": "",
                                "away": ""
                            },
                            "id7": {
                                "home": "",
                                "away": ""
                            },
                            "id8": {
                                "home": "",
                                "away": ""
                            },
                            "id9": {
                                "home": "0",
                                "away": "2"
                            },
                            "id10": {
                                "home": "",
                                "away": ""
                            },
                            "id11": {
                                "home": "",
                                "away": ""
                            },
                            "id12": {
                                "home": "",
                                "away": ""
                            },
                            "id13": {
                                "home": "",
                                "away": ""
                            },
                            "id14": {
                                "home": "39%",
                                "away": "61%"
                            },
                            "id15": {
                                "home": "",
                                "away": ""
                            },
                            "id16": {
                                "home": "0",
                                "away": "1"
                            },
                            "id17": {
                                "home": "",
                                "away": ""
                            },
                            "id18": {
                                "home": "",
                                "away": ""
                            },
                            "id19": {
                                "home": "",
                                "away": ""
                            },
                            "id20": {
                                "home": "",
                                "away": ""
                            },
                            "id21": {
                                "home": "",
                                "away": ""
                            },
                            "id22": {
                                "home": "",
                                "away": ""
                            },
                            "id23": {
                                "home": "",
                                "away": ""
                            },
                            "id24": {
                                "home": "",
                                "away": ""
                            },
                            "id25": {
                                "home": "",
                                "away": ""
                            },
                            "id26": {
                                "home": "",
                                "away": ""
                            },
                            "id27": {
                                "home": "",
                                "away": ""
                            },
                            "id28": {
                                "home": "",
                                "away": ""
                            },
                            "id29": {
                                "home": "",
                                "away": ""
                            },
                            "id30": {
                                "home": "",
                                "away": ""
                            },
                            "id31": {
                                "home": "",
                                "away": ""
                            },
                            "id32": {
                                "home": "",
                                "away": ""
                            },
                            "id33": {
                                "home": "",
                                "away": ""
                            },
                            "id34": {
                                "home": "",
                                "away": ""
                            },
                            "id35": {
                                "home": "",
                                "away": ""
                            },
                            "id36": {
                                "home": "",
                                "away": ""
                            },
                            "id37": {
                                "home": "",
                                "away": ""
                            },
                            "id38": {
                                "home": "",
                                "away": ""
                            },
                            "id39": {
                                "home": "",
                                "away": ""
                            },
                            "id40": {
                                "home": "",
                                "away": ""
                            }
                        },
                        "begin_home_rate": 1.52,
                        "begin_draw_rate": 3.9,
                        "begin_away_rate": 0,
                        "change_home_rate": 1.7,
                        "change_draw_rate": 3.55,
                        "change_away_rate": 4,
                        "oupei": {
                            "begin_home_rate": 1.52,
                            "begin_draw_rate": 3.9,
                            "begin_away_rate": 0,
                            "change_home_rate": 1.7,
                            "change_draw_rate": 3.55,
                            "change_away_rate": 4
                        },
                        "yapei": {
                            "begin_rate": 1.25,
                            "begin_home_rate": 1.18,
                            "begin_away_rate": 0,
                            "change_rate": 1,
                            "change_home_rate": 1.2,
                            "change_away_rate": 0.6
                        },
                        "daxiaoqiu": {
                            "begin_rate": 2.75,
                            "begin_big_rate": 0.85,
                            "begin_small_rate": 0.85,
                            "change_rate": 2.75,
                            "change_big_rate": 0.85,
                            "change_small_rate": 0.85
                        },
                        "jingcai": {
                            "home_rate": 2.75,
                            "away_rate": 0.85,
                            "draw_rate": 0.85,
                            "home_win_rate": 2.75,
                            "away_win_rate": 0.85,
                            "draw_win_rate": 0.85
                        },
                        "events": [],
                        "match_name": "",
                        "is_collect": 0,
                        "total_collect": 0
                    }
                ]
            }
        ]
    }
}';

print_r(json_decode($json, true));
die();

$input = array(12, 10, 9);

$result = array_pad($input, 5, 0);
print_r($result);
// result is array(12, 10, 9, 0, 0)

$result = array_pad($input, -7, -1);
// result is array(-1, -1, -1, -1, 12, 10, 9)
print_r($result);

$result = array_pad($input, 2, "noop");
print_r($result);
die();;
echo $tmp = 0 == "01"?1:2;
#echo MD5(microtime(true));
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