<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserAccount extends Model
{

    public function insertAccount($input)
    {
        $map['uuid'] = $input['uuid'];
        $map['amount'] = 0;
        $map['tail'] = 0;
        $map['gift'] = 0;
        $map['frozen'] = 0;
        $map['payment'] = 0;
        $map['transfers'] = 0;
        $map['type'] = 1;
        $map['status'] = 5;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function searchAccount($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('useraccount', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->find();
            $cache->set($res, 'useraccount', $uuid);
        }
        return $res;
    }

    public function updateAccount($input)
    {
        $map['amount'] = $input['amount'];
        $map['tail'] = $input['tail'];
        $map['gift'] = $input['gift'];
        $map['frozen'] = $input['frozen'];
        $map['payment'] = $input['charge'];
        $map['transfers'] = $input['transfers'];
        $map['update_time'] = $_SERVER['REQUEST_TIME'];
        return self::where('id', $input['id'])->update($map);
    }
}