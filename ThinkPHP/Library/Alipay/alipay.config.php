<?php 
/**
 * alipay_config 支付宝配置信息
 * @author aloner <qiang_killer@126.com>
 */
$alipay_config = array(
	'partner'		    => "2088801028918054",
	'key'   		    => "ysuc37b5zhpw3hr87st15dczsvyr0zpv",
	'seller_email'	    => "eb@sundan.com",
	'notify_url'		=> "http://pay.tupaidang.com/paynotify.alipay",
	'return_url'		=> "http://pay.tupaidang.com/payment.alipay",
	'show_url'		    => "http://www.alipay.com",
	'mainname'		    => "深圳前海图拍档科技有限公司",
	'sign_type'		    =>"MD5",
	'input_charset'	=> "utf-8",
	'transport'		    => "http",
	'gateway' 		    => 'https://www.alipay.com/cooperate/gateway.do?',
);
return $alipay_config;
?>