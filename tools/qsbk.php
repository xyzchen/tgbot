<?php

require_once '../library/util/HttpClient.php';
require_once '../library/util/SimpleHtml.php';
require_once '../library/util/html2text.php';

//获取段子
function qsbk_get_jokes($url="http://www.qiushibaike.com/")
{
	// 先下载文本，然后解析
	$http = new HttpClient(100);
	$http->setUseragent("Mozilla/5.0 (Linux; Android 4.4.2; XT1055 Build/KXA20.16-1.25.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.83 Mobile Safari/537.36");
	$text = $http->get($url);
	// 新建一个Dom实例
	$html = new SimpleHtmlDom();
	// 从url中加载
	$html->load($text);
	//查找html元素
	$dslist = $html->find('article');
	$result = array();
	foreach($dslist as $duanzi)
	{
		$dz = array();
		//=========================================
		//段子编号
		$dz["id"] = $duanzi->id;
		//--------------------------------
		//获取段子文本
		$parts = $duanzi->find(".content-text");
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

$dz = qsbk_get_jokes("http://www.qiushibaike.com/");
$filename = "../data/jokes_qb_" . date("Ymd") . ".txt";
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
