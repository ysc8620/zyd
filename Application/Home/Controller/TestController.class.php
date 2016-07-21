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
        $json['data'] = ['id'=>1,'time'=>time()];
        // $json['header'] = getallheaders();
        \Org\Util\File::write_file('./newpost.log', date("Y-m-d H:i:s")."==========================\r\n".json_encode($json)."\r\n==================================\r\rn");
        $this->ajaxReturn($json);
    }
}