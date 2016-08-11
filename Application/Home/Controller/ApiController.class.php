<?php
namespace Home\Controller;

use Think\Crypt\Driver\Think;
use Think\Exception;

class ApiController extends BaseController {


    /**
     * 回调地址
     */
    public function notify(){
        $type = I('request.type','','trim');
        if($type == 'weixin'){
            require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/example/notify.php";
            \Log::DEBUG(date("Y-m-d H:i:s")."begin notify");
            $notify = new \PayNotifyCallBack();
            $notify->Handle(false);

        }elseif($type == 'alipay'){
            echo 'okalipay';
        }else{
            die('error');
        }
    }


}