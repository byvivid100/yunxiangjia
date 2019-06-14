<?php
namespace app\api\model;

use think\Model;

class Propety extends Model
{


    public function insertPropety($input)
    {
        $map['user_id'] = $input['uuid'];
        $map['agent_id'] = $input['agent_id'];
        $map['form'] = $input['form'];
        $map['status'] = 0;
        $map['count'] = 0;
        $map['title'] = $input['title'];
        $map['price'] = $input['price'];
        $map['insert_time'] = time();
        return self::insertGetId($map);
    }


    public function searchPropety($input)
    {
        $cache = new Cache();
        $res = $cache->get('propety', $input['id']);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'propety', $input['id']);
        }
        return $res;
    }

    public function updatePropety($input)
    {
        $map['agent_id'] = $input['agent_id'];
        $map['title'] = $input['title'];
        $map['price'] = $input['price'];
        $map['update_time'] = time();
        return self::where('id', $input['id'])->update($map);
    }
}