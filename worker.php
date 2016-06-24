<?php

set_time_limit(0);

define('WEBHOOKMODE', 0);
define('BOT_TOKEN', 'YOUR_BOT_TOKEN');

if(php_sapi_name() != 'cli')
{
	echo "It's works!\n";
	exit;
}

//功能模块
require_once 'MyBot.php';
//日志记录类
require_once 'library/Logger.php';
//创建机器人并运行
$logger = new Logger("mysql:host=localhost;dbname=tgbot", "username", "password");
$bot = new MyBot(BOT_TOKEN, 'MyBotChat', $logger);
$bot->init();
echo "Id: {$bot->botId}, name:{$bot->botUsername}\n";

$bot->runLongpoll();

?>

