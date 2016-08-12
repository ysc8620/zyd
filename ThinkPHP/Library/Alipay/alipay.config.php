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
$alipay_config['private_key']	= 'MIICXAIBAAKBgQDLuU07i+5NOapBuGY8sJyj9pXSM+8ataQNJTNkaQVGCfirRDL6
7HdUteMUj4fOJqCnBSPj73FV+h1PZOTE3sztzguKCRbJBBca0IAOPY5mg+zQHQoP
nvXyFL05axWbvbG4Pg6Z7oeCvJedFEvAHIY4iA+eGBc4Zq0vDIq1+3UgqwIDAQAB
AoGAauibNxx/udcBGewC+uZTzVeh1+WZHJMlvCBBj/fs5IxnCrmHZ1agaLvqpueL
hQuQUxXHd943w761kuyHgSjKsTCrZ9SmlqbOgoS9MWiCfdTDcdf3kHwqfLGhOxPn
nlcEoE/NdqQscVgIyK0E4nE6adgWXYAAwAXiNz7IMrLI3sECQQD0UY1BxJm6Rg8Y
PcFmErqRsJcd4pf90wVAgkkoRHFtOG43PFa9oFpqKcT9eTkFFlXpFlaqG/1MNQj8
cndK3ENXAkEA1XbfTTLHKDFTgufMxP5z+dkVmW84XGUjDBo2Esz67HgpqKG96KW7
X8CNT/Kg4+QpOif7sh4+zjr0NpE8pJnszQJAG7DylbW2xsoSA+lb9jnBM6oUNqrs
FzTHr5wUtp+rS3nN5CzOmAeIt+esEB6hO9ZgPl7ty7IIb47DSfmfU7jLWQJBAJEr
Ezcz1h7uVKXMsAQ0K3j5eefrrD+hhBEh7i4g+nBAU8xbmoQR2XiHwVJiZao1qxfi
wxfh28Jyc0x8vuoNx+0CQGivPqksmBD6kWtiip8d5KOEGdtf1gh4z0TIETuKHiov
70wXdtUv4deKus98cbnldO1PFqVbn6QYy/MT8IRHe+w=';

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