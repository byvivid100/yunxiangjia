<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class User extends Controller
{
    public function login()
    {
        $input = input();
        $res = model('User')->check($input);
        Code::send(200, $res);
    }

    public function register()
    {
        $input = input();
        $res = model('User')->register($input);
        Code::send(200, $res);
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
            Code::send(500);
        }
        Code::send(200, $res);
    }
}