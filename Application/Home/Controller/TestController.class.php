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
        $json['data'] = $_POST;
        $this->ajaxReturn($json);
    }
}