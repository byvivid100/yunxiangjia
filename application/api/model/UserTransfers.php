<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserTransfers extends Model
{

    public function insertTransfers($input)
    {
        $map['uuid'] = $input['uuid'];
        $map['order_no'] = $input['order_no'];
        $map['title'] = $input['title'];
        $map['money'] = $input['money'];
        $map['type'] = $input['type'];
        $map['status'] = 1;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }

    public function searchTransfers($order_no)
    {
        $cache = new Cache();
        $res = $cache->get('usertransfers', $order_no);
        if ($res === null) {
            $res = self::where(['order_no' => $order_no])->find();
            $cache->set($res, 'usertransfers', $order_no);
        }
        return $res;
    }

    public function transferslist($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('usertransfers_transferslist', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->select();
            $cache->set($res, 'usertransfers_transferslist', $uuid);
        }
        return $res;
    }
}