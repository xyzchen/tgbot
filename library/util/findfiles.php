<?php
function find_match_files($path, $pattern)
{
	$files = array();
	$dir = @opendir($path);
	while(($file = readdir($dir)) !== false)
	{
		if(fnmatch($pattern, $file))
		{
			$files[] = $file;
		}
	}
	return $files;
}

?>
