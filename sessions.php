<?php

require "redis.php";

class redis_sessions
{
	private static $redis = NULL;
	private static $session_name = NULL;
	public static $db = 0;
	public static $host = "127.0.0.1";
	public static $port = 6379;
	
	private function __construct()
	{
	}
	
	public static function open($save_path, $session_name)
	{
		self::$session_name = $session_name;
		
		if (self::$redis === NULL)
		{
			self::$redis = new php_redis(self::$host, self::$port);
			if (self::$db != 0)
			{
				self::$redis->select(self::$db);
			}
		}
	}

	public static function close()
	{
		self::$redis = NULL;
	}
	
	public static function read($id)
	{
		$key = self::$session_name.":".$id;
		
		$sess_data = self::$redis->get($key);
		if ($sess_data === NULL)
		{
			return "";
		}
		return $sess_data;
	}
	
	public static function write($id, $sess_data)
	{
		$key = self::$session_name.":".$id;
		$lifetime = ini_get("session.gc_maxlifetime");
		
		self::$redis->set_expire($key, $lifetime, $sess_data);
	}
	
	public static function destroy($id)
	{
		$key = self::$session_name.":".$id;
		
		self::$redis->delete($key);
	}
	
	public static function gc($maxlifetime)
	{
	}

	public static function install()
	{
		session_set_save_handler("redis_sessions::open", "redis_sessions::close"
			, "redis_sessions::read", "redis_sessions::write"
			, "redis_sessions::destroy", "redis_sessions::gc");
	}
}
