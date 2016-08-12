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
// $aop->signType