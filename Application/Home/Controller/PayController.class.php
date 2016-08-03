<?php
namespace Home\Controller;

class PayController extends BaseApiController {

    /**
     * 苹果支付
     */
    public function apple(){
        $json = $this->simpleJson();
        do{

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 支付宝，微信支付
     */
    public function api(){

    }


}