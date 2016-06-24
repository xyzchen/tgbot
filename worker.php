<?php

set_time_limit(0);

define('WEBHOOKMODE', 0);
//这里定义你自己的机器人TOKEN
define('BOT_TOKEN', '');

if(php_sapi_name() != 'cli')
{
	echo "It's works!\n";
	exit;
}

//功能模块
require_once 'MyBot.php';

//创建机器人并运行
$bot = new MyBot(BOT_TOKEN, 'MyBotChat');
$bot->init();
echo "Id: {$bot->botId}, name:{$bot->botUsername}\n";

$bot->runLongpoll();

?>

