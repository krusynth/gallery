<?php

function require_once_dir($dir) {
	$d = dir($dir);
	while($entry = $d->read()) {
		preg_match('/(.+)\.(php|inc)$/', $entry, $matches);
		
		if($entry != '.' && $entry != '..' && $entry != '.svn') {
			if(is_dir($dir.$entry)) {
				require_once_dir($dir.$entry);
			}
			elseif(
				is_file($dir.$entry) && 
				preg_match('/(.+)\.(php|inc)$/', $entry, $matches) &&
				!function_exists($matches[1])
			) {
				require_once($dir.$entry);
			}
		}
	}
}

?>