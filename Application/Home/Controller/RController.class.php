<?php
namespace Home\Controller;

class RController extends BaseController {
    /**
     *
     */
    public function test(){
        $d = M('r')->find();
//        print_r($d);
//        die();
        $json = $this->simpleJson();
        do{
            $data = $_POST;
            $info = M('r')->where(array('mobile'=>$data['mobile']))->find();
            if($info){

                $json['id'] = $info['id'];
                $json['exit'] = true;
            }else{
                $data['create_time'] = time();
                $json['id'] = M('r')->add($data);
                $json['exit'] = false;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    function simpleJson(){
        return [
            'status' => 200
        ];
    }
}