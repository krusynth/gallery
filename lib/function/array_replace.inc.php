<?php

if(!function_exists('array_replace')) {
	function array_replace($array, $element, $replacement) {
		if(($index = array_search($element, $array)) !== false) {
			array_splice($array, $index, 1, $replacement);
		}
		
		return $array;
	}
}

?>