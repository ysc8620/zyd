<?php
namespace Home\Controller;

class ConfigController extends BaseApiController {

    /**
     * 系统初始化
     */
    public function init(){
        $json = $this->simpleJson();
        $json['data'] = array();

        $config = array();
        if(file_exists(APP_PATH .'/Runtime/Conf/config.php')){
            $config = include(APP_PATH .'/Runtime/Conf/config.php');
        }

        do{
            $data = $config['api'];
            $data['showflag'] = 'yes';
            $showflags = $data['showflags'];
            unset($data['showflags']);
            $showflags_list = explode(',', $showflags);
            foreach($showflags_list as $i=>$item){
                $showflags_list[$i] = trim($item);
            }
            //  == '1.0.2'
            //|| $this->header['appversion'] == 'ZYDfax iOS Client/1.0.2'

            if(in_array($this->header['appversion'], $showflags_list)){
                $data['showflag'] = 'no';
            }
            //
            $json['data'] = $data;
        }while(false);
        $this->ajaxReturn($json);
    }
}