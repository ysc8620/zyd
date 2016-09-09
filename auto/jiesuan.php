<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2016/8/25
 * Time: 22:36
 */

namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=match_api=\r\n";
// 0 等待结果， 1 赢， 2输，3赢半,4输半,5走
// 类型：1, 竞彩, 2欧赔,3大小球,4 让球
// 计算半场竞猜输赢
$match_list = M("match")->where(array('state'=>array('in',[2,3,-1]), 'is_half_count'=>0))->field('id,match_id,home_score,away_score,home_half_score,away_half_score')->select();
foreach($match_list as $match){
    echo "half_jiesuan=".$match["match_id"]."\r\n";
    // 获取所有竞猜信息
    $tuijian_list = M('tuijian')->where(array('match_id'=>$match['match_id'], 'sub_type'=>1,  'is_win'=>0))->select();
    foreach($tuijian_list as $tuijian){
        // < 1.5
        // 欧赔
        if($tuijian['type'] == 2){
            // 半场欧赔
            if($tuijian['sub_type'] == 1){
                $status = 0;
                $rate = 0;
                // 主胜
                if($tuijian['guess_1'] == 4){
                    $rate = $tuijian['rate_4'];
                    if($match['home_half_score'] > $match['away_half_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 和局
                }elseif($tuijian['guess_1'] == 5){
                    $rate = $tuijian['rate_5'];
                    if($match['home_half_score'] == $match['away_half_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 客胜
                }elseif($tuijian['guess_1'] == 6){
                    $rate = $tuijian['rate_6'];
                    if($match['home_half_score'] < $match['away_half_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                }

                $is_count = 0;
                if($rate>1.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                // 结算记录
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'is_count'=>$is_count, 'count_time'=>time()]);
                if($rate > 1.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        // 赢
                        if($status == 1){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        
                            // 输
                        }else{
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    // 走地
                    }else{
                        if($status == 1){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }else{
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }
                    }
                }
            }
        }elseif($tuijian['type'] == 3){
            // 半场大小球
            if($tuijian['sub_type'] == 1){
                $status = 0;

                // 大球计算
                if($tuijian['guess_1'] == 4){
                    $rate = $tuijian['rate_4'];

                    $result = $match['home_half_score'] + $match['away_half_score'] - $tuijian['rate_5'];
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= -0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['home_half_score']} + {$match['away_half_score']} - {$tuijian['rate_5']}\r\n";
                    }
                // 小球计算
                }elseif($tuijian['guess_1'] == 6){
                    $rate = $tuijian['rate_6'];
                    $result = $tuijian['rate_5'] -( $match['home_half_score'] + $match['away_half_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= -0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }

                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$tuijian['rate_5']} -( {$match['home_half_score']} + {$match['away_half_score']})\r\n";
                    }
                }
                $is_count = 0;
                if($rate>0.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status, 'is_count'=>$is_count, 'count_time'=>time()]);

                // 盘口大于0.5才参与计算
                if($rate > 0.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    // 走地
                    }else{
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    }
                }

            }
        }elseif($tuijian['type'] == 4){
            // 半场让球
            if($tuijian['sub_type'] == 1){
                $status = 0;
                // 主队赢
                if($tuijian['guess_1'] == 4){
                    $rate = $tuijian['rate_4'];
                    // 最终主队进球数-最终客队进球数+竞猜时的盘口-（竞猜时的主队进球数-竞猜时的客队进球数）
                    $result = $match['home_half_score'] - $match['away_half_score'] + $tuijian['rate_5'] - ($tuijian['tuijian_home_score'] - $tuijian['tuijian_away_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= 0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['home_half_score']} - {$match['away_half_score']} + {$tuijian['rate_5']} - ({$tuijian['tuijian_home_score']} - {$tuijian['tuijian_away_score']});\r\n";
                    }
                // 客队赢
                }elseif($tuijian['guess_1'] == 6){
                    $rate = $tuijian['rate_6'];
                    // 最终主队进球数-最终客队进球数+竞猜时的盘口-（竞猜时的主队进球数-竞猜时的客队进球数）
                    $result = $match['away_half_score'] - $match['home_half_score'] - $tuijian['rate_5'] - ($tuijian['tuijian_away_score'] - $tuijian['tuijian_home_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= 0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['away_half_score']} - {$match['home_half_score']} - {$tuijian['rate_5']} - ({$tuijian['tuijian_away_score']} - {$tuijian['tuijian_home_score']});\r\n";
                    }
                }

                $is_count = 0;
                if($rate>0.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                // 保存计算结果
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'is_count'=>$is_count, 'count_time'=>time()]);
                if($rate > 0.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    // 走地
                    }else{
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    }
                }
            }
        }
    }
    M('match')->where(['id'=>$match['id']])->save(['is_half_count'=>1, 'half_count_time'=>time()]);
}

// 计算全场竞猜输赢
$match_list = M("match")->where(array('state'=>"-1", 'is_full_count'=>0))->field('id,match_id,home_score,away_score,home_half_score,away_half_score')->select();
foreach($match_list as $match){
    echo "full_jiesuan=".$match["match_id"]."\r\n";
    // 获取所有竞猜信息 array('match_id'=>$match['match_id'],  'is_win'=>0)
    $tuijian_list = M('tuijian')->where("match_id='{$match['match_id']}' AND is_win=0")->select();
    foreach($tuijian_list as $tuijian){
        // 竞彩
        if($tuijian['type'] == 1){
            // 让球竞彩
            if($tuijian['sub_type'] == 1){
                $result = $match['home_score'] + $tuijian['left_ball'] - $match['away_score'];//主队进球数+让球盘口-客队进球数
                // 主胜
                if($result > 0){
                    if($tuijian['guess_1'] == 4 || $tuijian['guess_2'] == 4){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 和局
                }elseif($result<0){
                    if($tuijian['guess_1'] == 6 || $tuijian['guess_2'] == 6){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 客胜
                }else{
                    if($tuijian['guess_1'] == 5 || $tuijian['guess_2'] == 5){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                }
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'count_time'=>time()]);
            // 竞彩
            }elseif($tuijian['sub_type'] == 2){
                // 主胜
                if($match['home_score'] > $match['away_score']){
                    if($tuijian['guess_1'] == 1 || $tuijian['guess_2'] == 1){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 客胜
                }elseif($match['home_score'] < $match['away_score']){
                    if($tuijian['guess_1'] == 3 || $tuijian['guess_2'] == 3){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                // 和局
                }else{
                    if($tuijian['guess_1'] == 2 || $tuijian['guess_2'] == 2){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                }
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'count_time'=>time()]);
            }
        } // 欧赔
        elseif($tuijian['type'] == 2){

            // 半场欧赔
            if($tuijian['sub_type'] == 2){
                $status = 0;
                $rate = 0;
                // 主胜
                if($tuijian['guess_1'] == 1){
                    $rate = $tuijian['rate_1'];
                    if($match['home_score'] > $match['away_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                    // 和局
                }elseif($tuijian['guess_1'] == 2){
                    $rate = $tuijian['rate_2'];
                    if($match['home_score'] == $match['away_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                    // 客胜
                }elseif($tuijian['guess_1'] == 3){
                    $rate = $tuijian['rate_3'];
                    if($match['home_score'] < $match['away_score']){
                        $status = 1;
                    }else{
                        $status = 2;
                    }
                }

                $is_count = 0;
                if($rate>1.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                // 结算记录
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'is_count'=>$is_count, 'count_time'=>time()]);
                if($rate > 1.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        if($status == 1 ){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 ){
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    // 走地
                    }else{
                        if($status == 1 ){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }elseif($status == 2 ){
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    }
                }
            }
        }elseif($tuijian['type'] == 3){
            // 全场大小球
            if($tuijian['sub_type'] == 2){
                $status = 0;

                // 大球计算
                if($tuijian['guess_1'] == 1){
                    $rate = $tuijian['rate_1'];
                    $result = $match['home_score'] + $match['away_score'] - $tuijian['rate_2'];
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= -0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['home_score']} + {$match['away_score']} - {$tuijian['rate_2']}\r\n";
                    }
                    // 小球计算
                }elseif($tuijian['guess_1'] == 3){
                    $rate = $tuijian['rate_3'];
                    $result = $tuijian['rate_2'] -( $match['home_score'] + $match['away_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= -0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$tuijian['rate_2']} -( {$match['home_score']} + {$match['away_score']})\r\n";
                    }
                }
                $is_count = 0;
                if($rate>0.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'is_count'=> $is_count,'count_time'=>time()]);
                if($rate > 0.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                        // 走地
                    }else{
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    }
                }
            }
        }elseif($tuijian['type'] == 4){
            // 半场让球
            if($tuijian['sub_type'] == 2){

                // 主队赢
                if($tuijian['guess_1'] == 1){
                    $rate = $tuijian['rate_1'];
                    // 最终主队进球数-最终客队进球数+竞猜时的盘口-（竞猜时的主队进球数-竞猜时的客队进球数）
                    $result = $match['home_score'] - $match['away_score'] + $tuijian['rate_2'] - ($tuijian['tuijian_home_score'] - $tuijian['tuijian_away_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= -0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['home_score']} - {$match['away_score']} + {$tuijian['rate_2']} - ({$tuijian['tuijian_home_score']} - {$tuijian['tuijian_away_score']});\r\n";
                    }
                    // 客队赢
                }elseif($tuijian['guess_1'] == 3){
                    $rate = $tuijian['rate_3'];
                    // 最终主队进球数-最终客队进球数+竞猜时的盘口-（竞猜时的主队进球数-竞猜时的客队进球数）
                    $result = $match['away_score'] - $match['home_score'] - $tuijian['rate_2'] - ($tuijian['tuijian_away_score'] - $tuijian['tuijian_home_score']);
                    if($result >= 0.5){
                        $status = 1;
                    }elseif($result == 0.25){
                        $status = 3;
                    }elseif($result == '0'){
                        $status = 5;
                    }elseif($result == -0.25){
                        $status = 4;
                    }elseif($result <= 0.5){
                        $status = 2;
                    }else{
                        $status = 9;
                    }
                    if($status == 9){
                        echo "match_id={$match['match_id']},tuijian_id={$tuijian['id']},$result = {$match['away_score']} - {$match['home_score']} - {$tuijian['rate_2']} - ({$tuijian['tuijian_away_score']} - {$tuijian['tuijian_home_score']})\r\n";
                    }
                }
                $is_count = 0;
                if($rate>0.5){
                    if($status == 1 || $status == 3 || $status == 2 || $status == 4){
                        $is_count = 1;
                    }
                }

                // 保存计算结果
                M('tuijian')->where(array('id'=>$tuijian['id']))->save(['is_win'=>$status, 'status'=>$status,'is_count'=>$is_count, 'count_time'=>time()]);

                if($rate > 0.5){
                    // 赛前
                    if($tuijian['tuijian_type'] == 1){
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET before_win_total=before_win_total+1, win_total=win_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");

                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET before_loss_total=before_loss_total+1, loss_total=loss_total+1,
                            before_match_rate=before_win_total/(before_win_total+before_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                        // 走地
                    }else{
                        if($status == 1 || $status == 3){
                            M()->execute("UPDATE t_users SET zoudi_win_total=zoudi_win_total+1, win_total=win_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }elseif($status == 2 || $status == 4){
                            M()->execute("UPDATE t_users SET zoudi_loss_total=zoudi_loss_total+1, loss_total=loss_total+1,
                            grounder_rate=zoudi_win_total/(zoudi_win_total+zoudi_loss_total),total_rate=win_total/(win_total+loss_total) WHERE id='{$tuijian['user_id']}'");
                        }
                    }
                }
            }
        }
    }
    M('match')->where(['id'=>$match['id']])->save(['is_full_count'=>1, 'full_count_time'=>time()]);
}
// 通过竞猜计算
