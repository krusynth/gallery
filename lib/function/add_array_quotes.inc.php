<?php

function add_array_quotes(&$array) {
	array_walk($array, 'add_quotes_callback');
	
	return $array;
}

function add_quotes_callback(&$value, $key) {
	$value = '"'.$value.'"';
}

?>