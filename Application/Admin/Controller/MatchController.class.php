<?php
namespace Admin\Controller;
use Think\Controller;
class MatchController extends BaseController {
    public function index(){
        $mongo  = $this->initMongo();
        $curr = $mongo->zyd->match;
        $page = I('p',1,'intval');
        $total = $curr->count();
        $obj = $curr->find()->sort(array('match_id'=>1))->skip( ($page-1)*20)->limit(20);
        $list = [];
        while($row = $obj->hasNext()){
            $list[] = $obj->getNext();
        }
        $page = new \Think\Page($total, 20);
        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }
}