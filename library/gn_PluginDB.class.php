<?php
if(!class_exists('gn_PluginDB')):

class gn_PluginDB {
  var $db;
  var $mainTable;
  var $dbVersion;
  var $tableDefinitions;
  var $debug = true;
  var $cache = array();
  var $do_cache = true;
  var $localizeNames = true;



  var $DATE_COLS = array ();


	function __construct($pluginprefix) {

	  
	  $this->pluginprefix =$pluginprefix;

	    $this->init();
       

   }
   function init() {
   		global $wpdb;
   		$this->dbVersion = "1.0";
   		$this->db = $wpdb;
   		$this->initTableDefinitions();
      


	}


/**
 *
 * @param
 * @return
 */



function showdebug ($str) {

	if ($this->debug) {
		error_log("gnDB log:".$str);
	}
}

function showdebugObject($object) {
	ob_start();
	print_r($object);
	$str=ob_get_clean();
	$this->showdebug($str);
}

  function get_cached_results($sql, $output=OBJECT) {

  	$key = $sql.":".$output;

  	if (array_key_exists ($key, $this->cache)) {
  		$this->showdebug("Cached result:".$sql);

  		$results = $this->cache[$key];
  	}
  	else {
  		//$this->showdebug("$key not in cache");
  		//$this->showdebugObject($this->cache);
  		$this->showdebug("Queried result:".$sql);
  		$results = $this->db->get_results($sql, $output);
  		$this->cache[$key] = $results;
  	}
  	return ($results);
}


  function get_results($sql, $output=OBJECT) {

  	if ($this->do_cache && preg_match('/^select /i',$sql)) {
  		return ($this->get_cached_results($sql,$output));
  	}
  	else {
  		return $this->db->get_results($sql, $output);
  	}
  }

function getLocalTimeResults ($sql, $output=OBJECT) {
	// Sets connection time zone to blog's time zone 
	// for retrieval of timestamp columns.

	$serverTimezone = $this->db->get_var("select @@session.time_zone");
	$offset = get_option("gmt_offset");

	$this->db->query($this->db->prepare("set time_zone=%s", $this->formatTimezoneOffset($offset)));		
	$results = $this->get_results($sql, $output);		
	$this->db->query($this->db->prepare("set time_zone=%s", $serverTimezone));

	return $results;
}

function formatTimezoneOffset ($offset) {
	$sign = $offset >= 0 ? "+" : "-"; // mysql expects a sign for a zero timezone offset.
	$hours = floor(abs($offset));
	$minutes = (abs($offset)*60) % 60;
	
	return $sign . $hours . ":" . sprintf('%02d', $minutes);
}


function dbExecute($sql) {
  	$this->showdebug("dbExecute:".$sql);
	return($this->db->query($sql));
}

function dbSafeExecute ($sql) {
	$result = $this->dbExecute($sql);

	if($result === false) {
		throw new ErrorException(mysql_error(), mysql_errno());
	}
	else return $result;
}

function formatFieldList($array) {
	$sql="";
	foreach ($array as $value) {
		$sql.=$sql?',':'';
		if (in_array($value, $this->DATE_COLS)) {
			$sql.="DATE_FORMAT($value,'%Y-%c-%e') as $value";
		}
		else {
			$sql.=$value;
		}
	}
	return($sql);
}



function tablePrefix () {
  return(($this->localizeNames?$this->db->prefix:"") .$this->pluginprefix."_");
}

function var_to_string($object) {
	ob_start();
	var_dump($object);
	$str=ob_get_contents();
	ob_end_clean();
	return($str);
}


function replaceReferences($matches) {
  return ("references ".$this->tableName($matches[1]));

}

function replaceKey($matches) {
  return ("KEY ".$this->tableName($matches[1])." ");

}

function replaceConstraint($matches) {
  return ("CONSTRAINT ".$this->tableName($matches[1])." ");

}



	function createTable ($name) {

		$this->showdebug("Creating $name table");

		$sql ="CREATE TABLE ".$this->tableName($name)." ";

		$sql.=$this->tableDefinitions[$name];

		$sql = $this->replaceTableRefs($sql);

		error_log("create table sql: $sql");

		$this->db->show_errors();

		$this->db->query($sql);

	}


	function tablesInstalled() {
		reset($this->tableDefinitions); // make sure array pointer is at first element
		$firstTable = key($this->tableDefinitions);
		$firstTableName = $this->tableName($firstTable);
		return ($this->db->get_var("show tables like '$firstTableName'") == $firstTableName);

	}

	function tableExists ($key) {
		$tableName = $this->tableName($key);
		return ($this->db->get_var("show tables like '$tableName'"));
	}
	
	function insureTables () {

		$this->parent->GlobalMSG.="<br/>Checking installation";

		foreach($this->tableDefinitions as $name => $sql) {

			$this->showdebug($name);
			if (!$this->tableExists($name)) {
				$this->createTable($name);
			}
			else {
				$this->showdebug("$name table exists");
			}
		}



	}


	function upgradeTables() {}

	function initTableDefinitions() {




	}



function quoteString($str) {
	if (is_array($str)) {
		$str=implode(",",$str);
	}

	return $this->db->prepare('%s', stripslashes($str));	
	
}

function quoteInteger ($str){

	return $this->db->prepare('%d', $str);
}

function quoteNumericList ($str) {
	$values = preg_split('/,\s*/', $str);
	$format = implode(',', array_fill(0, count($values), '%d'));
	return $this->db->prepare($format, $values);
}

function filterArray($source,$filter) {
	$outArray = array();

	foreach($source as $key=>$value){
		if(in_array($key, $filter)){
			if (is_array($value) && count($value)==1) {
				$value=$value[0];
			}
			$outArray[$key] = $value;
		}
	}
	return($outArray);
}

function quoteData ($pageData, $nullEmptyString=false) {


	foreach($pageData as $key=>$value){
			if($nullEmptyString) {
				$pageData[$key] = $pageData[$key]==='' ? 'null' : $this->quoteString($value);
			}
			else $pageData[$key] = $this->quoteString($value);

	}

	return $pageData;

}

function loadValues($fieldArray, $valueArray) {

	$sql='';
	$mappedArray=$this->filterArray($valueArray, $fieldArray);


	// DS: Converting empty strings to null to prevent errors on integer fields.

	$quotedArray = $this->quoteData ($mappedArray, true);

	foreach ($quotedArray as $key=>$value) {
		if ($key!='id') {
			$sql.=$sql?',':'';
			$sql.=" $key=$value ";
		}
	}
	return($sql);
}


function valueList($fieldArray, $valueArray) {
	$sql='';
	$mappedArray=$this->filterArray($valueArray, $fieldArray);
	$quotedArray = $this->quoteData ($mappedArray);


	$outputArray= array();
	foreach ($quotedArray as $key=>$value) {
		if ($key!='id') {
			$outputArray[] =$value;
		}
	}
	return(" (".implode("," ,$outputArray).") ");
}

function columnList($fieldArray, $valueArray) {
	$sql='';
	$mappedArray=$this->filterArray($valueArray, $fieldArray);
	$quotedArray = $this->quoteData ($mappedArray);

	$outputArray= array();
	foreach ($quotedArray as $key=>$value) {
		if ($key!='id') {
			$outputArray[] =$key;
		}
	}
	return(" (".implode("," ,$outputArray).") ");
}



function getRecord($sql) {
	//$this->showdebug($sql);

  	$results=$this->get_results ($sql);

  	if (count($results)>0) {
  		return ($results[0]);
  	}

  	else return null;
}

function getRecordArray($sql) {
	//$this->showdebug($sql);

  	$results=$this->get_results ($sql,ARRAY_A);

  	if (count($results)>0) {
  		return ($results[0]);
  	}

  	else return null;
}





function dbDelta($queries, $execute = true) {
	global $wpdb;

	// Separate individual queries into an array
	if( !is_array($queries) ) {
		$queries = explode( ';', $queries );
		if('' == $queries[count($queries) - 1]) array_pop($queries);
	}

	$cqueries = array(); // Creation Queries
	$iqueries = array(); // Insertion Queries
	$for_update = array();

	// Create a tablename index for an array ($cqueries) of queries
	foreach($queries as $qry) {
		if(preg_match("|CREATE TABLE (?:IF NOT EXISTS )?([^ ]*)|", $qry, $matches)) {
			$cqueries[trim( strtolower($matches[1]), '`' )] = $qry;
			$for_update[$matches[1]] = 'Created table '.$matches[1];
		}
		else if(preg_match("|CREATE DATABASE ([^ ]*)|", $qry, $matches)) {
			array_unshift($cqueries, $qry);
		}
		else if(preg_match("|INSERT INTO ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		}
		else if(preg_match("|UPDATE ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		}
		else {
			// Unrecognized query type
		}
	}

	// Check to see which tables and fields exist
	if($tables = $wpdb->get_col('SHOW TABLES;')) {
		// For every table in the database
		foreach($tables as $table) {
			// If a table query exists for the database table...
			if( array_key_exists(strtolower($table), $cqueries) ) {
				// Clear the field and index arrays
				unset($cfields);
				unset($indices);
				// Get all of the field names in the query from between the parens
				preg_match("|\((.*)\)|ms", $cqueries[strtolower($table)], $match2);
				$qryline = trim($match2[1]);

				// Separate field lines into an array
				$flds = explode("\n", $qryline);

				//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";

				// For every field line specified in the query
				foreach($flds as $fld) {
					// Extract the field name
					preg_match("|^([^ ]*)|", trim($fld), $fvals);
					$fieldname = trim( $fvals[1], '`' );

					// Verify the found field name
					$validfield = true;
					switch(strtolower($fieldname))
					{
					case '':
					case 'primary':
					case 'index':
					case 'fulltext':
					case 'unique':
					case 'key':
					case 'constraint':
						$validfield = false;
						$indices[] = trim(trim($fld), ", \n");
						break;
					}
					$fld = trim($fld);

					// If it's a valid field, add it to the field array
					if($validfield) {
						$cfields[strtolower($fieldname)] = trim($fld, ", \n");
					}
				}

				// Fetch the table column structure from the database
				$tablefields = $wpdb->get_results("DESCRIBE {$table};");

				// For every field in the table
				foreach($tablefields as $tablefield) {
					// If the table field exists in the field array...
					if(array_key_exists(strtolower($tablefield->Field), $cfields)) {
						// Get the field type from the query
						preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
						$fieldtype = $matches[1];

						// Is actual field type different from the field type in query?
						if($tablefield->Type != $fieldtype) {
							// Add a query to change the column type
							$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
							$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
						}

						// Get the default value from the array
							//echo "{$cfields[strtolower($tablefield->Field)]}<br>";
						if(preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
							$default_value = $matches[1];
							if($tablefield->Default != $default_value)
							{
								// Add a query to change the column's default value
								$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
								$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
							}
						}

						// Remove the field from the array (so it's not added)
						unset($cfields[strtolower($tablefield->Field)]);
					}
					else {
						// This field exists in the table, but not in the creation queries?
					}
				}

				// For every remaining field specified for the table
				foreach($cfields as $fieldname => $fielddef) {
					// Push a query line into $cqueries that adds the field to that table
					$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
					$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
				}

				// Index stuff goes here
				// Fetch the table index structure from the database
				$tableindices = $wpdb->get_results("SHOW INDEX FROM {$table};");

				if($tableindices) {
					// Clear the index array
					unset($index_ary);

					// For every index in the table
					foreach($tableindices as $tableindex) {
						// Add the index to the index data array
						$keyname = $tableindex->Key_name;
						$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
						$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
					}

					// For each actual index in the index array
					foreach($index_ary as $index_name => $index_data) {
						// Build a create string to compare to the query
						$index_string = '';
						if($index_name == 'PRIMARY') {
							$index_string .= 'PRIMARY ';
						}
						else if($index_data['unique']) {
							$index_string .= 'UNIQUE ';
						}
						$index_string .= 'KEY ';
						if($index_name != 'PRIMARY') {
							$index_string .= $index_name;
						}
						$index_columns = '';
						// For each column in the index
						foreach($index_data['columns'] as $column_data) {
							if($index_columns != '') $index_columns .= ',';
							// Add the field to the column list string
							$index_columns .= $column_data['fieldname'];
							if($column_data['subpart'] != '') {
								$index_columns .= '('.$column_data['subpart'].')';
							}
						}
						// Add the column list to the index create string
						$index_string .= ' ('.$index_columns.')';
						if(!(($aindex = array_search($index_string, $indices)) === false)) {
							unset($indices[$aindex]);
							//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br />Found index:".$index_string."</pre>\n";
						}
						//else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br /><b>Did not find index:</b>".$index_string."<br />".print_r($indices, true)."</pre>\n";
					}
				}

				// For every remaining index specified for the table
				foreach ( (array) $indices as $index ) {
					// Push a query line into $cqueries that adds the index to that table
					$cqueries[] = "ALTER TABLE {$table} ADD $index";
					$for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
				}

				// Remove the original table creation query from processing
				unset($cqueries[strtolower($table)]);
				unset($for_update[strtolower($table)]);
			} else {
				// This table exists in the database, but not in the creation queries?
			}
		}
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if($execute) {
		foreach($allqueries as $query) {
			//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">".print_r($query, true)."</pre>\n";
			$wpdb->query($query);
		}
	}

	return $for_update;
}


	function getInsertID () {
		return $this->db->insert_id;
	}
	
	function quoteIdentifier ($str) {
		return '`'.str_replace('`', '', $str).'`';
	}

	function getDBID ($table, $lookupCol, $lookupVal, $idCol='id') {
		$sql = $this->db->prepare("select ". $this->quoteIdentifier($idCol) ." from ". $this->quoteIdentifier($table) ." where ". $this->quoteIdentifier($lookupCol) ."=%s", $lookupVal);

		return $this->db->get_var($sql);

	}

// Data definition and operations


	function getTableDefinition ($name) {
		return $this->tableDefinition[$name];
	}
	
	function tableDefExists ($name) {
		return array_key_exists($name, $this->tableDefinition) ? true : false;
	}

	function tableName($internalName) {
		return(($this->localizeNames?$this->db->prefix:"")  .$this->pluginprefix."_". $internalName);

	}
	


	function listSelectTableName($name) {
		$tableDefinition = $this->getTableDefinition($name);
		$listSelectTable = $tableDefinition["list_select_table"];
		
		if ($listSelectTable) {
			return $this->replaceTableRefs($listSelectTable);
		}
		else {
			return $this->tableName($name);
		}
	}

	function replaceTableRefs ($str) {

		return preg_replace('/#([^#]+?)#/', $this->prefixTableName("$1"), $str);
	}
	
	function prefixTableName ($internalName) {
		return($this->tablePrefix(). $internalName);
	}		

	function contextualizeColumns($table, $columns) {
		$dataColumns= array();

		foreach ($columns as $col) {
			if ((strpos($col,".")===false) && (strpos($col,"'")===false) && (strpos($col,"(")===false) ) {
				$dataColumns[] = "$table.$col";
			}
			else {
			$dataColumns[] = $col;

			}
		}
		return ($dataColumns);

	}
	
	function dataColumns($name, $contextualize=true) {
		$dataColumns= array();

		if ($tableDefinition = $this->getTableDefinition($name)) {
			$table=$this->tableName($name);
			$cols= array_key_exists("datacolumns", $tableDefinition)?$tableDefinition["datacolumns"]:array('*');

			if ($contextualize)
				$dataColumns= $this->contextualizeColumns($table, $cols);
			else
				$dataColumns=  $cols;



			return ($dataColumns);

		}
		else {
			$this->showdebug("ERROR: Definition not found for $name");
			return null;
		}
	}


	function filterList($name) {
		$sql=" where 1=1 ";
		$tableDefinition = $this->getTableDefinition($name);
		if ($filter = array_key_exists("filter", $tableDefinition)?$tableDefinition["filter"]:false) {

			$sql.=" and ".$filter;

		}
		return ($sql);
	}


	function contextFilter($name, $atts) {
		$sql=" ";
		if ($tableDefinition = $this->getTableDefinition($name)) {
		
			$contextFilters = array_key_exists("context_filters", $tableDefinition)?$tableDefinition["context_filters"]:false;
		}
		else {
			error_log("No table definition for: $name");

		}
		//$contextFilters = $this->tableDefinition[$name]["context_filters"];

		$contextFilterKey=$atts["context_filter"];

		if ($contextFilters && ($contextFilterKey)) {

			if ($filter = $contextFilters[$contextFilterKey]) {

					$sql.=" and ".$filter;
				}
			else {

				error_log("Context Filter: $contextFilterKey not found for $name");
			}

		}
		else if ($contextFilterKey=="context_active") {
				$sql.=" and record_status=1";

		}

		$sql = $this->envReplacements($sql, $atts);
		
		return ($sql);
	}
	
	function envReplacements ($sql, $atts) {
		
		$contextKey = $atts["context_key"] ? $atts["context_key"] : "id";
		$sql = preg_replace('/#current_id#/i', intval($_GET[$contextKey]), $sql);

		if (strpos($sql, '#current_user_id#') !== false) {
			global $user_ID;
			get_currentuserinfo();

			$sql = preg_replace('/#current_user_id#/i', intval($user_ID), $sql);
		}
		
		return $sql;
		
	}




	function tableColumns($name) {
		$tableDefinition = $this->getTableDefinition($name);
		return ($tableDefinition["columns"]);
	}


	function tableListColumns($name) {
		$table=$this->tableName($name);
		$tableDefinition = $this->getTableDefinition($name);
		
		$cols= ($tableDefinition["listcolumns"]?$tableDefinition["listcolumns"]:array("id","name"));

		return($this->contextualizeColumns($table, $cols));
	}





// Data Operations


	function updateEdit ($name, $data, $noDefaults = false) {

		$sql = $this->getUpdateEditSQL($name, $data, $noDefaults);
		return($this->dbExecute($sql));
	}


	function mergeData($novel, $base) {
		if (! is_array($novel)) {
			$novel=get_object_vars($novel);
		}

		return array_merge($base, $novel);
	}

	function clearEmptyProps($data) {

		foreach ($data as $key=>$value) {
			if (!$value) {
				unset ($data[$key]);
			}
		}

		return ($data);

	}

	function genericAssignReplace($name, $key, $base_id, $elementArray) {

		$this->showdebug("*** genericAssignReplace");

		if ($key && $base_id) {

			$tableName = $this->tableName($name);

			$sql = $this->db->prepare("delete from $tableName where ".$this->quoteIdentifier($key)."=%s", $base_id);

			$this->showdebug($sql);
			$this->dbSafeExecute($sql);

			foreach ($elementArray as $element) {
				
				$element = array_filter($element, 'strlen');
				
				$tableColumns = $this->tableColumns($name);
				$insertColumns = implode(',', array_intersect(array_keys($element), $tableColumns));
				$valueFormat = implode(',', array_fill(0, count($element), '%s'));
				
				$sql = $this->db->prepare("insert into $tableName ($insertColumns) values ($valueFormat)", array_values($element));
				$this->showdebug($sql);
				$this->dbSafeExecute($sql);

			}

		}
		else {

			$this->showdebug("*** genericAssignReplace Error: $key : $base_id");

		}


	}





	function doMultipleInsertUpdate($name, $elementArray, $baseData) {

		$return = true ;



		foreach ($elementArray as $element) {

			if ($element["id"]) {
				$element = $this->clearEmptyProps($element);

				unset ($baseData["id"]);

				$insertData=$this->mergeData($baseData, $element);
			}
			else {
				$insertData=$this->mergeData($element, $baseData);

			}

			$singleReturn = $this->updateEdit ($name, $insertData);

			$return = ($return || $singleReturn);
		}

		return ($return);

	}




		function getListSQL($name, $atts=array()) {
			$this->showdebug("Checking List SQL for: $name");

			if ($this->listSelectTableName($name)) {
				$sql="";

				$sql.="select ". implode($this->tableListColumns($name),",");
				$sql.="  from ".$this->listSelectTableName($name);

				$sql.=$this->filterList($name);
				$sql.=$this->contextFilter($name, $atts);

				$sql=$this->securityConstrainSQL($name,"list",$sql, $atts);

				return($sql);
			}
		}


		function getIdSQL($name, $atts=array()) {
			$this->showdebug("Checking List SQL for: $name");

			if ($this->listSelectTableName($name)) {
				$sql="";

				$sql.="select ".$this->tableName($name).".id" ;
				$sql.="  from ".$this->listSelectTableName($name);

				$sql.=$this->filterList($name);
				$sql.=$this->contextFilter($name, $atts);

				$sql=$this->securityConstrainSQL($name,"list",$sql, $atts);

				return($sql);
			}
		}


	    function getGroupBy($name) {
	    	if (($def=$this->getTableDefinition($name)) && $def["groupby"]) {
				return($def["groupby"]);

			}

	    }
	    
	function retrieveSelectOptions($name, $activeOnly=true, $atts = array()) {

			if ($this->tableName($name)) {
				//$sql = "select id, name from ".$this->tableName($name);

					$sql = "select * from ".$this->tableName($name);

				$sql.=$this->filterList($name);
				$sql.=$this->contextFilter($name, $atts);

				$sql=$this->securityConstrainSQL($name,"selectoptions",$sql, $atts);

				if (!$sql) {
					error_log("Security constraint failure retrieving select options for $name at:". $_SERVER['REQUEST_URI']);

				}

			} else {

				$this->showdebug("Missing definition for select:".$name);

			}


		if ($activeOnly) {

			$columns= $this->tableColumns($name);

			if ($columns) {
				if (in_array("record_status", $columns)) {
					$sql.=" and record_status=1 ";
				}
			}
			else {

				error_log("No column definition found for: $name while retrieving select options at:".$_SERVER['REQUEST_URI']);
			}
		}



			if ($sql) {
				$sql.=" order by name ";

				$this->showdebug("**** Select".$sql);

				return ($this->get_results($sql));
			}
			else {

				error_log("Error retrieving select options for $name at:".$_SERVER['REQUEST_URI']);
			}



		}




		function getCountSQL($name, $atts=array()) {
			$this->showdebug("Checking Count SQL for: $name");

			$sql="";

			$sql.="select count(*)";
			$sql.="  from ".$this->listSelectTableName($name);
			$sql.=$this->filterList($name);
			$sql.=$this->contextFilter($name, $atts);

			$sql=$this->securityConstrainSQL($name,"count",$sql, $atts);

			return($sql);
		}


		function checkFlags($name, $valueArray) {
			$tableDefinition = $this->getTableDefinition($name);
			if ($cols = $tableDefinition["required_flag_fields"]) {
				foreach ($cols as $col) {
					if (! array_key_exists ($col, $valueArray)) {
						$valueArray[$col] = 0;
					}
				}
			}
			
			else if ($defaults = $tableDefinition["defaults"]) {
				foreach($defaults as $key=>$value) {
					if(!array_key_exists($key, $valueArray) || !strlen(trim($valueArray[$key]))) {
						$valueArray[$key] = $value;	
					}
				}
			}
			
			return ($valueArray);
		}

		function getUpdateEditSQL($name, $valueArray, $noDefaults=false) {



			$sql='';

			if (! $noDefaults)
				$valueArray= $this->checkFlags($name, $valueArray);



			$valueArray = apply_filters("gn_db_update_edit_value_defaults_filter", $valueArray);

			if ($this->tableDefExists($name)) {
				if ($valueArray['id']) {

					$sql="update ".$this->tableName($name)." set ";
					$sql.= $this->loadValues($this->tableColumns($name),$valueArray);
					$sql.= " where id = ". $valueArray['id'];

					$this->showdebug("Table cols:".implode(", ",$this->tableColumns($name)));
					$this->showdebug("Update SQL:".$sql);
				}
				else {


					$sql="insert into ".$this->tableName($name). ' set ';
					$sql.= $this->loadValues($this->tableColumns($name),$valueArray);


					$this->showdebug("Insert SQL:".$sql);

				}
			}
			else {
			  error_log("***Error*** Definition not found for:".$name);

			}

				return ($sql);
		}



	function getRecordSQL($id, $name, $contextualize= true) {



		if (($table=$this->listSelectTableName($name)) && $id) {

			$columns = implode($this->dataColumns($name, $contextualize),",");
			$idColumn="id";

			$singleTable = $this->tableName($name);

			$pos = strpos($table,"dataTable");

			if ($pos !== false && $pos>=0) {
				$idColumn="dataTable.id";
			}

			$sql="select $columns from ".$table;
			$sql.= " where $singleTable.$idColumn = %d";


			return $this->db->prepare($sql, $id);
		}
		else {

			$this->showdebug("Can't get record for:$name  with id $id");
		}
	}


	function doInsert ($tableKey, $data) {
		$sql = $this->getUpdateEditSQL($tableKey, $data);
		$this->dbSafeExecute($sql);
		return $this->db->insert_id;

	}


	function normalizeSpace ($str) {
		$str = preg_replace('/\s+/', ' ', $str);

		return trim($str);
	}
	
	function securityConstrainSQL($name, $type, $sql, $atts) {
		// to be overriden by sub classes, if necessary
		return ($sql);
	}
	
	function fetchObject($name, $id, $output=ARRAY_A) {
		$sql = $this->getRecordSQL ($id, $name);
		$result =  $sql ? $this->get_results($sql, $output) : array();
		
		return count($result) ? $result[0] : null;
	}


}
endif;
?>