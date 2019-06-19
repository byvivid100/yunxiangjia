<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserAccountRecord extends Model
{


    public function insertAccountRecord($input)
    {
        $map['uuid'] = $input['uuid'];
        $map['order_no'] = $input['order_no'];
        $map['title'] = $input['title'];
        $map['change'] = $input['change'];
        $map['amount'] = $input['amount'];
        $map['tail'] = $input['tail'];
        $map['gift'] = $input['gift'];
        $map['frozen'] = $input['frozen'];
        $map['payment'] = $input['payment'];
        $map['transfers'] = $input['transfers'];
        $map['type'] = $input['type'];
        $map['status'] = 9;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function recordlist($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('useraccountrecord_recordlist', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->select();
            $cache->set($res, 'useraccountrecord_recordlist', $uuid);
        }
        return $res;
    }
}