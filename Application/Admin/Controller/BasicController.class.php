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
        $list = $curr->sort(array('update_time'=>-1))->skip( ($page-1)*20)->limit(20)->find();

        $page = new \Think\Page($total, 20);
        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }
}