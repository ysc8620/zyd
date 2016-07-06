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
        //$data['post_data'] = file_get_contents("php://input");
        $data = $_POST['data'];
        if($data){
            if(strpos($data,'Soccer') === false){
                $str = date("Y-m-d H:i:s")."==========================================\r\n";
                $str .= json_encode($data)."\r\n";
                $str .= date("Y-m-d H:i:s")."==========================================\r\n";
                \Org\Util\File::write_file(APP_PATH .'/post.log',$str,'a+');
            }else{
                $str = date("Y-m-d H:i:s")."==========================================\r\n";
                $str .= json_encode($data)."\r\n";
                $str .= date("Y-m-d H:i:s")."==========================================\r\n";
                \Org\Util\File::write_file(APP_PATH .'/other_post.log',$str,'a+');
            }
        }
//        //$data['get'] = $_GET;
//
//        $str = date("Y-m-d H:i:s")."==========================================\r\n";
//        $str .= json_encode($data)."\r\n";
//        $str .= date("Y-m-d H:i:s")."==========================================\r\n";
//        \Org\Util\File::write_file(APP_PATH .'/post.log',$str,'a+');
        echo "ok";
    }
}