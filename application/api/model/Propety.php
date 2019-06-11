<?php
namespace app\api\model;

use think\Model;

class Propety extends Model
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
        $res = $cache->get('propety', $input['id']);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'propety', $input['id']);
        }
        return $res;
    }
}