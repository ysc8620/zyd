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
            $json['load_pic'] = 'http://';
        }while(false);
        $this->ajaxReturn($json);
    }
}