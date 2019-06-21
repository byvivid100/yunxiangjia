<?php
namespace app\api\model;

use think\Model;
use app\common\Cache;

class User extends Model
{

    public function check($input)
    {
        $password = self::encryptPassword($input['uuid'], $input['password']);
        return self::where(['phone' => $input['phone'], 'password' => $password])->find();
    }

    public function register($input)
    {
        $map['phone'] = $input['phone'];
        $map['password'] = self::encryptPassword($input['uuid'], $input['password']);
        $map['update_time'] = $_SERVER['REQUEST_TIME'];
        return self::where('uuid', $input['uuid'])->update($map);
    }

    public function insertUser($uuid, $openid)
    {
        $map['uuid'] = $uuid;
        $map['openid'] = $openid;
        $map['type'] = 1;
        $map['status'] = 0;
        $map['insert_time'] = $_SERVER['REQUEST_TIME'];
        return self::save($map);
    }


    public function searchUser($uuid)
    {
        $cache = new Cache();
        $res = $cache->get('user', $uuid);
        if ($res === null) {
            $res = self::where(['uuid' => $uuid])->find();
            $cache->set($res, 'user', $uuid);
        }
        return $res;
    }

    public function updateUser($input)
    {
        $map['nickname'] = $input['nickname'];
        $map['gender'] = $input['gender'];
        $map['language'] = $input['language'];
        $map['city'] = $input['city'];
        $map['province'] = $input['province'];
        $map['country'] = $input['country'];
        $map['avatarurl'] = $input['avatarurl'];
        $map['update_time'] = $_SERVER['REQUEST_TIME'];
        return self::where('uuid', $input['uuid'])->update($map);
    }


    private function encryptPassword($uuid, $password)
    {
        $string = $password . substr($uuid, 2, 8);
        return md5($string);
    }
}