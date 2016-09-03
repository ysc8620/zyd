<?php
namespace Home\Controller;
use \Think\Page;
class NoticeController extends BaseApiController {
    /**
     *
     */
    public function  news(){
        $json = $this->simpleJson();
        do{
            $user_id = empty($this->user['id'])?0:$this->user['id'];
            if($user_id){
                // status 1待发， 2已发，3已读
                $total = M('notice_info')->where(array('to_id'=>$user_id,'status'=>0))->count();
                $json['data']['total'] = $total;
            }else{
                $json['data']['total'] = 0;
            }

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取消息
     */
    public function get(){
        $json = $this->simpleJson();
        $p = I('request.p',1,'intvl');
        $limit = I('request.limit',10,'intval');
        $type = I('request.type',1,'intval');
        do{
            $user_id = empty($this->user['id'])?0:$this->user['id'];
            if($user_id){
                // status 1待发， 2已发，3已读
                $total = M('notice_info')->where(array('to_id'=>$user_id))->count();
                $page = new Page($total, $limit);
                $list = M('notice_info')->where(array('to_id'=>$user_id,'status'=>array('lt', 4)))->limit($page->firstRow, $page->listRows)->order(" create_time DESC")->select();
                $ids = [];
                foreach($list as $item){
                    $ids[] = $item['id'];
                }
                if($ids){
                    M('notice_info')->where(array('id'=>array('in'=>$ids)))->save(array('status'=>1));
                }
                $json['data']['list'] = $list;
            }else{
                $json['data']['list'] = [];
            }

            $json['data']['total'] = $total;
            $json['data']['page'] = $p;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 消息读取接口
     */
    public function read(){
        $json = $this->simpleJson();
        do{
            $id = I('request.notice_id',0,'intval');
            if($id){
                $res = M('notice_info')->where(array('id'=>$id))->save(array('status'=>3,'update_time'=>time()));
                if($res){
                    $json['msg'] = "消息读取成功";
                    $json['data']['id'] = $id;
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = "消息读取失败";
                    $json['data']['id'] = $id;
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
     * 消息读取接口
     */
    public function delete(){
        $json = $this->simpleJson();
        do{
            $id = I('request.notice_id',0,'intval');
            if($id){
                $res = M('notice_info')->where(array('id'=>$id))->save(array('status'=>4,'update_time'=>time()));
                if($res){
                    $json['msg'] = "消息删除成功";
                    $json['data']['id'] = $id;
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = "消息删除失败";
                    $json['data']['id'] = $id;
                    break;
                }
            }else{
                $json['status'] = 110;
                $json['msg'] = "请选择要删除的消息";
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