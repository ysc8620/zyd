<?php
/**
 * 获取赛程信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=match_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test

do{

    $match_list = M('match')->where(array('state'=>array('in',[0,1,2,3,4]),'update_last'=>array('lt', time()-180)))->field("match_id")->limit(300)->order('update_last ASC')->select();
    $match_list2 = [];
    $match_list3 = [];
    foreach($match_list as $match){
        $match_list2[] = $match['match_id'];
    }
    if($match_list2){

        $match_ids = join(',',$match_list2);

        $postStr = file_get_contents("http://interface.win007.com/zq/BF_XMLByID.aspx?id={$match_ids}");
        $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($obj), true);

        foreach($data['match'] as $item){
            echo $item['a']."\r\n";
            $match_list3[] = $item['a'];
            $home = explode(',',trim($item['h']));
            $away = explode(',',trim($item['i']));
            $league = explode(',', $item['c']);
            $info = [
                'match_id' => getValue($item['a']),
                'color'     => getValue($item['b']),
                'league'    => getValue($item['c']),
                'league_id' => $league[3],
                'league_name' => $league[0],
                'time'      => getValue($item['d']),
                'sub_league'      => getValue($item['e']),
                'state'     => getValue($item['f']),
                'home'      => getValue($item['h']),
                'home_name' => $home[0],
                'home_id'   => $home[3],
                'away'      => getValue($item['i']),
                'away_name' => $away[0],
                'away_id'   => $away[3],
                'home_score' => getValue($item['j']),
                'away_score' => getValue($item['k']),
                'home_half_score' => getValue($item['l']),
                'away_half_score' => getValue($item['m']),
                'home_red'   => getValue($item['n']),
                'away_red'   => getValue($item['o']),
                'home_order' => getValue($item['p']),
                'away_order' => getValue($item['q']),
                'explain'    => getValue($item['r']),
                'match_round' => getValue($item['s']),
                'address' => getValue($item['t']),
                'weather_ico' => getValue($item['u']),
                'weather' => getValue($item['v']),
                'temperature' => getValue($item['w']),
                'match_league' => getValue($item['x']),
                'group' =>getValue($item['y']),
                'is_neutral' => getValue($item['z']),
                'update_time' => time(),
                'update_last' => time(),
                'update_date' => date('Y-m-d H:i:s'),
                'last_update_event' => 'match_id_api'
            ];

            M('match')->where(array('match_id'=>$info['match_id']))->save($info);

        }

        $match_list2_ids = join(',',$match_list2);
        $match_list3_ids = join(',',$match_list3);
        if(empty($match_list3_ids)){
            $match_list3_ids = '-100';
        }
        $time = time();
        M()->execute("UPDATE t_match SET state=99,update_last='$time' WHERE match_id in({$match_list2_ids}) AND match_id not in({$match_list3_ids})");
    }


}while(false);
echo "ok";