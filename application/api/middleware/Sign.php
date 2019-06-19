<?php

namespace app\api\middleware;

class Sign
{
    public function handle($request, \Closure $next)
    {
        $cache = new \app\common\Cache();
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

        }

        return $next($request);
    }
}