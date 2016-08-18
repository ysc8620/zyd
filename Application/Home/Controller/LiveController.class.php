<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;



class LiveController extends BaseApiController {
    /**
     * 返回直播赛事信息
    */
    public function index(){
        $json = $this->simpleJson();
        $list = M("match")->where(array('state'=>array('in',[1,2,3,4])))->field("match_id,home_score,away_score,home_half_score,away_half_score,home_red,away_red,time,technic,state")->select();
        foreach($list as $i=>$item){
            //
            $list[$i]['match_time2'] = floor((time()-strtotime($item['time']))/60);
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

            $event_list = M('event')->where(array('match_id'=>$item['match_id'], 'event_type'=>1))->order("time DESC")->select();
            $list[$i]['events'] = (array)$event_list;
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
            $baiou = get_rate($match['match_id'],'rangqiu',$match['state']);
            $tuijian['begin_home_rate'] = $baiou['begin_home_rate'];
            $tuijian['begin_rate'] = $baiou['begin_rate'];
            $tuijian['begin_away_rate'] = $baiou['begin_away_rate'];
            $tuijian['change_home_rate'] = $baiou['change_home_rate'];
            $tuijian['change_rate'] = $baiou['change_rate'];
            $tuijian['change_away_rate'] = $baiou['change_away_rate'];

            // 竞彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $tuijian['jingcai'] = ["rate_1"=>$jingcai['home_win_rate'], "rate_2"=>$jingcai['draw_win_rate'], "rate_3"=>$jingcai['away_win_rate']];
            // 竞彩让球

            // 亚赔
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);//`change_rate`, `change_home_rate`, `change_away_rate`
            $tuijian['rangqiu'] = ["rate_1"=>$yapei['change_home_rate'], "rate_2"=>$yapei['change_rate'], "rate_3"=>$yapei['change_away_rate']];

            // 欧赔
            $tuijian['oupei'] = ["rate_1"=>$baiou['change_home_rate'], "rate_2"=>$baiou['change_draw_rate'], "rate_3"=>$baiou['change_away_rate']];

            // 大小球
            $daxiaoqiu = get_rate($match['match_id'],'daxiaoqiu',$match['state']);//`change_rate`, `change_big_rate`, `change_small_rate`
            $tuijian['daxiaoqiu'] = ["rate_1"=>$daxiaoqiu['change_big_rate'], "rate_2"=>$daxiaoqiu['change_rate'], "rate_3"=>$daxiaoqiu['change_small_rate']];

            $peilv = get_rate_list($match['match_id']);
            $tuijian['live_oupei'] = $peilv['oupei'];
            $tuijian['live_rangqiu'] = $peilv['rangqiu'];
            $tuijian['live_daxiaoqiu'] = $peilv['daxiaoqiu'];

            $peilv = get_half_rate_list($match['match_id']);
            $tuijian['live_oupei_half'] = $peilv['oupei'];
            $tuijian['live_rangqiu_half'] = $peilv['rangqiu'];
            $tuijian['live_daxiaoqiu_half'] = $peilv['daxiaoqiu'];

            $event_list = M('event')->where(array('match_id'=>$match_id))->order("time ASC")->select();
            $tuijian['events'] = (array)$event_list;
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
            $tuijian['state'] = $match['state'];
            if(in_array($tuijian['state'],[0,1,2,3,4])){
                $tuijian['status'] = 1;
            }else{
                $tuijian['status'] = 0;
            }
            // 标准
            $baiou = get_rate($match['match_id'],'rangqiu',$match['state']);
            $tuijian['begin_home_rate'] = $baiou['begin_home_rate'];
            $tuijian['begin_rate'] = $baiou['begin_rate'];
            $tuijian['begin_away_rate'] = $baiou['begin_away_rate'];
            $tuijian['change_home_rate'] = $baiou['change_home_rate'];
            $tuijian['change_rate'] = $baiou['change_rate'];
            $tuijian['change_away_rate'] = $baiou['change_away_rate'];

            // 竞彩
            $jingcai = get_rate($match['match_id'],'jingcai',$match['state']);
            $tuijian['jingcai'] = ["rate_1"=>$jingcai['home_win_rate'], "rate_2"=>$jingcai['draw_win_rate'], "rate_3"=>$jingcai['away_win_rate']];
            // 竞彩让球
            $jingcai = get_rate($match['match_id'],'jingcai_rangqiu',$match['state']);
            $tuijian['jingcai_rangqiu'] = ["rate_4"=>$jingcai['home_win_rate'], "rate_5"=>$jingcai['draw_win_rate'], "rate_6"=>$jingcai['away_win_rate'],"left_ball"=>0];
            // 亚赔
            $yapei = get_rate($match['match_id'],'rangqiu',$match['state']);//`change_rate`, `change_home_rate`, `change_away_rate`
            $tuijian['rangqiu'] = ["rate_1"=>$yapei['change_home_rate'], "rate_2"=>$yapei['change_rate'], "rate_3"=>$yapei['change_away_rate']];
            // 亚赔半场
            $yapei = get_rate($match['match_id'],'rangqiu_half',$match['state']);
            $tuijian['rangqiu_half'] = ["rate_4"=>$yapei['change_home_rate'], "rate_5"=>$yapei['change_rate'], "rate_6"=>$yapei['change_away_rate']];

            // 欧赔
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
}