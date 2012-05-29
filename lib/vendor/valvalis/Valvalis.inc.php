<?php

class Valvalis {
	public function __construct() {
		Valvalis::load_plugins();
	}

	static public function load_plugins() {
		if(defined('VALVALIS_DIR') && file_exists(constant('VALVALIS_DIR').'/plugin/')) {
			$plugin_dir = constant('VALVALIS_DIR').'/plugin/';
		}
		elseif(file_exists( dirname(__FILE__).'/plugin/' )) {
			$plugin_dir = dirname(__FILE__).'/plugin/';
		}
	
		if($plugin_dir) {		
			$d = Dir($plugin_dir);
			while(($file = $d->read()) !== false) {
				if(
					file_exists($plugin_dir.$file.'/') &&
					is_dir($plugin_dir.$file.'/') &&
					file_exists($plugin_dir.$file.'/'.$file.'.inc.php')
				) {
					require_once($plugin_dir.$file.'/'.$file.'.inc.php');
				}
			}
			
		}
	}

	static $event_handlers = array();

	// Prereqs aren't implemented yet.
	static public function register_event_handler($event_name, $callback_name, $callback, $prereqs = array()) {
		Valvalis::$event_handlers[$event_name][$callback_name] = array(
			'callback' => $callback
		);
	}
	
	static public function event($event_name, $args) {
		if($callbacks = Valvalis::$event_handlers[$event_name]) {
			foreach($callbacks as $callback_name => $callback) {
				$args = call_user_func($callback['callback'], $args);
			}
		}
		
		return $args;
	}
	
	static public function include_dir($dirname, $recursive = false, $file_types = array('.php', '.inc')) {
		if(file_exists($dirname) && is_dir($dirname)) {
			$d = Dir($dirname);
			
			while(($file = $d->read()) !== false) {
				if(is_dir($file) && !in_array($file, array('.', '..')) && $recursive) {
					$this->include_dir($dirname.$file.'/');
				}
				if(is_file($dirname.$file) && in_array(substr($file, -4), $file_types)) {
					require_once($dirname.$file);
				}
			}
		}
	}
}

//new Valvalis();

?>