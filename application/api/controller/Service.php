<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Wechat;

class Service extends Controller
{

    public function normalBuyXinfang()
    {
        $input = input();
        $res = model('Service')->insertXinfang($input);
    }

}