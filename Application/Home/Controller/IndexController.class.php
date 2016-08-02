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


        if(true){
            echo 'ok='.date("Y-m-d H:i:s");

        }else{
            echo 'false='.date("Y-m-d H:i:s");

        }

    }

    /**
     *
     */
    public function test(){
        $d = new MyWechat(['appid'=>'123123','appsecret'=>'234234234']);

    }
}