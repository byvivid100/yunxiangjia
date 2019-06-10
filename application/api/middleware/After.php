<?php

namespace app\api\middleware;

class After
{
    public function handle($request, \Closure $next)
    {
		$response = $next($request);

        $cache = new \app\common\Cache();
        $cache->flash();

        return $response;
    }
}