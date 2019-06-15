<?php

namespace app\api\middleware;

class Before
{
    public function handle($request, \Closure $next)
    {
//        if (empty($request->param('openid'))) {
//        	exit('error');
//        }
    	$cache = new \app\common\Cache();
//    	$uuid = $cache->get('uuid', $request->param('openid'));
//    	if (empty($uuid)) {
//    		$uuid = db('user')->where(['openid' => $request->param('openid')])->value('uuid');
//    		if (empty($uuid)) {
//    			exit('uuid not found');
//    		}
//    		$cache->set($uuid, 'uuid', $request->param('openid'));
//    	}
//    	$request->uuid = $uuid;

        if (config('app_debug')) {
            return $next($request);
        }

    	if (empty($request->param('timestr')) || empty($request->param('sign'))) {
        	exit('error');
        }
    	$sign = sign($request->param('openid'), $request->param('timestr'));
    	if ($sign !== $request->param('sign')) {
    		exit('签名错误');
    	}
        return $next($request);
    }
}