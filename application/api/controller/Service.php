<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Wechat;

class Service extends Controller
{

    //会员申请服务
    public function insertService()
    {
        $input = input();

        \Db::transaction(function(){
        	$input['svid'] = model('Service')->insertService($input);
        	$applyid = model('Apply')->insertApply($input);
        });
        Code::send(200, $res);
    }

    //会员更新服务
    public function updateService()
    {
        $input = input();
        \Db::transaction(function(){
        	$res = model('Service')->updateService($input);
        });
        Code::send(200, $res);
    }
}