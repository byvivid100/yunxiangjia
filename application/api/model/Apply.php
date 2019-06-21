<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class Apply extends Model
{


    public function insertApply($input)
    {
        $input['user_id'] = $input['uuid'];
        $input['status'] = 1;
        $input['status2'] = 0;
        $input['insert_time'] = $_SERVER['REQUEST_TIME'];
        self::allowField(true)->save($input);
        return $this->id;
    }


    public function searchApply($id)
    {
        $cache = new Cache();
        $res = $cache->get('apply', $id);
        if ($res === null) {
            $res = self::get($id);
            $cache->set($res, 'apply', $id);
        }
        return $res;
    }

    public function userlist($user_id)
    {
        $cache = new Cache();
        $res = $cache->get('apply_userlist', $user_id);
        if ($res === null) {
            $res = self::where(['user_id' => $user_id])->select();
            $cache->set($res, 'apply_userlist', $user_id);
        }
        return $res;
    }

    public function agentlist($agent_id)
    {
        $cache = new Cache();
        $res = $cache->get('apply_agentlist', $agent_id);
        if ($res === null) {
            $res = self::where(['agent_id' => $agent_id])->select();
            $cache->set($res, 'apply_agentlist', $agent_id);
        }
        return $res;
    }
}