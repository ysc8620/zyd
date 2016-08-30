<?php
namespace Home\Controller;
use Think\Page;

class TuijianController extends BaseApiController {
    /**
     * 竞猜列表
     */
    public function index(){
        $json = $this->simpleJson();
        do{
            //
            $type = I('request.type', 1, 'intval'); // 1 某赛事的竞猜, 2 我发布的竞猜, 3 指定用户发布的竞猜, 4,最新竞猜, 5 我购买过的,6 我关注的专家发布的竞猜
            $sub_type = I('request.sub_type', 0, 'intval');
            $match_id = I('request.match_id', 0,'intval');
            $user_id = I('request.user_id',0,'intval');
            $limit = I('request.limit', 10, 'intval');
            $p = I('request.p',1,'intval');

            $where = [];
            $where['tuijian_status'] = 1;
            if($type == 1){
                if(empty($match_id)){
                    $json['status'] = 110;
                    $json['msg'] = "请选择查看赛事";
                    break;
                }
                $where['match_id'] = $match_id;
                if($sub_type){
                    $where['type'] = $sub_type;
                }
            }elseif($type == 2){
                $this->check_login();
                $user_id = $this->user['id'];
                $where['user_id'] = $user_id;
            }elseif($type == 3){
                if(empty($user_id)){
                    $json['status'] = 110;
                    $json['msg'] = "请选择查看专家";
                    break;
                }
                $where['user_id'] = $user_id;
            }elseif($type == 4){
                //$where['is_expert'] = 1;
            }elseif($type == 5){
                $this->check_login();
                $user_id = $this->user['id'];

                $where['id'] = array('exp', "in(SELECT tuijian_id FROM ".C('DB_PREFIX')."tuijian_order WHERE user_id='{$user_id}')");
            }elseif($type == 6){
                $this->check_login();
                $user_id = $this->user['id'];

                $where['user_id'] = array('exp', "in(SELECT to_user_id as user_id FROM ".C('DB_PREFIX')."users_follow WHERE from_user_id='{$user_id}')");
            }
            $total = M('tuijian')->where($where)->count();
            $Page = new Page($total, $limit);
            $list = M('tuijian')->where($where)->limit($Page->firstRow, $Page->listRows)->order("id DESC")->select();
            // 赛事
            foreach($list as $i=>$item){
                $match = M('match')->where(array('match_id'=>$item['match_id']))->field('id,match_id,league_id,league_name,home_name,away_name,time,state')->find();
                $item['league_id'] = $match['league_id'];
                $item['league_name'] = $match['league_name'];
                $item['home_name'] = $match['home_name'];
                $item['away_name'] = $match['away_name'];
                $item['match_time'] = $match['time'];
                $item['match_state'] = $match['state'];
                # 赛前
                if($item['tuijian_match_state'] == '0'){

                // 上半场
                }elseif($item['tuijian_match_time'] == 1){

                }
                $item['tuijian_match_time'] = '';
                #
                $user = M('users')->where(array('id'=>$item['user_id']))->field('id,nickname,pic,total_follow_user,total_rate')->find();
                $item['user_name'] = getNickName($user['nickname']);
                $item['user_pic'] = pic_url($user['pic']);
                $item['user_follow'] = $user['total_follow_user'];
                $item['user_rate'] = $user['total_rate'];
                # 是否购买
                $is_buy = 0;
                if($item['is_fee']){

                    if(!empty($this->user)){
                        if($this->user['id'] != $item['user_id']){
                            $buy = M('tuijian_order')->where(array('tuijian_id'=>$item['id'],'user_id'=>$this->user['id']))->field('id')->find();
                            if($buy){
                                $is_buy = 1;
                            }
                        }else{
                            $is_buy = 1;
                        }
                    }
                }else{
                    $is_buy = 1;
                }
                $item['is_buy'] = $is_buy;// 默认没有购买
                $list[$i] = $item;
            }

            $json['data']['list'] = (array)$list;
            $json['data']['total'] = $total;
            $json['data']['page'] = $p;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
            $json['data']['user_id'] = $user_id;
            $json['data']['match_id'] = $match_id;
            $json['data']['sub_type'] = $sub_type;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 竞猜列表
     */
    public function index2(){
        $json = $this->simpleJson();
        do{
            //
            $type = I('request.type', 1, 'intval'); // 1 某赛事的竞猜, 2 我发布的竞猜, 3 指定用户发布的竞猜, 4,最新竞猜, 5 我购买过的
            $sub_type = I('request.sub_type',0,'intval');
            $match_id = I('request.match_id', 0,'intval');
            $user_id = I('request.user_id',0,'intval');
            $limit = I('request.limit', 10, 'intval');
            $p = I('request.p',1,'intval');

            $where = [];
            $where['tuijian_status'] = 1;
            if($type == 1){
                if(empty($match_id)){
                    $json['status'] = 110;
                    $json['msg'] = "请选择查看赛事";
                    break;
                }
                $where['match_id'] = $match_id;
                if($sub_type){
                    $where['type'] = $sub_type;
                }
            }elseif($type == 2){
                $this->check_login();
                $user_id = $this->user['id'];
                $where['user_id'] = $user_id;
            }elseif($type == 3){
                if(empty($user_id)){
                    $json['status'] = 110;
                    $json['msg'] = "请选择查看专家";
                    break;
                }
                $where['user_id'] = $user_id;
            }elseif($type == 4){
                // $where['is_expert'] = 1;
            }elseif($type == 5){
                $this->check_login();
                $user_id = $this->user['id'];

                $where['id'] = array('exp', "in(SELECT tuijian_id FROM ".C('DB_PREFIX')."tuijian_order WHERE user_id='{$user_id}')");
            }elseif($type == 6){
                $this->check_login();
                $user_id = $this->user['id'];

                $where['user_id'] = array('exp', "in(SELECT to_user_id as user_id FROM ".C('DB_PREFIX')."users_follow WHERE from_user_id='{$user_id}')");
            }
            $total = M('tuijian')->where($where)->count();
            $Page = new Page($total, $limit);
            $list = M('tuijian')->where($where)->limit($Page->firstRow, $Page->listRows)->order(" id DESC")->
            field('*')->select();
            // 赛事
            foreach($list as $i=>$item){
                $match = M('match')->where(array('match_id'=>$item['match_id']))->field('id,match_id,league_id,league_name,home_name,away_name,time,state')->find();
                $item['league_id'] = $match['league_id'];
                $item['league_name'] = $match['league_name'];
                $item['home_name'] = $match['home_name'];
                $item['away_name'] = $match['away_name'];
                $item['match_time'] = $match['time'];
                $item['match_state'] = $match['state'];
                $item['match_id'] = $match['match_id'];
                #
                $user = M('users')->where(array('id'=>$item['user_id']))->field('id,nickname,pic,total_follow_user,total_rate')->find();
                $item['user_name'] = getNickName($user['nickname']);
                $item['user_pic'] = pic_url($user['pic']);
                $item['user_follow'] = $user['total_follow_user'];
                $item['user_rate'] = $user['total_rate'];
                # 是否购买
                $is_buy = 0;
                if($item['is_fee']){
                    if(!empty($this->user)){
                        if($this->user['id'] != $item['user_id']){
                            $buy = M('tuijian_order')->where(array('tuijian_id'=>$item['id'],'user_id'=>$this->user['id']))->field('id')->find();
                            if($buy){
                                $is_buy = 1;
                            }
                        }else{
                            $is_buy = 1;
                        }
                    }
                }else{
                    $is_buy = 1;
                }
                $item['is_buy'] = $is_buy;// 默认没有购买
                $list[$i] = $item;
            }


            $new = [];
            foreach($list as $i=>$item){
                $new[$item['match_id']]['league_id'] = $item['league_id'];
                $new[$item['match_id']]['league_name'] = $item['league_name'];
                $new[$item['match_id']]['match_id'] =$item['match_id'];
                $new[$item['match_id']]['home_name'] = $item['home_name'];
                $new[$item['match_id']]['away_name'] = $item['away_name'];
                $new[$item['match_id']]['match_time'] = $item['match_time'];
                $new[$item['match_id']]['list'][] = $item;
            }

            $new_list = [];
            foreach($new as $item){
                $new_list[] = $item;
            }
            $json['data']['list'] = $new_list;
            $json['data']['total'] = $total;
            $json['data']['page'] = $p;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['type'] = $type;
            $json['data']['limit'] = $limit;
            $json['data']['user_id'] = $user_id;
            $json['data']['match_id'] = $match_id;
            $json['data']['sub_type'] = $sub_type;
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 发布竞猜
     */
    public function post(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $user_name = getNickName($this->user['nickname']);
            $data = [];
            $data['user_id'] = $this->user['id'];
            $data['match_id'] = I('request.match_id',0,'intval');
            $data['type'] = I('request.type',0,'intval');
            $data['sub_type'] = I('request.sub_type',0,'intval');
            $data['is_fee'] = I('request.is_fee',0,'intval');
            $data['fee'] = I('request.fee',0,'trim');
            $data['remark'] = I('request.remark','','strval,trim,strip_tags,htmlspecialchars');
            $data['guess_1'] = I('request.guess_1',0,'intval');
            $data['guess_2'] = I('request.guess_2',0,'intval');
            $data['tuijian_home_score'] = I('request.tuijian_home_score',0,'trim');
            $data['tuijian_away_score'] = I('request.tuijian_away_score',0,'trim');
            $data['rate_1'] = I('request.rate_1',0,'trim');
            $data['rate_2'] = I('request.rate_2',0,'trim');
            $data['rate_3'] = I('request.rate_3',0,'trim');
            $data['rate_4'] = I('request.rate_4',0,'trim');
            $data['rate_5'] = I('request.rate_5',0,'trim');
            $data['rate_6'] = I('request.rate_6',0,'trim');
            $data['left_ball'] = I('request.left_ball',0,'trim');
            $data['tuijian_match_time'] = I('request.tuijian_match_time','','trim');
            $data['tuijian_match_state'] = I('request.tuijian_match_state','','trim');

            if(empty($data['match_id'])){
                $json['status'] = 110;
                $json['msg'] = "请选择竞猜赛事";
                break;
            }

            if(empty($data['type'])){
                $json['status'] = 110;
                $json['msg'] = "请选择竞猜类型";
                break;
            }

            if(empty($data['sub_type'])){
                $json['status'] = 110;
                $json['msg'] = "请选择竞猜子类型";
                break;
            }

            if(empty($data['user_id'])){
                $json['status'] = 110;
                $json['msg'] = "发布用户不能为空";
                break;
            }

            if(empty($data['guess_1'])){
                $json['status'] = 110;
                $json['msg'] = "竞彩类型不能为空";
                break;
            }

            if($data['is_fee'] && empty($data['fee'])){
                $json['status'] = 110;
                $json['msg'] = "请输入查看费用";
                break;
            }

            $match = M('match')->where(array('match_id'=>$data['match_id']))->field('id,match_id,state')->find();
            if( ! $match ){
                $json['status'] = 111;
                $json['msg'] = "没找到赛事信息";
                break;
            }


            // 状态处理
            if(!in_array($match['state'],['0','1','2','3'])){
                $json['status'] = 111;
                $json['msg'] = "赛事已经完结不支持竞猜";
                break;
            }

            // 重复提交处理

            $tuijian = M('tuijian')->where(array('user_id'=>$user_id, 'match_id'=>$data['match_id'],'type'=>$data['type'],'sub_type'=>$data['sub_type']))->order("id DESC")->find();
            if($tuijian){
                if(
                    $tuijian['tuijian_home_score'] == $data['tuijian_home_score'] &&
                    $tuijian['tuijian_away_score'] == $data['tuijian_away_score'] &&
                    $tuijian['rate_1'] == $data['rate_1'] &&
                    $tuijian['rate_2'] == $data['rate_2'] &&
                    $tuijian['rate_3'] == $data['rate_3'] &&
                    $tuijian['rate_4'] == $data['rate_4'] &&
                    $tuijian['rate_5'] == $data['rate_5'] &&
                    $tuijian['rate_6'] == $data['rate_6'] ){
                    $json['status'] = 111;
                    $json['msg'] = "请不要重复竞猜";
                    break;
                }
            }
            // 赛前 赛中
            if($match['state'] == 0){
                $data['tuijian_type'] = 1;
            }else{
                $data['tuijian_type'] = 2;
            }

            $data['create_time'] = time();
            $data['update_time'] = time();
            $res = M('tuijian')->add($data);

            if($res){
                // 更新赛事竞猜总数
                M('match')->where(array('match_id'=>$data['match_id']))->setInc('total_tuijian',1);
                // 更新用户发布总数
                M('users')->where(array('id'=>$data['user_id']))->setInc('total_send_info',1);
                // total_month_tuijian
                $time = time()-2592000;
                $total_month_tuijian = M('tuijian')->where(array('user_id'=>$user_id,'create_time'=>array('gt',$time)))->count();
                M('users')->where(array('id'=>$data['user_id']))->save(array('total_month_tuijian'=>$total_month_tuijian));

                // 用户关注该比赛 `user_id`, `match_id`
                $follow = M('match_follow')->where(array('user_id'=>$user_id, 'match_id'=>$data['match_id']))->field('id')->find();
                if(!$follow){
                    $folow_data = [
                        'user_id' =>$user_id,
                        'match_id' =>$data['match_id'],
                        'create_time' => time()
                    ];
                    M('match_follow')->add($folow_data);
                }

                // 发布推送
                /*
                 第4类消息推送
                标题：发布竞猜
                文案：您关注的XX发布了新的（初盘、滚球）竞猜
                触发条件：用户关注的专家发布新的竞猜的时候
                链接：链接到专家个人主页
                */
                $title = "发布竞彩";
                $state = $match['state'] == '0'?'初盘':"滚球";
                $remark = "您关注的{$user_name}发布了新的{$state}竞猜";
                $user_list = $user_list = M()->table("t_users_follow as f, t_users as u")->where("f.to_user_id='{$user_id}' AND u.id = f.from_user_id")->field('u.id, u.jiguang_id, u.jiguang_alias')->select();

                $jiguang_alias = [];
                $jiguang_id = [];
                foreach($user_list as $user){
                    if($user['jiguang_id']){
                        $jiguang_id[$user['jiguang_id']] = $user['jiguang_id'];
                    }
                    // 添加通知消息
                    $notice = [
                        'notice_type'=>2,
                        'from_id'=>$res,
                        'to_id'=>$user['id'],
                        'notice_title'=>'发布竞彩',
                        'notice_msg'=>$remark,
                        'create_time'=>time()
                    ];
                    M('notice_info')->add($notice);
                }

                // 关注的用户发布竞猜
                send_tuisong($jiguang_alias, $jiguang_id,$title,$remark,1,$user_id);

                $data['id'] = $res;
                $json['msg'] = '发布成功';
                $json['data'] = $data;
                break;
            }else{
                $json['status'] = 111;
                $json['msg'] = "发布失败";
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 竞猜购买
     */
    public function pay(){
        $json = $this->simpleJson();
        do{
            $this->check_login();
            $user_id = $this->user['id'];
            $tuijian_id = I('request.tuijian_id',0,'intval');

            if(empty($user_id)){
                $json['status'] = 110;
                $json['msg'] = '用户不能为空';
                break;
            }

            if(empty($tuijian_id)){
                $json['status'] = 110;
                $json['msg'] = '请选择购买竞猜';
                break;
            }
            // 购买记录
            $has = M('tuijian_order')->where(array('user_id'=>$user_id, 'tuijian_id'=>$tuijian_id))->find();
            if($has){
                $data = [
                    'user_id' => $user_id,
                    'tuijian_id' => $has['from_id'],
                    'credit' => $has['credit'],
                    'create_time' => time(),
                    'id'=>$has['id']
                ];
                $json['msg'] = '你已经购买过';
                $json['data'] = $data;
                break;
            }
            $tuijian = M('tuijian')->where(array('id'=>$tuijian_id))->find();

            if(empty($tuijian)){
                $json['status'] = 111;
                $json['msg'] = '请选择购买竞猜';
                break;
            }
            if($tuijian['user_id'] == $user_id){
                $data = [
                    'user_id' => $user_id,
                    'tuijian_id' => $tuijian['id'],
                    'credit' => $tuijian['fee'],
                    'create_time' => time(),
                    'id'=>0
                ];

                $json['msg'] = '购买成功';
                $json['data'] = $data;
                break;
            }
            if($this->user['credit'] < $tuijian['fee']){
                $json['status'] = 112;
                $json['msg'] = '球币不足,请先充值';
                break;
            }

            $tuijian_user = M('users')->where(['id'=>$tuijian['user_id']])->find();
            $data = [
                'user_id' => $user_id,
                'tuijian_id' => $tuijian_id,
                'credit' => $tuijian['fee'],
                'create_time' => time()
            ];
            $credit_log = [
                'type' => 2,
                'credit' => -$tuijian['fee'],
                'from_id' => $tuijian['id'],
                'remark' => "购买竞猜",
                'create_time' => time(),
                'user_id' => $user_id,
                'status' => 0
            ];

            M()->startTrans();
            $res = M('tuijian_order')->add($data);
            $res2 = M()->execute("UPDATE ".C('DB_PREFIX')."users SET credit=credit-'{$tuijian['fee']}' WHERE id='{$user_id}' AND credit>='{$tuijian['fee']}'");
            $credit_log['from_id'] = $res;
            $res3 = M('credit_log')->add($credit_log);

            if($res && $res2 && $res3){
                M()->commit();
                // 消息通知
                $notice = [
                    'notice_type'=>2,
                    'from_id'=>$tuijian['id'],
                    'to_id'=>$this->user['id'],
                    'notice_title'=>'购买竞彩',
                    'notice_msg'=>"您购买了".getNickName($tuijian_user['nickname'])."发布的竞彩",
                    'create_time'=>time()
                ];
                M('notice_info')->add($notice);

                // 消息通知
                $notice = [
                    'notice_type'=>2,
                    'from_id'=>$tuijian['id'],
                    'to_id'=>$tuijian['user_id'],
                    'notice_title'=>'销售竞彩',
                    'notice_msg'=>getNickName($this->user['nickname'])."购买了您发布的竞彩",
                    'create_time'=>time()
                ];
                M('notice_info')->add($notice);

                $credit_log2 = [
                    'type' => 3,
                    'credit' => $tuijian['fee'],
                    'from_id' => $tuijian['id'],
                    'remark' => "销售竞猜",
                    'create_time' => time(),
                    'user_id' => $tuijian['user_id'],
                    'status' => 0
                ];
                $credit_log2['from_id'] = $res;
                M('credit_log')->add($credit_log2);
                // 更新用户购买总数
                M('users')->where(array('id'=>$user_id))->setInc('total_buy_info',1);
                // 更新销售总数
                M('users')->where(array('id'=>$tuijian['user_id']))->setInc('total_seller_info',1);
                $data['id'] = $res;
                $json['msg'] = '购买成功';
                $json['data'] = $data;
            }else{
                M()->rollback();
                $json['msg'] = '购买失败';
                $json['status'] = 111;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 竞猜详情
     */
    public function info(){
        $json = $this->simpleJson();
        do {
            $tuijian_id = I('request.tuijian_id', 0, 'intval');

            if (empty($tuijian_id)) {
                $json['status'] = 110;
                $json['msg'] = '请选择查看竞猜';
                break;
            }
            $tuijian = M('tuijian')->where(array('id'=>$tuijian_id, 'tuijian_status'=>1))->find();
            if($tuijian){

                $match = M('match')->where(array('match_id'=>$tuijian['match_id']))->field('id,match_id,league_id,
                league_name,home_name,away_name,time,state,home_score,away_score,home_half_score,away_half_score')->find();
                $tuijian['league_id'] = $match['league_id'];
                $tuijian['league_name'] = $match['league_name'];
                $tuijian['home_name'] = $match['home_name'];
                $tuijian['away_name'] = $match['away_name'];
                $tuijian['match_time'] = $match['time'];
                $tuijian['match_state'] = $match['state'];

                $tuijian['home_score'] = $match['home_score'];
                $tuijian['away_score'] = $match['away_score'];
                $tuijian['home_half_score'] = $match['home_half_score'];
                $tuijian['away_half_score'] = $match['away_half_score'];
                #
                $user = M('users')->where(array('id'=>$tuijian['user_id']))->field('id,nickname,pic,total_follow_user,total_rate')->find();
                $tuijian['user_name'] = getNickName($user['nickname']);
                $tuijian['user_pic'] = pic_url($user['pic']);
                $tuijian['user_follow'] = $user['total_follow_user'];
                $tuijian['user_rate'] = $user['total_rate'];

                # 是否购买
                $is_buy = 0;
                if($tuijian['is_fee']){
                    if(!empty($this->user)){
                        if($this->user['id'] != $tuijian['user_id']){
                            $buy = M('tuijian_order')->where(array('tuijian_id'=>$tuijian['id'],'user_id'=>$this->user['id']))->field('id')->find();
                            if($buy){
                                $is_buy = 1;
                            }
                        }else{
                            $is_buy = 1;
                        }
                    }
                }else{
                    $is_buy = 1;
                }
                $tuijian['is_buy'] = $is_buy;// 默认没有购买

                $json['data'] = $tuijian;
            }else{
                $json['status'] = 111;
                $json['msg'] = '找不到竞猜信息或者竞猜已关闭';
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    /**
     * 获取竞猜购买用户列表
     */
    public function get_user_list(){
        $json = $this->simpleJson();
        do {
            $tuijian_id = I('request.tuijian_id', 0, 'intval');
            $p = I('request.p',1,'intval');
            $limit = I('request.limit',10,'intval');

            if (empty($tuijian_id)) {
                $json['status'] = 110;
                $json['msg'] = '用户不能为空';
                break;
            }
            $total = M('tuijian_order')->where(array('tuijian_id'=>$tuijian_id))->count();
            $Page = new Page($total, $limit);
            $list = M('tuijian_order')->where(array('tuijian_id'=>$tuijian_id))->limit($Page->firstRow, $Page->listRows)->order("create_time DESC")->select();
            foreach($list as $i=>$item){
                $user = M('users')->where(array('id'=>$item['user_id']))->field('id,nickname,pic')->find();
                $item['nickname'] = getNickName($user['nickname']);
                $item['pic'] = pic_url($user['pic']);
                $list[$i] = $item;
            }
            $json['data']['list'] = (array)$list;
            $json['data']['total'] = $total;
            $json['data']['page'] = $p;
            $json['data']['total_page'] = ceil($total/$limit);
            $json['data']['limit'] = $limit;
            $json['data']['tuijian_id'] = $tuijian_id;
        }while(false);
        $this->ajaxReturn($json);
    }
}