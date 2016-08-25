<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class LiveController extends BaseApiController {
    public function index2(){
        $json = $this->simpleJson();
        do{
            $league_idsstr = I('request.league_ids','','strval');
            $where = [];
            $user_id = intval($this->user['id']);

            $league_ids_list = explode(',',$league_idsstr);
            $league_ids = [];
            foreach($league_ids_list as $league_id){
                if(empty($league_id)){continue;}
                $league_ids[] = $league_id;
            }

            // 进行中
            $where['state'] = array('in',array(1,2,3,4));
            if($league_ids){
                $where['league_id'] = array('in', $league_ids);
            }
            $total = M('match')->where($where)->count();
            $list = M('match')->field
            ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
            away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,address,weather_ico,weather,temperature,is_neutral,technic,total_collect,total_tuijian'
            )->where($where)->order("time ASC")->select();

            foreach($list as $i=>$match){
                // 赛事统计
                $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
                $list[$i]['technic'] = [];

                $list[$i]['technic']['id3']['home'] = isset($technic['id3'])?($technic['id3']['home']):"0";
                $list[$i]['technic']['id3']['away'] = isset($technic['id3'])?($technic['id3']['away']):"0";
                $list[$i]['technic']['id4']['home'] = isset($technic['id4'])?($technic['id4']['home']):"0";
                $list[$i]['technic']['id4']['away'] = isset($technic['id4'])?($technic['id4']['away']):"0";
                $list[$i]['technic']['id6']['home'] = isset($technic['id6'])?($technic['id6']['home']):"0";
                $list[$i]['technic']['id6']['away'] = isset($technic['id6'])?($technic['id6']['away']):"0";
                $list[$i]['technic']['id14']['home'] = isset($technic['id14'])?($technic['id14']['home']):"0";
                $list[$i]['technic']['id14']['away'] = isset($technic['id14'])?($technic['id14']['away']):"0";

                // 亚赔
                $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);
                $list[$i]['yapei']['begin_rate'] = $yapei['begin_rate'];
                $list[$i]['yapei']['begin_home_rate'] = $yapei['begin_home_rate'];
                $list[$i]['yapei']['begin_away_rate'] = $yapei['begin_away_rate'];
                $list[$i]['yapei']['change_rate'] = $yapei['change_rate'];
                $list[$i]['yapei']['change_home_rate'] = $yapei['change_home_rate'];
                $list[$i]['yapei']['change_away_rate'] = $yapei['change_away_rate'];

                // 标准
                $biaozhun = get_rate($match['match_id'],'oupei',$match['state']);
                $list[$i]['begin_rate'] = $yapei['begin_rate'];
                $list[$i]['begin_home_rate'] = $yapei['begin_home_rate'];
                $list[$i]['begin_away_rate'] = $yapei['begin_away_rate'];
                $list[$i]['change_rate'] = $yapei['change_rate'];
                $list[$i]['change_home_rate'] = $yapei['change_home_rate'];
                $list[$i]['change_away_rate'] = $yapei['change_away_rate'];

                // 欧赔
                $list[$i]['oupei']['begin_home_rate'] =  $biaozhun['begin_home_rate'];
                $list[$i]['oupei']['begin_draw_rate'] = $biaozhun['begin_draw_rate'];
                $list[$i]['oupei']['begin_away_rate'] = $biaozhun['begin_away_rate'];
                $list[$i]['oupei']['change_home_rate'] = $biaozhun['change_home_rate'];
                $list[$i]['oupei']['change_draw_rate'] = $biaozhun['change_draw_rate'];
                $list[$i]['oupei']['change_away_rate'] = $biaozhun['change_away_rate'];

                // 大小球
                $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);
                $list[$i]['daxiaoqiu']['begin_rate'] = $daxiaoqiu['begin_rate'];
                $list[$i]['daxiaoqiu']['begin_big_rate'] = $daxiaoqiu['begin_big_rate'];
                $list[$i]['daxiaoqiu']['begin_small_rate'] = $daxiaoqiu['begin_small_rate'];
                $list[$i]['daxiaoqiu']['change_rate'] = $daxiaoqiu['change_rate'];
                $list[$i]['daxiaoqiu']['change_big_rate'] = $daxiaoqiu['change_big_rate'];
                $list[$i]['daxiaoqiu']['change_small_rate'] = $daxiaoqiu['change_small_rate'];

                // 竟彩
                $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
                $list[$i]['jingcai']['home_rate'] = $jingcai['home_rate'];
                $list[$i]['jingcai']['away_rate'] = $jingcai['away_rate'];
                $list[$i]['jingcai']['draw_rate'] = $jingcai['draw_rate'];
                $list[$i]['jingcai']['home_win_rate'] = $jingcai['home_win_rate'];
                $list[$i]['jingcai']['away_win_rate'] = $jingcai['away_win_rate'];
                $list[$i]['jingcai']['draw_win_rate'] = $jingcai['draw_win_rate'];

                // 直播事件
                $event_list = M('event')->where(array('match_id'=>$match['match_id'], 'event_type'=>1))->order("time DESC")->select();
                $list[$i]['events'] = (array)$event_list;
                $jingcai_info = M('jingcai')->where(array('match_id'=>$match['match_id']))->find();
                $match_name = "";
                if($jingcai_info){
                    $match_name = getWeekName($jingcai_info['date']).$jingcai_info['match_no'];
                }
                $list[$i]['match_name'] = $match_name;

                $zoudi = M('zoudi')->where(array('match_id'=>$match['match_id']))->order("id DESC")->find();
                if(!$zoudi){
                    unset($list[$i]);
                    continue;
                }
                $zoudi['time'] = str_replace('分','',$zoudi['time']);

                $list[$i]['match_time2'] = is_numeric($zoudi['time'])?$zoudi['time']."'":$zoudi['time'];
                if($zoudi['time'] == '全场'){
                    $list[$i]['match_time2'] = -1;
                }

                $list[$i]['state_name'] = getMatchStatus($match['state']);

                //
                $list[$i]['is_collect'] = 0;
                if($user_id){
                    $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match['match_id']))->find();
                    if($collect){
                        $list[$i]['is_collect'] = 1;
                    }
                }
            }

            $data = [];
            foreach($list as $match){
                if(empty($match)){continue;}
                $key = 'i'.date("dHi",strtotime($match['match_time'])).$match['league_id'];
                $data[$key]['league_id'] = $match['league_id'];
                $data[$key]['league_name'] = $match['league_name'];
                $data[$key]['league_ico'] = C('BASE_URL').'Public/static/noimg.png';
                $data[$key]['list'][] = $match;
            }
            // $json['ngss'] = $data;
            $newdata = [];
            foreach($data as $item){
                $newdata[] = $item;
            }

            $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state in(1,2,3,4)")
                ->field('l.league_id,l.color,l.cn_short,l.cn_name,l.type,l.sum_round,l.curr_round,l.curr_match_season,l.country_name,l.is_hot,count(*) as total_match')
                ->group('m.league_id')
                ->order("is_hot DESC, weight DESC")->select();

            foreach($league_list as $i=>$league){
                $league_list[$i]['index_name'] =Getzimu($league['cn_name']);
            }

            $json['data']['league_list'] = (array)$league_list;
            $json['data']['list'] = (array)$newdata;
            $json['data']['total'] = $total;
            $json['data']['league_ids'] = $league_idsstr;

        }while(false);
        $this->ajaxReturn($json);
    }


    /**
     * 返回直播赛事信息
    */
    public function index(){
        $json = $this->simpleJson();
        $list = M("match")->where(array('state'=>array('in',[1,2,3,4])))->order("asc")->field("match_id,league_id,league_name,home_id,home_name,home_score,away_id,away_name,home_score,away_score,home_half_score,away_half_score,home_red,away_red,time,time as match_time,technic,state,total_collect,total_tuijian")->select();

        $user_id = intval($this->user['id']);

        foreach($list as $i=>$item){
            //
            $zoudi = M('zoudi')->where(array('match_id'=>$item['match_id']))->order("id DESC")->find();
            if(!$zoudi){
                unset($list[$i]);
                continue;
            }
            $zoudi['time'] = str_replace('分','',$zoudi['time']);

            $list[$i]['match_time2'] = is_numeric($zoudi['time'])?$zoudi['time']."'":$zoudi['time'];
            if($zoudi['time'] == '全场'){
                $list[$i]['match_time2'] = -1;
            }

            $data = json_decode($item['technic'], true);
            $list[$i]['home_corner'] = isset($data['id6']['home'])?$data['id6']['home']:"0";
            $list[$i]['away_corner'] = isset($data['id6']['away'])?$data['id6']['away']:"0";
            unset($list[$i]['technic']);

            // 标准
            $baiou = get_rate($item['match_id'],'rangqiu',$item['state']);
            $list[$i]['begin_home_rate'] = "{$baiou['begin_home_rate']}";
            $list[$i]['begin_rate'] = "{$baiou['begin_rate']}";
            $list[$i]['begin_away_rate'] = "{$baiou['begin_away_rate']}";
            $list[$i]['change_home_rate'] = "{$baiou['change_home_rate']}";
            $list[$i]['change_rate'] = "{$baiou['change_rate']}";
            $list[$i]['change_away_rate'] = "{$baiou['change_away_rate']}";

            $list[$i]['state_name'] = getMatchStatus($item['state']);

            //
            $list[$i]['is_collect'] = 0;
            if($user_id){
                $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$item['match_id']))->find();
                if($collect){
                    $list[$i]['is_collect'] = 1;
                }
            }

            $event_list = M('event')->where(array('match_id'=>$item['match_id'], 'event_type'=>1))->order("time DESC")->select();
            $list[$i]['events'] = (array)$event_list;

            $jingcai_info = M('jingcai')->where(array('match_id'=>$item['match_id']))->find();
            $match_name = "";
            if($jingcai_info){
                $match_name = getWeekName($jingcai_info['date']).$jingcai_info['match_no'];
            }
            $list[$i]['match_name'] = $match_name;
        }
        $json['data'] = $list;
        $this->ajaxReturn($json);
    }

    /**
     * 单独赛事直播信息
     */
    public function match(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id',0,'intval');
            if(empty($match_id)){
                $json['status'] = 100;
                $json['msg'] = '找不到赛事信息';
                break;
            }
            // 验证登陆
           // $this->check_login();
            $match = M('match')->where(array('match_id'=>$match_id))->find();
            $tuijian['match_id'] = $match_id;
            $tuijian['league_id'] = $match['league_id'];
            $tuijian['league_name'] = $match['league_name'];
            $tuijian['home_name'] = $match['home_name'];
            $tuijian['away_name'] = $match['away_name'];
            $tuijian['match_time'] = $match['time'];
            $tuijian['home_score'] = $match['home_score'];
            $tuijian['away_score'] = $match['away_score'];
            $tuijian['total_collect'] = $match['total_collect'];
            $tuijian['total_tuijian'] = $match['total_tuijian'];
            $tuijian['state'] = $match['state'];

            // 赛事统计
            $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
            $tuijian['technic'] = [];
            $tuijian['technic']['id3']['home'] = isset($technic['id3'])?($technic['id3']['home']):0;
            $tuijian['technic']['id3']['away'] = isset($technic['id3'])?($technic['id3']['away']):0;
            $tuijian['technic']['id4']['home'] = isset($technic['id4'])?($technic['id4']['home']):0;
            $tuijian['technic']['id4']['away'] = isset($technic['id4'])?($technic['id4']['away']):0;
            $tuijian['technic']['id6']['home'] = isset($technic['id6'])?($technic['id6']['home']):0;
            $tuijian['technic']['id6']['away'] = isset($technic['id6'])?($technic['id6']['away']):0;
            $tuijian['technic']['id14']['home'] = isset($technic['id14'])?($technic['id14']['home']):0;
            $tuijian['technic']['id14']['away'] = isset($technic['id14'])?($technic['id14']['away']):0;


            if($match['state'] == '0'){
                $tuijian['match_time2'] = '未开';
            }elseif(in_array($match['state'],[1,2,3,4])){
//                $match['match_time2'] = floor(time() - strtotime($match['match_time'])/60);
                $zoudi = M('zoudi')->where(array('match_id'=>$match['match_id']))->order("id DESC")->find();
                $zoudi['time'] = str_replace('分','',$zoudi['time']);
                $tuijian['match_time2'] = is_numeric($zoudi['time'])?$zoudi['time']."'":$zoudi['time'];
            }elseif($match['state'] == -1){
                $tuijian['match_time2'] = '完场';
            }elseif($match['state'] == -10){
                $tuijian['match_time2'] = '取消';
            }elseif($match['state'] == -13){
                $tuijian['match_time2'] = '中断';
            }elseif($match['state'] == -12){
                $tuijian['match_time2'] = '腰斩';
            }elseif($match['state'] == -11){
                $tuijian['match_time2'] = '待定';
            }

            $tuijian['state_name'] = getMatchStatus($match['state']);

            if(in_array($tuijian['state'],[0,1,2,3,4])){
                $tuijian['status'] = 1;
            }else{
                $tuijian['status'] = 0;
            }
            // 标准
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);
            $tuijian['begin_home_rate'] = $yapei['begin_home_rate'];
            $tuijian['begin_rate'] = $yapei['begin_rate'];
            $tuijian['begin_away_rate'] = $yapei['begin_away_rate'];
            $tuijian['change_home_rate'] = $yapei['change_home_rate'];
            $tuijian['change_rate'] = $yapei['change_rate'];
            $tuijian['change_away_rate'] = $yapei['change_away_rate'];

            // 竞彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $tuijian['jingcai'] = ["rate_1"=>$jingcai['home_win_rate'], "rate_2"=>$jingcai['draw_win_rate'], "rate_3"=>$jingcai['away_win_rate']];
            // 竞彩让球

            // 亚赔
            $tuijian['rangqiu'] = ["rate_1"=>$yapei['change_home_rate'], "rate_2"=>$yapei['change_rate'], "rate_3"=>$yapei['change_away_rate']];

            // 欧赔
            $baiou = get_rate($match['match_id'],'oupei',$match['state']);//`change_rate`, `change_home_rate`, `change_away_rate`
            $tuijian['oupei'] = ["rate_1"=>$baiou['change_home_rate'], "rate_2"=>$baiou['change_draw_rate'], "rate_3"=>$baiou['change_away_rate']];

            // 大小球
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);//`change_rate`, `change_big_rate`, `change_small_rate`
            $tuijian['daxiaoqiu'] = ["rate_1"=>$daxiaoqiu['change_big_rate'], "rate_2"=>$daxiaoqiu['change_rate'], "rate_3"=>$daxiaoqiu['change_small_rate']];

            $peilv = get_rate_list($match['match_id'],$match['time']);
            $tuijian['live_oupei'] = $peilv['oupei'];
            $tuijian['live_rangqiu'] = $peilv['rangqiu'];
            $tuijian['live_daxiaoqiu'] = $peilv['daxiaoqiu'];

            $peilv = get_half_rate_list($match['match_id']);
            $tuijian['live_oupei_half'] = $peilv['oupei'];
            $tuijian['live_rangqiu_half'] = $peilv['rangqiu'];
            $tuijian['live_daxiaoqiu_half'] = $peilv['daxiaoqiu'];

            $event_list = M('event')->where(array('match_id'=>$match_id))->order("time ASC")->select();
            foreach($event_list as $i=>$item){
                if(!in_array($item['event_type'],[1,2,3,7,8])){
                    unset($event_list[$i]);
                }
            }
            $event_list = (array)$event_list;
            $new_event_list = [];
            foreach($event_list as $i=>$item){
                $new_event_list[] = $item;
            }
            $tuijian['events'] = $new_event_list;
            $json['data'] = $tuijian;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 赛事直播推荐
     */
    public function tuijian(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id',0,'intval');
            if(empty($match_id)){
                $json['status'] = 100;
                $json['msg'] = '找不到赛事信息';
                break;
            }
            // 验证登陆
            $this->check_login();
            $match = M('match')->where(array('match_id'=>$match_id))->find();
            $tuijian['match_id'] = $match_id;
            $tuijian['league_id'] = $match['league_id'];
            $tuijian['league_name'] = $match['league_name'];
            $tuijian['home_name'] = $match['home_name'];
            $tuijian['away_name'] = $match['away_name'];
            $tuijian['match_time'] = $match['time'];
            $tuijian['home_score'] = $match['home_score'];
            $tuijian['away_score'] = $match['away_score'];
            $tuijian['total_collect'] = $match['total_collect'];
            $tuijian['total_tuijian'] = $match['total_tuijian'];

            $tuijian['state'] = $match['state'];
            if(in_array($tuijian['state'],[0,1,2,3,4])){
                $tuijian['status'] = 1;
            }else{
                $tuijian['status'] = 0;
            }

            // 一分钟内有进球 禁止发布推荐
            $event = M('events')->where(array('match_id'=>$match['match_id'], 'event_type'=>1))->order("id DESC")->find();
            if($event['create_time'] > time()-60){
                $tuijian['status'] = 0;
            }

            // 走地封盘
            $zoudi = M('zoudi')->where(array('match_id'=>$match['match_id']))->order("id DESC")->find();
            if($zoudi['type']  == 3){
                $tuijian['status'] = 0;
            }

            // 标准
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);
            $tuijian['begin_home_rate'] = $yapei['begin_home_rate'];
            $tuijian['begin_rate'] = $yapei['begin_rate'];
            $tuijian['begin_away_rate'] = $yapei['begin_away_rate'];
            $tuijian['change_home_rate'] = $yapei['change_home_rate'];
            $tuijian['change_rate'] = $yapei['change_rate'];
            $tuijian['change_away_rate'] = $yapei['change_away_rate'];

            //
            $jingcai_info = M('jingcai')->where(array('match_id'=>$match_id))->find();
            $jingcai_state = 1;

            // 只有对应的竞彩才能发布
            if(!$jingcai_info){
                $jingcai_state = 0;
            }

            // 竞彩只能赛前推荐
            if($match['state'] != '0'){
                $jingcai_state = 0;
            }

            // 竞彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $jingcai2 = get_rate($match['match_id'],'jingcai_rangqiu',$match['state']);

            if($jingcai2['home_win_rate'] == '0' && $jingcai2['draw_win_rate'] == '0' && $jingcai2['away_win_rate'] == '0'){
                $jingcai_state = 0;
            }
            $tuijian['jingcai'] = ["rate_4"=>$jingcai['home_win_rate'], "rate_5"=>$jingcai['draw_win_rate'], "rate_6"=>$jingcai['away_win_rate'],"status"=>$jingcai_state];

            // 竞彩让球

            $tuijian['jingcai_rangqiu'] = ["rate_1"=>$jingcai2['home_win_rate'], "rate_2"=>$jingcai2['draw_win_rate'], "rate_3"=>$jingcai2['away_win_rate'],"left_ball"=>$jingcai['left_ball'],"status"=>$jingcai_state];

            $yapei_state = 1;
            // 亚赔
            if($yapei['change_home_rate'] == '0' &&$yapei['change_rate'] == '0' &&$yapei['change_away_rate'] == '0'){
                $yapei_state = 0;
            }
            $tuijian['rangqiu'] = ["rate_1"=>$yapei['change_home_rate'], "rate_2"=>$yapei['change_rate'], "rate_3"=>$yapei['change_away_rate'],"status"=>$yapei_state];


            // 亚赔半场
            $yapei = get_rate($match['match_id'],'rangqiu_half',$match['state']);
            $yapei_half_state = 1;
            if($yapei['change_home_rate'] == '0' &&$yapei['change_rate'] == '0' &&$yapei['change_away_rate'] == '0'){
                $yapei_half_state = 0;
            }
            if($match['state'] != '0' && $match['state'] != 1){
                $yapei_half_state = 0;
            }

            $tuijian['rangqiu_half'] = ["rate_4"=>$yapei['change_home_rate'], "rate_5"=>$yapei['change_rate'], "rate_6"=>$yapei['change_away_rate'],"status"=>$yapei_half_state];

            // 欧赔
            $baiou = get_rate($match['match_id'],'oupei',$match['state']);
            $oupe_state = 1;
            if($baiou['change_home_rate'] == '0' && $baiou['change_draw_rate'] == '0' || $baiou['change_away_rate'] == '0'){
                $oupe_state = 0;
            }

            $tuijian['oupei'] = ["rate_1"=>$baiou['change_home_rate'], "rate_2"=>$baiou['change_draw_rate'], "rate_3"=>$baiou['change_away_rate'],"status"=>$oupe_state];
            // 欧赔半场
            $baiou = get_rate($match['match_id'],'oupei_half',$match['state']);
            $oupei_half_state = 1;
            if($baiou['change_home_rate'] == '0' &&$baiou['change_draw_rate'] == '0' &&$baiou['change_away_rate'] == '0'){
                $oupei_half_state = 0;
            }
            if($match['state'] != '0' && $match['state'] != 1){
                $oupei_half_state = 0;
            }
            $tuijian['oupei_half'] = ["rate_4"=>$baiou['change_home_rate'], "rate_5"=>$baiou['change_draw_rate'], "rate_6"=>$baiou['change_away_rate'],"status"=>$oupei_half_state];

            // 大小球
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);//`change_rate`, `change_big_rate`, `change_small_rate`
            $daxiaqiu_state = 1;
            if($daxiaoqiu['change_big_rate'] == '0' && $daxiaoqiu['change_rate'] == '0' || $daxiaoqiu['change_small_rate'] == '0'){
                $daxiaqiu_state = 0;
            }
            $tuijian['daxiaoqiu'] = ["rate_1"=>$daxiaoqiu['change_big_rate'], "rate_2"=>$daxiaoqiu['change_rate'], "rate_3"=>$daxiaoqiu['change_small_rate'],"status"=>$daxiaqiu_state];

            // 大小球半场
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu_half',$match['state']);
            $daxiaoqiu_half_state = 1;
            if($daxiaoqiu['change_big_rate'] == '0' &&$daxiaoqiu['change_rate'] == '0' &&$daxiaoqiu['change_small_rate'] == '0'){
                $daxiaoqiu_half_state = 0;
            }
            if($match['state'] != '0' && $match['state'] != 1){
                $daxiaoqiu_half_state = 0;
            }
            $tuijian['daxiaoqiu_half'] = ["rate_4"=>$daxiaoqiu['change_big_rate'], "rate_5"=>$daxiaoqiu['change_rate'], "rate_6"=>$daxiaoqiu['change_small_rate'],"status"=>$daxiaoqiu_half_state];

            $json['data'] = $tuijian;
        }while(false);
        $this->ajaxReturn($json);
    }
}