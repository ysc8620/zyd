<?php
namespace Home\Controller;

use Think\Crypt\Driver\Think;
use Think\Exception;

class ApiController extends BaseController {


    /**
     * 回调地址
     */
    public function notify()
    {
        $type = I('request.type', '', 'trim');
        if ($type == 'weixin') {
            require_once APP_PATH . "../ThinkPHP/Library/Weixin/WxpayAPI/example/notify.php";
            \Log::DEBUG(date("Y-m-d H:i:s") . "begin notify");
            $notify = new \PayNotifyCallBack();
            $notify->Handle(false);

        } elseif ($type == 'alipay') {
            \Org\Util\File::write_file(APP_PATH . '/../alipay.log', date("Y-m-d H:i:s") . json_encode($_POST) . "\r\n","a+");
            require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/alipay.config.php");
            require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_notify.class.php");
            require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_rsa.function.php");
            require_once(APP_PATH . "/../ThinkPHP/Library/Alipay/lib/alipay_core.function.php");

            //计算得出通知验证结果
            $alipayNotify = new \AlipayNotify($alipay_config);

            if ($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
            {
                if ($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签

                    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
                    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                    //商户订单号
                    $out_trade_no = $_POST['out_trade_no'];

                    //支付宝交易号
                    $trade_no = $_POST['trade_no'];

                    //交易状态
                    $trade_status = $_POST['trade_status'];

                    if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                        //
                        $info = M('top')->where(array('order_no' => $out_trade_no))->find();
                        if ($info['status'] != 1) {
                            M()->startTrans();
                            $res1 = M('top')->where(array('order_no' => $out_trade_no))->save(array('number_no' => $trade_no, 'status' => 1, 'update_time' => time()));
                            $res2 = M('users')->where(array('id' => $info['user_id']))->setInc("credit", $info['credit']);
                            $res3 = M('users')->where(array('id' => $info['user_id']))->setInc("total_top_credit", $info['credit']);

                            if ($res1 && $res2 && $res3) {
                                M()->commit();

                                $credit_log2 = [
                                    'type' => 1,
                                    'credit' => $info['credit'],
                                    'from_id' => 0,
                                    'remark' => "用户充值",
                                    'create_time' => time(),
                                    'user_id' => $info['user_id'],
                                    'status' => 0,
                                    'from_id'=>$info['id']
                                ];
                                $user3 = M("users")->where(['id'=>$info['user_id']])->field('id,credit')->find();
                                $credit_log2['total_credit'] = $user3['credit'];
                                M('credit_log')->add($credit_log2);
                            } else {
                                M()->rollback();
                            }
                            // M('alipay_log')->add($_POST);
                        }


                        //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                        //注意：
                        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                        //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                    } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                        $info = M('top')->where(array('order_no' => $out_trade_no))->find();
                        if ($info['status'] != 1) {
                            M()->startTrans();
                            $res1 = M('top')->where(array('order_no' => $out_trade_no))->save(array('number_no' => $trade_no, 'status' => 1, 'update_time' => time()));
                            $res2 = M('users')->where(array('id' => $info['user_id']))->setInc("credit", $info['credit']);
                            $res3 = M('users')->where(array('id' => $info['user_id']))->setInc("total_top_credit", $info['credit']);

                            if ($res1 && $res2 && $res3) {
                                M()->commit();

                                $credit_log2 = [
                                    'type' => 1,
                                    'credit' => $info['credit'],
                                    'from_id' => 0,
                                    'remark' => "用户充值",
                                    'create_time' => time(),
                                    'user_id' => $info['user_id'],
                                    'status' => 0,
                                    'from_id'=>$info['id']
                                ];
                                $user3 = M("users")->where(['id'=>$info['user_id']])->field('id,credit')->find();
                                $credit_log2['total_credit'] = $user3['credit'];
                                M('credit_log')->add($credit_log2);
                            } else {
                                M()->rollback();
                            }

                            //判断该笔订单是否在商户网站中已经做过处理
                            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                            //如果有做过处理，不执行商户的业务程序
                            //注意：
                            //付款完成后，支付宝系统发送该交易状态通知
                            //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                        }
                        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                        echo "success";        //请不要修改或删除
                    } else //验证签名失败
                    {
                        echo "sign fail";
                    }
                } else //验证是否来自支付宝的通知失败
                {
                    echo "response fail";
                }
            } else {
                die('error');
            }
        }
    }

}