<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserPayment extends Model
{

    public function insertPayment($input)
    {
        $map['uuid'] = $input['uuid'];
        $map['order_no'] = $input['order_no'];
        $map['apply_id'] = $input['apply_id'];
        $map['title'] = $input['title'];
        $map['money'] = $input['money'];
        $map['type'] = $input['type'];
        $map['status'] = 1;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }

    public function searchPayment($order_no)
    {
        $cache = new Cache();
        $res = $cache->get('userpayment', $order_no);
        if ($res === null) {
            $res = self::where('order_no' => $order_no)->find();
            $cache->set($res, 'userpayment', $order_no);
        }
        return $res;
    }

    public function paymentlist($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('userpayment_paymentlist', $uuid);
        if ($res === null) {
            $res = self::where('uuid' => $uuid)->select();
            $cache->set($res, 'userpayment_paymentlist', $uuid);
        }
        return $res;
    }
}