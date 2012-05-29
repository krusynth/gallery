<?php

function is_valid_integer($intval) {
	if(
		is_int($intval) ||
		(is_string($intval) && preg_match('/^[0-9]+$/', $intval))
	) {
		return true;
	}
	else {
		return_false;
	}
		
}

function is_valid_int($intval) {
	return is_valid_integer($intval);
}

?>