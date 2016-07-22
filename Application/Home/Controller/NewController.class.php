<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;



class NewController extends BaseController {
    /**
     * json返回格式
     */
    public function simpleJson(){
        return [
            'status' => 100,
            'msg'  => '',
            'time' => time(),
            'data' => ''
        ];
    }

    /**
     *
     */
    public function test(){
        $json = $this->simpleJson();
        $data = $_POST;
        $json['data'] = $data;
        $json['post'] = $_POST;
        $json['header'] = getallheaders();
        \Org\Util\File::write_file('./apipost.log', date("Y-m-d H:i:s")."==========================\r\n".json_encode($json)."\r\n==================================\r\rn");
        $this->ajaxReturn($json);
    }
}