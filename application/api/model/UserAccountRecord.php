<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserAccountRecord extends Model
{


    public function insertAccountRecord($input)
    {
        unset($input['id']);
        unset($input['update_time']);

        $input['status'] = 9;
        $input['insert_time'] = $_SERVER['REQUEST_TIME'];
        self::allowField(true)->save($input);
        return $this->id;
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