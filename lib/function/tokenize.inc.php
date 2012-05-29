<?php

/* String tokenizing function.  Handles quoted strings, as well.  Useful for search queries. */

function tokenize($string) {
	$buffer = '';
	$keywords = array();
	$quote_string = 0;
	for($i = 0; $i< strlen($string); $i++) {
		if($string[$i] == '"') {
			if(strlen($buffer)) {
				$keywords[] = $buffer;
				unset($buffer);
			}
			$quote_string = ! (int) $quote_string;

		}
		else {
			if($string[$i] == ' ' && !$quote_string) {
				if(strlen($buffer)) {
					$keywords[] = $buffer;
					unset($buffer);
				}
			}
			else {
				$buffer .= $string[$i];
			}
		}
	}
	
	if(strlen($buffer)) {
		$keywords[] = $buffer;
	}
	
	return $keywords;
}

?>