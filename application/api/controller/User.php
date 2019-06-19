<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class User extends Controller
{
    public function loadByCode()
    {
        $input = input();
        if (empty($input['js_code'])) {
            return 'error code not found';
        }
        $wechat = new Wechat();
        $res = $wechat->code2Session($input['js_code']);
        if (!empty($res['errcode'])) {
            return $res['errmsg'];
        }

        \Db::transaction(function(){
            $uuid = makeUuid();
            $user = model('User')->insertUser($uuid, $res['openid']);
            $user_record = model('UserRecord')->insertUserRecord($uuid, 'Mini Program');
            $user_account = model('UserAccount')->insertAccount($uuid);
        });
        if (empty($user)) {
            return 'error';
        }
        $cache = new Cache();
        $cache->set($res['session_key'], 'session_key', $res['openid'], true);
        $cache->set($uuid, 'uuid', $res['openid'], true);
        return $res['openid'];
    }

    public function login()
    {
        $input = input();
        $res = model('User')->check($input);
    }

    public function register()
    {
        $input = input();
        $res = model('User')->register($input);
    }

    public function findUser()
    {
        $input = input();
        $res = model('User')->searchUser($input['uuid']);
        Code::send(200, $res);
    }

    public function updateUser()
    {
        $input = input();
        \Db::transaction(function(){
            $res = model('User')->updateUser($input);
        });
        if (!$res) {
            return 'error';
        }
    }
}