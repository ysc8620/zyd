<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2016/8/12
 * Time: 0:18
 */
echo 'ss';
require __DIR__."/aop/AopClient.php";
require __DIR__."/aop/request/AlipayTradeAppPayRequest.php";
$aop = new AopClient ();
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = '2016071901639611';
$aop->rsaPrivateKeyFilePath = __DIR__.'sign';
#$aop->alipayPublicKey='alipay_public_key_file';
$aop->apiVersion = '1.0';
$aop->postCharset='GBK';
$aop->format='json';
$request = new AlipayTradeAppPayRequest ();
var_dump($request);
$request->setBizContent("{" .
    "    \"body\":\"Iphone6 16G\"," .
    "    \"subject\":\"大乐透\"," .
    "    \"out_trade_no\":\"70501111111S001111119\"," .
    "    \"timeout_express\":\"90m\"," .
    "    \"total_amount\":\"9.00\"," .
    "    \"seller_id\":\"2088102147948060\"," .
    "    \"product_code\":\"QUICK_MSECURITY_PAY\"" .
    "  }");
$result = $aop->execute ( $request);
var_dump($result);