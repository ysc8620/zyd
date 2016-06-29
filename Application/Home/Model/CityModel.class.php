<?php
namespace Home\Model;
use Redis\MyRedis;


/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class CityModel extends BaseModel
{
    protected $tableName = 'city';

    /**
     * 获取公众号响起
     * @param int $city_id
     * @return bool|mixed
     */
    public function get_city($city_id=0){
        $key = 't_city_'.$city_id;
        $data = MyRedis::getProInstance()->new_get($key);
        if(!$data){
            $data = $this->find($city_id);
            if($data){
                MyRedis::getProInstance()->new_set($key, $data);
            }
        }

        return $data;
    }
}