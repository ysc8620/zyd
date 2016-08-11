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

            $this->check_login();
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

                require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Api.php";
                require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/lib/WxPay.Notify.php";

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
                    $input->SetNotify_url("https://api.zydzuqiu.com/pay/notify/type/weixin.html");
                    $input->SetTrade_type("APP");
                    $result = \WxPayApi::unifiedOrder($input);
                    $json['data'] = $result;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '下单失败~';
                    break;
                }
            }elseif($type == 'alipay'){

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
        $type = I('request.type','','trim');
        if($type == 'weixin'){
            
            require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/example/notify.php";
            \Log::DEBUG("begin notify");

            $notify = new \PayNotifyCallBack();
            $notify->Handle(false);

        }elseif($type == 'alipay'){

        }else{
            die('error');
        }
    }


}