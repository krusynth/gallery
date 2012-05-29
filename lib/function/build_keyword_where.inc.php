<?php

function build_keyword_where($keywords, $search_fields = array(), $glue = 'AND') {	
	if($keywords) {
		
		// Tokenize the search string.
		
		//$keywords = split(' ', $html->param('keyword'));
		
		// The function below handles quoted items.
		if(!is_array($keywords)) {
			$keywords = tokenize(html_decode($keywords));
		}
		
		foreach($keywords as $keyword) {
			// Add slash escaping
			$keyword = escape_rlike($keyword);
		
			unset($keyword_where);
			
			// Handle wildcards
			
			// At this point, *s have a slash in front of them.
			if(substr($keyword, 0, 2) == '\*') {
				$keyword = '.*'.substr($keyword, 2);
			}
			else {
				// Add front word boundary
				$keyword = '[[:<:]]'.$keyword;
			}
			
			if(substr($keyword, -2, 2) == '\*') {
				$keyword = substr($keyword, 0, -2).'.*';
			}
			else {
				// Add back word boundary
				$keyword .= '[[:>:]]';
			}
			
			// Replace remaining wildcards.
			$keyword = str_replace('\*', '.*', $keyword);
			
			foreach($search_fields as $search_field) {
				//$keyword_where[] = $search_field.' LIKE '.$program->db_quote('%'.$keyword.'%');
				$keyword_where[] = $search_field.' RLIKE "'.$keyword.'"';
			}
			if($keyword_where) {
				$where[] = '('.join(' OR ', $keyword_where).')';
			}
		}
	}
	
	if($where) {
		$where = '('.join(' '.$glue.' ', $where).')';
	}
	//echo $where;
	return $where;
	
}

?>
