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
        $list = M("match")->where(array('state'=>array('in',[1,2,3,4])))->field("match_id,home_score,away_score,home_half_score,away_half_score,home_red,away_red,time,technic,state")->select();
        foreach($list as $i=>$item){
            //
            $list[$i]['match_time2'] = floor((time()-strtotime($item['time']))/60);
            $data = json_decode($item['technic'], true);
            $list[$i]['home_corner'] = isset($data['id6']['home'])?$data['id6']['home']:"0";
            $list[$i]['away_corner'] = isset($data['id6']['away'])?$data['id6']['away']:"0";
            unset($list[$i]['technic']);

            // 标准
            $baiou = M('asia_oupei')->where(array('match_id'=>$item['match_id']))->find();
            $list[$i]['begin_home_rate'] = "{$baiou['begin_home_rate']}";
            $list[$i]['begin_draw_rate'] = "{$baiou['begin_draw_rate']}";
            $list[$i]['begin_away_rate'] = "{$baiou['begin_away_rate']}";
            $list[$i]['change_home_rate'] = "{$baiou['change_home_rate']}";
            $list[$i]['change_draw_rate'] = "{$baiou['change_draw_rate']}";
            $list[$i]['change_away_rate'] = "{$baiou['change_away_rate']}";
        }
    }

    /**
     * 单独赛事直播信息
     */
    public function match(){

    }

    /**
     * 赛事直播推荐
     */
    public function tuijian(){

    }
}