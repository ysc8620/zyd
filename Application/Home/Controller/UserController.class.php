<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class IndexController extends BaseController {

    /**
     *
     */
    public function index(){

        header("Content-type:text/html;charset=utf-8");
        echo 'ok';
    }

    /**
     *
     */
    public function test(){
        $d = new MyWechat(['appid'=>'123123','appsecret'=>'234234234']);

    }
}