<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class OrderRecord extends Model
{


    public function insertOrderRecord($uuid, $order, $type, $title)
    {
        $map['uuid'] = $uuid;
        $map['type'] = $type;
        $map['title'] = $title;
        $map['order_id'] = $order['id'];
        $map['status'] = $order['status'];
        $map['status2'] = $order['status2'];
        $map['request_uri'] = $_SERVER['REQUEST_URI'];
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function recordlist($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('orderrecord_recordlist', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->select();
            $cache->set($res, 'orderrecord_recordlist', $uuid);
        }
        return $res;
    }
}