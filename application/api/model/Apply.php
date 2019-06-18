<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Apply extends Model
{


    public function insertApply($input)
    {
        $map['svid'] = $input['svid'];
        $map['title'] = $input['title'];
        $map['ppid'] = $input['ppid'];
        $map['user_id'] = $input['uuid'];
        $map['agent_id'] = $input['agent_id'];
        $map['form'] = $input['form'];
        $map['type'] = $input['type'];
        $map['type2'] = $input['type2'];
        $map['status'] = 1;
        $map['status2'] = 0;
        $map['money'] = $input['money'];
        $map['money_adv'] = $input['money_adv'];
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function searchApply($id)
    {
        $cache = new Cache();
        $res = $cache->get('apply', $id);
        if ($res === null) {
            $res = self::get($input['id']);
            $cache->set($res, 'apply', $id);
        }
        return $res;
    }

    public function userlist($user_id)
    {
        $cache = new Cache();
        $res = $cache->get('apply_userlist', $user_id);
        if ($res === null) {
            $res = self::where('user_id' => $user_id)->select();
            $cache->set($res, 'apply_userlist', $user_id);
        }
        return $res;
    }

    public function agentlist($agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('apply_agentlist', $agent_id);
        if ($res === null) {
            $res = self::where('agent_id' => $agent_id)->select();
            $cache->set($res, 'apply_agentlist', $agent_id);
        }
        return $res;
    }
}