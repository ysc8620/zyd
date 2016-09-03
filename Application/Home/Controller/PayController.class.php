<?php
namespace Home\Controller;

use Think\Crypt\Driver\Think;
use Think\Exception;

class PayController extends BaseApiController {

    /**
     * 苹果支付
     */
    public function apple(){
        $json = $this->simpleJson();
        do{
            try{
            \Org\Util\File::write_file('./post.log', date("Y-m-d H:i:s = ").json_encode($_POST)."\r\n","a+");
            $apple_id = I('request.apple_id','','strval');
            $product_id = I('request.product_id',0,'intval');
            $apple_receipt = I('request.apple_receipt','','strval');
            if(empty($apple_id)){
                $json['status'] = 110;
                $json['msg'] = "请选择充值苹果产品";
                break;
            }

            if(empty($product_id)){
                $json['status'] = 110;
                $json['msg'] = "请选择充值产品";
                break;
            }


            if(empty($apple_receipt)){
                $json['status'] = 110;
                $json['msg'] = "请提交收据信息";
                break;
            }

            $product = M('product')->where(array('id'=>$product_id))->find();
            if(!$product){
                $json['status'] = 111;
                $json['msg'] = "没找到充值产品";
                break;
            }

            if($product['apple_id'] != $apple_id){
                $json['status'] = 111;
                $json['msg'] = "充值产品信息有误";
                break;
            }

            $apple_receipt_md5 = md5($apple_receipt);
            $top = M('top')->where(array('apple_receipt_md5'=>$apple_receipt_md5))->find();
            if(!$top){
                $data = [
                    'type' => 3,
                    'product_id' => $product_id,
                    'credit' => $product['credit'],
                    'amount' => $product['amount'],
                    'order_no' => get_order_no(),
                    'apple_receipt' => $apple_receipt,
                    'apple_receipt_md5' => $apple_receipt_md5,
                    'user_id' =>intval( $this->user['id']),
                    'create_time' => time(),
                    'update_time' => time()
                ];
                $res = M('top')->add($data);
                if(!$res){
                    $json['status'] = 111;
                    $json['msg'] = "充值失败";
                    break;
                }
                $top = M('top')->where(array('id'=>$res))->find();
                $json['data']['top_id'] = $top['id'];
            }else{
                $json['data']['top_id'] = $top['id'];
            }

            $json['data']['apple_id'] = $apple_id;
            $json['data']['product_id'] = $product_id;

            #$apple_receipt = $this->_post('apple_receipt'); //苹果内购的验证收据,由客户端传过来
            $jsonData = array('receipt-data'=>$apple_receipt);//这里本来是需要base64加密的，我这里没有加密的原因是客户端返回服务器端之前，已经作加密处理
            $jsonData = json_encode($jsonData);
            $url = 'https://buy.itunes.apple.com/verifyReceipt';  //正式验证地址
            $response = http_post_data($url,$jsonData);

            if($response['status'] == 21007){
                $url = 'https://sandbox.itunes.apple.com/verifyReceipt'; //测试验证地址
                $response2 = http_post_data($url,$jsonData);

                if($response2['status'] == 0){
                    $data = [
                        'status' => 1,
                        'update_time' => time()
                    ];
                    if($top['status'] == 0){
                        M('top')->where(array('id'=>$top['id']))->save($data);
                        // credit, total_top_credit
                        M('users')->where(array('id'=>$top['user_id']))->save(array('credit'=>array('exp',"credit+{$top['credit']}"),'total_top_credit'=>array('exp',"total_top_credit+{$top['credit']}")));

                        $credit_log2 = [
                            'type' => 1,
                            'credit' => $top['credit'],
                            'from_id' => 0,
                            'remark' => "用户充值",
                            'create_time' => time(),
                            'user_id' => $top['user_id'],
                            'status' => 0,
                            'from_id'=>$top['id']
                        ];

                        $user3 = M("users")->where(['id'=>$top['user_id']])->field('id,credit')->find();
                        $credit_log2['total_credit'] = $user3['credit'];

                    }
                   $json['data']['status'] = 0;
                    break;
                }else{
                    $json['data']['status'] = $response2['status'];
                    break;
                }
            }else{
                if($response['status'] == 0){
                    $data = [
                        'status' => 1,
                        'update_time' => time()
                    ];
                    if($top['status'] == 0){
                        M('top')->where(array('id'=>$top['id']))->save($data);
                        // credit, total_top_credit
                        M('users')->where(array('id'=>$top['user_id']))->save(array('credit'=>array('exp',"credit+{$top['credit']}"),'total_top_credit'=>array('exp',"total_top_credit+{$top['credit']}")));

                    }
                    $json['data']['status'] = 0;
                    break;
                }else{
                    $json['data']['status'] = $response['status'];
                    break;
                }
            }
            }catch (\Exception $e){
                $json['status'] = 111;
                $json["msg"] = "服务器错误";
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取苹果产品列表
     */
    public function get_apple_list(){
        $json = $this->simpleJson();
        $type = I('request.type',1,'intval');
        do{
            $list = M("product")->where(array('type'=>$type, 'status'=>1))->order("create_time DESC")->select();
            $json['data']['list'] = $list;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 支付宝，微信支付
     */
    public function api(){
        $json = $this->simpleJson();
        do{
            $type = I('request.type','','strval');
            $product_id = I('request.product_id',0,'intval');

            $this->check_login();
            $user_id = intval($this->user['id']);
            $product = M('product')->where(array('id'=>$product_id))->find();
            if(!$product){
                $json['status'] = 111;
                $json['msg'] = '没有找到支付产品';
                break;
            }

            if($product['status'] != 1){
                $json['status'] = 111;
                $json['msg'] = '支付产品已下架';
                break;
            }
            $out_trade_no = get_order_no();

            if($type == 'weixin'){
                ini_set('date.timezone','Asia/Shanghai');
                error_reporting(E_ERROR);

                require_once APP_PATH . "/../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Api.php";
                require_once APP_PATH . "/../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Notify.php";

                #微信订单

                // `id`, `type`, `product_id`, `credit`, `amount`, `order_no`, `apple_receipt`, `apple_receipt_md5`, `number_no`, `user_id`, `status`, `create_time`, `update_time`
                $data = [
                    'type'=> 2,
                    'product_id' => $product_id,
                    'credit' => $product['credit'],
                    'amount' => $product['amount'],
                    'order_no' => $out_trade_no,
                    'user_id' => $user_id,
                    'apple_receipt'=>'',
                    'apple_receipt_md5'=>'',
                    'create_time' => time(),
                    'update_time' => time()
                ];
                $res = M('top')->add($data);
                if($res){
                    //统一下单
                    $input = new \WxPayUnifiedOrder();
                    $input->SetBody('章鱼帝充值');
                    $input->SetAttach('章鱼帝充值');
                    $input->SetOut_trade_no($out_trade_no);
                    $input->SetTotal_fee($product['amount'] * 100);
                    $input->SetTime_start(date("YmdHis"));
                    $input->SetTime_expire(date("YmdHis", time() + 600));
                    $input->SetGoods_tag("top");
                    $input->SetNotify_url("https://api.zydzuqiu.com/api/notify/type/weixin.html");
                    $input->SetTrade_type("APP");
                    $result = \WxPayApi::unifiedOrder($input);
                    if($result['prepay_id']){
                        M('top')->where(array('id'=>$res))->save(array('prepay_id'=>$result['prepay_id']));
                    }
                    $json['data'] = $result;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '下单失败~';
                    break;
                }
            }elseif($type == 'alipay'){
                $data = [
                    'type'=> 3,
                    'product_id' => $product_id,
                    'credit' => $product['credit'],
                    'amount' => $product['amount'],
                    'order_no' => $out_trade_no,
                    'user_id' => $user_id,
                    'apple_receipt'=>'',
                    'apple_receipt_md5'=>'',
                    'create_time' => time(),
                    'update_time' => time()
                ];
                $res = M('top')->add($data);
                if($res) {
                    require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/alipay.config.php");
                    require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_notify.class.php");
                    require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_rsa.function.php");
                    require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_core.function.php");
                    #global $alipay_config;

                    //print_r($alipay_config);die();

                    //$data = 'partner="2088211317861588"&out_trade_no="0616152240-7392"&subject="测试的商品"&seller_id="chenyin@qjy168.com"&
                    //body="该测试商品的详细描述"&total_fee="0.01"&notify_url="http://notify.msp.hk/notify.htm"&service="mobile.securitypay.pay"&payment_type="1"&_input_charset="utf-8"';

                    $order = [
                        'partner' => '2088421319080851',
                        'out_trade_no' => $out_trade_no,
                        'subject' => '章鱼帝充值',
                        'seller_id' => 'xianhekeji@qq.com',
                        'body' => '章鱼帝充值',
                        'total_fee' => $product['amount'],
                        'notify_url' => 'http://api2.zydzuqiu.com/api/notify/type/alipay.html',
                        'service' => 'mobile.securitypay.pay',
                        'payment_type' => 1,
                        '_input_charset' => 'utf-8'
                    ];
                    //确认PID和接口名称是否匹配。
                    date_default_timezone_set("PRC");

                    //将post接收到的数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串。
                    $data = createLinkstring($order);

                    //打印待签名字符串。工程目录下的log文件夹中的log.txt。
                    logResult($data);

                    //将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
                    $rsa_sign = urlencode(rsaSign($data, $alipay_config['private_key']));

                    //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
                    $data = $data . '&sign=' . '"' . $rsa_sign . '"' . '&sign_type=' . '"' . $alipay_config['sign_type'] . '"';

                    //返回给客户端,建议在客户端使用私钥对应的公钥做一次验签，保证不是他人传输。
                    $json['data']['param'] =  $data;
                    $json['data']['out_trade_no'] = $out_trade_no;
                    $json['data']['total_fee'] = $order['total_fee'];
                    $json['data']['private_key'] = $alipay_config['private_key'];
                }
            }else{
                $json['status'] = 110;
                $json['msg'] = '错误支付类型';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

}