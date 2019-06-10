<?php
namespace app\common;

class Cache
{
    private $redis;

    function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new \Redis();
    	$this->redis->pconnect($host, $port);
    }

    public function client()
    {
    	return $this->redis;
    }

    //获取键值
    public function get($key, $field = null)
    {
    	if (!empty($field)) {
    		$this->redis->select(2);
    		if (!$this->redis->hExists($key, $field)) {
    			return null;
    		}
    		return unserialize($this->redis->hGet($key, $field));
    	}
    	else {
    		$this->redis->select(1);
    		if (!$this->redis->exists($key)) {
    			return null;
    		}
    		return unserialize($this->redis->get($key));
    	}
    } 

    //设置键值,value为非对象
    public function set($value, $key, $field = null)
    {
    	if (!empty($field)) {
    		$this->redis->select(2);
    		$this->redis->hSet($key, $field, serialize($value));
    		$this->redis->expireAt($key, time() + 7000);
    	}
    	else {
    		$this->redis->select(1);
    		$this->redis->set($key, serialize($value));
    		$this->redis->expireAt($key, time() + 7000);
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
    	if (!empty($id)) {
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