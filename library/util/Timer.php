<?php 
/**
 *===================================================
 *           计时器类
 *   陈逸少(jmchxy@gmail.com)
 *         2011-08-01
 *===================================================
**/

class Timer
{
	static $start;
    //启动计时器
	static function start()
	{
		self::$start = self::getMicrotime();
	}
	//获取微秒时间
	static function getMicrotime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
    //获取用去的时间
	static function getTime($length=6)
	{
		return round(self::getMicrotime() - self::$start, $length);
	}
}

?>
