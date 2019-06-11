<?php
namespace app\common;

class Wechat
{
    private $appid;
    private $secret;
    private $access_token;

    function __construct()
    {
        $this->appid = config('api.appid');
    	$this->secret = config('api.secret');
        $this->mch_id = config('api.mch_id');
        $this->key = config('api.key');
        $cache = new Cache();
        $this->access_token = $cache->get('access_token');
    }

    public function getAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $res = json_decode(curlRequest($url), true);
        if (empty($res['errcode'])) {
            $cache = new Cache();
            $cache->set($res['access_token'], 'access_token');
            $cache->set($res['expires_in'], 'expires_in');
            return $res['access_token'];
        }
        else {
            return $res['errmsg'];
        }
    }

    public function code2Session($js_code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session&appid=" . $this->appid . "&secret=" . $this->secret . "&js_code=" . $js_code . "&grant_type=authorization_code";
        return json_decode(curlRequest($url), true);
    }

    public function getUnlimited($page, $scene)
    {
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $this->access_token;
        $post['scene'] = $scene;
        $post['page'] = $page;
        $res = json_decode(curlRequest($url, $post), true);
        if (empty($res['errcode'])) {
            //保存图片到磁盘
            // file_put_contents($path, $res['buffer']);
        }
        else {
            return $res['errmsg'];
        }
    }

    public function unifiedorder($out_trade_no, $total_fee)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = "https://yxj-dev.bld1907.com";
        $post['appid'] = $this->appid;
        $post['mch_id'] = $this->mch_id;
        $post['nonce_str'] = md5(uniqid(mt_rand(), true));
        $post['body'] = '云享家支付';
        $post['out_trade_no'] = $out_trade_no;
        $post['total_fee'] = $total_fee;
        // $post['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $post['spbill_create_ip'] = get_client_ip();
        $post['notify_url'] = $notify_url;
        $post['trade_type'] = 'JSAPI';
        $post['sign'] = self::getSign($post);
        print_r($post);exit;
        $res = json_decode(curlRequest($url, $post), true);
        return $res;
        if (empty($res['errcode'])) {
            return $res;
        }
        else {
            return $res['errmsg'];
        }
    }

    private function getSign($arr)
    {
        $arr = array_filter($arr);
        if (isset($arr['sign'])) {
            unset($arr['sign']);
        }
        ksort($arr);
        $str = http_build_query($arr) . "&key=" . $this->key;
        $str = urldecode($str);
        return strtoupper(md5($str));
    }
}