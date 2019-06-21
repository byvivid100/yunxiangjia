<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Service extends Model
{


    public function insertService($input)
    {
        $input['user_id'] = $input['uuid'];
        $input['status'] = 0;
        $input['insert_time'] = $_SERVER['REQUEST_TIME'];
        self::allowField(true)->save($input);
        return $this->id;
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