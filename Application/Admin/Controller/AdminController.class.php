<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Page;
use Org\Util\File;

class AdminController extends BaseController
{
    /**
     * 微信openid信息
     */
    public function index()
    {
        $city = M('admin'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    public function edit(){
        $id = I('request.id',0,'intval');
        if(IS_POST){
            $password = I('post.password', '','strval');
            $uname = I('post.uname','','strval');
            $status = I('post.status',0,'strval');
            $id = I('post.id',0,'intval');

            if(strlen($uname) < 3 || strlen($uname) > 26){
                return $this->error('用户名3-26个字符之间.', tsurl('admin/edit',array('id'=>$id)));
            }
            $data = array();
            $data['uname'] = $uname;
            $data['status'] = $status;
            if($password){
                $password2 = I('post.password2','','strval');
                if($password != $password2){
                    return $this->error('两次输入密码不对', tsurl('admin/edit',array('id'=>$id)));
                }
                $data['salt'] = random(12,'all');
                $data['pwd'] = encrypt_password($password, $data['salt']);
            }else{
                if(empty($id)){
                    return $this->error('请输入登录密码',tsurl('admin/edit'));
                }
            }

            $data['update_time'] = time();
            if($id){
                $user = M('admin')->where(array('uname'=>$uname))->find();
                if($user && $user['id'] != $id){
                    return $this->error('用户名已经存在',tsurl('admin/edit', array('id'=>$id)));
                }
                $res = M('admin')->where(array('id'=>$id))->save($data);
            }else{
                $user = M('admin')->where(array('uname'=>$uname))->find();
                if($user){
                    return $this->error('用户名已经存在',tsurl('admin/edit'));
                }
                $res = M('admin')->add($data);
            }

            if($res){
                return $this->success('保存成功',tsurl('admin/index'));
            }else{
                return $this->error('编辑失败',tsurl('admin/edit',array('id'=>$id)));
            }

        }
        if($id){
            $admin = M('admin')->find($id);
            $this->assign('user', $admin);
        }
        $this->display();
    }


    public function set(){
        if(IS_POST){
            $config = (array)$_POST['config'];
            arr2file(APP_PATH .'/Runtime/Conf/config.php',$config);
            if(file_exists(APP_PATH .'/Runtime/common~runtime.php')){
                unlink(APP_PATH .'/Runtime/common~runtime.php');
            }
            return $this->success('保存成功', tsurl('admin/set'));
        }
        $config = array();
        if(file_exists(APP_PATH .'/Runtime/Conf/config.php')){
            $config = include(APP_PATH .'/Runtime/Conf/config.php');
        }

        $this->assign('config', $config);
        $this->display();
    }

    //清除系统缓存AJAX
    public function del(){
        header("Content-Type: text/html; charset=UTF-8");
        @unlink(APP_PATH .'/Runtime/common~runtime.php');
        if(!File::empty_dir(APP_PATH .'/Runtime/Data/_fields')){File::del_dir(APP_PATH.'/Runtime/Data/_fields');}
        if(!File::empty_dir(APP_PATH .'/Runtime/Temp')){File::del_dir(APP_PATH.'/Runtime/Temp');}
        if(!File::empty_dir(APP_PATH .'/Runtime/Cache')){File::del_dir(APP_PATH.'/Runtime/Cache');}
        if(!File::empty_dir(APP_PATH .'/Runtime/Logs')){File::del_dir(APP_PATH.'/Runtime/Logs');}
        echo('清除成功');
    }
}