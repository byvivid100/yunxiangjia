<?php

namespace app\api\middleware;

class After
{
    public function handle($request, \Closure $next)
    {
		$response = $next($request);

        $cache = new \app\common\Cache();
        $cache->flash(1);
        $cache->flash(2);

        return $response;
    }
}