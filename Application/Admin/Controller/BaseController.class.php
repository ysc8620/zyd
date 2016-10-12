<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {
    public static $mongo = null;
    public function _initialize(){
        /**
         * 验证登陆
         */
        if(! session('is_login')){
            return redirect(U('/admin/login/index'));
        }

        $this->admin = M('admin')->find(session('login_user_id'));
        if(!$this->admin){
            return redirect(U('/admin/login/index'));
        }

        $this->assign('admin', $this->admin);
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

}