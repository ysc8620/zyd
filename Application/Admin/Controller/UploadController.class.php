<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Page;
use Org\Util\File;

class UploadController extends BaseController
{
// 列表
    public function show(){
        $this->display();
    }
    // 本地图片上传
    public function upload(){

        echo('<div style="font-size:12px; height:30px; line-height:30px">');
        $uppath = './uploads/';
        $sid = trim($_POST['sid']);//模型
        $fileback = !empty($_POST['fileback']) ? trim($_POST['fileback']) : 'pic';//回跳input
        if ($sid) {
            $uppath.= $sid.'/';
            mkdirss($uppath);
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =    $uppath; // 设置附件上传根目录
        $upload->subName = array('date','Y/m');
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        if (!$info = $upload->upload()) {
            $error = $upload->getError();
            if($error == '上传文件类型不允许'){
                $error .= '，可上传<font color=red>JPEG,JPG,PNG,GIF</font>';
            }
            exit($error.' [<a href="?s=/admin/upload/show/sid/'.$sid.'/fileback/'.$fileback.'">重新上传</a>]');
            //dump($up->getErrorMsg());
        }

        //print_r($info);
//        //是否添加水印
//        if (C('upload_water')) {
//            import("ORG.Util.Image");
//            Image::water($uppath.$uploadList[0]['savename'],C('upload_water_img'),'',C('upload_water_pct'),C('upload_water_pos'));
//        }
//        //是否生成缩略图
//        if (C('upload_thumb')) {
//            $thumbdir = substr($uploadList[0]['savename'],0,strrpos($uploadList[0]['savename'], '/'));
//            mkdirss($uppath_s.$thumbdir);
//            import("ORG.Util.Image");
//            Image::thumb($uppath.$uploadList[0]['savename'],$uppath_s.$uploadList[0]['savename'],'',C('upload_thumb_w'),C('upload_thumb_h'),true);
//        }
        echo "<script type='text/javascript'>parent.document.getElementById('".$fileback."').value='".$sid.'/'.$info['upthumb']['savepath'].$info['upthumb']['savename']."';</script>";
        echo '文件上传成功　[<a href="?s=/admin/upload/show/sid/'.$sid.'/fileback/'.$fileback.'">重新上传</a>]';
        //<a href="'.$uppath.$uploadList[0]['savename'].'" target="_blank"><font color=red>'.$uploadList[0]['savename'].'</font></a>
        echo '</div>';
    }
}