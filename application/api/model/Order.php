<?php
namespace app\api\model;

use think\Model;

class Order extends Model
{

    public function insertOrder($apply, $propety)
    {
        $map['apply_id'] = $apply['id'];
        // $map['target_apply_id'] = $input['target_apply_id'];
        $map['ppid'] = $propety['id'];
        $map['title'] = $propety['title'];
        $map['user_id'] = $apply['user_id'];
        $map['agent_id'] = $apply['agent_id'];
        $map['target_user_id'] = $propety['user_id'];
        $map['target_agent_id'] = $propety['agent_id'];
        $map['form'] = $propety['form'];
        $map['type'] = $apply['type'];
        // $map['type2'] = $propety['type2'];
        $map['status'] = 1;
        $map['status2'] = 0;
        $map['insert_time'] = time();
        return self::insertGetId($map);
    }

    public function searchOrder($id)
    {
        $cache = new Cache();
        $res = $cache->get('order', $id);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'order', $id);
        }
        return $res;
    }

    public function buylist($agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('order_buylist', $agent_id);
        if ($res === null) {
            $res = self::where('agent_id' => $agent_id)->select();
            $cache->set($res, 'order_buylist', $agent_id);
        }
        return $res;
    }

    public function selllist($target_agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('order_selllist', $target_agent_id);
        if ($res === null) {
            $res = self::where('target_agent_id' => $target_agent_id)->select();
            $cache->set($res, 'order_selllist', $target_agent_id);
        }
        return $res;
    }
}