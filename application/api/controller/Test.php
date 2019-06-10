<?php
namespace app\api\controller;
use Predis;
use Firebase\JWT\JWT;
use EasyWeChat\Factory;
use app\common\Cache;

class Test
{

	public function redis()
    {
        $cache = new Cache();
    	// $redis->set('mjf', 'good4');
    	// $res = $redis->get('mjf');
    	// $res = $cache->client();
    	// print_r($res);
    	$cache->set('wiwiwi2ss2', 'access_token', 'access_token');
    	$res2 = $cache->get('access_token', 'access_token');
    	print_r($res2);
    	// $redis = connRedis();
    	// $cache->redis->pconnect('172.0.0.1', 6379);

		// $redis->close();
    }

  //   public function redis()
  //   {
  //       $client = new Predis\Client();
  //       try {
	 //    	$client->connect();
		// } catch (Predis\Connection\ConnectionException $exception) {
		//     echo('redis error');
		// }
		// print_r($client->info());
  //   }

    public function jwt()
    {
        $key = "example_key";
		$token = array(
		    "iss" => "yxj",
		    "aud" => "majiafeng",
		    "nbf" => (time() - 10),
		    "iat" => time(),
		    "exp" => (time() + 30),
		);

		$jwt = JWT::encode($token, $key);
		// print_r($jwt);

		$decoded = JWT::decode($jwt, $key, array('HS256'));
		$decoded_array = (array) $decoded;
		// print_r($decoded_array);

		$key = time();
		$sign = hash_hmac('SHA1', $jwt, $key);
		// $sign = JWT::sign($jwt, $key, 'HS256');
		echo $sign;
    }

    public function mini()
    {
        $config = [
		    'app_id'    => 'wx3cf0f39249eb0exxx',
		    'secret'    => 'f1c242f4f28f735d4687abb469072xxx',
		];

		$app = Factory::miniProgram($config);
		$auth = $app->auth;
		print_r($auth);

    }
}
