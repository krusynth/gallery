<?php

	/*  index.php
	
	Written by Bill Hunt (bill@krues8dr.com)
	
	Created: 10.17.07
	
	Purpose: Master Control Program - routes all requests to the proper controllers
		 
	Important: 
	
	Change Log:
	
	*/ 
	
	$base = dirname(__FILE__);
	require_once($base.'/lib/vendor/valvalis/ValvalisConfig.inc.php');

	$url = $_SERVER['REQUEST_URI'];

	$routes = array_values(array_filter(explode('/', $url)));
	
	// Also should go in the controller.
	$controller_part = $routes[0];
	$controller_name = $controller_part.'_actions';
	$controller_path = ValvalisConfig::path('app').$controller_part;
	$function_part = $routes[1];
	
	
	// The preg_match below prevents anyone from doing anything really nasty with the url.	
	if(
		preg_match('/^([a-zA-Z0-9_]+)$/', $controller_part) && 
		file_exists($controller_path.'/'.$controller_name.'.php') &&
		is_file($controller_path.'/'.$controller_name.'.php')
	){
		
		require_once($controller_path.'/'.$controller_name.'.php');
		
		$controller_name = camelize_snake($controller_name);
		
		$controller = new $controller_name(
			array(
				'controller_dir' => $controller_path
			)
		);
	
		$args = $_REQUEST;
		
		$controller->request['request'] = $_REQUEST;
		$controller->request['get'] = $_GET;
		$controller->request['post'] = $_POST;
		$controller->request['cookie'] = $_COOKIE;
		if(preg_match('/^([a-zA-Z0-9_]+)$/', $function_part)) {

			$args[$controller->function_parameter] = $function_part;
		}

		print $controller->execute($args, $routes);
		
	}
	else {
		trigger_error("Error initializing '$controller_name'", E_USER_ERROR);
	}

?>
