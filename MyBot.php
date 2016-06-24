<?php

//基本辅助函数（类）
require_once 'library/phpqrcode/qrlib.php';
require_once 'library/util/HttpClient.php';
require_once 'library/util/findfiles.php';
//功能模块
require_once 'library/tools/getnews.php';
//机器人API
require_once 'TelegramBot.php';

//从数据文件中获取随机的图片链接
function data_get_image($filename)
{
	$text = file_get_contents($filename);
	$lines = explode("\n", $text);
	//随机选择一行
	$line = $lines[rand(0, count($lines)-1)];
	$info = explode("|", chop($line));
	if(count($info) ==2)
	{
		return $info[1];
	}
	else
	{
		return FALSE;
	}
}


//从数据文件中获取段子
function data_get_joke($filename, $limit=1)
{
	$text = file_get_contents($filename);
	$jokes = json_decode($text);
	//随机选择一条
	$i = rand(0, count($jokes) - $limit);
	$text = "";
	for($j=$i; $j<$i+$limit; $j++)
	{
		$text .= $jokes[$j]->text ."\n----------------------\n";
	}
	return rtrim($text, "-\n");
}


//定义我的机器人
class MyBot extends TelegramBot
{
	
}

//聊天对象
class MyBotChat extends TelegramBotChat
{
	private $cmd_reply_message = array();
	//构造函数
	public function __construct($core, $chat_id)
	{
		parent::__construct($core, $chat_id);
		//装载应答数据文件
		$this->cmd_reply_message = array(
			"start" => "start",
			"help"  => "help",
			"about" => "about",
			"contact" => "text",
		);
		//从文件中装载应答数据
		$keys = array_keys($this->cmd_reply_message);
		foreach($keys as $key)
		{
			$this->cmd_reply_message[$key] = file_get_contents("data/".$key);
		}
		//var_dump($this->cmd_reply_message);
	}
	
	//"start" 命令
	public function command_start($message)
	{
		$this->apiSendMessage($this->cmd_reply_message["start"], array("reply_to_message_id"=>$message["message_id"]));
	}
	
	// "about" 命令
	public function command_about($message)
	{
		//$this->apiUploadFile("data/images/0000.jpg", 'photo', array("reply_to_message_id"=>$message["message_id"]));
		$this->apiSendMessage($this->cmd_reply_message["start"], array("reply_to_message_id"=>$message["message_id"]));
	}

	// "help" 命令
	public function command_help($message)
	{
		$this->sendHelp($message);
	}
	
	// "contact" 命令
	public function command_contact($message)
	{
		$this->apiSendMessage($this->cmd_reply_message["contact"], array("reply_to_message_id"=>$message["message_id"]));
	}

	// "map" 命令
	public function command_map($message)
	{
		$this->apiSendLocation(30.657476, 104.065554, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	// "me" 命令
	public function command_me($message)
	{
		$userid = $message['from']['id'];
		$username = $message['from']['username'];
		$nickname = $message['from']['first_name'] . ' ' . $message['from']['last_name'];
		$chatType = $message['chat']['type'];
		if($chatType === 'private')
		{
			$reply = "私聊，发送者：{$nickname}\n用户名：{$username}\nID:{$userid}";
		}
		else if($chatType === 'group')
		{
			$groupId = $message['chat']['id'];
			$groupName = $message['chat']['title'];
			$reply = "群聊，ID：{$groupId}, 群名称：{$groupName}";
			$reply .= "发送者：{$nickname}\n用户名：{$username}\nID：{$userid}";
		}
		else
		{
			$reply = "未知类型({$chatType})，发送者：{$nickname}\n用户名：{$username}\nID:{$userid}";
		}
		$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//发送新闻
	public function command_news($message)
	{
		$reply = get_news_text(100, 5);
		$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//图片
	public function command_girls($message)
	{
		//获取数据文件列表
		$files = find_match_files("data", "girls*");
		$filename = $files[rand(0, count($files)-1)];
		//echo "select file {$filename}\n";
		//获取图片，发送给客户端
		$imgsrc = data_get_image("data/" .$filename);
		if($imgsrc)
		{
			//直接发送图片链接
			$this->apiSendMessage($imgsrc, array("reply_to_message_id"=>$message["message_id"]));
		}
		else
		{
			$reply = "未找到图片信息";
			$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
		}
	}
	
	//图片
	public function command_joke($message)
	{
		//获取数据文件列表
		$files = find_match_files("data", "jokes*");
		$filename = $files[rand(0, count($files)-1)];
		//echo "select file {$filename}\n";
		$reply = data_get_joke($url="data/".$filename);
		$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	public function command_jokes($message)
	{
		//获取数据文件列表
		$files = find_match_files("data", "jokes*");
		$filename = $files[rand(0, count($files)-1)];
		//echo "select file {$filename}\n";
		$reply = data_get_joke($url="data/".$filename, 5);
		$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//二维码处理
	public function command_qrencode($message)
	{
		$fields = explode(' ', $message['text'], 2);
		if(count($fields) < 2)
		{
			$reply = "qrencode 命令缺少参数";
			$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
			return;
		}
		//生成二维码
		$text = $fields[1];
		$filename = 'data/tmp/' . $message['message_id'] . '_' . time() . '.png';
		//生成二维码
		QRcode::png($text, $filename, 'L', 4, 2);
		if(WEBHOOKMODE ==1)
		{
			//如果是webhook调用，则发送链接
			$qrurl = "https://your_url/{$filename}";
			$this->apiSendMessage($qrurl, array("reply_to_message_id"=>$message["message_id"]));
		}
		else
		{
			//pull 模式，则上传图片
			$this->apiUploadFile($filename, 'photo', array("reply_to_message_id"=>$message["message_id"]));
		}
	}
	
	//获取 Android 版汉化文件
	public function command_getandroid($message)
	{
		$filename = "data/local/strings-zh_CN.xml";
		$this->apiUploadFile($filename, 'document', array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//获取 iOS 版汉化文件
	public function command_getios($message)
	{
		$filename = "data/local/Localizable_zh_CN.strings";
		$this->apiUploadFile($filename, 'document', array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//清除临时文件
	public function command_clean($message)
	{
		$files = scandir("data/tmp/");
		$reply = "清理了以下临时文件：\n";
		foreach($files as $f)
		{
			if(!($f == '.' or $f == '..' or $f == 'index.html'))
			{
				$reply .= "data/tmp/".$f."\n";
				@unlink("data/tmp/".$f);
			}
		}
		$this->apiSendMessage($reply, array("reply_to_message_id"=>$message["message_id"]));
	}
	
	//机器人被加入到一个新聊天中
	public function bot_added_to_chat($message)
	{
		$this->sendHelp($message);
	}

	//上面没有进行定义的其它命令
	public function some_command($command, $message)
	{
		$text = "不支持的命令，支持如下命令:";
		$text .= $this->cmd_reply_message["help"];
		$this->apiSendMessage($text, array("reply_to_message_id"=>$message["message_id"]));
	}

	//非命令消息，普通消息
	public function message($text, $message)
	{
		
		if(strstr("😍😘❤️💔💜💕💞💓💗💖💘💝", $text) != FALSE)
		{
			$this->command_girls($message);
		}
		else if(strstr("😂😞😒😖😣😔😒😢😓", $text) != FALSE)
		{
			$this->command_joke($message);
		}
		else if(((strstr($text, "我要") != FALSE) or (strstr($text, "给我") != FALSE))and ((strstr($text, "美女") != FALSE) or (strstr($text, "妹子") != FALSE)))
		{
			$this->command_girls($message);
		}
		else if((strstr($text, "段子") != FALSE) or (strstr($text, "吐槽") != FALSE))
		{
			$this->command_joke($message);
		}
		else if((strstr($text, "帮助") != FALSE) or (strstr($text, "帮我") != FALSE))
		{
			$this->sendHelp($message);
		}
		else if((strstr($text, "新闻") != FALSE) or (strstr($text, "消息") != FALSE))
		{
			$this->command_news($message);
		}
		else if((strstr($text, "你好") != FALSE))
		{
			$this->command_about($message);
		}
		else
		{
			$this->apiSendMessage($text, array("reply_to_message_id"=>$message["message_id"]));
		}
	}
	
	//发送帮助消息
	protected function sendHelp($message)
	{
		if($this->isGroup)
		{
			$text = "这个机器人将处理一些有趣的事情。";
		}
		else
		{
			$text = "这个机器人将处理一些有趣的事情，你可以将她分享到群中。";
		}
		$this->apiSendMessage($this->cmd_reply_message["help"], array("reply_to_message_id"=>$message["message_id"]));
	}
}
?>

