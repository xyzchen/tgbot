<?php

//获取图片链接地址
require_once '../library/util/HttpClient.php';
require_once '../library/util/SimpleHtml.php';

//获取图片
function jiandan_get_images($url="http://jandan.net/ooxx")
{
	// 先下载文本，然后解析
	$http = new HttpClient(100);
	$text = $http->get($url);
	//echo "{$text}\n";
	// 新建一个Dom实例
	$html = new SimpleHtmlDom();
	// 从url中加载
	$html->load($text);
	//查找html元素
	$images = $html->find('#comments img');
	//获取图片链接地址
	$links = array();
	foreach($images as $img)
	{
		$links[] = $img->src;
	}
	//获取下一页的链接
	$next = $html->find(".previous-comment-page");
	if($next)
	{
		$next = $next[0]->href;
	}
	else
	{
		$next = false;
	}
	return array($links, $next);
}

$result =  jiandan_get_images("http://jandan.net/ooxx");
$links  = $result[0];
$filename = "../data/girls_" . date("Ymd") . ".txt";
if(count($links) > 0)
{
	$file = fopen($filename, "w");
	foreach($links as $link)
	{
		fwrite($file, "妹子图|{$link}\n");
		echo "{$link}\n";
	}
	fclose($file);
}

echo "OK!\n";

?>
