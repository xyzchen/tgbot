<?php

require_once '../library/util/HttpClient.php';
require_once '../library/util/SimpleHtml.php';
require_once '../library/util/html2text.php';

//获取段子
function jiandan_get_jokes($url="http://jandan.net/duan")
{
	// 先下载文本，然后解析
	$http = new HttpClient(100);
	$text = $http->get($url);
	// 新建一个Dom实例
	$html = new SimpleHtmlDom();
	// 从url中加载
	$html->load($text);
	//查找html元素
	$dslist = $html->find('ol.commentlist li');
	$result = array();
	foreach($dslist as $duanzi)
	{
		$dz = array();
		//=========================================
		//段子编号
		$dz["id"] = $duanzi->id;
		//--------------------------------
		//获取段子文本
		$parts = $duanzi->find("div.text p");
		//获取字符串
		$text = "";
		foreach($parts as $p)
		{
			$text .= $p->outertext;
		}
		$dz["text"] = html2text($text);
		$result[] = $dz;
	}
	return $result;
}

$dz = jiandan_get_jokes("http://jandan.net/duan");
$filename = "../data/jokes_" . date("Ymd") . ".txt";
file_put_contents($filename, json_encode($dz, JSON_UNESCAPED_UNICODE));
//输出
foreach($dz as $joke)
{
	echo "{$joke['id']}:\n";
	echo "{$joke['text']}\n";
	echo "------------------------------------\n\n";
}

echo "OK!\n";

?>
