<?php

define('BOT_TOKEN', 'YOUR_BOT_TOKEN');
define('BOT_WEBHOOK', 'YOUR_WEBHOOK_URL');
define('WEBHOOKMODE', 1);

require_once 'MyBot.php';
require_once 'library/Logger.php';

$logger = new Logger("mysql:host=localhost;dbname=bot", "username", "password");
$bot = new MyBot(BOT_TOKEN, 'MyBotChat', $logger);

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

