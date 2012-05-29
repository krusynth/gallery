<?php

function hash_to_url($base_url, $url_args = array()) {
	$url = $base_url;
	
	if(strpos('?', $url) !== false) {
		list($url, ) = split('/?', $url);
	}

	foreach($url_args as $key=>$value) {
		if(is_array($value)) {
			foreach($value as $current_value) {
				$args[] = $key.'[]='.$current_value;
			}
		}
		else {
			$args[] = $key.'='.$value;
		}
	}
	
	if($args) {
		$arg_string = join('&', $args);
		$url .= '?'.$arg_string;
	}
	
	return $url;
}

function hash_to_args($url_args = array())
{
	foreach($url_args as $key=>$value)
	{
		if(is_array($value))
		{
			foreach($value as $current_value)
				$args[] = $key . '[]=' . $current_value;
		}
		else
			$args[] = $key . '=' . $value;
	}
	
	if(count($args) > 0)
		$arg_string = implode('&', $args);
	else
		$arg_string = '';
		
	return $arg_string;
}

?>