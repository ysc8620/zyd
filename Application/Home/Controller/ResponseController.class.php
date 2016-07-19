<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class ResponseController extends BaseController {

    /**
     *
     */
    public function index(){
        header("Content-type:text/html;charset=utf-8");
        $this->assign('title', '用户信息反馈');
        $sign = md5(microtime(true));
        session('sign', $sign);
        $this->assign('sign', $sign);
        $this->display();
    }

    /**
     *
     */
    public function post(){
        $json = ['state' => 1, 'msg' => ''];
        $mongo = $this->initMongo();
        $curr = $mongo->zyd->response;
        do{
            $sign = I('post.sign','','strval');
            if($sign != session('sign')){
                $json['state'] = 2;
                $json['msg'] = '验证不通过~';
                break;
            }

            $data['username'] = I('post.username','','strip_tags,htmlspecialchars');
            $data['contact'] = I('post.contact','','strip_tags,htmlspecialchars');
            $data['content'] = I('post.content','','strip_tags,htmlspecialchars');
            $curr->insert($data);

        }while(false);
        $this->ajaxReturn($json);

    }
}