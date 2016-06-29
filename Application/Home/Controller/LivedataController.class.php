<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class LivedataController extends BaseController {

    /**
     *
     */
    public function index(){
        $data['post_data'] = file_get_contents("php://input");
        $data['post'] = $_POST;
        $data['get'] = $_GET;

        $str = date("Y-m-d H:i:s")."==========================================\r\n";
        $str .= json_encode($data);
        $str .= date("Y-m-d H:i:s")."==========================================\r\n";
        \Org\Util\File::write_file(APP_PATH .'/post.log',$str,'a+');
        echo "ok";
    }
}