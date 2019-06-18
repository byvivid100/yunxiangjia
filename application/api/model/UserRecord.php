<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class UserRecord extends Model
{

    public function insertUserRecord($uuid, $platform, $type = 0)
    {
        $map['uuid'] = $uuid;
        $map['ip'] = get_client_ip();
        $map['platform'] = $platform;
        $map['type'] = $type;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::insertGetId($map);
    }

}