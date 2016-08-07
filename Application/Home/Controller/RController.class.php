<?php
namespace Home\Controller;

class RController extends BaseController {
    /**
     *
     */
    public function test(){
        $json = '{"status":100,"msg":"","time":1470539240,"data":{"league_list":[{"league_id":"7","color":"#6969e0","cn_short":"丹麦超","cn_name":"丹麦超级联赛","type":"1","sum_round":"26","curr_round":"4","curr_match_season":"2016-2017","country_name":"丹麦","is_hot":"0","total_match":"1"}],"list":[{"league_id":"772","league_name":"乌兹超","league_ico":"http://api2.zydzuqiu.com/Public/static/noimg.png","list":[{"match_id":"1230827","match_time":"2016-09-1618:00:00","league_id":"772","league_name":"乌兹超","kind":"","level":"","state":"1","home_id":"9918","home_name":"比克波德","home_score":"0","away_id":"4032","away_name":"塔什干棉农","away_score":"0","home_red":"0","away_red":"0","home_yellow":"0","away_yellow":"0","match_round":"21","address":"","weather_ico":"","weather":"","temperature":"","is_neutral":"False","technic":{"id0":{"home":0,"away":0},"id1":{"home":0,"away":0},"id2":{"home":0,"away":0},"id3":{"home":0,"away":0},"id4":{"home":0,"away":0},"id5":{"home":0,"away":0},"id6":{"home":0,"away":0},"id7":{"home":0,"away":0},"id8":{"home":0,"away":0},"id9":{"home":0,"away":0},"id10":{"home":0,"away":0},"id11":{"home":0,"away":0},"id12":{"home":0,"away":0},"id13":{"home":0,"away":0},"id14":{"home":0,"away":0},"id15":{"home":0,"away":0},"id16":{"home":0,"away":0},"id17":{"home":0,"away":0},"id18":{"home":0,"away":0},"id19":{"home":0,"away":0},"id20":{"home":0,"away":0},"id21":{"home":0,"away":0},"id22":{"home":0,"away":0},"id23":{"home":0,"away":0},"id24":{"home":0,"away":0},"id25":{"home":0,"away":0},"id26":{"home":0,"away":0},"id27":{"home":0,"away":0},"id28":{"home":0,"away":0},"id29":{"home":0,"away":0},"id30":{"home":0,"away":0},"id31":{"home":0,"away":0},"id32":{"home":0,"away":0},"id33":{"home":0,"away":0},"id34":{"home":0,"away":0},"id35":{"home":0,"away":0},"id36":{"home":0,"away":0},"id37":{"home":0,"away":0},"id38":{"home":0,"away":0},"id39":{"home":0,"away":0},"id40":{"home":0,"away":0}},"begin_home_rate":0,"begin_draw_rate":0,"begin_away_rate":0,"change_home_rate":0,"change_draw_rate":0,"change_away_rate":0,"oupei":{"begin_home_rate":0,"begin_draw_rate":0,"begin_away_rate":0,"change_home_rate":0,"change_draw_rate":0,"change_away_rate":0},"yapei":{"begin_rate":0,"begin_home_rate":0,"begin_away_rate":0,"change_rate":0,"change_home_rate":0,"change_away_rate":0},"daxiaoqiu":{"begin_rate":0,"begin_big_rate":0,"begin_small_rate":0,"change_rate":0,"change_big_rate":0,"change_small_rate":0},"jingcai":{"home_rate":0,"away_rate":0,"draw_rate":0,"home_win_rate":0,"away_win_rate":0,"draw_win_rate":0},"events":[],"match_name":"","match_time2":"","is_collect":0,"total_collect":0}]}],"total":"105","page":1,"total_page":11,"type":1,"limit":10,"league_ids":""}}';
        header('Content-Type:application/json; charset=utf-8');
        echo $json;
        die();
        $this->ajaxReturn($json);
    }

    public function test1(){
        $json = '{"status":100,"msg":"","time":1469319671,"data":{"league_list":[],"list":[],"total":"0","page":1,"total_page": 0,"type": 1,"limit": 10,"league_ids": ""}';
        header('Content-Type:application/json; charset=utf-8');
        echo $json;
        die();
        // $this->ajaxReturn($json);
    }

    public function test2(){
        $json = '{"status":100,"msg":"","time":1469319671,"data":{}';
        header('Content-Type:application/json; charset=utf-8');
        echo $json;
        die();
        // $this->ajaxReturn($json);
    }
}