<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Wechat;

class Propety extends Controller
{

    public function insertXinfang()
    {
        $input = input();
        $res = model('Propety')->insertXinfang($input);
    }

}