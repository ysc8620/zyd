<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;
class SportteryController extends BaseController {
    public function yapei(){
        $curr = M('asia_yapei'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function oupei(){

        $curr = M('asia_oupei'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function daxiaoqiu(){

        $curr = M('asia_daxiaoqiu'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function half(){

        $curr = M('asia_half'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function half_daxiaoqiu(){

        $curr = M('asia_half_daxiaoqiu'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function baiou(){

        $curr = M('baiou'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出

        $this->display();
    }

    public function jingcai(){
        $curr = M('jingcai'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    // 竞彩编辑
    public function jingcai_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选竞彩', U('sporttery/jingcai'));
        }

        if(IS_POST){
            $data = $_POST;
            $data['update_time'] = time();
            $res = M('jingcai')->where(array('id'=>$id))->save($data);
            if($res){
                return $this->success('编辑成功',U('sporttery/jingcai'));
            }else{
                return $this->error('编辑失败', U('sporttery/jingcai_edit',array('id'=>$id)));
            }
        }

        $jingcai = M('jingcai')->where(array('id'=>$id))->find();
        $this->assign('jingcai', $jingcai);

        $this->display();
    }

    public function beidan(){
        $curr = M('beidan'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 北单编辑
     */
    public function beidan_edit(){
        $id = I('request.id',0,"intval");
        if(empty($id)){
            return $this->error('请选竞彩记录', U('sporttery/beidan'));
        }

        if(IS_POST){
            $data = $_POST;
            $data['update_time'] = time();
            $res = M('beidan')->where(array('id'=>$id))->save($data);
            if($res){
                return $this->success('编辑成功',U('sporttery/beidan'));
            }else{
                return $this->error('编辑失败', U('sporttery/beidan_edit',array('id'=>$id)));
            }
        }

        $referee = M('beidan')->where(array('id'=>$id))->find();
        $this->assign('beidan', $referee);

        $this->display();
    }

    public function jiaoqiu(){
        $curr = M('jiaoqiu'); // 实例化User对象

        $count = $curr->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出

        $list = $curr->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }
}