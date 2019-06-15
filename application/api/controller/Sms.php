<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/13
 * Time: 10:38
 */

namespace app\api\controller;


use app\common\Code;
use think\Controller;

class Sms extends Controller
{
    public function sendSms()
    {
        $phone=input('get.phone');
        $type=input('get.type');
        if(!$phone){
            return false;
        }
        $MaxCount=model('Config')->getConfig('sms_num');
        $valCode=random_int(10000,99999);
        $params=['code'=>$valCode,'phone'=>$phone];
        $code= model('Sms')->sendSms('249797',$params);
        $cache = set($code, 'sms_code'.$phone);
        \app\api\model\Sms::create(['code'=>'12345','phone'=>$phone,'ip'=>request()->ip(),'type'=>$type]);
        return Code::send(200,$code);
    }
}