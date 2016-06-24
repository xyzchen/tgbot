<?php

class Logger
{
	// PDO 对象
	public $db;
	
	/**
	 * 连接到数据库.
	 *
	 * @param string 连接DSN.
	 * @param string 数据库用户名. (依赖 DSN)
	 * @param string 数据库用户密码. (依赖 DSN)
	 * @param array  数据库驱动程序选项 (选项)
	 * @return boolean 成功返回ture
	 */
	function __construct($dsn, $username, $password, $driver_options=array())
	{
		try
		{
    		$this->db = new PDO($dsn, $username, $password, $driver_options);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->db->query("SET NAMES 'UTF8'");
		}
		catch(Exception $e)
		{
			$this->db = NULL;
			echo $e->getMessage() ."\n";
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 插入收到的消息到数据库中
	 *
	 * @param table   : 数据表名
	 * @param message : 用户发送给机器人的消息
	 *
	 */
	 
	function log($message)
	{
		if($this->db == NULL)
		{
			echo "打开数据库失败!\n";
			return FALSE;
		}
		echo "正在记录数据到数据库中...\n";
		$uptime  = date("Y-m-d H:i:s", $message["date"]);
		$msgtext = json_encode($message, JSON_UNESCAPED_UNICODE);
		$sql = "INSERT INTO log(id, userid, uptime, message) VALUES(:id, :userid, :uptime, :message)";
		//绑定参数
		try{
			$stmt=$this->db->prepare($sql);
			$stmt->bindValue(':id', $message['message_id']);
			$stmt->bindValue(':userid', $message['chat']['id']);
			$stmt->bindValue(':uptime', $uptime);
			$stmt->bindValue(':message', $msgtext);
			$stmt->execute();
		}
		catch(Exception $e)
		{
			echo $e->getMessage() ."\n";
			return FALSE;
		}
		return TRUE;
	}
}
?>
