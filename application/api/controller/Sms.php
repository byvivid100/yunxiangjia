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
use app\common\Cache;

class Sms extends Controller
{
    //发送验证码
    public function sendSms()
    {
        $phone=input('get.phone');
        $type=input('get.type');
        if(!$phone){
            return false;
        }
        $valCode=random_int(10000,99999);
        $params=['code'=>$valCode,'phone'=>$phone];
        $code= model('Sms')->sendSms('249797',$params);
        $result=['code'=>$valCode,'phone'=>$phone,'ip'=>request()->ip(),'type'=>$type];
        if($code->result==0){
            $cache = new Cache();
            $cache->set($result,$phone,null,true,300);
            return Code::send(200,$valCode);
        }
        else
            return Code::send(500,null);

    }
    //验证验证码
    public function checkCode()
    {
        $params=input();
        $res= model('Sms')->checkCode($params);
        if($res){
            return Code::send(200,true);
        }
        return Code::send(500,false);
    }
}