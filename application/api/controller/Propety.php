<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Wechat;

class Propety extends Controller
{

	//经纪人录入商品
    public function insertPropety()
    {
        $input = input();
        if (empty($input['id'])) exit;
        \Db::transaction(function(){
            $res = model('Propety')->insertPropety($input);
            $res2 = db('apply')->where(['id' => $input['id']])->update(['status2' => 5, 'ppid' =>$res, 'update_time' => $_SERVER['REQUEST_TIME']]);
        });
        Code::send(200, $res);
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