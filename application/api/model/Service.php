<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Service extends Model
{


    public function insertService($input)
    {
        $map['user_id'] = $input['uuid'];
        $map['agent_id'] = $input['agent_id'];
        $map['form'] = $input['form'];
        $map['status'] = 0;
        $map['title'] = $input['title'];
        $map['price'] = $input['price'];
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function searchService($input)
    {
        $cache = new Cache();
        $res = $cache->get('service', $input['id']);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'service', $input['id']);
        }
        return $res;
    }

    public function updateService($input)
    {
        $map['agent_id'] = $input['agent_id'];
        $map['title'] = $input['title'];
        $map['price'] = $input['price'];
        $map['update_time'] = $_SERVER['REQUEST_TIME'];
        return self::where('id', $input['id'])->update($map);
    }
}