<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/13
 * Time: 10:37
 */

namespace app\api\model;


use app\common\Cache;
use think\Model;
use Qcloud\Sms\SmsSingleSender;

class Sms extends Model
{
    //发送短信
    public function sendSms($templateId,$params)
    {
        $appid=config('api.sms_appid');
        $appkey=config('api.sms_appkey');
        $sign=config('api.sms_smsSign');
        $ssender = new SmsSingleSender($appid, $appkey);
        $result = $ssender->sendWithParam("86", $params['phone'], $templateId,
            [$params['code']], $sign, "", "");
        return json_decode($result);
    }
    //验证短信
    public function checkCode($params)
    {
        $cache = new Cache();
        $result=$cache->get($params['phone'],null,true);
        if($result)
        {
            if($result['code']==$params['code']&&$result['type']==$params['type'])
            return true;
            else return false;
        }
        return false;
    }
}