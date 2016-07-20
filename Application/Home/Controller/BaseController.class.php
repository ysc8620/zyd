<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\File;
use Think\Exception;
use Weixin\MyWechat;
class BaseController extends Controller {

    public $wechat = null;
    public $from = 0;
    public static $mongo = null;

    /**
     * 初始化操作
     */
    public function _initialize(){

    }


}