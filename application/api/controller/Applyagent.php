<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 15:31
 * 申请成为经纪人
 */

namespace app\api\controller;


use app\common\Code;
use think\Controller;

class Applyagent extends Controller
{
    //申请
    public function apply()
    {
        $params=request()->params();
        $res=model('ApplyAgent')->apply($params);
        if($res)
        {
            Code::send(200,$res);
        }
        Code::send(500,null);
    }
}