<?php 
/**
 * alipay_config 支付宝配置信息
 * @author aloner <qiang_killer@126.com>
 */
$alipay_config = array(
	'partner'		    => "2088421319080851",
	'key'   		    => "9ph6eh0bg8wn0yp2lsqk62uhlagu6itk",
	'seller_email'	    => "xianhekeji@qq.com",
	'notify_url'		=> "https://api.zydzuqiu.com/api/notify/type/alipay.html",
	'return_url'		=> "https://api.zydzuqiu.com",
	'show_url'		    => "https://api.zydzuqiu.com",
	'mainname'		    => "杭州闲鹤科技有限公司",
	'sign_type'		    =>"MD5",
	'input_charset'	=> "utf-8",
	'transport'		    => "http",
	'gateway' 		    => 'https://www.alipay.com/cooperate/gateway.do?',
);
return $alipay_config;
?>