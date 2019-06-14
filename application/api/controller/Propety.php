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
        $res = model('Propety')->insertPropety($input);
    }

    //经纪人更新商品
    public function updatePropety()
    {
        $input = input();
        \Db::transaction(function(){
        	$res = model('Propety')->updatePropety($input);
        });
    }

}