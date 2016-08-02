<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Page;

class DefrayController extends BaseController
{
    /**
     * 微信openid信息
     */
    public function sell()
    {
        $city = M('tuijian_order'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $i=>$item) {
            $user = M('users')->where(array('id'=>$item['user_id']))->find();
            $list[$i]['user'] = $user;
        }
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 充值列表
     */
    public function top(){
        $city = M('top'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $i=>$item) {
            $user = M('users')->where(array('id'=>$item['user_id']))->find();
            $list[$i]['user'] = $user;
        }
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function withdraw(){
        $this->display();
    }


    public function apple(){
        $city = M('product'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

}