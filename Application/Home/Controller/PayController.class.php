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

            //$this->check_login();
            $user_id = intval($this->user['id']);


            if($type == 'weixin'){

                ini_set('date.timezone','Asia/Shanghai');
                error_reporting(E_ERROR);

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

                require_once APP_PATH . "/../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Api.php";
                require_once APP_PATH . "/../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Notify.php";

                #微信订单

                $out_trade_no = get_order_no();
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
                /* *
        * 功能：即时到账交易接口接入页
        * 版本：3.3
        * 修改日期：2012-07-23
        * 说明：
        * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
        * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

        *************************注意*************************
        * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
        * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
        * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
        * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
        * 如果不想使用扩展功能请把扩展功能参数赋空值。
        */
                $alipay_config  = include_once(APP_PATH."/ThinkPHP/Library/Alipay/alipay.config.php");
                require_once(APP_PATH."/ThinkPHP/Library/Alipay/alipay/lib/alipay_submit.class.php");
                $alipay_config['cacert'] = APP_PATH."/ThinkPHP/Library/Alipay/alipay/cacert.pem";
                /**************************请求参数**************************/
                //支付类型
                $payment_type = "1";
                //必填，不能修改
                //服务器异步通知页面路径
                $notify_url = $alipay_config['notify_url'];//"http://商户网关地址/create_direct_pay_by_user-PHP-UTF-8/notify_url.php";
                //需http://格式的完整路径，不能加?id=123这类自定义参数

                //页面跳转同步通知页面路径
                $return_url = $alipay_config['return_url'];//"http://商户网关地址/create_direct_pay_by_user-PHP-UTF-8/return_url.php";
                //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

                //商户订单号
                $out_trade_no = get_order_no();
                //商户网站订单系统中唯一订单号，必填

                //订单名称
                $subject = "章鱼帝充值";
                //必填

                //付款金额
                $total_fee = 0.01;
                //必填

                //订单描述
                $body = "章鱼帝充值";

                //商品展示地址
                $show_url = $alipay_config['show_url'];
                //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

                //防钓鱼时间戳
                $anti_phishing_key = "";
                //若要使用请调用类文件submit中的query_timestamp函数

                //客户端的IP地址
                $exter_invoke_ip = "";
                //非局域网的外网IP地址，如：221.0.0.1
                /************************************************************/
                //构造要请求的参数数组，无需改动
                $parameter = array(
                    "service" => "create_direct_pay_by_user",
                    "partner" => trim($alipay_config['partner']),
                    "seller_email" => trim($alipay_config['seller_email']),
                    "payment_type"	=> $payment_type,
                    "notify_url"	=> $notify_url,
                    "return_url"	=> $return_url,
                    "out_trade_no"	=> $out_trade_no,
                    "subject"	=> $subject,
                    "total_fee"	=> $total_fee,
                    "body"	=> $body,
                    "show_url"	=> $show_url,
                    "anti_phishing_key"	=> $anti_phishing_key,
                    "exter_invoke_ip"	=> $exter_invoke_ip,
                    "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
                );

                //建立请求
                $alipaySubmit = new \AlipaySubmit($alipay_config);
                $alipaySubmit->buildRequestPara($parameter);
                $json['data'] = $alipaySubmit;
            }else{
                $json['status'] = 110;
                $json['msg'] = '错误支付类型';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 回调地址
     */
    public function notify(){
        die('xxx');
        $type = I('request.type','','trim');
        if($type == 'weixin'){
            require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/example/notify.php";
            \Log::DEBUG(date("Y-m-d H:i:s")."begin notify");
            $notify = new \PayNotifyCallBack();
            $notify->Handle(false);

        }elseif($type == 'alipay'){

        }else{
            die('error');
        }
    }


}