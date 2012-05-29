<?php

class ValvalisConfig {
	public $paths = array();
	public $conf = array();

	static $instance;

	public function __construct($base_dir) {

		
		$this->set_paths($base_dir);
		$this->import_libraries();
		$this->import_config();
		
	}

	public static function create($base_dir) {


		if(!ValvalisConfig::$instance) {
			ValvalisConfig::$instance = new ValvalisConfig($base_dir);
		}
		
		return ValvalisConfig::$instance;
	}
	
	public function set_paths($base_dir) {
		if(!$base_dir) {
			$base_dir = dirname(dirname(dirname(dirname(__FILE__)))).'/';
		}
	
		$this->paths['base'] = $base_dir;

		$this->paths['conf'] = $base_dir .'conf/';
		$this->paths['lib'] = $base_dir .'lib/';
		$this->paths['vendor'] = $base_dir .'lib/vendor/';
		$this->paths['valvalis'] = $base_dir .'lib/vendor/valvalis/';
		$this->paths['function'] = $base_dir .'lib/function/';
		$this->paths['app'] = $base_dir.'app/';
		$this->paths['upload'] = $base_dir .'upload/';
		$this->paths['logs'] = $base_dir .'logs/';
	}
	
	public function import_libraries() {
		require_once($this->paths['function'].'require_once_dir.inc.php');
		
		// Add all of our functions
		require_once_dir($this->paths['function']);
		
		// Add the Valvalis base
		require_once_dir($this->paths['valvalis']);
	}

	
	public function import_config() {
		$d = dir($this->paths['conf']);
		
		while(($entry = $d->read()) !== false) {
			if(substr($entry, -5, 5) == '.json') {
				$json_data = file_get_contents($this->paths['conf'].$entry);
				$name = substr($entry, 0, -5);

			}
		}
	}
	
	public static function path($path = '') {
		$instance = ValvalisConfig::create();
		
		if(strlen($path)) {
			return $instance->paths[$path];
		}
		else {
			return $instance->paths;
		}
		
	}
}