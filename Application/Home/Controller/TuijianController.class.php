<?php
namespace Home\Controller;

class TuijianController extends BaseApiController {
    /**
     * 推荐列表
     */
    public function index(){
        $json = $this->simpleJson();
        do{

        }while(false);
        $this->ajaxReturn($json);
    }
    /**
     * 发布推荐
     */
    public function post(){
        $json = $this->simpleJson();
        do{
            $this->check_login();

            $data = [];
            $data['user_id'] = $this->user['id'];
            $data['match_id'] = I('request.match_id',0,'intval');
            $data['type'] = I('request.type',0,'intval');
            $data['sub_type'] = I('request.sub_type',0,'intval');
            $data['is_fee'] = I('request.is_fee',0,'intval');
            $data['fee'] = I('request.fee',0,'intval');
            $data['remark'] = I('request.remark','','strval,trim,strip_tags,htmlspecialchars');
            $data['guess_1'] = I('request.guess_1',0,'intval');
            $data['guess_2'] = I('request.guess_2',0,'intval');

            if(empty($data['match_id'])){
                $json['status'] = 110;
                $json['msg'] = "请选择推荐赛事";
                break;
            }

            if(empty($data['type'])){
                $json['status'] = 110;
                $json['msg'] = "请选择推荐类型";
                break;
            }

            if(empty($data['user_id'])){
                $json['status'] = 110;
                $json['msg'] = "发布用户不能为空";
                break;
            }

            if(empty($data['guess_1'])){
                $json['status'] = 110;
                $json['msg'] = "竞彩类型不能为空";
                break;
            }

            if($data['is_fee'] && empty($data['fee'])){
                $json['status'] = 110;
                $json['msg'] = "请输入查看费用";
                break;
            }

            if(strlen($data['remark']) < 1 ){
                $json['status'] = 110;
                $json['msg'] = "请输入推荐理由";
                break;
            }
            
            $data['create_time'] = time();
            $data['update_time'] = time();
            $res = M('tuijian')->add($data);
            if($res){
                $json['msg'] = '发布成功';
                $json['data'] = $res;
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = "发布失败";
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 推荐购买
     */
    public function pay(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $tuijian_id = I('request.tuijian_id',0,'intval');

            if(empty($user_id)){
                $json['status'] = 110;
                $json['msg'] = '用户不能为空';
                break;
            }

            if(empty($tuijian_id)){
                $json['status'] = 110;
                $json['msg'] = '推荐用户不能为空';
                break;
            }
            // 购买记录
            $has = M('tuijian_order')->where(array('user_id'=>$user_id, 'tuijian_id'=>$tuijian_id))->find();
            if($has){
                $json['msg'] = '你已经购买过';
                $json['data'] = $has['id'];
                break;
            }
            $user = M('users')->where(array('id'=>$user_id))->find();
            $tuijian = M('tuijian')->where(array('id'=>$tuijian_id))->find();
            if($user['credit'] < $tuijian['fee']){
                $json['status'] = 111;
                $json['msg'] = '球币不足,请先充值';
                break;
            }
            $data = [
                'user_id' => $user_id,
                'tuijian_id' => $tuijian_id,
                'create_time' => time()
            ];
            M()->startTrans();
            $res = M('tuijian_order')->add($data);
            $res2 = M()->execute("UPDATE ".C('DB_PREFIX')."users SET credit=credit-'{$tuijian['fee']}' WHERE user_id='{$user_id}' AND credit>='{$tuijian['fee']}'");
            if($res && $res2){
                M()->commit();
                $json['msg'] = '购买成功';
                $json['data'] = $res;
            }else{
                M()->rollback();
                $json['msg'] = '购买失败';
                $json['status'] = 111;
            }
        }while(false);
        $this->ajaxReturn($json);
    }
}