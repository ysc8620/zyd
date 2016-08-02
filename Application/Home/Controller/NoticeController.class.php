<?php
namespace Home\Controller;

class NoticeController extends BaseApiController {

    /**
     * 获取消息
     */
    public function get(){
        $json = $this->simpleJson();
        do{
            $user_id = empty($this->user['id'])?0:$this->user['id'];
            if($user_id){
                // status 1待发， 2已发，3已读
                $list = M('notice_info')->where(array('to_id'=>$user_id, 'status'=>1))->order("create_time ASC")->select();
                $ids = [];
                if($list){
                    foreach($list as $item){
                        $ids[] = $item['id'];
                    }
                    // 更新状态
                    M('notice_info')->where(array('id'=>array('in', $ids)))->save(array('status'=>2,'update_time'=>time()));
                }else{
                    $list = [];
                }

                $json['data'] = $list;
            }else{
                $json['data'] = [];
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 消息读取接口
     */
    public function read(){
        $json = $this->simpleJson();
        do{
            $id = I('request.id',0,'intval');
            if($id){
                $res = M('notice_info')->where(array('id'=>$id))->save(array('status'=>3,'update_time'=>time()));
                if($res){
                    $json['msg'] = "消息读取成功";
                    $json['data'] = $id;
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = "消息读取失败";
                    $json['data'] = $id;
                    break;
                }
            }else{
                $json['status'] = 110;
                $json['msg'] = "请选择要读取的消息";
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 发送消息
     */
    public function send(){
        
    }

}