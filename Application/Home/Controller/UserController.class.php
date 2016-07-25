<?php
namespace Home\Controller;

class MsgController extends BaseApiController {

    /**
     * 用户注册
     */
    public function register(){
        $json = $this->simpleJson();
        do{
            // 1 注册, 2登录, 3,找回密码
            $type = I('request.type',0,'intval');
            $mobile = I('request.mobile','','strval');
            if( ! is_mobile($mobile)){
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            //
            $time = time() - 1800;
            $send_total = M('sms_log')->where(array('send_time'=>array('gt', $time)))->count();
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

            send_sms($mobile, $data['log']);
            $json['data'] = [
                'code' => $data['msg'],
                'send_time' => $data['send_time'],
                'mobile' => $mobile
            ];
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
            if (!is_mobile($mobile)) {
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            //
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

            if($code == $sms['msg']){
                M('sms_log')->where(array('id'=>$sms['id']))->save(array('status'=>1));
                $json['msg'] = '验证成功';
                $json['data']['code'] = $code;
                $json['data']['mobile'] = $mobile;
                $json['data']['send_time'] = $sms['send_time'];
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '验证码错误';
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取用户信息
     */
    public function info(){

    }

}