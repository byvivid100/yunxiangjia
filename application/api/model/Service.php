<?php
namespace app\api\model;

use think\Model;

class Service extends Model
{


    public function insertXinfang($input)
    {
        $map['uuid'] = $uuid;
        // .....
        $map['insert_time'] = time();
        return self::insertGetId($map);
    }


    private function findXinfang($input)
    {
        $cache = new Cache();
        $res = $cache->get('service', $input['id']);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'service', $input['id']);
        }
        return $res;
    }
}