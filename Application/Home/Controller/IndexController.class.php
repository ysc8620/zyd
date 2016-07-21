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
        M()->startTrans();
        $a = M()->execute("UPDATE t_bank SET credit=credit-20 WHERE credit>=20 AND id=1");
        $b = M()->execute("UPDATE t_goods SET sku=sku-1 WHERE sku>=1 AND id=1");
        if(true){
            M()->commit();
            echo 'ok='.date("Y-m-d H:i:s");

        }else{
            M()->rollback();
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