<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Order extends Model
{

    public function insertOrder($apply, $propety)
    {
        $map['apply_id'] = $apply['id'];
        $target_apply = model('Apply')->where(['ppid' => $propety['id'], 'agent_id' => $propety['agent_id']])->where('type', 'in', [11, 12, 13])->find();
        $map['target_apply_id'] = $target_apply['id'];
        // $map['target_apply_id'] = $input['target_apply_id'];
        $map['ppid'] = $propety['id'];
        $map['title'] = $propety['title'];
        $map['user_id'] = $apply['user_id'];
        $map['agent_id'] = $apply['agent_id'];
        $map['target_user_id'] = $propety['user_id'];
        $map['target_agent_id'] = $propety['agent_id'];
        $map['form'] = $propety['form'];
        $map['type'] = $apply['type'];
        $map['type2'] = $target_apply['type'];
        $map['status'] = 1;
        $map['status2'] = 0;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }

    public function searchOrder($id)
    {
        $cache = new Cache();
        $res = $cache->get('order', $id);
        if ($res === null) {
            $res = self::get($id);
            $cache->set($res, 'order', $id);
        }
        return $res;
    }

    public function buylist($agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('order_buylist', $agent_id);
        if ($res === null) {
            $res = self::where(['agent_id' => $agent_id])->select();
            $cache->set($res, 'order_buylist', $agent_id);
        }
        return $res;
    }

    public function selllist($target_agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('order_selllist', $target_agent_id);
        if ($res === null) {
            $res = self::where(['target_agent_id' => $target_agent_id])->select();
            $cache->set($res, 'order_selllist', $target_agent_id);
        }
        return $res;
    }
}