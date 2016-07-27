<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\File;
use Think\Exception;
use Weixin\MyWechat;
class BaseApiController extends BaseController {

    public $wechat = null;
    public $from = 0;
    public static $mongo = null;
    public $user = [];
    public $ssid = '';

    /**
     * 初始化操作
     */
    public function _initialize(){
        if($_SERVER["HTTP_HOST"] != 'api2.zydzuqiu.com'){
            $header = getallheaders();
            $header = array_change_key_case($header, CASE_LOWER);

            $sign = $header['sign'];
            $appid = $header['appid'];
            if(empty($sign) || empty($appid)){
                $json = $this->simpleJson();
                $json['status'] = 102;
                $json['msg'] = '权限no1';
                $this->ajaxReturn($json);
            }

            $header['appsecret'] = C('app')[$appid];

            if(strtolower($sign) != $this->sign($header)){
                $json = $this->simpleJson();
                $json['status'] = 102;
                $json['msg'] = '权限no2';
                $this->ajaxReturn($json);
            }

            // 用户登录
            $user_ssid = $header['ssid'];
            $this->ssid = $user_ssid;
            if($user_ssid){
                $user = M('users')->where(array('ssid'=>$user_ssid))->find();
                if($user && $user['ssid'] == $user_ssid){
                    $this->ssid = $user_ssid;
                    $this->user = $user;
                }
            }
        }
    }
    
    /**
     * 登录验证
     */
    public function check_login(){
        if(empty($this->user)){
            if($this->ssid){
                $json = $this->simpleJson();
                $json['status'] = 101;
                $json['msg'] = '登录超时,请重新登录';
                $this->ajaxReturn($json);
            }else{
                $json = $this->simpleJson();
                $json['status'] = 101;
                $json['msg'] = '请先登录';
                $this->ajaxReturn($json);
            }
        }
    }

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

    public function initMongo(){
        if(self::$mongo == null){
            try{
                self::$mongo = new \Mongo("mongodb://".C('MONGO_USER').":".C('MONGO_PWD')."@".C('MONGO_HOST').":".C('MONGO_PORT'));
            }catch (\Exception $e){
                exit('mongodb连接失败');
            }
        }

        return self::$mongo;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if(self::$mongo){
            try{
                self::$mongo->close();
            }catch (\Exception $e){}
        }
    }

    public function sign($data)
    {
        return md5(trim($data['appversion']).trim($data['appid']).trim($data['time']).trim($data['appsecret']));
    }

}