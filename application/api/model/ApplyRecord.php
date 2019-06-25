<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class ApplyRecord extends Model
{


    public function insertApplyRecord($uuid, $apply, $type, $title)
    {
        $map['uuid'] = $uuid;
        $map['type'] = $type;
        $map['title'] = $title;
        $map['apply_id'] = $apply['id'];
        $map['status'] = $apply['status'];
        $map['status2'] = $apply['status2'];
        $map['request_uri'] = $_SERVER['REQUEST_URI'];
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }


    public function recordlist($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('applyrecord_recordlist', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->select();
            $cache->set($res, 'applyrecord_recordlist', $uuid);
        }
        return $res;
    }
}