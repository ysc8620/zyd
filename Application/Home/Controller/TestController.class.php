<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class TestController extends BaseApiController {
    /**
     *
     */
    public function test(){
        $json = $this->simpleJson();
        $data = $_POST;
        unset($data['appid']);
        unset($data['version']);
        unset($data['time']);
        unset($data['sign']);
        $json['data'] = $data;
        $this->ajaxReturn($json);
    }
}