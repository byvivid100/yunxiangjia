<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Account extends Controller
{

    //账户详情
    public function detail()
    {
        $input = input();
        $account = model('UserAccount')->searchAccount($input['uuid']);
        Code::send(200, $account);
    }

    //账户流水
    public function recordlist()
    {
        $input = input();
        $record = model('UserAccountRecord')->recordlist($input['uuid']);
        Code::send(200, $record);
    }

}