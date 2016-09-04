<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once __DIR__."/../lib/WxPay.Api.php";
require_once __DIR__.'/../lib/WxPay.Notify.php';
require_once __DIR__ .'/log.php';

//初始化日志
$logHandler= new CLogFileHandler(__DIR__."/../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			//
			$info = M('top')->where(array('order_no'=>$result['out_trade_no']))->find();
			if($info['status'] != 1){
				M()->startTrans();
				$res1 = M('top')->where(array('order_no'=>$result['out_trade_no']))->save(array('number_no'=>$transaction_id,'status'=>1,'update_time'=>time()));
				$res2 = M('users')->where(array('id'=>$info['user_id']))->setInc("credit", $info['credit']);
				$res3 = M('users')->where(array('id'=>$info['user_id']))->setInc("total_top_credit", $info['credit']);

				if($res1 && $res2 && $res3){
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
				}else{
					M()->rollback();
				}
				M('weixin_log')->add($result);
			}

			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}

