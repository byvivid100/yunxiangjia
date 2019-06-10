<?php
namespace app\common;

class Wechat
{
    private $appid;
    private $secret;
    private $access_token;

    function __construct()
    {
        $this->appid = config('appid');
    	$this->secret = config('secret');
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
}