<?php 
class thirdpart_payment_alipay implements  thirdpart_payment_base {
    /**
     * @param $data = array(
    'order_id'=>'',  // 订单编号  必填
    'order_name'=>'',// 订单名称  必填
    'order_amount'=>'',// 订单金额  必填
    'order_body'=>'',  // 订单描述
    'show_url'=>'',    // 商品展示链接

    );
     */
    public function make_forms($data){
       //GET方式传递
       header("Content-type: text/html; charset=utf-8");
       $html = <<<DOC
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付宝即时到账交易接口接口</title>
</head>
DOC;

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
        $alipay_config  = core_config::get('alipay');
        require_once("alipay/lib/alipay_submit.class.php");
        $alipay_config['cacert'] = getcwd().'/alipay/cacert.pem';
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
        $out_trade_no = $data['order_id'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $data['order_name'];
        //必填

        //付款金额
        $total_fee = $data['order_amount'];
        //必填

        //订单描述
        $body = $data['order_body'];

        //商品展示地址
        $show_url = $data['show_url'];
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
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "");
        echo $html;
        echo $html_text;
echo <<<DOC
</body>
</html>
DOC;
       die();
   }
    /* *
     * 功能：支付宝页面跳转同步通知页面
     * 版本：3.3
     * 日期：2012-07-23
     * 说明：
     * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
     * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

     *************************页面功能说明*************************
     * 该页面可在本机电脑测试
     * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
     * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
     */
    public function return_url(){
        $alipay_config  = core_config::get('alipay');
        require_once("alipay/lib/alipay_notify.class.php");

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            //调试用，写文本函数记录程序运行情况是否正常
            logResult('==tongbu_success=='.http_build_query($_GET));
            tools_file::to_log(dirname(__FILE__).'/payment.log', date("Y-m-d H:i:s")." = tongbu_success =" . http_build_query($_GET));
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $data = array(
                'order_id'=>$_GET['out_trade_no'], //订单编号
                'order_name'=>$_GET['subject'],//订单名
                'payment_type'=>'alipay',// 接口
                'trade_no'=>$_GET['trade_no'],//第三方流水号
                'trade_status'=>$_GET['trade_status'],//交易状态
                'total_fee'=>$_GET['total_fee'],//交易金额
                'state'=>'success'
            );
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号

            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];


            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
            }else {
                //echo "trade_status=".$_GET['trade_status'];
            }

            interface_order::pay_callback($data);

           // echo "验证成功<br />";
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }else {
            logResult('==tongbu_fail=='.http_build_query($_GET));
            tools_file::to_log(dirname(__FILE__).'/payment.log', date("Y-m-d H:i:s")." = tongbu_fail =" . http_build_query($_GET));

            $data = array(
                'order_id'=>$_GET['out_trade_no'], //订单编号
                'order_name'=>$_GET['subject'],//订单名
                'payment_type'=>'alipay',// 接口
                'trade_no'=>$_GET['trade_no'],//第三方流水号
                'trade_status'=>$_GET['trade_status'],//交易状态
                'total_fee'=>$_GET['total_fee'],//交易金额
                'state'=>'fail'
            );

            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //if($data['order_id']){
                interface_order::pay_callback($data);
           // }
           // echo "验证失败";
        }
        

    }

    /* *
    * 功能：支付宝服务器异步通知页面
    * 版本：3.3
    * 日期：2012-07-23
    * 说明：
    * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
    * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


    *************************页面功能说明*************************
    * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
    * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
    * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
    * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
    */
    public function notify_url(){
        $alipay_config  = core_config::get('alipay');

        require_once("alipay/lib/alipay_notify.class.php");

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代


            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            //商户订单号
            $data = array(
                'order_id'=>$_POST['out_trade_no'], //订单编号
                'order_name'=>$_POST['subject'],//订单名
                'payment_type'=>'alipay',// 接口
                'trade_no'=>$_POST['trade_no'],//第三方流水号
                'trade_status'=>$_POST['trade_status'],//交易状态
                'total_fee'=>$_POST['total_fee'],//交易金额
                'state'=>'success'
            );

            // api($data);
            echo "success";		//请不要修改或删除

            //if($data['order_id']){
                interface_order::pay_callback($data);
           // }
            logResult('==houtai_success=='.http_build_query($_POST));
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            $data = array(
                'order_id'=>$_POST['out_trade_no'], //订单编号
                'order_name'=>$_POST['subject'],//订单名
                'payment_type'=>'alipay',// 接口
                'trade_no'=>$_POST['trade_no'],//第三方流水号
                'trade_status'=>$_POST['trade_status'],//交易状态
                'total_fee'=>$_POST['total_fee'],//交易金额
                'state'=>'fail'
            );
            // api($data);

            //验证失败
            echo "fail";

            //if($data['order_id']){
                interface_order::pay_callback($data);
           // }

            //调试用，写文本函数记录程序运行情况是否正常
            logResult('==houtai_fail=='.http_build_query($_POST));
        }

    }
}
?>