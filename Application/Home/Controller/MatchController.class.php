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
            }elseif($type == 2){
                $where['state'] = -1;
            }elseif($type == 3){
                $where['state'] = 0;
            }else{
                //

            }

            if($league_ids){
                $where['league_id'] = array('in', $league_ids);
            }
            $total = M('match')->where($where)->count();
            $Page = new Page($total, $limit);
            $list = M('match')->field
                ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,address,weather_ico,weather,temperature,is_neutral,technic'
                )
                ->where($where)->order("level DESC, time ASC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $json['sql'] = M()->getLastSql();

            foreach($list as $i=>$match){
                //
                //$list[$i]['technic'] = empty($match['technic'])?[]:json_decode($match['technic'], true);
                // 赛事统计
                $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
                $list[$i]['technic'] = [];

                $list[$i]['technic']['id3']['home'] = isset($technic['id3'])?($technic['id3']['home']):0;
                $list[$i]['technic']['id3']['away'] = isset($technic['id3'])?($technic['id3']['away']):0;
                $list[$i]['technic']['id4']['home'] = isset($technic['id4'])?($technic['id4']['home']):0;
                $list[$i]['technic']['id4']['away'] = isset($technic['id4'])?($technic['id4']['away']):0;
                $list[$i]['technic']['id6']['home'] = isset($technic['id6'])?($technic['id6']['home']):0;
                $list[$i]['technic']['id6']['away'] = isset($technic['id6'])?($technic['id6']['away']):0;
                $list[$i]['technic']['id14']['home'] = isset($technic['id14'])?($technic['id14']['home']):0;
                $list[$i]['technic']['id14']['away'] = isset($technic['id14'])?($technic['id14']['away']):0;

                // 标准
                $baiou = M('asia_oupei')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['begin_home_rate'] = ("{$baiou['begin_home_rate']}");
                $list[$i]['begin_draw_rate'] = ("{$baiou['begin_draw_rate']}");
                $list[$i]['begin_away_rate'] = ("{$baiou['begin_away_rate']}");
                $list[$i]['change_home_rate'] = ("{$baiou['change_home_rate']}");
                $list[$i]['change_draw_rate'] = ("{$baiou['change_draw_rate']}");
                $list[$i]['change_away_rate'] = ("{$baiou['change_away_rate']}");

                // 欧赔
                $list[$i]['oupei']['begin_home_rate'] = ("{$baiou['begin_home_rate']}");
                $list[$i]['oupei']['begin_draw_rate'] = ("{$baiou['begin_draw_rate']}");
                $list[$i]['oupei']['begin_away_rate'] = ("{$baiou['begin_away_rate']}");
                $list[$i]['oupei']['change_home_rate'] = ("{$baiou['change_home_rate']}");
                $list[$i]['oupei']['change_draw_rate'] = ("{$baiou['change_draw_rate']}");
                $list[$i]['oupei']['change_away_rate'] = ("{$baiou['change_away_rate']}");

                // 亚赔
                $yapei = M('asia_yapei')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['yapei']['begin_rate'] = ("{$yapei['begin_rate']}");
                $list[$i]['yapei']['begin_home_rate'] = ("{$yapei['begin_home_rate']}");
                $list[$i]['yapei']['begin_away_rate'] = ("{$yapei['begin_away_rate']}");
                $list[$i]['yapei']['change_rate'] = ("{$yapei['change_rate']}");
                $list[$i]['yapei']['change_home_rate'] = ("{$yapei['change_home_rate']}");
                $list[$i]['yapei']['change_away_rate'] = ("{$yapei['change_away_rate']}");

                // 大小球
                $daxiaoqiu = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['daxiaoqiu']['begin_rate'] = ("{$daxiaoqiu['begin_rate']}");
                $list[$i]['daxiaoqiu']['begin_big_rate'] = ("{$daxiaoqiu['begin_big_rate']}");
                $list[$i]['daxiaoqiu']['begin_small_rate'] = ("{$daxiaoqiu['begin_small_rate']}");
                $list[$i]['daxiaoqiu']['change_rate'] = ("{$daxiaoqiu['change_rate']}");
                $list[$i]['daxiaoqiu']['change_big_rate'] = ("{$daxiaoqiu['change_big_rate']}");
                $list[$i]['daxiaoqiu']['change_small_rate'] = ("{$daxiaoqiu['change_small_rate']}");

                // 竟彩
                $jingcai = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['jingcai']['home_rate'] = ("{$jingcai['begin_rate']}");
                $list[$i]['jingcai']['away_rate'] = ("{$jingcai['begin_big_rate']}");
                $list[$i]['jingcai']['draw_rate'] = ("{$jingcai['begin_small_rate']}");
                $list[$i]['jingcai']['home_win_rate'] = ("{$jingcai['change_rate']}");
                $list[$i]['jingcai']['away_win_rate'] = ("{$jingcai['change_big_rate']}");
                $list[$i]['jingcai']['draw_win_rate'] = ("{$jingcai['change_small_rate']}");

                // 直播事件
                $event_list = M('event')->where(array('match_id'=>$match['match_id']))->order("time ASC")->select();
                $list[$i]['events'] = (array)$event_list;
                $list[$i]['match_name'] = $match['league_name'];
                $list[$i]['time22'] = $match['match_time'];
                $list[$i]['time33'] = strtotime($match['match_time']);
                $list[$i]['time44'] = time();
                $list[$i]['time44'] = time()-strtotime($match['match_time']);
                // 比赛状态 0:未开,1:上半场,2:中场,3:下半场,4,加时，-11:待定,-12:腰斩,-13:中断,-14:推迟,-1:完场，-10取消
                if($match['state'] == '0'){
                    $list[$i]['match_time2'] = '未开';
                }elseif(in_array($match['state'],[1,2,3,4])){
                    $list[$i]['match_time2'] = floor(time() - strtotime($match['match_time'])/60);
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
                    $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match['match_id']))->find();
                    if($collect){
                        $list[$i]['is_collect'] = 1;
                    }
                }
            }

            $data = [];
            foreach($list as $match){
                $data[$match['league_id']]['league_id'] = $match['league_id'];
                $data[$match['league_id']]['league_name'] = $match['league_name'];
                $data[$match['league_id']]['league_ico'] = C('BASE_URL').'Public/static/noimg.png';
                $data[$match['league_id']]['list'][] = $match;
            }
            $newdata = [];
            foreach($data as $item){
                $newdata[] = $item;
            }
            // league_list
            $league_list = [];
            // if($page)
            if($type == 1){
                $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state in(1,2,3,4)")
                    ->field('l.league_id,l.color,l.cn_short,l.cn_name,l.type,l.sum_round,l.curr_round,l.curr_match_season,l.country_name,l.is_hot,count(*) as total_match')
                    ->group('m.league_id')
                    ->order("is_hot DESC, weight DESC")->select();
            }elseif($type == 2){
                $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state = -1")
                    ->field('l.league_id,l.color,l.cn_short,l.cn_name,l.type,l.sum_round,l.curr_round,l.curr_match_season,l.country_name,l.is_hot,count(*) as total_match')
                    ->group('m.league_id')
                    ->order("is_hot DESC, weight DESC")->select();
            }elseif($type == 3){
                $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state =0")
                    ->field('l.league_id,l.color,l.cn_short,l.cn_name,l.type,l.sum_round,l.curr_round,l.curr_match_season,l.country_name,l.is_hot,count(*) as total_match')
                    ->group('m.league_id')
                    ->order("is_hot DESC, weight DESC")->select();
            }else{
                $league_list = M()->table(C('DB_PREFIX').'league as l, '.C('DB_PREFIX').'match as m')->where("l.league_id = m.league_id AND m.state in(1,2,3,4)")
                    ->field('l.league_id,l.color,l.cn_short,l.cn_name,l.type,l.sum_round,l.curr_round,l.curr_match_season,l.country_name,l.is_hot,count(*) as total_match')
                    ->group('m.league_id')
                    ->order("is_hot DESC, weight DESC")->select();
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
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,address,weather_ico,weather,temperature,is_neutral,technic'
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
            $baiou = M('asia_oupei')->where(array('match_id'=>$match['match_id']))->find();
            $match['begin_home_rate'] = ("{$baiou['begin_home_rate']}");
            $match['begin_draw_rate'] = ("{$baiou['begin_draw_rate']}");
            $match['begin_away_rate'] = ("{$baiou['begin_away_rate']}");
            $match['change_home_rate'] = ("{$baiou['change_home_rate']}");
            $match['change_draw_rate'] = ("{$baiou['change_draw_rate']}");
            $match['change_away_rate'] = ("{$baiou['change_away_rate']}");

            // 欧赔
            $match['oupei']['begin_home_rate'] = ("{$baiou['begin_home_rate']}");
            $match['oupei']['begin_draw_rate'] = ("{$baiou['begin_draw_rate']}");
            $match['oupei']['begin_away_rate'] = ("{$baiou['begin_away_rate']}");
            $match['oupei']['change_home_rate'] = ("{$baiou['change_home_rate']}");
            $match['oupei']['change_draw_rate'] = ("{$baiou['change_draw_rate']}");
            $match['oupei']['change_away_rate'] = ("{$baiou['change_away_rate']}");

            // 亚赔
            $yapei = M('asia_yapei')->where(array('match_id'=>$match['match_id']))->find();
            $match['yapei']['begin_rate'] = ("{$yapei['begin_rate']}");
            $match['yapei']['begin_home_rate'] = ("{$yapei['begin_home_rate']}");
            $match['yapei']['begin_away_rate'] = ("{$yapei['begin_away_rate']}");
            $match['yapei']['change_rate'] = ("{$yapei['change_rate']}");
            $match['yapei']['change_home_rate'] = ("{$yapei['change_home_rate']}");
            $match['yapei']['change_away_rate'] = ("{$yapei['change_away_rate']}");

            // 大小球
            $daxiaoqiu = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
            $match['daxiaoqiu']['begin_rate'] = ("{$daxiaoqiu['begin_rate']}");
            $match['daxiaoqiu']['begin_big_rate'] = ("{$daxiaoqiu['begin_big_rate']}");
            $match['daxiaoqiu']['begin_small_rate'] = ("{$daxiaoqiu['begin_small_rate']}");
            $match['daxiaoqiu']['change_rate'] = ("{$daxiaoqiu['change_rate']}");
            $match['daxiaoqiu']['change_big_rate'] = ("{$daxiaoqiu['change_big_rate']}");
            $match['daxiaoqiu']['change_small_rate'] = ("{$daxiaoqiu['change_small_rate']}");

            // 竟彩
            $jingcai = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
            $match['jingcai']['home_rate'] = ("{$jingcai['begin_rate']}");
            $match['jingcai']['away_rate'] = ("{$jingcai['begin_big_rate']}");
            $match['jingcai']['draw_rate'] = ("{$jingcai['begin_small_rate']}");
            $match['jingcai']['home_win_rate'] = ("{$jingcai['change_rate']}");
            $match['jingcai']['away_win_rate'] = ("{$jingcai['change_big_rate']}");
            $match['jingcai']['draw_win_rate'] = ("{$jingcai['change_small_rate']}");

            // 直播事件
            $event_list = M('event')->where(array('match_id'=>$match['match_id']))->order("time ASC")->select();
            $match['events'] = (array)$event_list;
            $match['match_name'] = $match['league_name'];

            if($match['state'] == '0'){
                $match['match_time2'] = '未开';
            }elseif(in_array($match['state'],[1,2,3,4])){
                $match['match_time2'] = floor(time() - strtotime($match['match_time'])/60);
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
                $collect = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match['match_id']))->find();
                if($collect){
                    $match['is_collect'] = 1;
                }
            }
            $json['data'] = $match;

        }while(false);
        $this->ajaxReturn($json);
    }


    /**
     * 获取赛事推荐
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
            // 标准
            $baiou = M('asia_oupei')->where(array('match_id'=>$match['match_id']))->find();
            $tuijian['begin_home_rate'] = ("{$baiou['begin_home_rate']}");
            $tuijian['begin_draw_rate'] = ("{$baiou['begin_draw_rate']}");
            $tuijian['begin_away_rate'] = ("{$baiou['begin_away_rate']}");
            $tuijian['change_home_rate'] = ("{$baiou['change_home_rate']}");
            $tuijian['change_draw_rate'] = ("{$baiou['change_draw_rate']}");
            $tuijian['change_away_rate'] = ("{$baiou['change_away_rate']}");

            // 竞彩
            $tuijian['jingcai'] = ["rate_1"=>"0", "rate_2"=>"0", "rate_3"=>"0"];
            // 竞彩让球
            $tuijian['jingcai_rangqiu'] = ["rate_4"=>"0", "rate_5"=>"0", "rate_6"=>"0"];
            // 亚赔
            $tuijian['rangqiu'] = ["rate_1"=>"0", "rate_2"=>"0", "rate_3"=>"0"];
            // 亚赔半场
            $tuijian['rangqiu_half'] = ["rate_4"=>"0", "rate_5"=>"0", "rate_6"=>"0"];

            // 欧赔
            $tuijian['oupei'] = ["rate_1"=>"0", "rate_2"=>"0", "rate_3"=>"0"];
            // 欧赔半场
            $tuijian['oupei_half'] = ["rate_4"=>"0", "rate_5"=>"0", "rate_6"=>"0"];

            // 大小球
            $tuijian['daxiaoqiu'] = ["rate_1"=>"0", "rate_2"=>"0", "rate_3"=>"0"];

            // 大小球半场
            $tuijian['daxiaoqiu_half'] = ["rate_4"=>"0", "rate_5"=>"0", "rate_6"=>"0"];

            // 大小球
            $tuijian['jiaoqiu'] = ["rate_1"=>"0", "rate_2"=>"0", "rate_3"=>"0"];

            // 大小球半场
            $tuijian['jiaoqiu_half'] = ["rate_4"=>"0", "rate_5"=>"0", "rate_6"=>"0"];

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
            $follow = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match_id))->find();
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
                M('match')->where(array('match_id'=>$match_id))->setInc('total_collect', 1);
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
            $follow = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$match_id))->find();
            if($follow){
                M('match_follow')->where(array('id'=>$follow['id']))->delete();
                M('match')->where(array('match_id'=>$match_id))->setDec('total_collect', 1);
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
            $event_list = M('event')->where(array('match_id'=>$match_id))->order("time ASC")->select();
            $json['data']['list'] = (array)$event_list;
        }while(false);
        $this->ajaxReturn($json);

    }

    /*
     * 获取赛事全场盘口
     */
    public function get_rate_list(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id', 0,'intval');
            if(!$match_id){
                $json['status'] = 110;
                $json['msg'] = "请选择查看赛事";
                break;
            }
            // 3,24,31,8
            // 走地
            $json['data']['match_id'] = $match_id;
            $json['data']['oupei'] = [];
            $field = ' `match_id`, `time`, `home_score`, `away_score`, `company_id`, `rate_1`, `rate_2`, `rate_3`, `change_date`';

            // 走地数据
            $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>4, 'company_id'=>3))->order('id DESC')->field($field)->select();
            if($oupei){
                $json['data']['oupei'] = $oupei;
            }else{
                $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>4, 'company_id'=>24))->order('id DESC')->field($field)->select();
                if($oupei){
                    $json['data']['oupei'] = $oupei;
                }else{
                    $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>4, 'company_id'=>31))->order('id DESC')->field($field)->select();
                    if($oupei){
                        $json['data']['oupei'] = $oupei;
                    }else{
                        $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>4, 'company_id'=>8))->order('id DESC')->field($field)->select();
                        if($oupei){
                            $json['data']['oupei'] = $oupei;
                        }
                    }
                }
            }

            // 变盘数据
            $field2 = " `match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_home_rate as  rate_1, change_draw_rate as rate_2, change_away_rate as rate_3, '' as change_date";
            $oupei = M('asia_oupei_change')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['oupei'] = array_merge($json['data']['oupei'],$oupei);
            }else{
                $oupei = M('asia_oupei_change')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['oupei'] = array_merge($json['data']['oupei'],$oupei);
                }else{
                    $oupei = M('asia_oupei_change')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['oupei'] = array_merge($json['data']['oupei'],$oupei);
                    }else{
                        $oupei = M('asia_oupei_change')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['oupei'] = array_merge($json['data']['oupei'],$oupei);
                        }
                    }
                }
            }

            $json['data']['rangqiu'] = [];
            $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>1, 'company_id'=>3))->field($field)->select();
            if($oupei){
                $json['data']['rangqiu'] = $oupei;
            }else{
                $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>1, 'company_id'=>24))->field($field)->select();
                if($oupei){
                    $json['data']['rangqiu'] = $oupei;
                }else{
                    $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>1, 'company_id'=>31))->field($field)->select();
                    if($oupei){
                        $json['data']['rangqiu'] = $oupei;
                    }else{
                        $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>1, 'company_id'=>8))->field($field)->select();
                        if($oupei){
                            $json['data']['rangqiu'] = $oupei;
                        }
                    }
                }
            }

            // 变盘数据
            $field2 = " `match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_home_rate as  rate_1, change_rate as rate_2, change_away_rate as rate_3, '' as change_date";
            $oupei = M('asia_yapei_change')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['rangqiu'] = array_merge($json['data']['rangqiu'],$oupei);
            }else{
                $oupei = M('asia_yapei_change')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['rangqiu'] = array_merge($json['data']['rangqiu'],$oupei);
                }else{
                    $oupei = M('asia_yapei_change')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['rangqiu'] = array_merge($json['data']['rangqiu'],$oupei);
                    }else{
                        $oupei = M('asia_yapei_change')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['rangqiu'] = array_merge($json['data']['rangqiu'],$oupei);
                        }
                    }
                }
            }

            $json['data']['daxiaoqiu'] = [];
            $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>2, 'company_id'=>3))->field($field)->select();
            if($oupei){
                $json['data']['daxiaoqiu'] = $oupei;
            }else{
                $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>2, 'company_id'=>24))->field($field)->select();
                if($oupei){
                    $json['data']['daxiaoqiu'] = $oupei;
                }else{
                    $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>2, 'company_id'=>31))->field($field)->select();
                    if($oupei){
                        $json['data']['daxiaoqiu'] = $oupei;
                    }else{
                        $oupei = M('zoudi')->where(array('match_id'=>$match_id, 'type'=>2, 'company_id'=>8))->field($field)->select();
                        if($oupei){
                            $json['data']['daxiaoqiu'] = $oupei;
                        }
                    }
                }
            }

            // 变盘数据
            $field2 = "`match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_big_rate as  rate_1, change_rate as rate_2, change_small_rate as rate_3, '' as change_date";
            $oupei = M('asia_daxiaoqiu_change')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['daxiaoqiu'] = array_merge($json['data']['daxiaoqiu'],$oupei);
            }else{
                $oupei = M('asia_daxiaoqiu_change')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['daxiaoqiu'] = array_merge($json['data']['daxiaoqiu'],$oupei);
                }else{
                    $oupei = M('asia_daxiaoqiu_change')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['daxiaoqiu'] = array_merge($json['data']['daxiaoqiu'],$oupei);
                    }else{
                        $oupei = M('asia_daxiaoqiu_change')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['daxiaoqiu'] = array_merge($json['data']['daxiaoqiu'],$oupei);
                        }
                    }
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取半场赛事盘口
     */
    public function get_half_rate_list(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.match_id', 0,'intval');
            if(!$match_id){
                $json['status'] = 110;
                $json['msg'] = "请选择查看赛事";
                break;
            }
            // 3,24,31,8
            // 走地
            $json['data']['match_id'] = $match_id;
            $json['data']['oupei'] = [];

            // `match_id`, `company_id`, `begin_home_rate`, `begin_draw_rate`, `begin_away_rate`, `change_home_rate`, `change_draw_rate`, `change_away_rate`, `change_date`, `update_time`
            // 变盘数据
            $field2 = "0 as zoudi_id, `match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_home_rate as  rate_1, change_draw_rate as rate_2, change_away_rate as rate_3, change_date";
            $oupei = M('asia_half_oupei')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['oupei'] = $oupei;
            }else{
                $oupei = M('asia_half_oupei')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['oupei'] = $oupei;
                }else{
                    $oupei = M('asia_half_oupei')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['oupei'] = $oupei;
                    }else{
                        $oupei = M('asia_half_oupei')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['oupei'] = $oupei;
                        }
                    }
                }
            }

            $json['data']['rangqiu'] = [];
            // 变盘数据
            $field2 = "0 as zoudi_id, `match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_home_rate as  rate_1, change_rate as rate_2, change_away_rate as rate_3, '' as change_date";
            $oupei = M('asia_half')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['rangqiu'] = $oupei;
            }else{
                $oupei = M('asia_half')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['rangqiu'] = $oupei;
                }else{
                    $oupei = M('asia_half')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['rangqiu'] = $oupei;
                    }else{
                        $oupei = M('asia_half')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['rangqiu'] = $oupei;
                        }
                    }
                }
            }

            $json['data']['daxiaoqiu'] = [];
            // 变盘数据
            $field2 = "0 as zoudi_id, `match_id`, '' as time, '' as home_score, '' as away_score, `company_id`, change_big_rate as  rate_1, change_rate as rate_2, change_small_rate as rate_3, '' as change_date";
            $oupei = M('asia_half_daxiaoqiu_change')->where(array('match_id'=>$match_id, 'company_id'=>3))->field($field2)->order('id DESC')->select();
            if($oupei){
                $json['data']['daxiaoqiu'] = $oupei;
            }else{
                $oupei = M('asia_half_daxiaoqiu_change')->where(array('match_id'=>$match_id, 'company_id'=>24))->field($field2)->select();
                if($oupei){
                    $json['data']['daxiaoqiu'] = $oupei;
                }else{
                    $oupei = M('asia_half_daxiaoqiu_change')->where(array('match_id'=>$match_id,  'company_id'=>31))->field($field2)->select();
                    if($oupei){
                        $json['data']['daxiaoqiu'] = $oupei;
                    }else{
                        $oupei = M('asia_half_daxiaoqiu_change')->where(array('match_id'=>$match_id,  'company_id'=>8))->field($field2)->select();
                        if($oupei){
                            $json['data']['daxiaoqiu'] = $oupei;
                        }
                    }
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }
}