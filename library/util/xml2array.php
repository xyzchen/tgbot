<?php

/**
 * xml 转换成关联数组对象.
 *
 * @param   要转换的XML字符串
 * @return  转换后的数组.
 *
 */
function xml_to_array($xml)
{
	function normalizeSimpleXML($obj, &$result)
	{
		$data = $obj;
		if(is_object($data))
		{
			$data = get_object_vars($data);
		}
		if(is_array($data))
		{
			foreach($data as $key => $value)
			{
				$res = null;
				normalizeSimpleXML($value, $res);
				if(($key == '@attributes') && ($key))
				{
					$result = $res;
				}
				else
				{
					$result[$key] = $res;
				}
			}
		}
		else
		{
			$result = $data;
		}
	}
	normalizeSimpleXML(simplexml_load_string($xml), $result);
	return $result;
}

//xml字符串转换成json字符串
function xml_to_json($xml)
{
	return json_encode(xml_to_array($xml));
}


//分析RSS文本转换成数组
function parse_rss($rsstext, $limit=100)
{
	$rss = array();
	try
	{
		//从RSS文本创建 Xml 对象
		$x = simplexml_load_string($rsstext);
		//遍历对象
		$i = 0;
		foreach($x->channel->item as $entry)
		{
			$article = array();
			$article["title"] = @strval($entry->title);
			$article["pubDate"] = @strtotime($entry->pubDate);
			$article["link"] = @strval($entry->link);
			$article["description"] = @strval($entry->description);
			$rss[$i] = $article;
			$i++;
			if($i>=$limit)
			{
				break;
			}
		}
	}
	catch(Exception $e)
	{
		//echo "\n[错误]:" .$e->getMessage() . "\n";
	}
	return $rss;
}

?>
