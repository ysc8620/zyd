<?php
namespace Admin\Controller;
use Think\Controller;
class BasicController extends BaseController {

    /**
     * 联赛信息
     */
    public function league(){
        $mongo  = $this->initMongo();
        $curr = $mongo->zyd->league;
        $page = I('p',1,'intval');
        $total = $curr->count();
        $obj = $curr->find()->sort(array('update_time'=>-1))->skip( ($page-1)*20)->limit(20);
        $list = [];
        while($row = $obj->hasNext()){
            $list[] = $obj->getNext();
        }
        $page = new \Think\Page($total, 20);
        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 联赛编辑
     */
    public function league_edit(){
        $mongo = $this->initMongo();
        $curr = $mongo->zyd->league;
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选择联赛', U('basic/league'));
        }
        if(IS_POST){

        }

        $league = $curr->findOne(array('league_id'=>$id));
        $this->assign('league', $league);

        $this->display();
    }
}