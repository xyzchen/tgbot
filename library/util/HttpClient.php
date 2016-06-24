<?php
//------------------------------------------------------
//              HTTP 客户端类
//         陈逸少（jmchxy@gmail.com）
//               2013-11-24
//------------------------------------------------------
class HttpClient
{
	//------------------------------
	//请求方式和数据
	//------------------------------
	protected $_timeout;		//超时
	protected $_followlocation; //是否自动跳转
	protected $_includeHeader;	//返回的数据是否包含HTTP头信息
	//保存 cookie 的文件
	protected $_cookieFileLocation;
	//根证书
	protected $_cacert = './cacert.pem';
	//------------------------------
	//  客户端信息
	//------------------------------
	protected $_proxy;			//代理服务器
	//用户代理
	protected $_useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36';
	//用户认证信息
	public    $authentication = false; 
	public    $auth_name      = ''; 
	public    $auth_pass      = ''; 
	//返回的数据
	protected $_webpage;
	protected $_status;
	
	//构造函数
	public function __construct($timeout=30, $proxy="", $followlocation=true, $includeHeader=false) 
	{
		//设置信息
		$this->_timeout = $timeout;
		$this->_proxy   = $proxy;
		$this->_followlocation = $followlocation; 
		$this->_includeHeader  = $includeHeader; 
		$this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';
		$this->_cacert = dirname(__FILE__).'/cacert.pem';
		//返回值
		$this->_webpage = "";
		$this->_status  = 0;
	}
	
	/** 
	 * HTTP GET 方法
	 * 
	 * @param   url     string  url 
	 * @param   params  dict    请求参数（名-值 关联数组）， 附加到地址上
	 * @param   CA      bool    HTTPS时是否进行严格认证 
	 * @return  string  服务器返回的数据
	 */  
	public function get($url, $params=array(), $CA = false)
	{    
		$SSL = substr($url, 0, 8) == "https://" ? true : false;	//是否是https?
		//附加参数
		if(count($params) > 0)
		{
			$url .= "?" . HttpClient::urlencode($params);
		}
		$ch = curl_init();
		//设置URL
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		//重定位
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
		//SSL
		if ($SSL && $CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);	// 只信任CA颁布的证书
			curl_setopt($ch, CURLOPT_CAINFO, $this->_cacert);// CA根证书（用来验证的网站证书是否是CA颁布）
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名，并且是否与提供的主机名匹配
		}
		else if ($SSL && !$CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名
		}
		//设置用户代理字符串
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_useragent);
		//代理服务器
		if($this->_proxy != "")
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy);
		}
		//设置includeHeader
		if($this->_includeHeader != false)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		//需要用户认证，设置认证信息
		if($this->authentication == true)
		{
			curl_setopt($ch, CURLOPT_USERPWD, $this->authname . ':' . $this->authpass);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//设置参数，输出到字符串中
		//保存返回的数据
		$this->_webpage = curl_exec($ch); 
		$this->_status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//返回数据
		return $this->_webpage;    
	}
	
	/** 
	 * HTTP POST 方法
	 * 
	 * @param   url     string  url 
	 * @param   params  dict    请求参数（名-值 关联数组），POST到服务器
	 * @param   CA      bool    HTTPS时是否进行严格认证 
	 * @return  string  服务器返回的数据
	 */  
	public function post($url, $params=array(), $CA = false)
	{    
		$SSL = substr($url, 0, 8) == "https://" ? true : false;	//是否是https?
		$ch = curl_init();
		//设置URL
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		//重定位
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
		//SSL
		if ($SSL && $CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);	// 只信任CA颁布的证书
			curl_setopt($ch, CURLOPT_CAINFO, $this->_cacert);// CA根证书（用来验证的网站证书是否是CA颁布）
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名，并且是否与提供的主机名匹配
		}
		else if ($SSL && !$CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名
		}
		//设置用户代理字符串
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_useragent);
		//代理服务器
		if($this->_proxy != "")
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy);
		}
		//设置includeHeader
		if($this->_includeHeader != false)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		//需要用户认证，设置认证信息
		if($this->authentication == true)
		{
			curl_setopt($ch, CURLOPT_USERPWD, $this->authname . ':' . $this->authpass);
		}
		curl_setopt($ch, CURLOPT_POST, true);			//设置post方式		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);	//设置post的数据
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//设置参数，输出到字符串中
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
		//保存返回的数据
		$this->_webpage = curl_exec($ch); 
		$this->_status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//返回数据
		return $this->_webpage;  
	}
	
	/** 
	 * AJAX POST 方法
	 * 
	 * @param   url     string  url 
	 * @param   params  dict    请求参数（名-值 关联数组）
	 * @param   CA      bool    HTTPS时是否进行严格认证 
	 * @return  string  服务器返回的数据
	 */  
	public function ajax($url, $params=array(), $CA = false)
	{    
		$SSL = substr($url, 0, 8) == "https://" ? true : false;	//是否是https?
		$ch = curl_init();
		//设置URL
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		//重定位
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
		//SSL
		if ($SSL && $CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);	// 只信任CA颁布的证书
			curl_setopt($ch, CURLOPT_CAINFO, $this->_cacert);// CA根证书（用来验证的网站证书是否是CA颁布）
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名，并且是否与提供的主机名匹配
		}
		else if ($SSL && !$CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名
		}
		//设置用户代理字符串
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_useragent);
		//代理服务器
		if($this->_proxy != "")
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy);
		}
		//设置includeHeader
		if($this->_includeHeader != false)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		//需要用户认证，设置认证信息
		if($this->authentication == true)
		{
			curl_setopt($ch, CURLOPT_USERPWD, $this->authname . ':' . $this->authpass);
		}
		//ajax 需要的特别的头部
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array("Content-type: application/json; charset=UTF-8",
				  "X-Requested-With:XMLHttpRequest",
				  "Expect:"	//避免data数据过长问题
			));
		//POST方式和POST数据
		curl_setopt($ch, CURLOPT_POST, true);			//设置post方式		
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));	//设置post的数据
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//设置参数，输出到字符串中
		//保存返回的数据
		$this->_webpage = curl_exec($ch); 
		$this->_status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//返回数据
		return $this->_webpage;    
	}
	
	//----------------------------------------------
	// 上传文件到服务器
	//   @url     : string , 网页地址
	//   @filename: string , 要保存的文件名
	//   @inputname:string , 上传的文件表单字段名
	//   @ca      : bool   , 是否使用严格模式的证书
	//----------------------------------------------
	public function upload($url, $filename, $inputname="fileupload", $CA=false)
	{
		$SSL = substr($url, 0, 8) == "https://" ? true : false;	//是否是https?
		$ch = curl_init();
		//设置URL
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		//重定位
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
		//SSL
		if ($SSL && $CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);	// 只信任CA颁布的证书
			curl_setopt($ch, CURLOPT_CAINFO, $this->_cacert);// CA根证书（用来验证的网站证书是否是CA颁布）
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名，并且是否与提供的主机名匹配
		}
		else if ($SSL && !$CA)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 信任任何证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	// 检查证书中是否设置域名
		}
		//设置用户代理字符串
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_useragent);
		//代理服务器
		if($this->_proxy != "")
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy);
		}
		//设置includeHeader
		if($this->_includeHeader != false)
		{
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		//需要用户认证，设置认证信息
		if($this->authentication == true)
		{
			curl_setopt($ch, CURLOPT_USERPWD, $this->authname . ':' . $this->authpass);
		}
		curl_setopt($ch, CURLOPT_POST, true); //设置post方式		
		//设置上传的文件
		curl_setopt($ch, CURLOPT_POSTFIELDS,
			array(
				$inputname => curl_file_create($filename)
			)
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//设置参数，输出到字符串中
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
		//保存返回的数据
		$this->_webpage = curl_exec($ch); 
		$this->_status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//返回数据
		return ($this->_status == 200);
	}
	
	//设置用户代理
	public function setUseragent($useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36')
	{
		$this->_useragent = $useragent;
	}
	
	//设置cookie保存文件
	public function setCookiFileLocation($path) 
	{
		$this->_cookieFileLocation = $path; 
	}
	
	//获取HTTP 状态
	public function getHttpStatus() 
	{
		return $this->_status; 
	}

	//获取页面文本
	public function getText()
	{
		return $this->_webpage; 
	}
	
	//返回页面文本
	public function __tostring()
	{
		return $this->_webpage; 
	}
	
	/**
	 * 对HTTP GET请求的参数进行URL编码
	 *   @param args array 请求参数的数组
	 */
	static function urlencode($args)
	{
		if(!is_array($args))
		{
			return false;
		}
		$out = '';
		foreach($args as $name => $value)
		{
			$out .= urlencode("$name").'=' . urlencode("$value") . '&';
		}
		$out = substr($out, 0, -1);
		return $out;
	}
}

?>
