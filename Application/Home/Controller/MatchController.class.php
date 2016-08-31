<?php
namespace Home\Controller;
use \Think\Page;

class MatchController extends BaseApiController {
    /**
     * $data = [
     *      'total' => 100,
     *      'pages' => 10,
     *       'page' => 1,
     *       'limit'=> 10,
     *      'list'=>[
     *      [
     *      ],
     *      []
     *      ]
     * ];
     */
    public function index(){
        $json = $this->simpleJson();
        //$mongo = $this->initMongo();
        do{
            $p = I('request.p', 1,'intval');
            $type = I('request.type',1,'strval');// 1进行中， 2已完成，3为开始，4个人收藏
            $league_idsstr = I('request.league_ids','','strval');
            $date = I('request.date','','trim');
            $limit = I('request.limit',10,'intval');
            $where = [];

            $user_id = intval($this->user['id']);

            $league_ids_list = explode(',',$league_idsstr);
            $league_ids = [];
            foreach($league_ids_list as $league_id){
                if(empty($league_id)){continue;}
                $league_ids[] = $league_id;
            }

            // 进行中
            if($type == 1){
                $where['state'] = array('in',array(1,2,3,4));
                if($league_ids){
                    $where['league_id'] = array('in', $league_ids);
                }
                $total = M('match')->where($where)->count();
                $list = M('match')->field
                ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,technic,total_collect,total_tuijian'
                )->where($where)->order("time ASC")->select();

            }elseif($type == 2){

                if($date){
                    $where2 = "state = -1 AND date(time) = '{$date}'";
                }else{
                    $where2 = "state = -1";
                }
                $total = M('match')->where($where2)->count();
                $Page = new Page($total, $limit);
                $list = M('match')->field
                ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,technic,total_collect,total_tuijian'
                )->where($where2)->order("time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            }elseif($type == 3){


                if($date){
                    $where2 = "state = 0 AND date(time) = '{$date}'";
                }else{
                    $where2 = "state = 0 ";
                }
                $total = M('match')->where($where2)->count();
                $Page = new Page($total, $limit);
                $list = M('match')->field
                ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,technic,total_collect,total_tuijian'
                )->where($where2)->order("time ASC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                #$json['sql'] = M()->getLastSql();

            }else{
                // 验证登录
                $this->check_login();
                //
                if($date){
                    $where2 = " AND m.state < 99 AND date(m.time) = '{$date}'";
                }else{
                    $where2 = " AND m.state < 99 ";
                }
                $total = M()->table(C('DB_PREFIX').'match as m, '.C('DB_PREFIX').'match_follow as mf')->where("mf.user_id=$user_id AND m.match_id = mf.match_id ".$where2)->count();
                $Page = new Page($total, $limit);
                $list =  M()->table(C('DB_PREFIX').'match as m, '.C('DB_PREFIX').'match_follow as mf')->where("mf.user_id=$user_id AND m.match_id = mf.match_id ".$where2)->field
                ('m.match_id,m.time as match_time,m.league_id,m.league_name,m.kind,m.level,m.state,m.home_id,m.home_name,m.home_score,m.away_id,
                m.away_name,m.away_score,m.home_red,m.away_red,m.home_yellow,m.away_yellow,m.match_round,m.technic,m.total_collect,total_tuijian'
                )->order("m.time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            }

            foreach($list as $i=>$match){
                //
                //$list[$i]['technic'] = empty($match['technic'])?[]:json_decode($match['technic'], true);
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
                $event_list = M('event')->where(array('match_id'=>$match['match_id'], 'event_type'=>1))->field('id, match_id, is_home_away, event_type, time')->order("time DESC")->select();
                $list[$i]['events'] = (array)$event_list;
                $jingcai_info = M('jingcai')->where(array('match_id'=>$match['match_id']))->find();
                $match_name = "";
                if($jingcai_info){
                    $match_name = getWeekName($jingcai_info['date']).$jingcai_info['match_no'];
                }
                $list[$i]['match_name'] = $match_name;

                // 比赛状态 0:未开,1:上半场,2:中场,3:下半场,4,加时，-11:待定,-12:腰斩,-13:中断,-14:推迟,-1:完场，-10取消
                if($match['state'] == '0'){
                    $list[$i]['match_time2'] = '未开';
                }elseif(in_array($match['state'],[1,2,3,4])){
                    $zoudi = M('zoudi')->where(array('match_id'=>$match['match_id']))->field('id,time')->order("zoudi_id DESC")->find();
                    if(!$zoudi){
                        unset($list[$i]);
                        continue;
                    }
                    $zoudi['time'] = str_replace('分','',$zoudi['time']);

                    $list[$i]['match_time2'] = is_numeric($zoudi['time'])?$zoudi['time']."'":$zoudi['time'];
                    if($zoudi['time'] == '全场'){
                        $list[$i]['match_time2'] = -1;
                    }
//                    if($match['state'] == 1){
//                        $list[$i]['match_time2'] = floor((time() - strtotime($match['match_time']))/60)."'";
//                    }elseif($match['state'] == 2){
//                        $list[$i]['match_time2'] = "45'";
//                    }else{
//                        $list[$i]['match_time2'] = floor((time() - strtotime($match['match_time'])-900)/60)."'";
//                    }

                }elseif($match['state'] == -1){
                    $list[$i]['match_time2'] = '完场';
                }elseif($match['state'] == -10){
                    $list[$i]['match_time2'] = '取消';
                }elseif($match['state'] == -13){
                    $list[$i]['match_time2'] = '中断';
                }elseif($match['state'] == -12){
                    $list[$i]['match_time2'] = '腰斩';
                }elseif($match['state'] == -11){
                    $list[$i]['match_time2'] = '待定';
                }

                $list[$i]['state_name'] = getMatchStatus($match['state']);

                //
                $list[$i]['is_collect'] = 0;
                if($user_id){
                    $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match['match_id']))->field('id')->find();
                    if($collect){
                        $list[$i]['is_collect'] = 1;
                    }
                }
            }

            $data = [];
            foreach($list as $match){
                if($type == 1){
                    if(empty($match)){continue;}
                    $key = 'i'.date("dHi",strtotime($match['match_time'])).$match['league_id'];
                    $data[$key]['league_id'] = $match['league_id'];
                    $data[$key]['league_name'] = $match['league_name'];
                    $data[$key]['league_ico'] = C('BASE_URL').'Public/static/noimg.png';
                    $data[$key]['list'][] = $match;

                }else{
                    $data[$match['league_id']]['league_id'] = $match['league_id'];
                    $data[$match['league_id']]['league_name'] = $match['league_name'];
                    $data[$match['league_id']]['league_ico'] = C('BASE_URL').'Public/static/noimg.png';
                    $data[$match['league_id']]['list'][] = $match;
                }
            }
            // $json['ngss'] = $data;
            $newdata = [];
            foreach($data as $item){
                $newdata[] = $item;
            }
            // league_list
            $league_list = [];
            // if($page)
            if($type == 1){
                $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state in(1,2,3,4)")
                    ->field('l.league_id,l.cn_short,l.cn_name,l.type,l.is_hot,count(*) as total_match')
                    ->group('m.league_id')
                    ->order("is_hot DESC, weight DESC")->select();
            }elseif($type == 2){
                $league_list = [];
            }elseif($type == 3){
                $league_list = [];
            }else{
                $league_list = [];
            }

            foreach($league_list as $i=>$league){
                $league_list[$i]['index_name'] =Getzimu($league['cn_name']);
            }

            $json['data']['league_list'] = (array)$league_list;
            $json['data']['list'] = (array)$newdata;
            $json['data']['total'] = $total;
            $json['data']['page'] = $p;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
            $json['data']['date'] = $date;
            $json['data']['league_ids'] = $league_idsstr;

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 比赛详情
     */
    public function info(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id', 0,'intval');
            if(empty($match_id)){
                $json['status'] = 110;
                $json['msg'] = '请选择查看赛事';
                break;
            }

            $user_id = intval($this->user['id']);

            $match = M('match')->field
            ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,technic,total_collect,total_tuijian'
            )
                ->where(array('match_id'=>$match_id))->find();

            // 赛事统计
            $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
            $match['technic'] = [];
            $match['technic']['id3']['home'] = isset($technic['id3'])?($technic['id3']['home']):0;
            $match['technic']['id3']['away'] = isset($technic['id3'])?($technic['id3']['away']):0;
            $match['technic']['id4']['home'] = isset($technic['id4'])?($technic['id4']['home']):0;
            $match['technic']['id4']['away'] = isset($technic['id4'])?($technic['id4']['away']):0;
            $match['technic']['id6']['home'] = isset($technic['id6'])?($technic['id6']['home']):0;
            $match['technic']['id6']['away'] = isset($technic['id6'])?($technic['id6']['away']):0;
            $match['technic']['id14']['home'] = isset($technic['id14'])?($technic['id14']['home']):0;
            $match['technic']['id14']['away'] = isset($technic['id14'])?($technic['id14']['away']):0;

            // 标准

            // 亚赔
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);
            $match['yapei']['begin_rate'] = $yapei['begin_rate'];
            $match['yapei']['begin_home_rate'] = $yapei['begin_home_rate'];
            $match['yapei']['begin_away_rate'] = $yapei['begin_away_rate'];
            $match['yapei']['change_rate'] = $yapei['change_rate'];
            $match['yapei']['change_home_rate'] = $yapei['change_home_rate'];
            $match['yapei']['change_away_rate'] = $yapei['change_away_rate'];

            $match['begin_rate'] = $yapei['begin_rate'];
            $match['begin_home_rate'] = $yapei['begin_home_rate'];
            $match['begin_away_rate'] = $yapei['begin_away_rate'];

            $match['change_rate'] = $yapei['change_rate'];
            $match['change_home_rate'] = $yapei['change_home_rate'];
            $match['change_away_rate'] = $yapei['change_away_rate'];

            // 欧赔
            $baiou = get_rate($match['match_id'],'oupei',$match['state']);
            $match['oupei']['begin_home_rate'] = $baiou['begin_home_rate'];
            $match['oupei']['begin_draw_rate'] = $baiou['begin_draw_rate'];
            $match['oupei']['begin_away_rate'] = $baiou['begin_away_rate'];
            $match['oupei']['change_home_rate'] = $baiou['change_home_rate'];
            $match['oupei']['change_draw_rate'] = $baiou['change_draw_rate'];
            $match['oupei']['change_away_rate'] = $baiou['change_away_rate'];


            // 大小球
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);
            $match['daxiaoqiu']['begin_rate'] = $daxiaoqiu['begin_rate'];
            $match['daxiaoqiu']['begin_big_rate'] = $daxiaoqiu['begin_big_rate'];
            $match['daxiaoqiu']['begin_small_rate'] = $daxiaoqiu['begin_small_rate'];
            $match['daxiaoqiu']['change_rate'] = $daxiaoqiu['change_rate'];
            $match['daxiaoqiu']['change_big_rate'] = $daxiaoqiu['change_big_rate'];
            $match['daxiaoqiu']['change_small_rate'] = $daxiaoqiu['change_small_rate'];

            // 竟彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $match['jingcai']['home_rate'] = $jingcai['home_rate'];
            $match['jingcai']['away_rate'] = $jingcai['away_rate'];
            $match['jingcai']['draw_rate'] = $jingcai['draw_rate'];
            $match['jingcai']['home_win_rate'] = $jingcai['home_win_rate'];
            $match['jingcai']['away_win_rate'] = $jingcai['away_win_rate'];
            $match['jingcai']['draw_win_rate'] = $jingcai['draw_win_rate'];


            $peilv = get_rate_list($match['match_id'],$match['match_time']);
            $match['live_oupei'] = $peilv['oupei'];
            $match['live_rangqiu'] = $peilv['rangqiu'];
            $match['live_daxiaoqiu'] = $peilv['daxiaoqiu'];

            $peilv = get_half_rate_list($match['match_id']);
            $match['live_oupei_half'] = $peilv['oupei'];
            $match['live_rangqiu_half'] = $peilv['rangqiu'];
            $match['live_daxiaoqiu_half'] = $peilv['daxiaoqiu'];

            // 直播事件
            $event_list = M('event')->where(array('match_id'=>$match['match_id']))->field('id, match_id, is_home_away, event_type, time')->order("time ASC")->select();
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
            $match['events'] = $new_event_list;
            $jingcai_info = M('jingcai')->where(array('match_id'=>$match['match_id']))->find();
            $match_name = "";
            if($jingcai_info){
                $match_name = getWeekName($jingcai_info['date']).$jingcai_info['match_no'];
            }
            $match['match_name'] = $match_name;

            if($match['state'] == '0'){
                $match['match_time2'] = '未开';
            }elseif(in_array($match['state'],[1,2,3,4])){
//                $match['match_time2'] = floor(time() - strtotime($match['match_time'])/60);
                $zoudi = M('zoudi')->where(array('match_id'=>$match['match_id']))->field('id,time')->order("zoudi_id DESC")->find();
                $zoudi['time'] = str_replace('分','',$zoudi['time']);

                $match['match_time2'] = is_numeric($zoudi['time'])?$zoudi['time']."'":$zoudi['time'];
                if($zoudi['time'] == '全场'){
                    $match['match_time2'] = -1;
                }
            }elseif($match['state'] == -1){
                $match['match_time2'] = '完场';
            }elseif($match['state'] == -10){
                $match['match_time2'] = '取消';
            }elseif($match['state'] == -13){
                $match['match_time2'] = '中断';
            }elseif($match['state'] == -12){
                $match['match_time2'] = '腰斩';
            }elseif($match['state'] == -11){
                $match['match_time2'] = '待定';
            }

            $match['state_name'] = getMatchStatus($match['state']);


            $match['is_collect'] = 0;
            if($user_id){
                $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match['match_id']))->field('id')->find();
                if($collect){
                    $match['is_collect'] = 1;
                }
            }
            $json['data'] = $match;

        }while(false);
        $this->ajaxReturn($json);
    }


    /**
     * 获取赛事竞猜
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
            $tuijian['state'] = $match['state'];
            if(in_array($tuijian['state'],[0,1,2,3,4])){
                $tuijian['status'] = 1;
            }else{
                $tuijian['status'] = 0;
            }
            // 标准
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);
            $tuijian['begin_rate'] = $yapei['begin_rate'];
            $tuijian['begin_draw_rate'] = $yapei['begin_draw_rate'];
            $tuijian['begin_away_rate'] = $yapei['begin_away_rate'];
            $tuijian['change_rate'] = $yapei['change_rate'];
            $tuijian['change_draw_rate'] = $yapei['change_draw_rate'];
            $tuijian['change_away_rate'] = $yapei['change_away_rate'];

            // 竞彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $tuijian['jingcai'] = ["rate_1"=>$jingcai['home_win_rate'], "rate_2"=>$jingcai['draw_win_rate'], "rate_3"=>$jingcai['away_win_rate']];
            // 竞彩让球
            $jingcai = get_rate($match['match_id'],'jingcai_rangqiu',$match['state']);
            $tuijian['jingcai_rangqiu'] = ["rate_4"=>$jingcai['home_win_rate'], "rate_5"=>$jingcai['draw_win_rate'], "rate_6"=>$jingcai['away_win_rate']];
            // 亚赔
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);//`change_rate`, `change_home_rate`, `change_away_rate`
            $tuijian['rangqiu'] = ["rate_1"=>$yapei['change_home_rate'], "rate_2"=>$yapei['change_rate'], "rate_3"=>$yapei['change_away_rate']];
            // 亚赔半场
            $yapei = get_rate($match['match_id'],'rangqiu_half',$match['state']);
            $tuijian['rangqiu_half'] = ["rate_4"=>$yapei['change_home_rate'], "rate_5"=>$yapei['change_rate'], "rate_6"=>$yapei['change_away_rate']];

            // 欧赔
            $baiou = get_rate($match['match_id'],'oupei',$match['state']);
            $tuijian['oupei'] = ["rate_1"=>$baiou['change_home_rate'], "rate_2"=>$baiou['change_draw_rate'], "rate_3"=>$baiou['change_away_rate']];
            // 欧赔半场
            $baiou = get_rate($match['match_id'],'oupei_half',$match['state']);
            $tuijian['oupei_half'] = ["rate_4"=>$baiou['change_home_rate'], "rate_5"=>$baiou['change_draw_rate'], "rate_6"=>$baiou['change_away_rate']];

            // 大小球
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);//`change_rate`, `change_big_rate`, `change_small_rate`
            $tuijian['daxiaoqiu'] = ["rate_1"=>$daxiaoqiu['change_big_rate'], "rate_2"=>$daxiaoqiu['change_rate'], "rate_3"=>$daxiaoqiu['change_small_rate']];

            // 大小球半场
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu_half',$match['state']);
            $tuijian['daxiaoqiu_half'] = ["rate_4"=>$daxiaoqiu['change_big_rate'], "rate_5"=>$daxiaoqiu['change_rate'], "rate_6"=>$daxiaoqiu['change_small_rate']];

            $json['data'] = $tuijian;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 赛事关注
     */
    public function follow(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $match_id = I('request.match_id', 0,'intval');
            if( empty($match_id)){
                $json['status'] = 110;
                $json['msg'] = '请输入关注赛事';
                break;
            }
            $follow = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match_id))->field('id')->find();
            if($follow){
                $json['msg'] = '赛事关注成功';
                $json['data']['id'] = $follow['id'];
                $json['data']['match_id'] = $match_id;
                $json['data']['user_id'] = $user_id;
                break;
            }else{
                $data = [
                    'match_id' => $match_id,
                    'user_id' => $user_id,
                    'create_time' => time()
                ];
                $res = M('match_follow')->add($data);
                // 增加赛事收藏统计
                M('match')->where(array('match_id'=>$match_id))->setInc('total_collect', 1);
                // 增加用户收藏总计
                M('users')->where(array('id'=>$user_id))->setInc('total_collect_match', 1);
                if($res){
                    $json['msg'] = '赛事关注成功';
                    $json['data']['id'] = $res;
                    $json['data']['match_id'] = $match_id;
                    $json['data']['user_id'] = $user_id;
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '赛事关注失败';
                    break;
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 赛事取消关注
     */
    public function un_follow(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $match_id = I('request.match_id', 0,'intval');
            if( empty($match_id)){
                $json['status'] = 110;
                $json['msg'] = '请输入关注赛事';
                break;
            }
            $follow = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match_id))->field('id')->find();
            if($follow){
                M('match_follow')->where(array('id'=>$follow['id']))->delete();
                // 减少赛事收藏总数
                M('match')->where(array('match_id'=>$match_id))->setDec('total_collect', 1);
                // 减少用户收藏总数
                M('users')->where(array('id'=>$user_id))->setDec('total_collect_match', 1);
                $json['msg'] = '取消关注成功';
                $json['data']['match_id'] = $match_id;
                $json['data']['user_id'] = $user_id;
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '没找到关注信息';
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取事件
     */
    public function events(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id',0,'intval');
            if(empty($match_id)){
                $json['status'] = 110;
                $json['msg'] = "请选择赛事";
                break;
            }
            $event_list = M('event')->where(array('match_id'=>$match_id))->field('id, match_id, is_home_away, event_type, time')->order("time ASC")->select();
            $json['data']['list'] = (array)$event_list;
        }while(false);
        $this->ajaxReturn($json);

    }
}