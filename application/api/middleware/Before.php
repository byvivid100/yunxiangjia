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

        //获取uuid
    	$uuid = $cache->get('uuid', $request->param('openid'), true);
    	if (empty($uuid)) {
    		$uuid = db('user')->where(['openid' => $request->param('openid')])->value('uuid');
    		if (empty($uuid)) {
    			exit('uuid not found');
    		}
    		$cache->set($uuid, 'uuid', $request->param('openid'), true);
    	}

        if (!config('app_debug')) {
            //校验签名
        	if (empty($request->param('timestr')) || empty($request->param('sign'))) {
            	exit('sign not found');
            }
            //时效
            if ($request->param('timestr') < $_SERVER['REQUEST_TIME'] - 120) {
                exit('签名过期');
            }
            if ($cache->get('sign_' . $request->param('sign'), null, true)) {
                exit('签名重复，稍后再试');
            }
        	$sign = sign($request->param());
        	if ($sign !== $request->param('sign')) {
        		exit('签名错误');
        	}
            $cache->set($request->param('timestr'), 'sign_' . $request->param('sign'), null, true, 120);

            //获取access_token
            $access_token = $cache->get('access_token');
            if (empty($access_token)) {
                $res = $wechat->getAccessToken();
                if (empty($res['errcode'])) {
                    $cache->set($res['access_token'], 'access_token', null, ture, 7000);
                    $cache->set($res['expires_in'], 'expires_in', null, ture, 7000);
                    $access_token = $res['access_token'];
                }
                else {
                    exit('access_token错误');
                }
            }
            config('access_token', $access_token);
        }

        $request->uuid = $uuid;
        return $next($request);
    }
}