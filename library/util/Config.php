<?php

//定义平台属性
if(preg_match("/^Windows/i", php_uname('s')))
{
	define('WINNT', true);	//Windows 平台
}
else
{
	define('WINNT', false);	//非Windows平台
}

//对字符串本地化编码， utf8编码转换成本地编码
function local_string($message)
{
	if(WINNT == true)
	{
		$message = mb_convert_encoding($message, "GBK", "UTF-8");
	}
	return $message;
}

//对字符串编码,本地编码转换成utf8编码
function net_string($message)
{
	if(WINNT == true)
	{
		$message = mb_convert_encoding($message, "UTF-8", "GBK");
	}
	return $message;
}

//使用本地字符编码方式输出变量
function local_vardump($value)
{
	$txt = var_export($value, true);
	echo local_string($txt)."\n";
}

//日志记录函数，输出消息到控制台
function console_log($message)
{
	if(WINNT == true)
	{
		$message = mb_convert_encoding($message, "GBK", "UTF-8");
	}
	$datetime = date("[Y-m-d H:i:s]");
	echo "{$datetime} {$message}\n";
}

?>
