<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\File;
use Think\Exception;
use Weixin\MyWechat;
class BaseController extends Controller {

    public $wechat = null;
    public $from = 0;

    /**
     * 初始化操作
     */
    public function _initialize(){
        $this->openid = '';
        $this->from = I('request.from',0,'intval');

        if($this->from != 4 && strtolower(CONTROLLER_NAME) != 'access'){
            $this->initPage($this->type, $this->from);
            $this->openid = session('openid'.$this->type);
        }else{
            $this->openid = 'obZe1uAPFrT9R46w_aYoQbEOf6Ns';
        }

        if( !$this->openid){
            die('No Found Openid.');
        }
    }


    /**
     * 调用微信类返回 access_token
     * @param  int $type 城市ID
     * @return object 微信公共类的对象
     */
    protected function initWechat()
    {
        if ($this->wechat) {
            return $this->wechat;
        }

        $cityInfo = C('weixin');
        if(empty($cityInfo)){
            die('No Found Weixin Option.');
        }
        $options = array(
            'token' => $cityInfo['red_token'], //填写你设定的key
            'encodingaeskey' => $cityInfo['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid' => $cityInfo['appid'], //填写高级调用功能的app id
            'appsecret' => $cityInfo['appsecret'] //填写高级调用功能的密钥
        );

        return $this->wechat = new MyWechat($options);
    }

    /**
     * 用户给公众号发信息,公众号处理并返回下面的信息
     * @param object $weObj 微信公共类
     * @param string $msgType 事件类型
     */
    protected function msgReply($weObj,$msgType){
        if(true){
            $msg1 = "玩游戏、抢红包、攒积分，聚宝商城百万豪礼，等你来抢";
            switch($msgType){
                case "text" :
                    $content = $weObj->getRevContent();
                    $msg_data = D('CityAutoReply')->get_reply_msg( 'text', $content);
                    if($msg_data){
                        if($msg_data['reply_type'] == 'text'){
                            $weObj->text($msg_data['reply_content'])->reply();
                            exit;
                        }elseif($msg_data['reply_type'] == 'img'){
                            $weObj->image($msg_data['reply_content'])->reply();
                            exit;
                        }
                    }
                    break;

                case "subscribe" :
                    $openid =  $weObj->getRevFrom();
                    $user = D('Users')->get_user($openid);
                    session('openid', $openid);

                    cookie("FUserId", $openid, time() + 1800);
                    //设置一个虚拟的设备id，不然签到无法顺利插入数据
                    if($user){
                        $data = array('is_subscribe'=>1, 'subcribe_time'=>time());
                        D('Users')->update_user($openid, $data);
                    }else {
                        $uinfo = $this->getUserInfo();
                        $data = array(
                            'openid' => $openid,
                            'is_subscribe' => 1,
                            'subcribe_time' => time(),
                            'create_time' => time()
                        );

                        if ($uinfo) {
                            $data['unionid'] = $uinfo['unionid'];
                            $data['wx_name'] = $uinfo['nickname'];
                            $data['wx_pic'] = $uinfo['headimgurl'];
                            $data['wx_sex'] = $uinfo['sex'];
                            $data['wx_city'] = $uinfo['city'];
                            $data['wx_province'] = $uinfo['province'];
                            $data['wx_country'] = $uinfo['country'];
                            $data['wx_remark'] = $uinfo['remark'];
                            $data['wx_groupid'] = $uinfo['groupid'];
                        }

                        // subscribe, openid,nickname,sex,language,city,province,country,headimgurl,subscribe_time,unionid,remark,groupid
                        // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                        D('Users')->init_user($data);
                    }

                    $msg_data = D('CityAutoReply')->get_reply_msg( 'event', 'subscribe');

                    $weObj->text($msg_data?$msg_data['reply_content']:$msg1)->reply();
                    die();
                    break;

                // 用户取消关注
                case 'unsubscribe':
                    $openid =  $weObj->getRevFrom();
                    $data = array('is_subscribe'=>0, 'unsubcribe_time'=>time());
                    D('Users')->update_user($openid,$data);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * 分享接口的信息
     * @param $type 城市id
     * @param bool $ajax 是否是ajax请求,是的话返回数据
     * @return array|null 直接渲染模板 或 返回分享接口的凭据信息
     */
    protected function getShareSign($ajax=false)
    {
        $wechat = $this->initWechat();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $signPackage = $wechat->getJsSign($url);
        $share = [
            "appid" => $signPackage["appid"],
            "str" => $signPackage['noncestr'],
            "time" => $signPackage['timestamp'],
            "ticket" => $signPackage['signature']
        ];
        if($ajax){
            return $share;
        }

        $this->assign('onlyurl', $share);
    }


    /**
     * 初始化页面,主要是要获取用户的openid 并将openid写入reidis
     * @param int $type 城市ID
     * @param int $from 来源 1:摇一摇进来的, 2:菜单进来的, 3:分享链接进来的, 4:用来调试的
     */
    protected function initPage(){
        /*******************初始化******/
        $wechat = $this->initWechat();

        $FUserId = cookie('FUserId');
        $openid = session('openid');


                /**
                 * @shengyue 2016-05-26 session改成redis
                 */
                //if (empty($FUserId) || !isset($_SESSION["openid".$type]))
                //if (empty($FUserId) || !session("openid".$type))
                if(true)
                {
                    //用户授权
                    $info = $this->authorize();
                    if ($info) {
                        $FUserId = $info['openid'];
                        cookie("FUserId" , $info['openid'], time() + 1800);
                        //设置一个虚拟的设备id，不然签到无法顺利插入数据
                        // $_SESSION["openid".$type] = $FUserId;
                        session("openid", $FUserId);

                        // 获取微信用户信息
                        $weObj = $this->initWechat();
                        $openid = $info["openid"];
                        $access_token = $info["access_token"];
                        $info = $weObj->getOauthUserinfo($access_token, $openid);

                        //openid,nickname,sex,province,city,country,headimgurl,unionid

                        /*
                         `id`, `unionid`, `openid`, `cityid`, `wx_name`, `wx_pic`, `wx_sex`, `wx_city`, `wx_province`, `wx_country`,
                        `wx_remark`, `wx_groupid`, `tag_list`, `beacon_id`, `is_subscribe`, `subcribe_time`, `unsubcribe_time`, `create_time`*/
                        $user = [
                            'openid' =>$openid,
                            'wx_name' =>$info['nickname'],
                            'unionid' =>$info['unionid'],
                            'wx_sex' =>$info['sex'],
                            'wx_city' =>$info['city'],
                            'wx_province'=>$info['province'],
                            'wx_country' => $info['country'],
                            'wx_pic' => $info['headimgurl']
                        ];
                        $user_info = D('Users')->get_user($openid);
                        if($user_info){
                            D('Users')->update_user($openid, $user);
                        }else{
                            // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                            D('Users')->init_user($user);
                        }

                    } else {
                        File::write_file(APP_PATH .'log/error.log',  "authorize error : " . $info,'a+');
                        $error = "网络繁忙，请稍后再试";
                    }
                }

        if (empty($FUserId)) {
            header("Content-type: text/html; charset=utf-8");
            $context = "<html><script>alert('" . $error . "')</script></html>";
            exit($context);
        }

    }


    /**
     * 获取授权access_token 用户进来必须要用到此方法
     * @param int $type 城市ID
     * @return bool 获取授权access_token 是否成功
     */
    protected function authorize()
    {

        $weObj = $this->initWechat();

        if (isset($_GET['code'])) {
            $info = $weObj->getOauthAccessToken();
            if ($info) {
                /**
                 * @ShengYue 2016-05-26 session改成使用redis
                 */
                $expire = $info["expires_in"] ? intval($info["expires_in"]) - 100 : 3600;
                $oauthname = "oauth_access_token" . $info["openid"];
                //$_SESSION[$oauthname] = $info['access_token'];
                session($oauthname, $info['access_token'], $expire);
                return $info;
            } else {
                File::write_file(APP_PATH.'log/error.log', "getOauthAccessToken error,errCode:" . $weObj->errCode . "  errMsg: " . $weObj->errMsg,'a+');
            }
            return false;
        } else {
            $url = $weObj->getOauthRedirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], uniqid());
            if ($url) {
                header("Location:$url");
                exit;
            }
            return false;
        }
    }

    /*获取用户的详细信息*/
    protected function getUserInfo(){
        /**
         * @shengyue 2016-05-26 session改成redis
         */
        // $openid = $_SESSION["openid".$type];
        $openid = session("openid");
        if( empty($openid)) return false;

        $weObj = $this->initWechat();
        $uinfo = $weObj->getUserInfo($openid);

        return $uinfo;
    }


}