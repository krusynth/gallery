<?php


/*
	Valvalis Data Access Object
	
	Author: Bill Hunt (bill.hunt@valvalis.org)
	
	Purpose: 
	Prototype interface for a database table.  
	
	Change Log:
	07.27.04 - Created file, imported functions 
			   from old DBInterface class.
	01.08.05 - Completely rewrote class to use querybuilder.
			 - Added query caching.
	01.10.06 - Modified for use with ADODB.
			 _ Changed return results in DB query functions
			   to match expected.
	01.30.06 - Modified to allow 'data' argument in db_update 
			   and db_insert functions.
	03.12.09 - Rewrote most of class, PHP5 compliant.
	
	Usage:
	
	// You'll need to define DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, and DB_DATABASE elsewhere.
	
	$table = new DataAccessObject('my_table');
	$table->select();
	
	while($row = $table->get_row()) {
		$rows[] = $row;
	}
	
*/

	class DataAccessObject {
		public $table = '';
		public $id = ''; // Defaults to primary key from get_column_info()
		public $idColumn = ''; // properly-quoted and tableized column name
		
		public $queryHandle;
		
		protected $columnInfo;
		
		public $nonQuotedTypes = array('int', 'tinyint', 'bigint', 'float', 'decimal');
		
		public $nonQuotedFunctions = array('NOW()', 'CURRENT_TIMESTAMP()', 'NULL', 'null');
		
		public $safe_order_dirs = array('ASC', 'DESC');
		
		public $dieOnError = false;
		
		public static $dbh = array(); // Database Handle
		
		public function __construct($table = '', $id = '', $connection_args = array()) {
			// Use default connection args if we don't have any.
			if(!$connection_args) {
				$this->connect(
					array(
						'hostname' => DB_HOSTNAME,
						'username' => DB_USERNAME,
						'password' => DB_PASSWORD,
						'database' => DB_DATABASE
					)
				);
			}
			
		
			if($table) {
				$this->table = $table;
			}
			if($id) {
				$this->id = $id;
				$this->idColumn = $id;
			}
			
			if($this->table) {
				$this->columnInfo = $this->get_column_info();
			} else {
				trigger_error('dataAccessObject needs a table!', E_USER_ERROR);
			}
		}
		
		public function connect($connection_args) {
			// Create a unique fingerprint for this connection string.
			// This way, we can have connections to multiple DBs
			$connection_token = md5(
				$connection_args['hostname'] . ':' .
				$connection_args['username'] . ':' .
				$connection_args['password'] . ':' .
				$connection_args['database']
			);
			
			// Re-use static connections.  Reduces DB connection load.
			if(!self::$dbh[$connection_token]) {
				$handle = mysql_connect($connection_args['hostname'], $connection_args['username'], $connection_args['password']);
				mysql_select_db($connection_args['database'], $handle);
				
				// Don't set the handle until we know everything is ok.
				self::$dbh[$connection_token] = $handle;
				
				// Todo: Add error checking here.
			}
			
			return self::$dbh[$connection_token];
		}
		
		public function __destruct() {
			if($this->queryHandle) {
				$this->free_result($this->queryHandle);
			}
		}
		
		public function get_column_info() {
			$handle = mysql_query('DESC '.$this->table);
			
			while($row = $this->get_result_hash($handle)) {
				$columnInfo[$row['Field']] = $row;
				$columnInfo[$row['Field']]['short_type'] = preg_replace('/\(.*?\).*/', '', $columnInfo[$row['Field']]['Type']);
				
				if(!$this->id && $row['Key'] == 'PRI') {
					$this->id = $row['Field'];
					$this->idColumn = $this->quote_field($this->table).'.'.$this->quote_field($row['Field']);
				}
			}
			
			return $columnInfo;
		}
		
		public function free_result() {
			mysql_free_result($this->queryHandle);
			unset($this->queryHandle);
		}
		
		public function set_query_handle($handle) {
			if($handle) {
				if($this->queryHandle) {
					$this->free_result($this->queryHandle);
				}	
				
				$this->queryHandle = $handle;
			}
		}
		
		public function select($args = array()) {
			if(is_string($args) && strtoupper(substr(trim($args), 0, 7)) == 'SELECT ') {
				$query = $args;
			}
			elseif(is_array($args)) {
				if(is_array($args['fields'])) {
					$fields = join(', ', $args['fields']);
				} elseif(strlen($args['fields'])) {
					$fields = $args['fields'];
				} else {
					$fields = $this->table.'.*';
				}
				
				if($args['where']) {
					if(is_array($args['where'])) {
						$args['where'] = array_diff($args['where'], array(''));
						if(count($args['where'])) {
							$where = ' WHERE '. join(' AND ', $args['where']);
						}
					} elseif(strlen($args['where'])) {
						$where = ' WHERE '.$args['where'];
					}
				}
				
				if($args['group']) {
					if(is_array($args['group'])) {
						$group = ' GROUP BY '.join(', ', $args['group']);
					} elseif(strlen($args['group'])) {
						$group = ' GROUP BY ' . $args['group'];
					}
				}
				
				if($args['having']) {
					if(is_array($args['having'])) {
						$args['having'] = array_diff($args['having'], array(''));
						if(count($args['having'])) {
							$having = ' HAVING '. join(' AND ', $args['having']);
						}
					} elseif(strlen($args['having'])) {
						$having = ' HAVING '.$args['having'];
					}
				}
				
				if($args['joins']) {
					if(is_array($args['joins'])) {
						$joins = ' '.join(' ', $args['joins']);
					}
					elseif(strlen($args['joins'])) {
						$joins = ' '.$args['joins'];
					}
				}
				
				if($args['distinct']) {
					$distinct = 'DISTINCT ';
				}
				
				
				if($args['order']) {
					if(is_array($args['order'])) {
						$args['order'] = array_diff($args['order'], array(''));
						if(count($args['order'])) {
							$order = ' ORDER BY '.join(', ', $args['order']);
						}
					} elseif(strlen($args['order'])) {
						$order = ' ORDER BY ' . $args['order'];
					}
				}
				
				if($args['limit']) {
					$limit = ' LIMIT '.$args['limit'];
				}
				
				
				$query = 'SELECT '.$distinct.$fields.' FROM '.$this->table.$joins.$where.$group.$having.$order.$limit;
			}

			if(is_array($args) && $args['debug']) {
				return $this->debug($query);
			} else {
				$this->set_query_handle($this->query($query, $args['cache']));

				$count = $this->count_rows($this->queryHandle);
			}
			
			return $count;
		}
		
		function query($query, $cache = false){
			$cache = false;
			if($cache) {
				$handle = mysql_query($query, $cache);
			} else {
				$handle = mysql_query($query);
				
				$error = mysql_error();
				if($error) {
					trigger_error("ERROR: '$error' in query '$query'", E_USER_ERROR);
					
					if($this->dieOnError) {
						die();
					}
				}
			}

			return $handle;
		}
		
		public function debug($string) {
			return $string;
		}
		
		public function count_rows($handle) {
			if($handle) {
				return mysql_num_rows($handle);
			}
		}
		
		public function get_result_hash($handle) {
			return @mysql_fetch_assoc($handle);
		}

		public function get_row($queryHandle = null) {
			if(!$queryHandle) {
				$queryHandle = $this->queryHandle;
			}
		
			if($queryHandle) {
				return $this->get_result_hash($queryHandle);
			}
		}
		
		public function get_row_by_id($id, $fields = null, $cache = null) {
			
			if(strlen($id)) {
			
				$this->select(
					array(
						'fields' => $fields,
						'where' => array(
							$this->idColumn.' = '.$id
						),
						'cache' => $cache
					)
				);
				
				$return_value = $this->get_row();
				
				if(!is_array($fields) && strlen($fields)) {
					$return_value = $return_value[$fields];
				}
			}
			
			return $return_value;
		}
		
		public function update($args = array()) {
			if(is_array($args['data'])) {
		
				if($args['where']) {
					if(is_array($args['where'])) {
						$where = ' WHERE '. join(' AND ', array_diff($args['where'], array('')));
					} elseif(strlen($args['where'])) {
						$where = ' WHERE '.$args['where'];
					}
				}
				
				if($args['limit'] && (int)$args['limit'] == $args['limit'])
					$limit = ' LIMIT ' . (int)$args['limit'];
				
				$data = $this->process_data($args['data'], $args['override_quoting']);
				
				if($args['priority']) {
					switch ($args['priority']) {
						case 'low' :
						case 'low_priority' :
						case 'lowpriority' :
							$priority = 'LOW_PRIORITY';
							break;
					}
				}
				
				$query = 'UPDATE '.$priority.' '.$this->table.$data.$where.$limit;
				
				if($args['debug']) {
					return $this->debug($query);
				} else {
					$this->query($query);
		
					$count = mysql_affected_rows();
				}
			} else {
				trigger_error('No update data passed to '.get_class($this), E_USER_WARNING);
			}
			
			
			return $count;
		}
		
		public function insert($args = array()) {
			if(is_array($args['data'])) {
				$data = $this->process_data($args['data'], $args['override_quoting']);				

				if($args['priority']) {
					switch ($args['priority']) {
						case 'low' :
						case 'low_priority' :
						case 'lowpriority' :
							$priority = 'LOW_PRIORITY';
							break;
							
						case 'delayed' :
						case 'delayed_priority' : 
						case 'delayedpriority' : 
							$priority = 'DELAYED';
							break;
						
						case 'high' :
						case 'high_priority' :
						case 'highpriority' :
							$priority = 'HIGH_PRIORITY';
							break;
						
					}
				}

				$query = 'INSERT '.$priority.' INTO '.$this->table.$data;
				
				if($args['debug']) {
					return $this->debug($query);
				} else {
					$this->query($query);
					
					$insertId = mysql_insert_id();
				}
			} else {
				trigger_error('No insert data passed to '.get_class($this), E_USER_WARNING);
			}
			
			return $insertId;	
		}
		
		public function process_data($data, $overrides = array()) {
			// Quick and dirty quoting schema
			foreach($data as $field => $value) {
				if($this->columnInfo[$field]) {
					if(!in_array($value, $this->nonQuotedFunctions) && !is_null($value) && !in_array($this->columnInfo[$field]['short_type'], $this->nonQuotedTypes) && (!is_array($overrides) || !in_array($field, $overrides))) {
						### TODO : Need a catch to make sure numerics are really numeric!
						$value = $this->quote_string($value);
					} else {
						//sometimes we've got empty fields here ... it ends up breaking the SQL. 
						//if we don't assign the var something the SQL will break.
						if (is_null($value) OR '' === $value) {
							$value = 'NULL';
						}
						//Nonquoted types are all numeric currently.  
						elseif(in_array($this->columnInfo[$field]['short_type'], $this->nonQuotedTypes)) {
							if(!preg_match('/^[0-9.]+$/', $value) && 'NULL' != strtoupper($value)) {
								trigger_error("Error parsing value '$value' as a number in field '$field'.", E_USER_ERROR);
							}
						}

					}
					$data_fields[] = $this->quote_field($field).' = '.$value;
				}
			}
			
			if(count($data_fields)) {
				$returnData = ' SET '.join(', ', $data_fields);
			}
			return $returnData;
		}

		// helper function; ex use: 'where' => array( $this->is_in($field, $values) )
		public function is_in($field, $values)
		{
			if(is_array($values))
				return $field . ' IN (' . implode(',', $this->quote_array($values)) . ')';
			else
				return $field . '=' . $this->quote_string($values);
		}

		// helper function; ex use: 'where' => array( $this->is_not_in($field, $values) )
		public function is_not_in($field, $values)
		{
			if(is_array($values))
				return $field . ' NOT IN (' . implode(',', $this->quote_array($values)) . ')';
			else
				return $field . '!=' . $this->quote_string($values);
		}
		
		// Use field quoting to avoid nasty field-name/reserved-keyword collisions.
		public function quote_field($string) {
			return '`'.$string.'`';
		}
		
		public function quote_string($string) {
			return '"'.$this->escape_string($string).'"';
		}
		
		public function quote_array($array) {
			foreach($array as $key=>$value) {
				$array[$key] = $this->quote_string($value);
			}
			
			return $array;
		}
		
		public function escape_string($string) {
			return mysql_real_escape_string($string);
		}
		
		
		public function escape_rlike($string){
			// Crazy slash-escaping ahead!
			return preg_replace('/([?*|+\[\(.\^])/', '\\\\\\\$1', preg_replace("/([\]\$\)])/", '\\\$1', $string));
		}
		
		public function delete($args = array()) {
			if($args['where']) {
				if(is_array($args['where'])) {
					$where = ' WHERE '. join(' AND ', array_diff($args['where'], array('')));
				} elseif(strlen($args['where'])) {
					$where = ' WHERE '.$args['where'];
				}
			}
				
			if($args['priority']) {
				switch ($args['priority']) {
					case 'low' :
					case 'low_priority' :
					case 'lowpriority' :
						$priority = 'LOW_PRIORITY';
						break;
				}
			}
			
			$query = 'DELETE '.$priority.' FROM '.$this->table.$where;
			
			if($args['debug']) {
				return $this->debug($query);
			} else {
				$this->query($query);
			
				$count = mysql_affected_rows();
			}
			
			return $count;
		}
		
		public function has_field($string) {
			if($this->columnInfo[$string]) {
				return true;
			}
		}
		
		public function field_is_safe($string) {
			if(preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]$)?/', $string)) {
				return true;
			}
		}
		
		public function order_dir_is_safe($string) {
			if(in_array(strtoupper($string), $this->safe_order_dirs)) {
				return true;
			}
		}
	}

?>
