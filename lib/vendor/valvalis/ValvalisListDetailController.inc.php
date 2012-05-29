<?php

require_once(ValvalisConfig::path('valvalis').'ValvalisController.inc.php');

class ValvalisListDetailController extends ValvalisController {
	public $authorized = false;

	public function route_request(&$args = array(), $routes = array()) {
		if($this->valid_id($routes[1])) {
			$args['id'] = $routes[1];
			$base_fn = 'detail';
		}
		else {
			$base_fn = 'list';
		}
		
		return $base_fn;
	}

	public function get_detail_url($id) {
		$url = $this->get_base_url();
		
		$url .= $id . '/';
		
		return $url;
	}
	
	public function get_list_url() {
		$url = $this->get_base_url();
		
		return $url;
	}
}

?>