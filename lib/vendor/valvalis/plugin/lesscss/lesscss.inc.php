<?php
	
require_once('lessc.inc.php');

class LessCss {

	static public function register_plugin() {
		Valvalis::register_event_handler('template_pre_process_content', 'replace_less_css', array('LessCss','replace_less_css'));
	}
	
	static public function replace_less_css($content) {
		// Fix this to add backreferences
		
		preg_match_all('/\<link([^>]+)rel=("|\')stylesheet\/less("|\')([^>]*)\/?\>/', $content, $matches);
		if($matches && $matches[0]) {
			foreach($matches[0] as $match) {
				var_dump($match);
				if(preg_match('/href=("|\')([^"\']+)\.less("|\')/', $match, $href_matches)) {
					var_dump($href_matches);
				}
			}
		}
		
		return $content;
	}
	
}

LessCss::register_plugin();


?>