<?php
namespace app\common;
class Code
{
    public static function send($code = 200, $result = null)
    {
        if ($code == 999)
            $index[999]['message'] = (string)$result;
        $index[200]['message'] = 'success';
        $index[500]['message'] = 'error';

        $data = [
            'code' => $code,
            'message' => $index[$code]['message'],
            'result' => $result
        ];

        //清除redis缓存
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cache = new \app\common\Cache();
            $cache->flash(1);
            $cache->flash(2);
        }
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}