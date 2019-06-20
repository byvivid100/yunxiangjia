<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Index extends Controller
{
    public function initByCode()
    {
        $input = input();
        if (empty($input['js_code'])) {
            return 'error code not found';
        }
        $wechat = new Wechat();
        $res = $wechat->code2Session($input['js_code']);
        if (!empty($res['errcode'])) {
            Code::send(500, $res);
            // $res['openid'] = 'oL-pK5AaBSUrfzX58laKEV75pJb4';
        }
        
        \Db::transaction(function() use($res) {
            $cache = new Cache();
            //锁
            $cache->lock('index_initbycode_' . $res['openid']);
            $uuid = $cache->get('uuid', $res['openid'], true);
            if ($uuid === null) {
                $openid = db('user')->where(['openid' => $res['openid']])->value('openid');
                if (empty($openid)) {
                    //账户不存在
                    $uuid = makeUuid();
                    $user = model('User')->insertUser($uuid, $res['openid']);
                    $user_record = model('UserRecord')->insertUserRecord($uuid, 'Mini Program');
                    $user_account = model('UserAccount')->insertAccount($uuid);
                    $cache->set($res['session_key'], 'session_key', $res['openid'], true);
                    $cache->set($uuid, 'uuid', $res['openid'], true);
                }
            }
            $cache->unlock('index_initbycode_' . $res['openid']);
            Code::send(200, $res['openid']);
        });
        Code::send(999, 'sql error');
    }

    public function resetAccessToken($sign)
    {
        if (config('app_debug'))
            return 'debug';
        if (empty($sign) || $sign <> '1fab443823b8d353d09d4bc883babc17')
            return 'input error';
        
        $cache = new Cache();
        if ($cache->get('lock_rat', '', 1) === '1')
            return 'try later';
        $cache->set('1', 'lock_rat', '', 1, 60);

        $wechat = new Wechat();
        $res = $wechat->getAccessToken();
        if (empty($res['errcode'])) {
            $cache->set($res['access_token'], 'access_token', null, true, 7000);
            $cache->set($res['expires_in'], 'expires_in', null, true, 7000);
            $access_token = $res['access_token'];
        }
        else {
            Code::send(500, $res);
        }
        Code::send(200, $res);
    }
}