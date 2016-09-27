<?php
/**
 * 获取赛程信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=auto update match no=\r\n";

$res = M()->execute("update `t_match` m, t_jingcai j set j.match_id=m.match_id WHERE j.match_id = 0 and m.time = j.match_time and (m.home_name=j.home_name or m.home_name=j.away_name or m.away_name=j.away_name or m.away_name=j.home_name)");
var_dump($res);