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
        $header = getallheaders();
        $header = array_change_key_case($header, CASE_LOWER);
        $json['header'] = $header;
        $this->ajaxReturn($json);
    }
}