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
        $json['header'] = getallheaders();
        \Org\Util\File::write_file('./newpost.log', date("Y-m-d H:i:s")."==========================\r\n".json_encode($json)."\r\n==================================\r\rn");
        $this->ajaxReturn($json);
    }
}