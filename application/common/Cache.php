<?php
namespace app\common;

class Cache
{
    private $redis;

    function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new \Redis();
    	$this->redis->connect($host, $port);
    }

    //返回连接对象
    public function client()
    {
    	return $this->redis;
    }

    //获取键值isBase=true为不清缓存
    public function get($key, $field = null, $isBase = false)
    {
        $db = $isBase ? 2 : 0;
    	if (strlen($field)) {
    		$this->redis->select($db + 2);
    		if (!$this->redis->hExists($key, $field)) {
    			return null;
    		}
    		return unserialize($this->redis->hGet($key, $field));
    	}
    	else {
    		$this->redis->select($db + 1);
    		if (!$this->redis->exists($key)) {
    			return null;
    		}
    		return unserialize($this->redis->get($key));
    	}
    } 

    //设置键值,value为非对象
    public function set($value, $key, $field = null, $isBase = false, $expireTime = 7000)
    {
        $db = $isBase ? 2 : 0;
    	if (strlen($field)) {
    		$this->redis->select($db + 2);
    		$this->redis->hSet($key, $field, serialize($value));
    		$this->redis->expire($key, $expireTime);
    	}
    	else {
    		$this->redis->select($db + 1);
    		$this->redis->set($key, serialize($value));
    		$this->redis->expire($key, $expireTime);
    	}
    } 

    //锁
    public function lock($key, $expireTime = 15)
    {
        $this->redis->select(5);
        while (!$this->redis->setnx($key, 1)) {
            sleep(1);
        }
        $this->redis->expire($key, $expireTime);
    }

    //解锁
    public function unlock($key)
    {
        $this->redis->select(5);
        if ($this->redis->exists($key)) {
            $this->redis->del($key);
        }
    }

    //数据持久化
    public function save()
    {
    	$this->redis->save();
    } 

    //清空缓存
    public function flash($id = null)
    {
    	if (strlen($id)) {
    		$this->redis->select($id);
    		$this->redis->flushDB();
    	}
    	else {
    		$this->redis->flushAll();
    	}
    } 

	function __destruct()
	{
		$this->redis->close();
	}
}