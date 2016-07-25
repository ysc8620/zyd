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
            $page = I('request.p', 1,'intval');
            $type = I('request.type',1,'strval');// 1进行中， 2已完成，3为开始，4个人收藏
            $league_ids = (array)I('request.league_ids',[]);
            $limit = I('request.limit',10,'intval');
            $where = [];

            // 进行中
            if($type == 1){
                $where['state'] = array('in',array(1,2,3,4));
            }elseif($type == 2){
                $where['state'] = -1;
            }elseif($type == 3){
                $where['state'] = 0;
            }else{

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
                ->where($where)->order("time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

            foreach($list as $i=>$match){
                //
                //$list[$i]['technic'] = empty($match['technic'])?[]:json_decode($match['technic'], true);
                // 赛事统计
                $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
                $list[$i]['technic'] = [];
                for($j=0; $j<41;$j++){
                    $list[$i]['technic']['id'.$j]['home'] = isset($technic['id'.$j])?$technic['id'.$j]['home']:'';
                    $list[$i]['technic']['id'.$j]['away'] = isset($technic['id'.$j])?$technic['id'.$j]['away']:'';
                }

                // 标准
                $baiou = M('asia_oupei')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['begin_home_rate'] = floatval("{$baiou['begin_home_rate']}");
                $list[$i]['begin_draw_rate'] = floatval("{$baiou['begin_draw_rate']}");
                $list[$i]['begin_away_rate'] = floatval("{$baiou['begin_away_rate']}");
                $list[$i]['change_home_rate'] = floatval("{$baiou['change_home_rate']}");
                $list[$i]['change_draw_rate'] = floatval("{$baiou['change_draw_rate']}");
                $list[$i]['change_away_rate'] = floatval("{$baiou['change_away_rate']}");

                // 欧赔
                $list[$i]['oupei']['begin_home_rate'] = floatval("{$baiou['begin_home_rate']}");
                $list[$i]['oupei']['begin_draw_rate'] = floatval("{$baiou['begin_draw_rate']}");
                $list[$i]['oupei']['begin_away_rate'] = floatval("{$baiou['begin_away_rate']}");
                $list[$i]['oupei']['change_home_rate'] = floatval("{$baiou['change_home_rate']}");
                $list[$i]['oupei']['change_draw_rate'] = floatval("{$baiou['change_draw_rate']}");
                $list[$i]['oupei']['change_away_rate'] = floatval("{$baiou['change_away_rate']}");

                // 亚赔
                $yapei = M('asia_yapei')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['yapei']['begin_rate'] = floatval("{$yapei['begin_rate']}");
                $list[$i]['yapei']['begin_home_rate'] = floatval("{$yapei['begin_home_rate']}");
                $list[$i]['yapei']['begin_away_rate'] = floatval("{$yapei['begin_away_rate']}");
                $list[$i]['yapei']['change_rate'] = floatval("{$yapei['change_rate']}");
                $list[$i]['yapei']['change_home_rate'] = floatval("{$yapei['change_home_rate']}");
                $list[$i]['yapei']['change_away_rate'] = floatval("{$yapei['change_away_rate']}");

                // 大小球
                $daxiaoqiu = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['daxiaoqiu']['begin_rate'] = floatval("{$daxiaoqiu['begin_rate']}");
                $list[$i]['daxiaoqiu']['begin_big_rate'] = floatval("{$daxiaoqiu['begin_big_rate']}");
                $list[$i]['daxiaoqiu']['begin_small_rate'] = floatval("{$daxiaoqiu['begin_small_rate']}");
                $list[$i]['daxiaoqiu']['change_rate'] = floatval("{$daxiaoqiu['change_rate']}");
                $list[$i]['daxiaoqiu']['change_big_rate'] = floatval("{$daxiaoqiu['change_big_rate']}");
                $list[$i]['daxiaoqiu']['change_small_rate'] = floatval("{$daxiaoqiu['change_small_rate']}");

                // 竟彩
                $jingcai = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
                $list[$i]['jingcai']['home_rate'] = floatval("{$jingcai['begin_rate']}");
                $list[$i]['jingcai']['away_rate'] = floatval("{$jingcai['begin_big_rate']}");
                $list[$i]['jingcai']['draw_rate'] = floatval("{$jingcai['begin_small_rate']}");
                $list[$i]['jingcai']['home_win_rate'] = floatval("{$jingcai['change_rate']}");
                $list[$i]['jingcai']['away_win_rate'] = floatval("{$jingcai['change_big_rate']}");
                $list[$i]['jingcai']['draw_win_rate'] = floatval("{$jingcai['change_small_rate']}");

                // 直播事件
                $event_list = M('event')->where(array('match_id'=>$match['match_id']))->order("time ASC")->select();
                $list[$i]['events'] = (array)$event_list;
                $list[$i]['match_name'] = '';
                $list[$i]['is_collect'] = 0;
                $list[$i]['total_collect'] = 0;
            }

            $data = [];
            foreach($list as $match){
                $data[$match['league_id']]['league_id'] = $match['league_id'];
                $data[$match['league_id']]['league_name'] = $match['league_name'];
                $data[$match['league_id']]['league_ico'] = C('BASE_URL').'Public/static/noimg.png';
                $data[$match['league_id']]['list'][] = $match;
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

            $json['data']['league_list'] = $league_list;
            $json['data']['list'] = $data;
            $json['data']['total'] = $total;
            $json['data']['page'] = $page;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
            $json['data']['league_ids'] = $league_ids;

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 比赛详情
     */
    public function info(){
        $json = $this->simpleJson();
        do{
            $match_id = I('request.id', 0,'intval');
            $match = M('match')->field
            ('match_id,time as match_time,league_id,league_name,kind,level,state,home_id,home_name,home_score,away_id,
                away_name,away_score,home_red,away_red,home_yellow,away_yellow,match_round,address,weather_ico,weather,temperature,is_neutral,technic'
            )
                ->where(array('match_id'=>$match_id))->find();

            // 赛事统计
            $technic = empty($match['technic'])?[]:json_decode($match['technic'], true);
            $match['technic'] = [];
            for($j=0; $j<41;$j++){
                $match['technic']['id'.$j]['home'] = isset($technic['id'.$j])?$technic['id'.$j]['home']:'';
                $match['technic']['id'.$j]['away'] = isset($technic['id'.$j])?$technic['id'.$j]['away']:'';
            }

            // 标准
            $baiou = M('asia_oupei')->where(array('match_id'=>$match['match_id']))->find();
            $match['begin_home_rate'] = floatval("{$baiou['begin_home_rate']}");
            $match['begin_draw_rate'] = floatval("{$baiou['begin_draw_rate']}");
            $match['begin_away_rate'] = floatval("{$baiou['begin_away_rate']}");
            $match['change_home_rate'] = floatval("{$baiou['change_home_rate']}");
            $match['change_draw_rate'] = floatval("{$baiou['change_draw_rate']}");
            $match['change_away_rate'] = floatval("{$baiou['change_away_rate']}");

            // 欧赔
            $match['oupei']['begin_home_rate'] = floatval("{$baiou['begin_home_rate']}");
            $match['oupei']['begin_draw_rate'] = floatval("{$baiou['begin_draw_rate']}");
            $match['oupei']['begin_away_rate'] = floatval("{$baiou['begin_away_rate']}");
            $match['oupei']['change_home_rate'] = floatval("{$baiou['change_home_rate']}");
            $match['oupei']['change_draw_rate'] = floatval("{$baiou['change_draw_rate']}");
            $match['oupei']['change_away_rate'] = floatval("{$baiou['change_away_rate']}");

            // 亚赔
            $yapei = M('asia_yapei')->where(array('match_id'=>$match['match_id']))->find();
            $match['yapei']['begin_rate'] = floatval("{$yapei['begin_rate']}");
            $match['yapei']['begin_home_rate'] = floatval("{$yapei['begin_home_rate']}");
            $match['yapei']['begin_away_rate'] = floatval("{$yapei['begin_away_rate']}");
            $match['yapei']['change_rate'] = floatval("{$yapei['change_rate']}");
            $match['yapei']['change_home_rate'] = floatval("{$yapei['change_home_rate']}");
            $match['yapei']['change_away_rate'] = floatval("{$yapei['change_away_rate']}");

            // 大小球
            $daxiaoqiu = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
            $match['daxiaoqiu']['begin_rate'] = floatval("{$daxiaoqiu['begin_rate']}");
            $match['daxiaoqiu']['begin_big_rate'] = floatval("{$daxiaoqiu['begin_big_rate']}");
            $match['daxiaoqiu']['begin_small_rate'] = floatval("{$daxiaoqiu['begin_small_rate']}");
            $match['daxiaoqiu']['change_rate'] = floatval("{$daxiaoqiu['change_rate']}");
            $match['daxiaoqiu']['change_big_rate'] = floatval("{$daxiaoqiu['change_big_rate']}");
            $match['daxiaoqiu']['change_small_rate'] = floatval("{$daxiaoqiu['change_small_rate']}");

            // 竟彩
            $jingcai = M('asia_daxiaoqiu')->where(array('match_id'=>$match['match_id']))->find();
            $match['jingcai']['home_rate'] = floatval("{$jingcai['begin_rate']}");
            $match['jingcai']['away_rate'] = floatval("{$jingcai['begin_big_rate']}");
            $match['jingcai']['draw_rate'] = floatval("{$jingcai['begin_small_rate']}");
            $match['jingcai']['home_win_rate'] = floatval("{$jingcai['change_rate']}");
            $match['jingcai']['away_win_rate'] = floatval("{$jingcai['change_big_rate']}");
            $match['jingcai']['draw_win_rate'] = floatval("{$jingcai['change_small_rate']}");

            // 直播事件
            $event_list = M('event')->where(array('match_id'=>$match['match_id']))->order("time ASC")->select();
            $match['events'] = (array)$event_list;
            $match['match_name'] = '';
            $match['is_collect'] = 0;
            $match['total_collect'] = 0;
            $json['data'] = $match;

        }while(false);
        $this->ajaxReturn($json);
    }
}