<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Page;

class UsersController extends BaseController
{
    /**
     * 微信openid信息
     */
    public function index()
    {
        $city = M('users_all'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function userview()
    {
        $id = I('request.id');
        $model = M('users_all')->find($id);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 微信union信息
     *
     */
    public function union()
    {
        $city = M('users_union'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function unionview()
    {
        $id = I('request.id');
        $model = M('users_union')->find($id);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 绑定用户
     */
    public function member()
    {
        $city = D('users_member'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('create_time')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function memberview()
    {
        $id = I('request.id');
        $model = M('users_member')->find($id);
        $this->assign('model', $model);
        $this->display();
    }

    public function address()
    {
        $model = D('users_address');
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function addressview()
    {
        $id = I('request.id');
        $model = M('users_address')->find($id);
        $this->assign('model', $model);
        $this->display();
    }
}