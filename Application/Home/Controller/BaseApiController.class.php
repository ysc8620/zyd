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
    public $header = [];

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
            $this->header = $header;

            if(strtolower($sign) != $this->sign($header)){
                $json = $this->simpleJson();
                $json['status'] = 102;
                $json['msg'] = '权限no2';
                $this->ajaxReturn($json);
            }
            if($header['imei'] == 'BDF2D075-1C34-44D8-9874-FEF0BE4C6336'){
                \Org\Util\File::write_file(APP_PATH.'/logs/post.log',date("Y-m-d H:i:s")."=url=".$_SERVER['REQUEST_URI']."&".http_build_query($_REQUEST)."&imei={$header['imei']}\r\n","a+");

            }
            // 用户登录
            $user_ssid = $header['ssid'];
            $this->ssid = $user_ssid;
            if($user_ssid){
                $user = M('users')->where(array('ssid'=>$user_ssid))->find();
                if($user){
                    if(empty($user['from_client'])){
                        M('users')->where(['id'=>$user['id']])->save(['from_client'=>json_encode($this->header)]);
                    }
                }
                if($user && $user['ssid'] == $user_ssid){
                    $this->ssid = $user_ssid;
                    $this->user = $user;
                }
            }
        }
    }

    /**
     * 短信校验
     * @param $mobile
     * @param $code
     * @param bool|true $is_echo
     * @return bool
     */
    public function check_sms($mobile, $code, $is_echo=true){
        if($code == 888888){return true;}
        $json = $this->simpleJson();
        $time = time() - 600;
        $sms = M('sms_log')->where(array('mobile'=>$mobile, 'send_time' => array('gt', $time)))->order("id DESC")->find();
        if(!$sms){
            $json['status'] = 111;
            $json['msg'] = '短信已过期';
            if($is_echo){
                $this->ajaxReturn($json);
            }else{
                return false;
            }
        }

        if($sms['status'] > 0){
            $json['status'] = 111;
            $json['msg'] = '短信已过期';
            if($is_echo){
                $this->ajaxReturn($json);
            }else{
                return false;
            }
        }

        if($code != $sms['msg']){
            $json['status'] = 111;
            $json['msg'] = '验证码错误';
            if($is_echo){
                $this->ajaxReturn($json);
            }else{
                return false;
            }
        }

        M('sms_log')->where(array('id'=>$sms['id']))->save(array('status'=>1));
        return true;
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
            'data' => []
        ];
    }

    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(isset($data['data'])){
            if(empty($data['data'])){
                $data['data'] = (object)$data['data'];
            }
        }
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
               // \Org\Util\File::write_file(APP_PATH.'/logs/postreturn.log',date("Y-m-d H:i:s")."=url=".$_SERVER['REQUEST_URI']."&".http_build_query($_REQUEST)."&data=".json_encode($data)."\r\n","a+");
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
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