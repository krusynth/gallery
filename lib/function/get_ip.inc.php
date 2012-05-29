<?php

	###
	# get_ip()
	#
	# Author: Bill Hunt <bill.hunt@valvalis.org>
	#
	# Purpose: Gets the IP of the client requesting the current page.
	#
	# Change Log:
	# 08.08.01 - Imported from contact page.
	###
	
	function get_ip() {
		# make sure to get the *right* IP, 'cause sometimes you 
		# might get the IP of the ISP Cache Server. When this happens, 
		# HTTP_X_FORWARDED_FOR is set.
		$ip = getenv(REMOTE_ADDR);
		if (getenv(HTTP_X_FORWARDED_FOR)) { $ip = getenv(HTTP_X_FORWARDED_FOR); }
		return $ip;
	}
	
?>