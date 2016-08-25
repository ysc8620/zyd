<?php
namespace Home\Controller;

class ConfigController extends BaseApiController {

    /**
     * 系统初始化
     */
    public function init(){
        $json = $this->simpleJson();
        $json['data'] = (object)array();
        do{
            //
            $json['data']['share_url'] = 'http://api2.zydzuqiu.com/response/reg.html?share_id=';
        }while(false);
        $this->ajaxReturn($json);
    }
}