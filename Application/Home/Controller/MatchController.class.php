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
            $curr = $mongo->zyd->match;
            $p = I('POST.p', 1,'intval');
            $type = I('POST.type','');// 1进行中， 2已完成，3为开始，个人收藏
            $league_ids = I('POST.league_ids',[]);
            $list = $curr->find()->sort(array("update_time"=>1))->limt(10);


        }while(false);
        $this->ajaxReturn($json);
    }
}