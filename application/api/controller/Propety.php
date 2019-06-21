<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Propety extends Controller
{

	//经纪人录入商品
    public function insertPropety()
    {
        $input = input();
        if (empty($input['apply_id']))
            Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['apply_id']);
        if ($apply['status2'] <> 2) 
            Code::send(500);
        \Db::transaction(function() use($input) {
            $ppid = model('Propety')->insertPropety($input);
            $res = \Db::name('apply')->where(['id' => $input['apply_id']])->update(['status2' => 5, 'ppid' => $ppid, 'update_time' => $_SERVER['REQUEST_TIME']]);

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //经纪人更新商品
    public function updatePropety()
    {
        $input = input();
        \Db::transaction(function(){
        	$res = model('Propety')->updatePropety($input);
        });
        Code::send(200, $res);
    }

}