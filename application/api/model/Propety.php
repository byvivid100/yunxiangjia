<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Propety extends Model
{


    public function insertPropety($input)
    {
        $input['agent_id'] = $input['uuid'];
        $input['status'] = 5;
        $input['count'] = 0;
        $input['insert_time'] = $_SERVER['REQUEST_TIME'];
        self::allowField(true)->save($input);
        return $this->id;
    }


    public function searchPropety($id)
    {
        $cache = new Cache();
        $res = $cache->get('propety', $id);
        if ($res === null) {
            $res = self::get($id);
            $cache->set($res, 'propety', $id);
        }
        return $res;
    }

    public function updatePropety($input)
    {
        $map['agent_id'] = $input['agent_id'];
        $map['title'] = $input['title'];
        $map['price'] = $input['price'];
        $map['update_time'] = $_SERVER['REQUEST_TIME'];
        return self::where('id', $input['id'])->update($map);
    }
}