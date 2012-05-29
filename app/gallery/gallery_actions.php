<?php

class GalleryActions extends ValvalisController {
	function execute_index($args = array()) {
		return $content;
	}
	
	function execute_post($args = array()) {
		$upload_dir  = '/tmp/';
		$num_files = count($_FILES['user_file']['name']);
		
		foreach($_REQUEST['files'] as $name => $file_data) {
			$file_data = base64_decode(str_replace('data:image/jpeg;base64', '', $file_data));
			
			$fh = fopen('/tmp/'.$name, 'w+');
			
			fputs($fh, $file_data);
			
			fclose($fh);
		}
		
		return $message;

	}
	
	
}