<?php

//这里定义你自己的机器人TOKEN
define('BOT_TOKEN', '');
//这里定义你自己的网页地址
define('BOT_WEBHOOK', 'https://youweburl/web.php');
define('WEBHOOKMODE', 1);

require_once 'MyBot.php';

$bot = new MyBot(BOT_TOKEN, 'MyBotChat');

//如果是在控制台运行，设置webhook
if(php_sapi_name() == 'cli')
{
	if ($argv[1] == 'set') {
		$bot->setWebhook(BOT_WEBHOOK);
	}
	else if ($argv[1] == 'remove')
	{
		$bot->removeWebhook();
	}
	exit;
}

//读取telegram推送消息，然后运行命令处理程序
$response = file_get_contents('php://input');
if($response and count($response) ==0)
{
	echo "It's ok!";
	die();
}

$update = json_decode($response, true);

$bot->init();
$bot->onUpdateReceived($update);

