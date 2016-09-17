<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

class ResponseController extends BaseController {
    public $default_pic = '/Public/static/userimg.jpg';
    /**
     *
     */
    public function index(){
        header("Content-type:text/html;charset=utf-8");
        $this->assign('title', '用户信息反馈');
        $username = I('request.username', '','strval');
        $contact = I('request.contact', '','strval');
        $this->assign('username', $username);
        $this->assign('contact', $contact);
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

        do{
            $sign = I('post.sign','','strval');
            if($sign != session('sign')){
                $json['state'] = 2;
                $json['msg'] = '验证不通过~';
                break;
            }
            session('sign','-');
            $data['username'] = I('post.username','','strip_tags,htmlspecialchars');
            $data['contact'] = I('post.contact','','strip_tags,htmlspecialchars');
            $data['content'] = I('post.content','','strip_tags,htmlspecialchars');
            $data['create_time'] = date("Y-m-d H:i:s");
            M('response')->add($data);

        }while(false);
        $this->ajaxReturn($json);

    }

    public function reg(){
        $share_id = strip_tags(trim($_GET['share_id']));
        $this->assign('share_id', $share_id);
        $this->display();
    }
    /**
     * json返回格式
     */
    public function simpleJson(){
        return [
            'status' => 100,
            'msg'  => '',
            'time' => time(),
            'data' => []
        ];
    }
    /**
     * 短信验证
     */
    public function send(){
        $json = $this->simpleJson();
        do{
            // 1 注册, 2登录, 3,找回密码
            $type = 1;
            $mobile = I('request.mobile','','strval');
            if( ! is_mobile($mobile)){
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }
            $_SESSION['total_num'] = intval($_SESSION['total_num']+1);
            $total_num = $_SESSION['total_num'];
            if($total_num > 10){
                $json['status'] = 111;
                $json['msg'] = '请不要频繁获取短信验证码';
                break;
            }

            //
            $time = time() - 1800;
            $send_total = M('sms_log')->where(array('mobile'=>$mobile, 'send_time'=>array('gt', $time)))->count();
            if($send_total > 10){
                $json['status'] = 111;
                $json['msg'] = '超过数量限制';
                break;
            }
            $data = [
                'type' => $type,
                'mobile' => $mobile,
                'msg' => random(6,'number'),
                'send_time' => time(),
                'status' => 0,
                'log' => ''
            ];
            switch($type){
                // 注册【章鱼帝】您的验证码是
                case 1:
                    $data['log'] = "【章鱼帝】您的验证码是{$data['msg']}";
                    $member = M('users')->where(array('mobile'=>$mobile))->find();
                    if($member){
                        $json['status'] = 111;
                        $json['msg'] = '该手机号已被注册';
                        break;
                    }
                    break;
                case 2:
                    $member = M('users')->where(array('mobile'=>$mobile))->find();
                    if(!$member){
                        $json['status'] = 111;
                        $json['msg'] = '没找到用户信息';
                        break;
                    }
                    $data['log'] = "【章鱼帝】您的验证码是{$data['msg']}";
                    break;
                case 3:
                    $member = M('users')->where(array('mobile'=>$mobile))->find();
                    if(!$member){
                        $json['status'] = 111;
                        $json['msg'] = '没找到用户信息';
                        break;
                    }
                    $data['log'] = "【章鱼帝】您的验证码是{$data['msg']}";
                    break;
                default:
                    $data['log'] = "【章鱼帝】您的验证码是{$data['msg']}";
                    break;
            }
            if($json['status'] == 100){
                M('sms_log')->add($data);
                if($data['log']){
                    $res = send_sms($mobile, $data['log']);
                }

                $json['data'] = [
                    'mobile' => $mobile,
                    'res' => $res
                ];
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 提交注册
     */
    public function dored(){
        $json = $this->simpleJson();
        do{
            $mobile = trim($_POST['mobile']);
            $code = trim($_POST['code']);
            $share_id = trim($_POST['share_id']);

            if(!is_mobile($mobile)){
                $json['status'] = 110;
                $json['msg'] = "请正确输入手机号";
                break;
            }
            if(!is_numeric($code) || strlen($code) != 6){
                $json['status'] = 110;
                $json['msg'] = "请正确输入验证码";
                break;
            }
            ////////////////////////////////////////////////短信验证
            $time = time() - 600;
            $sms = M('sms_log')->where(array('mobile'=>$mobile,'send_time' => array('gt', $time)))->order("id DESC")->find();
            if(!$sms){
                $json['status'] = 111;
                $json['msg'] = '短信已过期';
                break;
            }

            if($sms['status'] > 0){
                $json['status'] = 111;
                $json['msg'] = '短信已过期';
                break;
            }

            if($code == $sms['msg']){
                M('sms_log')->where(array('id'=>$sms['id']))->save(array('status'=>1));
            }else{
                $json['status'] = 111;
                $json['msg'] = '验证码错误';
                break;
            }
            ////////////////////////////////////////////////短信验证
            $user = M('users')->where(array('mobile'=>$mobile))->find();
            if($user){
                $json['status'] = 111;
                $json['msg'] = '该手机号已被注册';
                break;
            }

            ////////////////////////////////////////////////用户注册

            $user = [
                'nickname' => $mobile,
                'mobile' => $mobile,
                'pic' => $this->default_pic
            ];


            $user['last_login_time'] = time();
            $user['last_login_type'] = 1;



            if($share_id){
                $share_user_id = intval(base64_decode($share_id));
                $user['share_user_id'] = $share_user_id;
            }
            $user['register_time'] = time();
            $user['update_time'] = time();
            $user['ssid'] = get_login_ssid();
            $user_id = M('users')->add($user);

            if($user_id){
                $json['msg'] = '用户注册成功';
                $json['data'] = 1;
            }else{
                $json['status'] = 111;
                $json['msg'] = '用户注册失败';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    // 推送测试
    public function test(){
        echo date("Y-m-d H:i:s");
        $jiguang_alias = [
           // 'U10016','U10007','U10008','U10009','U10010'
        ];
        $jiguang_id = [
            '121c83f7602c1ae6e6a','13165ffa4e0e30cf716','1114a89792a1ad1d258','141fe1da9ea34dcf8fe','121c83f7602c1aabff1'
        ];
        $match_title = "测试比赛推送:收到在群里@下2222";
        $res = send_tuisong($jiguang_alias, $jiguang_id,'比赛即将开始:收到在群里@下2222',$match_title,0,1305590);
        var_dump($res);
    }
}