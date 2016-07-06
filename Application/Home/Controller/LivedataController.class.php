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
        $data = $_POST['data'];
        if($data){
            if(strpos($data,'Soccer') !== false){
                $h = array(
                    'post_data'=>$data,
                    'addtime'=>date("Y-m-d H:i:s")
                );
                M('post')->add($h);
                $str = date("Y-m-d H:i:s")."start===========\r\n";
                $str .= json_encode($data)."\r\n";
                $str .= date("Y-m-d H:i:s")."end===========\r\n";
                \Org\Util\File::write_file(APP_PATH .'/post.log',$str,'a+');


            }else{
                $str = date("Y-m-d H:i:s")."start===========\r\n";
                $str .= json_encode($data)."\r\n";
                $str .= date("Y-m-d H:i:s")."end===========\r\n";
                \Org\Util\File::write_file(APP_PATH .'/other_post.log',$str,'a+');
            }
        }
    }
}