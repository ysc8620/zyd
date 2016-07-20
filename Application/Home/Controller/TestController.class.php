<?php
namespace Home\Controller;
use Weixin\MyWechat;
use Home\Model\UsersModel;
use Redis\MyRedis;

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

class TestController extends BaseApiController {
    /**
     *
     */
    public function test(){
        $json = $this->simpleJson();
        $data = $_POST;
        unset($data['appid']);
        unset($data['version']);
        unset($data['time']);
        unset($data['sign']);
        unset($data['appsecret']);
        $json['data'] = $data;
        $json['header'] = getallheaders();
        \Org\Util\File::write_file('./post.log', date("Y-m-d H:i:s")."==========================\r\n".json_decode($json)."\r\n==================================\r\rn");
        $this->ajaxReturn($json);
    }
}