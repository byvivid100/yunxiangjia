<?php

namespace app\api\middleware;

class Before
{
    public function handle($request, \Closure $next)
    {
        if (empty($request->param('openid'))) {
        	exit('openid not found');
        }
    	$cache = new \app\common\Cache();
        $wechat = new \app\common\Wechat();

        //获取access_token
        if (!config('app_debug')) {
            $access_token = $cache->get('access_token', null, true);
            if (empty($access_token)) {
                $res = $wechat->getAccessToken();
                if (empty($res['errcode'])) {
                    $cache->set($res['access_token'], 'access_token', null, true, 7000);
                    $cache->set($res['expires_in'], 'expires_in', null, true, 7000);
                    $access_token = $res['access_token'];
                }
                else {
                    exit('access_token错误');
                }
            }
            config('api.access_token', $access_token);
        }

        //获取uuid
    	$uuid = $cache->get('uuid', $request->param('openid'), true);
    	if (empty($uuid)) {
    		$uuid = db('user')->where(['openid' => $request->param('openid')])->value('uuid');
    		if (empty($uuid)) {
    			exit('uuid not found');
    		}
    		$cache->set($uuid, 'uuid', $request->param('openid'), true);
    	}

        $request->uuid = $uuid;
        return $next($request);
    }
}