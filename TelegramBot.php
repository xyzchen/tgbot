<?php

//Telegram 机器人核心类
abstract class TelegramBotCore
{
	//机器人API地址，不会变化
	protected $apiUrl;

	//机器人信息：id, username, token
	public    $botId;
	public    $botUsername;
	protected $botToken;

	//curl句柄，以及是否初始化
	protected $handle;		//curl句柄
	protected $inited = false;

	//延迟
	protected $lpDelay = 1;
	protected $netDelay = 1;

	//更新限制
	protected $updatesOffset = false;
	protected $updatesLimit = 30;
	protected $updatesTimeout = 120;

	//网络超时
	protected $netTimeout = 120;
	protected $netConnectTimeout = 120;

	//构造函数
	public function __construct($token)
	{
		//机器人api地址
		$this->botToken = $token;
		$this->apiUrl = "https://api.telegram.org/bot{$token}/";
	}

	//初始化机器人，并获取机器人信息
	public function init()
	{
		if ($this->inited)
		{
			return true;
		}

		$this->handle = curl_init();

		$response = $this->request('getMe');
		if (!$response['ok'])
		{
			throw new Exception("Can't connect to server");
		}

		$bot = $response['result'];
		$this->botId = $bot['id'];
		$this->botUsername = $bot['username'];
		$this->inited = true;
		return true;
	}

	//使用getUpdates更新提供服务
	public function runLongpoll()
	{
		$this->init();
		$this->longpoll();
	}

	//设置webhook
	public function setWebhook($url)
	{
		$this->init();
		$result = $this->request('setWebhook', array('url' => $url));
		return $result['ok'];
	}

	//取消webhook
	public function removeWebhook()
	{
		$this->init();
		$result = $this->request('setWebhook', array('url' => ''));
		return $result['ok'];
	}
	
	//发送请求，返回json解码后的参数
	public function request($method, $params = array(), $options = array())
	{
		$options += array(
			'http_method' => 'GET',
			'timeout' => $this->netTimeout,
		);
		$params_arr = array();
		foreach ($params as $key => &$val)
		{
			if (!is_numeric($val) && !is_string($val))
			{
				$val = json_encode($val);
			}
			$params_arr[] = urlencode($key).'='.urlencode($val);
		}
		$query_string = implode('&', $params_arr);
		//请求的url
		$url = $this->apiUrl . $method;
		//设置curl参数
		if ($options['http_method'] === 'POST')
		{
			curl_setopt($this->handle, CURLOPT_SAFE_UPLOAD, false);
			curl_setopt($this->handle, CURLOPT_POST, true);
			curl_setopt($this->handle, CURLOPT_POSTFIELDS, $query_string);
		}
		else
		{
			$url .= ($query_string ? '?'.$query_string : '');
			curl_setopt($this->handle, CURLOPT_HTTPGET, true);
		}
		//网络超时信息
		$connect_timeout = $this->netConnectTimeout;
		$timeout = $options['timeout'] ?: $this->netTimeout;
		//设置curl参数
		curl_setopt($this->handle, CURLOPT_URL, $url);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		curl_setopt($this->handle, CURLOPT_TIMEOUT, $timeout);
		//获取应答参数
		$response_str = curl_exec($this->handle);
		$errno = curl_errno($this->handle);
		$http_code = intval(curl_getinfo($this->handle, CURLINFO_HTTP_CODE));
		//检查应答信息
		if ($http_code == 401)
		{
			throw new Exception('Invalid access token provided');
		}
		else if ($http_code >= 500 || $errno)
		{
			sleep($this->netDelay);
			if ($this->netDelay < 30)
			{
				$this->netDelay *= 2;
			}
		}
		$response = json_decode($response_str, true);
		return $response;
	}
	
	//----------------------------------------------
	//发送文件，返回json解码后的参数
	// $options 包含文件名，文件类型，聊天id等信息
	//----------------------------------------------
	public function uploadfile($method, $params = array(), $options = array())
	{
		//请求的url
		$url = $this->apiUrl . $method;
		
		//设置curl参数，肯定是POST方法
		curl_setopt($this->handle, CURLOPT_POST, true);
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, $params);
		
		//网络超时信息
		$connect_timeout = $this->netConnectTimeout;
		$timeout = $this->netTimeout;
		//设置curl参数
		curl_setopt($this->handle, CURLOPT_URL, $url);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
		curl_setopt($this->handle, CURLOPT_TIMEOUT, $timeout);
		//获取应答参数
		$response_str = curl_exec($this->handle);
		$errno = curl_errno($this->handle);
		$http_code = intval(curl_getinfo($this->handle, CURLINFO_HTTP_CODE));
		//检查应答信息
		if ($http_code == 401)
		{
			throw new Exception('Invalid access token provided');
		}
		else if ($http_code >= 500 || $errno)
		{
			sleep($this->netDelay);
			if ($this->netDelay < 30)
			{
				$this->netDelay *= 2;
			}
		}
		$response = json_decode($response_str, true);
		return $response;
	}
	
	//使用update服务
	protected function longpoll()
	{
		$params = array(
			'limit' => $this->updatesLimit,
			'timeout' => $this->updatesTimeout,
		);
		
		if ($this->updatesOffset)
		{
			$params['offset'] = $this->updatesOffset;
		}
		$options = array(
			'timeout' => $this->netConnectTimeout + $this->updatesTimeout + 2,
		);
		$response = $this->request('getUpdates', $params, $options);
		if($response['ok'])
		{
			$updates = $response['result'];
			if (is_array($updates))
			{
				foreach ($updates as $update)
				{
					$this->updatesOffset = $update['update_id'] + 1;
					$this->onUpdateReceived($update);
				}
			}
		}
		$this->longpoll();
	}
	//自定义的应答函数，处理消息
	abstract public function onUpdateReceived($update);
}

//Chat类
abstract class TelegramBotChat
{
	protected $core;		//bot
	protected $chatId;		//chatid
	protected $isGroup;		//是否是群消息

	public function __construct($core, $chat_id)
	{
		if (!($core instanceof TelegramBot))
		{
			throw new Exception('$core must be TelegramBot instance');
		}
		$this->core = $core;
		$this->chatId = $chat_id;
		$this->isGroup = $chat_id < 0;
	}

	public function init() {}
	public function bot_added_to_chat($message) {}
	public function bot_kicked_from_chat($message) {}
	public function some_command($command, $params, $message) {}
	public function message($text, $message) {}
	
	//发送消息
	protected function apiSendMessage($text, $params = array())
	{
		$params += array(
			'chat_id' => $this->chatId,
			'text' => $text,
		);
		return $this->core->request('sendMessage', $params);	
	}
	
	//转发消息
	protected function apiForwardMessage($from_chat_id, $message_id, $params = array())
	{
		$params += array(
			'chat_id' => $this->chatId,
			'from_chat_id' => $from_chat_id,
			'message_id' => $message_id,
		);
		return $this->core->request('forwardMessage', $params);	
	}
	
	//发送定位消息
	protected function apiSendLocation($latitude, $longitude, $params = array())
	{
		$params += array(
			'chat_id'  => $this->chatId,
			'latitude' => $latitude,
			'longitude' => $longitude,
		);
		return $this->core->request('sendLocation', $params);
	}
	
	#------------------------------------------
	#  发送本地文件(document, audio, photo)，返回发送的结果
	#------------------------------------------
	protected function apiUploadFile($filename, $filetype, $params = array())
	{
		//api
		$method = 'send' . ucfirst($filetype);
		$params += array(
			'chat_id' => $this->chatId,
			$filetype => curl_file_create($filename),
		);
		return $this->core->uploadfile($method, $params);
	}
	
	//发送action
	protected function apiSendChatAction($action="upload_photo", $params = array())
	{
		$params += array(
			'chat_id' => $this->chatId,
			'action' => $action,
		);
		return $this->core->request('sendChatAction', $params);
	}
}

//定义一个机器人
class TelegramBot extends TelegramBotCore
{
	protected $chatClass;
	protected $chatInstances = array();
	protected $logger = NULL;	//日志记录类
	
	public function __construct($token, $chat_class, $logger=NULL)
	{
		parent::__construct($token);
		$instance = new $chat_class($this, 0);
		if (!($instance instanceof TelegramBotChat))
		{
			throw new Exception('ChatClass must be extends TelegramBotChat');
		}
		$this->chatClass = $chat_class;
		$this->logger = $logger;
	}

	public function onUpdateReceived($update)
	{
		if($update['message'])
		{
			$message = $update['message'];
			//记录日志
			if($this->logger)
			{
				$this->logger->log($message);
			}
			//echo "\n===================================================\n";
			//echo "message:\n";
			//echo var_export($message, true);
			//echo "\n===================================================\n";
			//处理消息
			$chat_id = intval($message['chat']['id']);
			if ($chat_id)
			{
				$chat = $this->getChatInstance($chat_id);
				if (isset($message['group_chat_created']))
				{
					$chat->bot_added_to_chat($message);
				}
				else if (isset($message['new_chat_participant']))
				{
					if ($message['new_chat_participant']['id'] == $this->botId)
					{
						$chat->bot_added_to_chat($message);
					}
				}
				else if (isset($message['left_chat_participant']))
				{
					if ($message['left_chat_participant']['id'] == $this->botId)
					{
						$chat->bot_kicked_from_chat($message);
					}
				}
				else
				{
					$text = trim($message['text']);
					$username = strtolower('@'.$this->botUsername);
					$username_len = strlen($username);
					if (strtolower(substr($text, 0, $username_len)) == $username)
					{
						$text = trim(substr($text, $username_len));
					}
					if(preg_match('/^(?:\/([a-z0-9_]+))/is', $text, $matches))
					{	// 是 “/”开头的命令
						$command = $matches[1];
						$method = 'command_'.$command;
						//存在 command_ 方法
						if (method_exists($chat, $method))
						{
							$chat->$method($message);
						}
						else
						{	//不存在 command_ 的方法
							$chat->some_command($command, $message);
						}
					}
					else
					{
						//非命令的文本消息的处理
						$chat->message($text, $message);
					}
				}
			}
		}
	}

	protected function getChatInstance($chat_id)
	{
		if (!isset($this->chatInstances[$chat_id]))
		{
			$instance = new $this->chatClass($this, $chat_id);
			$this->chatInstances[$chat_id] = $instance;
			$instance->init();
		}
		return $this->chatInstances[$chat_id];
	}
}
?>

