<?php
namespace Home\Controller;

class TestController extends BaseApiController {
    /**
     *
     */
    public function test(){
        $json = $this->simpleJson();
        $data = $_POST;
        $json['post'] = $data;
        $json['data'] = ['id'=>1,'time'=>time(),'d'=>'返回中文测试'];
        M('match')->where(array('match_id'=>array('exp','in(select id from '.C('DB_PREFIX').'match)')))->limit(10)->select();
        echo M()->getLastSql();
        echo "====";
        #$this->ajaxReturn($json);
    }
}