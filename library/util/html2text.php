<?php
//------------------------------------
// 把 html 字符串转换成纯文本字符串
//------------------------------------
function html2text($str)
{
	$str  = str_replace("<br>", "\n", $str);
	$str  = str_replace("<br />", "\n", $str);
	$str  = str_replace("</p>", "</p>\n", $str);
	$str  = preg_replace("/<sty(.*)\/style>|<scr(.*)\/script>|<!--(.*)-->/isU", "", $str);
	
	$alltext = "";
	$start   = 1;
	for($i=0; $i<strlen($str); $i++)
	{
		if($start==0 && $str[$i]==">")
		{
			$start = 1;
		}
		else if($start==1)
		{
			if($str[$i]=="<")
			{
				$start = 0;
				$alltext .= " ";
			}
			else
			{
				$alltext .= $str[$i];
			}
		}
	}
	$alltext = preg_replace("/&([^;&]*)(;|&)/", "", $alltext);
	$alltext = preg_replace("/[ ]+/s", " ", $alltext);
	return $alltext;
}
?>
