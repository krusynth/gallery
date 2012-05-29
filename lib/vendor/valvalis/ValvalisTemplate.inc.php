<?php

require_once(ValvalisConfig::path('valvalis').'Valvalis.inc.php');

	/* 
	
	class ValvalisTemplate
	
	Author: Bill Hunt (bill.hunt@valvalis.org)
	
	Purpose : Template wrapper class. 
	
	Change Log:
	2005.09.07 - Created file from ValvalisTemplate.
	2009.11.01 - Made more user-friendly
	
	Usage: 
	
		$template_data = array(
			'title' => 'My Page', // Replaces $title in template
			'content' => 'Body Text Lorem Ipsum' // Replaces $content in template
		);
	
		$template = new ValvalisTemplate(TEMPLATE_DIR.'/my_template.php');
		print $template->show($template_data); 
		
		// Note that you can re-use the same template multiple times and just change the data:
		$template = new ValvalisTemplate(TEMPLATE_DIR.'/row_template.php');
		
		print $template->show($row_data1); 
		print $template->show($row_data2); 
		print $template->show($row_data3); 
	
	Note:
		This class may act unexpectedly when short_open_tag is on and dealing with XML, due to 
		XML's opening '<?xml' string.
	
	*/
	class ValvalisTemplate {
		
		private $__xml_replacements;
		private $__file_data;
		
		// Takes the data that will be used within the eval() below
		// e.g. $this->rows, $this->count, etc.
		
		public function ValvalisTemplate($template_file) {
			$this->__construct($template_file);
		}
		
		public function __construct($template_file) {
			// Short tags cause havok with XML declaration.
			// We do what we can to mitigate the issue:
			if(ini_get('short_open_tag')) {
				Valvalis::register_event_handler('template_pre_process_content', 'remove_xml_tags', array($this, 'remove_xml'));
				Valvalis::register_event_handler('template_post_process_content', 'add_xml_tags', array($this, 'add_xml'));				
			}
		
			if(file_exists($template_file)) {
			    $this->__file_data = file_get_contents($template_file);
			}
			else {
				trigger_error(E_USER_ERROR, 'Unable to find template file "'.$template_file.'" in ValvalisTemplate');
			}
		}
		
		// Takes the actual content of the template, not a path.
		public function show($__valvalis_data = array()) {
			
			$__valvalis_file_data = $this->__file_data;
			
			// Make the data local.
			@extract($__valvalis_data);
			
			$__valvalis_file_data = Valvalis::event('template_pre_process_content', $__valvalis_file_data);
			
			// You can eval() html, but you have to fake out the parser by putting 
			// a closing php tag before it.  Adding a trailing opening tag will cause an error.
			ob_start();
			eval('?>'.$__valvalis_file_data);
			$__valvalis_file_data = ob_get_contents();
			ob_end_clean();
			
			$__valvalis_file_data = Valvalis::event('template_post_process_content', $__valvalis_file_data);
			
			return $__valvalis_file_data;
		}

		public function preg_catch_xml($matches) {
			$index_id = count($this->__xml_replacements);
			
			$index_name = '<!--REPLACE_XML_'.$index_id.'-->';
			
			$this->__xml_replacements[$index_name] = $matches[0];
			
			return $index_name;
		}
		
		public function remove_xml($string) {
			// Before we can eval() html, we must remove any xml tags within it.
			$valvalis_expression = '/<\\?xml(.*?)\\?>/s';
			return preg_replace_callback($valvalis_expression, array(&$this, 'preg_catch_xml'), $string);
		}
		
		// Add xml headers back
		public function add_xml($string) {
			if(is_array($this->__xml_replacements) && count($this->__xml_replacements)) {
				foreach($this->__xml_replacements as $key=>$value) {
					$string = str_replace($key, $value, $string);
				}
			}
            return $string;
		}
		
	}
	
?>