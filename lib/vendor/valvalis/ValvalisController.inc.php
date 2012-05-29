<?php

require_once(ValvalisConfig::path('valvalis').'Valvalis.inc.php');
require_once(ValvalisConfig::path('valvalis').'ValvalisTemplate.inc.php');

class ValvalisController {
	public $controller_dir;
	public $templates;
	public $page_template;
	
	public $request;
	
	public $default_function = 'index';
	public $function_parameter = 'fn';
	
	public $authorized = false;
	
	public $executed = false;
	
	public $included = false;

	public function __construct($args = array()) {
		foreach($args as $field=>$value) {
			$this->$field = $value;
		}
	}
	
	public function RequiresAuthorization() { return true; }
	
	// Routes added below for temporary compatability.
	public function execute($args = array(), $routes = array()) {
		if($args['include']) {
			$this->included = $args['include'];
		}
	
		$base_fn = $this->route_request($args, $routes);
		
		unset($args['fn']);
		
		if($base_fn) {
			$fn = 'execute_'.$base_fn;
			$this->page_template = $this->controller_dir.'/templates/'.$base_fn;

			$data = $this->$fn($args, $routes);

			if(
				$args['format'] 
				&& preg_match('/^[a-z]+$/', $args['format']) 
				&& file_exists($this->page_template . '.' . $args['format'] . '.php')
			) {
				$format = $args['format'];
			}
			else {
				$format = 'html';
			}
			
			$this->page_template .= '.'.$format;

			$this->page_template .= '.php';
		}

		if(!is_array($data)) {
			$data = array(
				'content' => $data
			);
		}

		if(!$this->executed) {
			$this->executed = true;
			
			$return_value = $this->apply_template_wrappers($data, $args);
		}
		else {
			$return_value = $data['content'];
		}

		return $return_value;
	}
	
	protected function default_format(&$args, $default)
	{
		if(!array_key_exists('format', $args))
			$args['format'] = $default;
	}
	
	public function route_request(&$args = array(), &$routes = array()) {
		if(
			$args[$this->function_parameter] && 
			method_exists($this, 'execute_'.$args[$this->function_parameter])
		) {
			$base_fn = $args[$this->function_parameter];
		} elseif($this->default_function) {
			$base_fn = $this->default_function;		
		}
		
		return $base_fn;
	}
	
	public function redirect($location, $args = array()) {
		if(is_array($location)) {
			// handle ($object, $fn) syntax
			if(is_object($location[0]) && is_string($location[1])) {
				$location_url = $location[0]->get_base_url().'/'.$loaction[1].'/';
			}
		}
		elseif(is_string($location)) {
			$location_url = $location;
		}
		
		if(is_array($args)) {
			foreach($args as $key=>$value) {
				$arg_list[] = urlencode($key).'='.urlencode($value);
			}
			
			$arg_list = '?'.join('&', $arg_list);
		}
		
		session_write_close();
		header('Location: '.$location_url.$arg_list);
		exit();
	}

	public function apply_template_wrappers(&$data = array(), &$args = array()) {
		$data['args'] = $args;
		
		// Show the page-specific template
		if($this->page_template) {
			$data['content'] = $this->show($this->page_template, $data);
		}

		// Show the master template.
		if(!$this->included && $this->templates['main']) {
			$content = $this->show(TEMPLATE_DIR . $this->templates['main'], $data); 
		}
		else {
			$content = $data['content'];
		}
		
		return $content;
	}
	
	protected function show($template_file, $template_data = array()) {	
		if(file_exists($template_file)) {
			$this->get_helpers();
			
			$template = new ValvalisTemplate($template_file);
			return $template->show($template_data);
		}
	}
	
	public function get_helpers() {
		$helper_dir = TEMPLATE_DIR.'helpers/';
		$helper_file_types = array('.php', '.inc');
		
		$this->include_dir($helper_dir);
		
		$this_dir = $helper_dir.get_class($this).'/';
		$this->include_dir($this_dir);
	}
	
	public function include_dir($dirname, $recursive = false, $file_types = array('.php', '.inc')) {
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
	
	public function get_base_url() {
		if($this->authorized) {
			$url .= '/authorized';
		}
		
		$class = preg_replace('/Controller$/', '', get_class($this));
		
		$url .= '/' . snakeize_camel($class) . '/';
		
		return $url;
	}
	
	public function valid_id($id) {
		// One could also add checks in here to see if it's a real id.
		if(preg_match('/^[0-9]+$/', $id)) {
			return true;
		}
	}

}

?>