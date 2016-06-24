<?php

require_once dirname(dirname(__FILE__)).'/util/HttpClient.php';
require_once dirname(dirname(__FILE__)).'/util/xml2array.php';
require_once dirname(dirname(__FILE__)).'/util/html2text.php';

//获取新闻
function get_news_rss($url, $limit=5)
{
	$http = new HttpClient();
	$xmltext = $http->get($url);
	//检查编码，如果是gb2312或gbk等，转换成utf8
	preg_match("/encoding=\"(gb[1-9k]+)\"/i", $xmltext, $matches);
	//var_dump($matches);
	if(count($matches)>0)
	{
		//转换到 utf-8 编码
		$xmltext = mb_convert_encoding($xmltext, "UTF-8", "GBK");
		//替换参数
		$xmltext = preg_replace("/encoding=\"gb[1-9k]+\"/i", "encoding=\"utf-8\"", $xmltext);
	}
	$rss = parse_rss($xmltext, $limit);
	return $rss;
}

function get_news_text($length=100, $limit=5)
{
	$feeds = array(
		"http://cn.engadget.com/rss.xml",		//Engadget 中国版
		"http://rss.cnbeta.com/rss",			//cnbeta
		"http://www.ifanr.com/feed",			//爱范儿
		"http://www.geekpark.net/rss",			//极客公园
		"http://www.qdaily.com/feed.xml",		//好奇心日报
		"http://rss.sina.com.cn/news/china/focus15.xml",	//新浪
		"http://news.baidu.com/n?cmd=4&class=civilnews&tn=rss",	//百度
	);
	$i = rand(0, count($feeds)-1);
	$rss = get_news_rss($feeds[$i], $limit);
	$text = "";
	foreach($rss as $art)
	{
		$title = $art['title'];
		$date  = date("Y-m-d H:i:s", $art['pubDate']);
		$link  = $art['link'];
		$desc  = html2text($art["description"]);
		$desc  = mb_substr($desc, 0, $length, "utf-8");
		$text .= "{$title}\n({$date})\n{$desc}\n{$link}\n\n";
	}
	return $text;
}

//echo get_news_text();

?>
