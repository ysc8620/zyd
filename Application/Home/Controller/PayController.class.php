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
     * 获取苹果产品列表
     */
    public function get_apple_list(){
        $json = $this->simpleJson();
        do{
            $list = M("product")->where(array('status'=>1))->order("create_time DESC")->select();
            $json['data'] = $list;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 支付宝，微信支付
     */
    public function api(){

    }


}