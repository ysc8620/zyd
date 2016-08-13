<?php
/* *
 * 配置文件
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088421319080851';

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMu5TTuL7k05qkG4
ZjywnKP2ldIz7xq1pA0lM2RpBUYJ+KtEMvrsd1S14xSPh84moKcFI+PvcVX6HU9k
5MTezO3OC4oJFskEFxrQgA49jmaD7NAdCg+e9fIUvTlrFZu9sbg+Dpnuh4K8l50U
S8AchjiID54YFzhmrS8MirX7dSCrAgMBAAECgYBq6Js3HH+51wEZ7AL65lPNV6HX
5ZkckyW8IEGP9+zkjGcKuYdnVqBou+qm54uFC5BTFcd33jfDvrWS7IeBKMqxMKtn
1KaWps6ChL0xaIJ91MNx1/eQfCp8saE7E+eeVwSgT812pCxxWAjIrQTicTpp2BZd
gADABeI3PsgyssjewQJBAPRRjUHEmbpGDxg9wWYSupGwlx3il/3TBUCCSShEcW04
bjc8Vr2gWmopxP15OQUWVekWVqob/Uw1CPxyd0rcQ1cCQQDVdt9NMscoMVOC58zE
/nP52RWZbzhcZSMMGjYSzPrseCmoob3opbtfwI1P8qDj5Ck6J/uyHj7OOvQ2kTyk
mezNAkAbsPKVtbbGyhID6Vv2OcEzqhQ2quwXNMevnBS2n6tLec3kLM6YB4i356wQ
HqE71mA+Xu3LsghvjsNJ+Z9TuMtZAkEAkSsTNzPWHu5UpcywBDQrePl55+usP6GE
ESHuLiD6cEBTzFuahBHZeIfBUmJlqjWrF+LDF+HbwnJzTHy+6g3H7QJAaK8+qSyY
EPqRa2KKnx3ko4QZ21/WCHjPRMgRO4oeKi/vTBd21S/h14q6z3xxueV07U8WpVuf
pBjL8xPwhEd77A==';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
//异步通知接口
$alipay_config['service']= 'mobile.securitypay.pay';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
?>