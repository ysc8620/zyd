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

    /**
     * 初始化操作
     */
    public function _initialize(){
        $header = getallheaders();
        $sign = $header['sign'];
        $appid = $header['appid'];
        if(empty($sign) || empty($appid)){
            $json = $this->simpleJson();

            $json['status'] = 102;
            $json['msg'] = '没有权限1';
            $this->ajaxReturn($json);
        }

        $header['appsecret'] = C('app')[$appid];


        if(strtolower($sign) != $this->sign($header)){

            $json = $this->simpleJson();

            $json['status'] = 102;
            $json['msg'] = '没有权限2';
            $this->ajaxReturn($json);
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
        return md5(trim($data['appVersion']).trim($data['appid']).trim($data['time']).trim($data['appsecret']));
    }

}