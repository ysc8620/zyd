<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

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
        $mongo = $this->initMongo();
        do{
            $page = I('request.p', 1,'intval');
            $type = I('request.type','');// 1进行中， 2已完成，3为开始，4个人收藏
            $league_ids = (array)I('request.league_ids',[]);
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
            $list = M('match')->field('match_id,time as match_time,league_id,league_name,home_id,home_name,home_score,away_id,away_name,away_score')->where($where)->order("time DESC")->limit(10)->select();
            foreach($list as $i=>$item){
                $baiou = M('baiou')->where(array('match_id'=>$item['match_id']))->find();
                $list[$i]['begin_home_win'] = floatval("{$baiou['begin_home_win']}");
                $list[$i]['begin_draw'] = floatval("{$baiou['begin_draw']}");
                $list[$i]['begin_away_win'] = floatval("{$baiou['begin_away_win']}");
                $list[$i]['home_win'] = floatval("{$baiou['home_win']}");
                $list[$i]['draw_win'] = floatval("{$baiou['draw_win']}");
                $list[$i]['away_win'] = floatval("{$baiou['away_win']}");
                $event_list = M('event')->where(array('match_id'=>$item['match_id']))->order("time ASC")->select();
                $list[$i]['events'] = (array)$event_list;
                $list[$i]['match_name'] = '';
                $list[$i]['home_corner'] = 0;
                $list[$i]['away_corner'] = 0;
            }
            $json['data']['list'] = $list;
            $json['data']['total'] = $total;
            $json['data']['page'] = $page;
            $json['data']['total_page'] = ceil($total/10);
        }while(false);
        $this->ajaxReturn($json);
    }
}