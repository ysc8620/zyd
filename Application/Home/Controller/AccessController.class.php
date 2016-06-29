<?php
namespace Home\Controller;

use Weixin\MyWechat;
use Org\Util\File;
class AccessController extends BaseController
{
   function index(){
       $weObj = $this->initWechat();
       $weObj->valid();
       $type = $weObj->getRev()->getRevType();
       File::write_file(APP_PATH.'log/test.log', "city={$_GET['type']}&type={$type}&data={$weObj->getRevContent()}\r\n",'a+');

       //exit();
       switch ($type){
           case MyWechat::MSGTYPE_TEXT:
               $this->msgReply($weObj,"text");
               break;

           case MyWechat::MSGTYPE_EVENT:
               $event = $weObj->getRevEvent();
               if (isset($event['event'])) {
                   switch ($event['event']) {
                       case MyWechat::EVENT_SUBSCRIBE:
                           $this->msgReply($weObj,"subscribe");
                           break;

                       case MyWechat::EVENT_UNSUBSCRIBE:
                           $this->msgReply($weObj,"unsubscribe");
                           break;

                       case MyWechat::EVENT_MENU_VIEW:
                           //$userInfo = $weObj->getRevData();
                           break;
                       default:
                           # code...
                           break;
                   }
               }
               break;
           default:
               break;
       }
       echo 'success';
   }
}
?>