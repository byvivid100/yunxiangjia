<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Service extends Controller
{

    //会员申请服务
    public function insertService()
    {
        $input = input();
        if (empty($input['form']) || empty($input['type']) || empty($input['type2']))
            Code::send(999, '参数错误');

        \Db::transaction(function() use($input) {
        	$input['svid'] = model('Service')->insertService($input);
        	$res = model('Apply')->insertApply($input);
            
            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
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