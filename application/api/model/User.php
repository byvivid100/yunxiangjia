<?php
namespace app\api\model;

use think\Model;

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
        $map['update_time'] = time();
        return self::where('uuid', $input['uuid'])->update($map);
    }

    public function insertUser($uuid, $openid)
    {
        $map['uuid'] = $uuid;
        $map['openid'] = $openid;
        $map['insert_time'] = time();
        return self::insertGetId($map);
    }


    public function findUser($input)
    {
        $cache = new Cache();
        $res = $cache->get('user', $input['uuid']);
        if ($res === null) {
            $res = self::get($input['uuid']);
            $cache->set($res, 'user', $input['uuid']);
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
        $map['update_time'] = time();
        return self::where('uuid', $input['uuid'])->update($map);
    }


    private function encryptPassword($uuid, $password)
    {
        $string = $password . substr($uuid, 2, 8);
        return md5($string);
    }
}