<<<<<<< HEAD
<?php
namespace app\common;
class Code
{
    public function send($code = 200, $result = null)
    {
        $index[999]['message'] = (string)$result;
        $index[200]['message'] = 'success';
        $index[500]['message'] = 'error';

        $data = [
            'code' => $code,
            'message' => $index[$code]['message'],
            'result' => $result
        ];
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}