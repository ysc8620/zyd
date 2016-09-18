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
            return $type .'.'.$this->field;
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
        unset($member['before_win_total']);
        unset($member['before_loss_total']);
        unset($member['win_total']);
        unset($member['loss_total']);
        unset($member['zoudi_win_total']);
        unset($member['zoudi_loss_total']);
        unset($member['from_client']);
        $member['total_rate'] = number_format($member['total_rate']*100,2,'.','');
        if($member['last_time'] < time()-600){
            $win = M('tuijian')->where(['user_id'=>$member['id'],'is_count'=>1, 'status'=>['in',[1,3]], 'create_time'=>['gt', time()-2592000]])->count();
            $loss = M('tuijian')->where(['user_id'=>$member['id'],'is_count'=>1, 'status'=>['in',[2,4]], 'create_time'=>['gt', time()-2592000]])->count();
            $total = $win + $loss;
            $member['total_month_rate'] = $total > 0?number_format(($win/$total)*100,2,'.',''):0.00;

            // total_month_tuijian
            $time = time()-2592000;
            $total_month_tuijian = M('tuijian')->where(array('user_id'=>$member['id'],'create_time'=>array('gt',$time)))->count();
            M('users')->where(['id'=>$member['id']])->save(['total_month_rate'=>$member['total_month_rate'],'total_month_tuijian'=>$total_month_tuijian,'last_time'=>time()]);
        }else{
            $member['total_month_rate'] = number_format($member['total_month_rate'],2,'.','');
        }

        $member['before_match_rate'] = number_format($member['before_match_rate']*100,2,'.','');
        $member['grounder_rate'] = number_format($member['grounder_rate']*100,2,'.','');

        if(!$mysalf){
            if($member['mobile']){$member['mobile'] = substr_replace($member['mobile'],'*****',3,5);}
            if(is_mobile($member['nickname'])){
                $member['nickname'] = substr_replace($member['nickname'],'*****',3,5);
            }

            unset($member['jiguang_id']);
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
            $password = I('request.password','','strval,trim');
            $share_id = I('request.share_id','','strval,trim');
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

            if($share_id){
                $share_user_id = intval(base64_decode($share_id));
                $user['share_user_id'] = $share_user_id;
            }
            $user['register_time'] = time();
            $user['update_time'] = time();
            $user['ssid'] = get_login_ssid();
            $user['from_client']=json_encode($this->header);

            $user_id = M('users')->add($user);
            if($user_id){
                // 默认关注 杨林
                $data = [
                    'from_user_id' => $user_id,
                    'to_user_id' => '10016',
                    'create_time' => time()
                ];
                M('users_follow')->add($data);
                // 增加用户收藏总数
                M('users')->where(array('id'=>$user_id))->setInc('total_collect_user', 1);
                // 增加用户粉丝总数
                M('users')->where(array('id'=>'10016'))->setInc('total_follow_user', 1);

                $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
                $member['share_id'] = base64_encode($member['id']);
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
                $member['share_id'] = base64_encode($member['id']);
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
                $member['share_id'] = base64_encode($member['id']);
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

            $member['share_id'] = base64_encode($member['id']);

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

            $from_user_id = intval($this->user['id']);
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

            $member['is_follow'] = 0;
            if($from_user_id){
                $res = M('users_follow')->where(array('from_user_id'=>$from_user_id, 'to_user_id'=>$user_id))->find();
                if($res){
                    $member['is_follow'] = 1;
                }
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
            $share_id = I('request.share_id','','strval,trime');

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
                    $wx_user['share_id'] = base64_encode($wx_user['id']);
                    $json['data'] = $this->get_return_member($wx_user, true);
                    break;
                }else{
                    $json['status'] = 111;
                    $json['msg'] = '微信登录失败';
                    break;
                }
            }else{
                save_img($pic, APP_PATH.'../Public/static/head/'.md5($pic).'.jpg');
                $pic = '/Public/static/head/'.md5($pic).'.jpg';
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

                if($share_id){
                    $share_user_id = intval(base64_decode($share_id));
                    $data['share_user_id'] = $share_user_id;

                }

                $data['from_client']=json_encode($this->header);

                $user_id = M('users')->add($data);
                if($user_id){
                    $data = [
                        'from_user_id' => $user_id,
                        'to_user_id' => '10016',
                        'create_time' => time()
                    ];
                    M('users_follow')->add($data);
                    // 增加用户收藏总数
                    M('users')->where(array('id'=>$user_id))->setInc('total_collect_user', 1);
                    // 增加用户粉丝总数
                    M('users')->where(array('id'=>'10016'))->setInc('total_follow_user', 1);

                    $member = M('users')->where(array('wx_openid'=>$openid))->field($this->field)->find();
                    $json['msg'] = '微信登录成功';
                    $member['share_id'] = base64_encode($member['id']);
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
                    $json['data'] = [];
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
                    save_img($pic, APP_PATH.'../Public/static/head/'.md5($pic).'.jpg');
                    $pic = '/Public/static/head/'.md5($pic).'.jpg';
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
            $password = I('request.password','','trim,strval');
            $type = I('request.type',0,'intval');
            $jiguang_id = I('request.jiguang_id','','trim,strval');
            $jiguang_type = I('request.jiguang_type','','trim,strval');

            if(empty($nickname) || strlen($nickname) > 36){
//                $json['status'] = 110;
//                $json['msg'] = '用户昵称在1-36个字符之间';
//                break;
            }
            $member = M('users')->where(array('id'=>$user_id))->field($this->field)->find();
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没找到用户信息';
                break;
            }
            $data = [
                'update_time' => time()
            ];

            if($nickname){
                if($member['is_edit'] == '0'){
                    $data['nickname'] = $nickname;
                    $data['is_edit'] = 1;
                }
            }
            if($type){
                $data['type'] = $type;
                if($type == 2){
                    $data['is_expert'] = 1;
                }else{
                    $data['is_expert'] = 0;
                }
            }
            if($jiguang_id){
                $data['jiguang_id'] = $jiguang_id;
                $data['jiguang_type'] = $jiguang_type;
            }

            if($password){
                if( strlen($password) < 6 || strlen($password) > 26){
                    $json['status'] = 110;
                    $json['msg'] = '密码长度在6-26个字符之间';
                    break;
                }
                $data['salt'] = random(12,'all');
                $data['password'] = encrypt_password(trim($password), $data['salt']);
            }
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
                // 增加用户收藏总数
                M('users')->where(array('id'=>$user_id))->setInc('total_collect_user', 1);
                // 增加用户粉丝总数
                M('users')->where(array('id'=>$to_user_id))->setInc('total_follow_user', 1);
                if($res){
                    $json['msg'] = '关注成功';
                    $json['data']['id'] = $res;
                    $json['data']['user_id'] = $user_id;
                    $json['data']['to_user_id'] = $to_user_id;
                    $json['data']['follow_id'] = $res;
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
     *  用户取消关注接口
     */
    public function un_follow(){
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
                M('users_follow')->where(array('id'=>$follow['id']))->delete();
                // 减少用户收藏总数
                M('users')->where(array('id'=>$user_id))->setDec('total_collect_user', 1);
                // 减少用户粉丝总数
                M('users')->where(array('id'=>$to_user_id))->setDec('total_follow_user', 1);
                $json['msg'] = '取消关注成功';
                $json['data']['user_id'] = $user_id;
                $json['data']['to_user_id'] = $to_user_id;
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '您没有关注该用户';
                break;

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
            $total = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.to_user_id")->count();
            $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page->show();

            $list = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.to_user_id")
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
                $total = M('users')->where("status = 1 AND total_send_info>5")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M('users')->where("status = 1 AND total_send_info>5")
                    ->field($this->field)
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("total_month_rate DESC, total_rate DESC")->select();

                foreach($list as $i=>$item){
                    $item['is_follow'] = 0;
                    if($user_id){
                        $res = M('users_follow')->where(array('from_user_id'=>$user_id, 'to_user_id'=>$item['id']))->find();
                        if($res){
                            $item['is_follow'] = 1;
                        }
                    }
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
                $total = M('users')->where("status = 1")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M('users')->where(" status = 1")
                    ->field($this->field)
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("total_follow_user DESC,total_rate DESC")->select();
                foreach($list as $i=>$item){
                    $item['is_follow'] = 0;
                    if($user_id){
                        $res = M('users_follow')->where(array('from_user_id'=>$user_id, 'to_user_id'=>$item['id']))->find();
                        if($res){
                            $item['is_follow'] = 1;
                        }
                    }
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

                $total = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")->count();
                $Page = new \Think\Page($total, $limit); // 实例化分页类 传入总记录数和每页显示的记录数(25)
                $Page->show();

                $list = M()->table(C('DB_PREFIX').'users as u, '.C('DB_PREFIX').'users_follow as uf')->where("u.status = 1 AND uf.from_user_id=$user_id AND u.id = uf.from_user_id")
                    ->field($this->getField('u'))
                    ->limit($Page->firstRow . ',' . $Page->listRows)->order("uf.create_time DESC")->select();

                foreach($list as $i=>$item){
                    $item['is_follow'] = 1;
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

    /**
     * 绑定手机
     */
    public function bind_mobile(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $mobile = I('request.mobile','','strval');
            $code = I('request.code',0,'intval');

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


            $this->check_sms($mobile, $code);
            $member = M('users')->where(array('mobile'=>$mobile))->find();
            if($member){
                $json['status'] = 111;
                $json['msg'] = '手机已经被绑定';
                break;
            }

            $member = M('users')->where(array('id'=>$user_id))->find();
            if(!$member){
                $json['status'] = 111;
                $json['msg'] = '没找到用户信息';
                break;
            }
            $user = [];
            $user['update_time'] = time();
            $user['mobile'] = $mobile;
            $res = M('users')->where(array('id'=>$member['id']))->save($user);
            if($res){
                $json['msg'] = '手机绑定成功';
                $json['data'] = $this->get_return_member(M('users')->where(array('id'=>$member['id']))->field($this->field)->find(), true);
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = '手机绑定失败';
                break;
            }

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取用户流水
     */
    public function get_record_list(){
        $json = $this->simpleJson();
        do{
            $page = I('request.p',1,'intval');
            $limit = I('request.limit',10,'intval');
            $type = I('request.type', 1,'intval');// type=1 获取自己的流水记录，需要验证登陆, type=2获取指定用户流水
            $user_id = I('request.user_id',0,'intval'); // user_id 用户编号, 只有状态type=2时需要
            if($type == 1){
                $this->check_login();
                $user_id = $this->user['id'];
            }elseif($type == 2){
                if(! $user_id){
                    $json['status'] = 110;
                    $json['msg'] = '请选择查看用户ID';
                    break;
                }
            }
            $total = M('credit_log')->where(['user_id'=>$user_id])->count();
            $pagevo = new \Think\Page($total, $limit);
            $list = M('credit_log')->where(['user_id'=>$user_id])->order('id DESC')->limit($pagevo->firstRow . ',' . $pagevo->listRows)->select();
            foreach($list as $i=>$item){
                // 充值
                if($item['type'] == '1'){
                    $list[$i]['title'] = "球币充值";
                    $list[$i]['content'] = "您充值了{$item['credit']}个球币";
                // 购买
                }elseif($item['type'] == 2){
                    $list[$i]['title'] = '竞猜购买';
                    $tuijian_order = M('tuijian_order')->where(['id'=>$item['from_id']])->find();
                    $match = M('match')->where(['match_id'=>$tuijian_order['match_id']])->field('id,match_id,league_name,home_name,away_name')->find();
                    //$user = M('users')->where(['id'=>$tuijian['user_id']])->find();
                    $list[$i]['content'] = "您花了{$tuijian_order['credit']}个球币,购买了{$match['league_name']} {$match['home_name']} VS {$match['away_name']} 竞猜";
                }elseif($item['type'] == 3){
                    $list[$i]['title'] = '竞猜销售';
                    $tuijian_order = M('tuijian_order')->where(['id'=>$item['from_id']])->find();
                    $match = M('match')->where(['match_id'=>$tuijian_order['match_id']])->field('id,match_id,league_name,home_name,away_name')->find();
                    //$user = M('users')->where(['id'=>$item['from_user']])->find();
                    $list[$i]['content'] = "您挣了{$tuijian_order['credit']}个球币,销售了 {$match['league_name']} {$match['home_name']} VS {$match['away_name']} 竞猜";
                }elseif($item['type'] == 4){

                    $list[$i]['title'] = "球币提现";
                    $list[$i]['content'] = "您提现了{$item['credit']}个球币";
                }
                $list[$i]['create_date'] = date("Y-m-d H:i:s",$item['create_time']);
            }
            $json['data']['list'] = $list;
            $json['data']['total'] = $total;
            $json['data']['page'] = $page;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
            $json['data']['user_id'] = $user_id;

        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 用户申请提现
     */
    public function withdrawal(){
        $json = $this->simpleJson();
        do{}while(false);
        $this->ajaxReturn($json);
    }
}