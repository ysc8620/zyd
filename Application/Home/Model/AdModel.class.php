<?php

/* add by allen */

namespace Home\Model;

use Think\Model;

class AdModel extends Model {
    /* 用户模型自动验证 */

    protected $_validate = array(
            /* 验证用户名 */
//        array('username','require','用户名不得为空！',self::MUST_VALIDATE,self::MODEL_INSERT), //默认情况下用正则进行验证
//        array('username','2,16','账号长度不得小于2位，大于10位！',self::MUST_VALIDATE,'length',self::MODEL_INSERT), // 判断用户名长度
//        array('username','','帐号名称已经被注册！',self::MUST_VALIDATE,'unique'), // 在新增的时候验证name字段是否唯一
//        array('username','checkuser','账号禁止注册或含有敏感字符',self::MUST_VALIDATE,'callback',self::MODEL_INSERT), //自定义函数验证账号敏感字符
//
//        
//        /* 验证邮箱 */
//        array('email','require','邮箱不得为空！',self::EXISTS_VALIDATE), //默认情况下用正则进行验证
//		array('email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE), //邮箱格式不正确
//        array('email','2,30','邮箱长度不合法！',self::EXISTS_VALIDATE,'length',self::MODEL_BOTH), // 判断用户名长度
//		array('email', '', '邮箱已经被注册', self::EXISTS_VALIDATE, 'unique',self::MODEL_BOTH), //邮箱被占用
//        
//        /* 验证密码 */
//		array('password', '6,30', '密码长度不合法', self::MUST_VALIDATE, 'length',self::MODEL_BOTH), //密码长度不合法
//	);
//    
//    /* 用户模型自动完成 */
//	protected $_auto = array(
//        array('password','sha1',3,'function') , // 对password字段在新增和编辑的时候使md5函数处理
//        array('reg_time', NOW_TIME, self::MODEL_INSERT),
//        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
//        array('uniqid', '_uniqid', self::MODEL_INSERT, 'callback', 1),
    );

    public function get_city_ad($cityid) {
        echo '  get_ad';
        return LocalModel::getAdInstance()->get("s{$cityid}");
    }

}
