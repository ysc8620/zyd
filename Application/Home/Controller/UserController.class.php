<?php
namespace Home\Controller;

class UserController extends BaseApiController {
    public $default_pic = '/Public/static/userimg.jpg';

    private $field = "*";

    private function getField($type=''){
        if($type){
            $fields = explode(',', $this->field);
            foreach($fields as $i=>$field){
                $fields[$i] = $type.'.'.$field;
            }
            return $type .'.'.$this->type;
        }else{
            return $this->field;
        }
    }

    private function get_return_member($member,$mysalf=false){
        $member['pic'] = pic_url($member['pic']);
        unset($member['status']);
        unset($member['password']);
        unset($member['salt']);
        unset($member['wx_openid']);
        unset($member['qq_openid']);

        if(!$mysalf){
            if($member['mobile']){$member['mobile'] = substr_replace($member['mobile'],'*****',3,5);}
            if(is_mobile($member['nickname'])){
                $member['nickname'] = substr_replace($member['nickname'],'*****',3,5);
            }

            unset($member['ssid']);
            unset($member['credit']);
            unset($member['register_time']);
            unset($member['update_time']);
            unset($member['last_login_time']);
            unset($member['last_login_type']);
            unset($member['total_top_credit']);
        }
        return $member;
    }

    /**
     * 用户注册
     */
    public function register(){
        $json = $this->simpleJson();
        do{
            //
            $mobile = I('request.mobile','','strval');
            $password = trim(I('request.password','','strval'));
            $code = I('request.code','','strval');

            if( ! is_mobile($mobile)){
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            if(!$code){
                $json['status'] = 110;
                $json['msg'] = '请输入验证码';
                break;
            }

            #短信校验
            $this->check_sms($mobile, $code);

            $user = [
                'nickname' => $mobile,
                'mobile' => $mobile,
                'pic' => $this->default_pic
            ];

            if($password){
                if( strlen($password) < 6 || strlen($password) > 26){
                    $json['status'] = 110;
                    $json['msg'] = '密码长度在6-26个字符之间';
                    break;
                }
                $user['salt'] = random(12,'all');
                $user['password'] = encrypt_password(trim($password), $user['salt']);


                $user['last_login_time'] = time();
                $user['last_login_type'] = 2;
            }else{

                $user['last_login_time'] = time();
                $user['last_login_type'] = 1;
            }

            $member = M('users')->where(array('mobile'=>$mobile))->find();
            if($member){
                $json['status'] = 111;
                $json['msg'] = '该手机号已注册';
                break;
            }
            $user['register_time'] = time();
            $user['update_time'] = time();
            $user['ssid'] = get_login_ssid();

            $user_id = M('users')->add($user);
            if($user_id){
                $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
                $json['msg'] = '用户注册成功';
                $json['data'] = $this->get_return_member($member, true);
            }else{
                $json['status'] = 111;
                $json['msg'] = '用户注册失败';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 用户登录
     */
    public function login(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $code = I('request.code', 0, 'intval');
            $mobile = I('request.mobile', '', 'strval');
            $password = I('request.password','','strval');

            if (!is_mobile($mobile)) {
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            // 短信登录
            if($code){
                $this->check_sms($mobile, $code);
                $member = M('users')->where(array('mobile'=>$mobile))->field($this->field)->find();
                // 用户锁定
                if(!$member){
                    $json['status'] = 111;
                    $json['msg'] = '没找到登录用户';
                    break;
                }

                // 用户锁定
                if($member['status'] < 1){
                    $json['status'] = 111;
                    $json['msg'] = '用户被锁定不能使用';
                    break;
                }
                # 更新登录ssid
                $member['ssid'] = get_login_ssid();
                $save = [];
                $save['ssid'] = $member['ssid'];
                $save['last_login_time'] = time();
                $save['last_login_type'] = 1;
                $save['update_time'] = time();
                M('users')->where(array('id'=>$member['id']))->save($save);
                $json['msg'] = '用户登录成功';
                $json['data'] = $this->get_return_member($member, true);

            // 密码登陆
            }elseif($password){
                $member = M('users')->where(array('mobile'=>$mobile))->field($this->field)->find();
                // 用户锁定
                if(!$member){
                    $json['status'] = 111;
                    $json['msg'] = '没找到登录用户';
                    break;
                }

                // 用户锁定
                if($member['status'] < 1){
                    $json['status'] = 111;
                    $json['msg'] = '用户被锁定不能使用';
                    break;
                }

                if($member['password']!= encrypt_password($password, $member['salt'])){
                    $json['status'] = 111;
                    $json['msg'] = '登录密码错误';
                    break;
                }

                # 更新登录ssid
                $member['ssid'] = get_login_ssid();

                $save = [];
                $save['ssid'] = $member['ssid'];
                $save['last_login_time'] = time();
                $save['last_login_type'] = 1;
                $save['update_time'] = time();

                M('users')->where(array('id'=>$member['id']))->save($save);
                $json['msg'] = '用户登录成功';
                $json['data'] = $this->get_return_member($member, true);
            }else{
                $json['status'] = 111;
                $json['msg'] = '请输入密码或验证码';
                break;
            }

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     *
     */
    public function check_user(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $this->check_login();
            $user_id = $this->user['id'];
            if(!$user_id){
                $json['status'] = 110;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
            // 用户锁定
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            // 用户锁定
            if($member['status'] < 1){
                $json['status'] = 111;
                $json['msg'] = '用户被锁定不能使用';
                break;
            }

            $json['data'] = $this->get_return_member($member, true);

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取用户信息
     */
    public function info(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $user_id = I('request.user_id', 0, 'intval');
            if(!$user_id){
                $json['status'] = 110;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
            // 用户锁定
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没有找到用户信息';
                break;
            }

            // 用户锁定
            if($member['status'] < 1){
                $json['status'] = 111;
                $json['msg'] = '用户被锁定不能使用';
                break;
            }

            $json['data'] = $this->get_return_member($member);

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 微信登录
     */
    public function wx_login(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $openid = I('request.openid','','strval');
            $nickname = I('request.nickname','','strval');
            $pic = I('request.pic','','strval');

            if(empty($openid)){
                $json['status'] = 110;
                $json['msg'] = '微信授权ID不能为空';
                break;
            }
            $wx_user = M('users')->where(array('wx_openid'=>$openid))->field($this->field)->find();
            if($wx_user){
                if($wx_user['status'] < 1){
                    $json['status'] = 111;
                    $json['msg'] = '用户被锁定不能使用';
                    break;
                }
                $data = [
                    'ssid' => get_login_ssid(),
                    'update_time' => time(),
                    'last_login_time' => time(),
                    'last_login_type' => 3
                ];

                $res = M('users')->where(array('id'=>$wx_user['id']))->save($data);
                if($res){
                    $wx_user['ssid'] = $data['ssid'];
                    $wx_user['update_time'] = $data['update_time'];
                    $json['msg'] = '微信登录成功';
                    $json['data'] = $this->get_return_member($wx_user, true);
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '微信登录失败';
                    break;
                }
            }else{
                $data = [
                    'nickname' => $nickname,
                    'pic' => $pic,
                    'mobile' => '',
                    'wx_openid' => $openid,
                    'register_time' => time(),
                    'update_time' => time(),
                    'ssid' => get_login_ssid(),
                    'last_login_time' => time(),
                    'last_login_type' => 3
                ];
                $res = M('users')->add($data);
                if($res){
                    $member = M('users')->where(array('wx_openid'=>$openid))->field($this->field)->find();
                    $json['msg'] = '微信登录成功';
                    $json['data'] = $this->get_return_member($member, true);
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '微信登录失败';
                    break;
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 微信用户绑定或登录
     */
    public function wx_bind(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $this->check_login();
            $user_id = $this->user['id'];
//            $user_id = I('request.user_id', 0, 'intval');
            $openid = I('request.openid','','strval');
            $nickname = I('request.nickname','','strval');
            $pic = I('request.pic','','strval');
            if(empty($openid)){
                $json['status'] = 110;
                $json['msg'] = '微信授权ID不能为空';
                break;
            }

            if(empty($user_id)){
                $json['status'] = 110;
                $json['msg'] = '绑定用户ID不能为空';
                break;
            }
            $member = M('users')->where(array('id'=>$user_id))->find();
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '找不到绑定用户信息';
                break;
            }

            $wx_user = M('users')->where(array('wx_openid'=>$openid))->field($this->field)->find();
            if($wx_user){
                if($wx_user['id'] == $user_id){
                    $json['msg'] = '微信绑定成功';
                    $json['data'] = $this->get_return_member($wx_user, true);
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '微信已绑定其他用户';
                    $json['data'] = '';
                    break;
                }
            }else{
                $data = [
                    'wx_openid' => $openid,
                    'update_time' => time()
                ];
                if($member['nickname'] == $member['mobile'] && $nickname){
                    $data['nickname'] = $nickname;
                }
                if($member['pic'] == $this->default_pic){
                    $member['pic'] = $pic;
                }
                $res = M('users')->where(array('id'=>$user_id))->save($data);
                if($res){
                    $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
                    $json['msg'] = '微信绑定成功';
                    $json['data'] = $this->get_return_member($member,true);
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '微信绑定失败';
                    break;
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 用户编辑
     */
    public function edit(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $this->check_login();
            $user_id = $this->user['id'];
            $nickname = I('request.nickname','','trim,strval,htmlspecialchars,strip_tags');

            if(empty($nickname) || strlen($nickname) > 36){
                $json['status'] = 110;
                $json['msg'] = '用户昵称在1-36个字符之间';
                break;
            }
            $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没找到用户信息';
                break;
            }
            $data = [
                'nickname' => $nickname,
                'update_time' => time()
            ];
            $res = M('users')->where(array('id'=>$user_id))->save($data);
            if($res){
                $member['nickname'] = $nickname;
                $json['data'] = $this->get_return_member($member);
                $json['msg'] = '用户编辑成功';
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '用户编辑失败';
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     *  用户关注接口
     */
    public function follow(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $to_user_id = I('request.to_user_id',0,'intval');
            $this->check_login();
            $user_id = $this->user['id'];
            if(empty($to_user_id)){
                $json['status'] = 110;
                $json['msg'] = '用户ID不能为空';
                break;
            }
            $follow = M('users_follow')->where(array('from_user_id'=>$user_id,'to_user_id'=>$to_user_id))->find();
            if($follow){
                $json['msg'] = '关注成功';
                $json['data']['id'] = $follow['id'];
                $json['data']['user_id'] = $user_id;
                $json['data']['to_user_id'] = $to_user_id;
                break;
            }else{
                $data = [
                    'from_user_id' => $user_id,
                    'to_user_id' => $to_user_id,
                    'create_time' => time()
                ];
                $res = M('users_follow')->add($data);
                if($res){
                    $json['msg'] = '关注成功';
                    $json['data']['id'] = $res;
                    $json['data']['user_id'] = $user_id;
                    $json['data']['to_user_id'] = $to_user_id;
                    $json['follow_id'] = $res;
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '关注失败';
                    break;
                }
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 用户关注列表
     */
    public function follow_list(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $this->check_login();
            $user_id = $this->user['id'];
            $limit = I('request.limit',10,'intval');
            $page = I('request.p',1,'intval');
            if(empty($user_id) ){
                $json['status'] = 110;
                $json['msg'] = '用户ID不能为空';
                break;
            }
            $total = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")->count();
            $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page->show();

            $list = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")
                ->field($this->getField('u'))
                ->limit($Page->firstRow . ',' . $Page->listRows)->order("uf.create_time DESC")->select();

            foreach($list as $i=>$item){
                $list[$i] = $this->get_return_member($item);
            }
            $json['data'] = [
                'list' => (array)$list,
                'page' => $page,
                'total' => $total,
                'limit' => $limit,
                'total_page' => ceil($total/$limit),
                'user_id' => $user_id
            ];
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取专家列表
     */
    public function get_expert_list(){
        $json = $this->simpleJson();
        do {
            // 1 注册, 2登录, 3,找回密码
            $user_id = intval($this->user['id']);
            $limit = I('request.limit',10,'intval');
            $page = I('request.p',1,'intval');
            $type = I('request.type',1,'intval'); // 专家类型： 1 按30天胜率， 2，按关注数， 3, 我关注的
            if($type == 1){
                $total = M('users')->where("is_expert=1 AND status = 1")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M('users')->where("is_expert=1 AND status = 1")
                    ->field($this->field)
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("total_follow_user DESC, total_rate DESC")->select();

                foreach($list as $i=>$item){
                    $list[$i] = $this->get_return_member($item);
                }

                $json['data'] = [
                    'list' => (array)$list,
                    'page' => $page,
                    'total' => $total,
                    'limit' => $limit,
                    'total_page' => ceil($total/$limit),
                    'user_id' => $user_id,
                    'type' => $type
                ];
            }elseif($type == 2){
                $total = M('users')->where("is_expert=1 AND status = 1")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M('users')->where("is_expert=1 AND status = 1")
                    ->field($this->field)
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("total_month_rate DESC, total_rate DESC")->select();
                foreach($list as $i=>$item){
                    $list[$i] = $this->get_return_member($item);
                }
                $json['data'] = [
                    'list' => (array)$list,
                    'page' => $page,
                    'total' => $total,
                    'limit' => $limit,
                    'total_page' => ceil($total/$limit),
                    'user_id' => $user_id,
                    'type' => $type
                ];
            }elseif($type == 3){
                $this->check_login();

                $total = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.is_expert=1 AND u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.is_expert=1 AND u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")
                    ->field($this->getField('u'))
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("uf.create_time DESC")->select();

                foreach($list as $i=>$item){
                    $list[$i] = $this->get_return_member($item);
                }

                $json['data'] = [
                    'list' => (array)$list,
                    'page' => $page,
                    'total' => $total,
                    'limit' => $limit,
                    'total_page' => ceil($total/$limit),
                    'user_id' => $user_id,
                    'type' => $type
                ];
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 找回密码
     */
    public function find_password(){
        $json = $this->simpleJson();
        do {
            //
            $mobile = I('request.mobile', '', 'strval');
            $password = trim(I('request.password', '', 'strval'));
            $code = I('request.code', '', 'strval');

            if (!is_mobile($mobile)) {
                $json['status'] = 110;
                $json['msg'] = '请正确输入手机号';
                break;
            }

            if (!$code) {
                $json['status'] = 110;
                $json['msg'] = '请输入验证码';
                break;
            }

            if( strlen($password) < 6 || strlen($password) > 26){
                $json['status'] = 110;
                $json['msg'] = '密码长度在6-26个字符之间';
                break;
            }

            $this->check_sms($mobile, $code);

            $member = M('users')->where(array('mobile'=>$mobile))->find();
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没找到用户信息';
                break;
            }
            $user = [];
            $user['salt'] = random(12,'all');
            $user['password'] = encrypt_password(trim($password), $user['salt']);
            $user['update_time'] = time();
            $user['last_login_time'] = time();
            $user['last_login_type'] = 2;
            $user['ssid'] = get_login_ssid();
            $res = M('users')->where(array('id'=>$member['id']))->save($user);
            if($res){
                $json['msg'] = '密码更新成功';
                $json['data'] = $this->get_return_member(M('users')->where(array('id'=>$member['id']))->field($this->field)->find(), true);
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '密码更新失败';
                break;
            }

        }while(false);
        $this->ajaxReturn($json);

    }

    # 退出登录
    public function logout(){
        $json = $this->simpleJson();
        do {
            # $this->check_login();
            $user_id = intval($this->user['id']);
            if($user_id){
                $res = M('users')->where(array('id'=>$user_id))->save(array('ssid'=>md5(time()),'update_time'=>time()));
                if(! $res){
                    $json['status'] = 111;
                    $json['msg'] = '退出失败';
                    break;
                }
            }
            $json['msg'] = '退出成功';
            break;
        }while(false);
        $this->ajaxReturn($json);
    }

}