<?php
namespace Home\Controller;

class ConfigController extends BaseApiController {

    /**
     * 系统初始化
     */
    public function init(){
        $json = $this->simpleJson();
        $json['data'] = array();

        $config = array();
        if(file_exists(APP_PATH .'/Runtime/Conf/config.php')){
            $config = include(APP_PATH .'/Runtime/Conf/config.php');
        }

        do{
            //
            $json['data'] = $config['api'];
        }while(false);
        $this->ajaxReturn($json);
    }
}