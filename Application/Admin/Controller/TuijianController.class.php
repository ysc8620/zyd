<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;
class TuijianController extends BaseController {
    public function index(){
        $state = I('status','','strval');
        $match = M('tuijian'); // 实例化User对象
        if($state !== ''){
            $match->where(array('state'=>$state));
        }
        $count = $match->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        if($state !== ''){
            $match->where(array('state'=>$state));
        }
        $this->assign('state', $state);
        $list = $match->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$item){
            $info = M('match')->field('id,match_id,time,league_id,league_name,home_name,away_name,home_score,away_score')->where(array('match_id'=>$item['match_id']))->find();
            $user = M('users')->where(array('id'=>$item['user_id']))->find();
            $item['match'] = $info;
            $item['user'] = $user;
            $list[$i] = $item;
        }
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 赛事关注列表
     */
    public function pay(){
        $match = M('tuijian_order'); // 实例化User对象

        $count = $match->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性

        $list = $match->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$item){
            $tuijian = M('tuijian')->where(array('id'=>$item['tuijian_id']))->find();
            $info = M('match')->field('id,match_id,time,league_id,league_name,home_name,away_name,home_score,away_score')->where(array('match_id'=>$tuijian['match_id']))->find();
            $user = M('users')->where(array('id'=>$item['user_id']))->find();
            $item['tuijian'] = $tuijian;
            $tuijia_user = M('users')->where(array('id'=>$tuijian['user_id']))->find();
            $item['tuijian_user'] = $tuijia_user;
            $item['match'] = $info;
            $item['user'] = $user;
            $list[$i] = $item;
        }
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function del(){
        $id = I('request.id',0,'intval');
        if($id){
            M('tuijian')->where(['id'=>$id])->delete();
        }
        $this->success('删除成功');
    }
}