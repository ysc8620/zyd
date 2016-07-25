<?php
namespace Home\Controller;

class UserController extends BaseApiController {

    /**
     * 用户注册
     */
    public function register(){
        $json = $this->simpleJson();
        do{
            //
            $mobile = I('request.mobile','','strval');
            $password = trim(I('request.password','','strval'));
            $code = I('request.code','','strval');

            if( ! is_mobile($mobile)){
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            if(!$code){
                $json['status'] = 110;
                $json['msg'] = '请输入验证码';
                break;
            }

            if($code != 888888){
                $time = time() - 600;
                $sms = M('sms_log')->where(array('send_time' => array('gt', $time)))->order("id DESC")->find();
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

                if($code != $sms['msg']){
                    $json['status'] = 111;
                    $json['msg'] = '验证码错误';
                    break;
                }
            }

            $user = [
                'nickname' => $mobile,
                'mobile' => $mobile,
                'pic' => '/Public/static/userimg.jpg',
                'status' => 1
            ];

            if($password){
                if( strlen($password) < 6 || strlen($password) > 26){
                    $json['status'] = 111;
                    $json['msg'] = '密码长度在6-26个字符之间';
                    break;
                }
                $user['salt'] = random(12,'all');
                $user['password'] = encrypt_password(trim($password), $user['salt']);
            }

            $member = M('users')->where(array('mobile'=>$mobile))->find();
            if($member){
                $json['status'] = 111;
                $json['msg'] = '该手机号已注册';
                break;
            }
            $user['register_time'] = time();
            $user['update_time'] = time();

            $user_id = M('users')->add($user);
            if($user_id){
                $member = M('users')->where(array('id'=>$user_id))->field("`id`, `nickname`, `pic`, `mobile`, `is_expert`, `vip`, `credit`, `total_top_credit`,`register_time`, `update_time`,  `total_send_info`, `total_collect_user`, `total_collect_match`, `total_follow_user`")->find();
                $member['pic'] = pic_url($member['pic']);
                $json['msg'] = '用户注册成功';
                $json['data'] = $member;
            }else{
                $json['status'] = 111;
                $json['msg'] = '用户注册失败';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 用户登录
     */
    public function login(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $code = I('request.code', 0, 'intval');
            $mobile = I('request.mobile', '', 'strval');
            $password = I('request.password','','strval');

            if (!is_mobile($mobile)) {
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            // 短信登录
            if($code){
                if($code != 888888){
                    $time = time() - 600;
                    $sms = M('sms_log')->where(array('send_time' => array('gt', $time)))->order("id DESC")->find();
                    if(!$sms || $sms['status'] > 0){
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
                }
                $member = M('users')->where(array('mobile'=>$mobile))->field("`id`, `nickname`, `pic`, `mobile`, `is_expert`, `vip`, `credit`,`register_time`, `update_time`,  `total_send_info`, `total_collect_user`, `total_collect_match`, `total_follow_user`")->find();
                // 用户锁定
                if(!$member){
                    $json['status'] = 111;
                    $json['msg'] = '登录信息错误';
                    break;
                }

                // 用户锁定
                if($member['status'] < 1){
                    $json['status'] = 111;
                    $json['msg'] = '用户异常不能正常使用';
                    break;
                }
                $member['pic'] = pic_url($member['pic']);
                $json['msg'] = '用户登录成功';
                $json['data'] = $member;

            // 密码登陆
            }elseif($password){
                $member = M('users')->where(array('mobile'=>$mobile))->field("`id`,`password`,`salt`, `nickname`, `pic`, `mobile`, `is_expert`, `vip`, `credit`,`register_time`, `update_time`,  `total_send_info`, `total_collect_user`, `total_collect_match`, `total_follow_user`")->find();
                // 用户锁定
                if(!$member){
                    $json['status'] = 111;
                    $json['msg'] = '登录信息错误';
                    break;
                }

                // 用户锁定
                if($member['status'] < 1){
                    $json['status'] = 111;
                    $json['msg'] = '用户异常不能正常使用';
                    break;
                }

                if($password != encrypt_password($member['password'], $member['salt'])){
                    $json['status'] = 111;
                    $json['msg'] = '登录信息错误';
                    break;
                }

                unset($member['password']);
                unset($member['salt']);
                $member['pic'] = pic_url($member['pic']);
                $json['msg'] = '用户登录成功';
                $json['data'] = $member;
            }else{
                $json['status'] = 111;
                $json['msg'] = '登录信息错误';
                break;
            }

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取用户信息
     */
    public function info(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $user_id = I('request.user_id', 0, 'intval');
            if(!$user_id){
                $json['status'] = 110;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            $member = M('users')->where(array('id'=>$user_id))->field("`id`,`nickname`, `pic`, `mobile`, `is_expert`, `vip`, `credit`,`register_time`, `update_time`,  `total_send_info`, `total_collect_user`, `total_collect_match`, `total_follow_user`")->find();
            // 用户锁定
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            // 用户锁定
            if($member['status'] < 1){
                $json['status'] = 111;
                $json['msg'] = '用户异常不能正常使用';
                break;
            }
            $member['pic'] = pic_url($member['pic']);
            $json['data'] = $member;

        }while(false);
        $this->ajaxReturn($json);
    }

}