<?php
namespace Admin\Controller;
use Think\Controller;
class BasicController extends BaseController {

    /**
     * 联赛信息
     */
    public function league(){
        $total = M('league')->count();
        $page = new \Think\Page($total, 20);
        $list = M('league')->order('league_id DESC')->limit($page->firstRow, $page->listRows)->select();

        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 联赛编辑
     */
    public function league_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选择联赛', U('basic/league'));
        }
        if(IS_POST){

        }

        $league =  M('league')->where(array('league_id'=>$id))->find();
        $this->assign('league', $league);

        $this->display();
    }

    /**
     * 球队列表
     */
    public function team(){
        $total = M('team')->count();
        $page = new \Think\Page($total, 20);
        $list = M('team')->order('team_id DESC')->limit($page->firstRow, $page->listRows)->select();

        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 球队编辑
     */
    public function team_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选择球队', U('basic/team'));
        }
        if(IS_POST){

        }

        $team = M('team')->where(array('team_id'=>$id))->find();
        $this->assign('team', $team);

        $this->display();
    }

    /**
     * 赛事列表
     */
    public function player(){
        $total = M('player')->count();
        $page = new \Think\Page($total, 20);
        $list = M('player')->order('player_id DESC')->limit($page->firstRow, $page->listRows)->select();

        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 球队编辑
     */
    public function player_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选球员', U('basic/player'));
        }
        if(IS_POST){

        }

        $player= M('player')->where(array('player_id'=>$id))->find();
        $this->assign('player', $player);

        $this->display();
    }

    public function referee(){
        $total = M('referee')->count();
        $page = new \Think\Page($total, 20);
        $list = M('referee')->order('referee_id DESC')->limit($page->firstRow, $page->listRows)->select();

        $this->assign('page', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 球队编辑
     */
    public function referee_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选裁判', U('basic/referee'));
        }
        
        if(IS_POST){

        }

        $referee = M('referee')->where(array('referee_id'=>$id))->find();
        $this->assign('referee', $referee);

        $this->display();
    }
}