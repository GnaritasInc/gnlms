<?php
if(!class_exists('gn_Date')):

class gn_Date {
	var $mydate;


	public static function getDateFromTimestamp ($timestamp=null, $format="Y-m-d H:i:s"){
		// default format: "2011-01-03 16:02:33"

		if($timestamp===null) {
			$timestamp = time();
		}
		return date($format, $timestamp);
	}

	function __construct ($str=false) {
		if ($str) {
			$this->mydate = strtotime($str);
		}
		else {
				$this->mydate = time();
		}
	}

	function format($str) {
		return (date($str,$this->mydate));
	}

	function formatMySQL () {
		return $this->format("Y-m-d H:i:s");
	}

	function modify($str) {
		//print ($this->format("Y-m-d")."<br>");

		$this->mydate =strtotime($this->format("Y-m-d")." ".$str);

		//print ($this->format("Y-m-d")."<br>");

	}

	function getDatePart($key) {
		$dateinfo = getdate(date("U", $this->mydate));
		return $dateinfo[$key];
	}
	function valueOf () {
		return (int) date("U", $this->mydate);
	}

}


endif;

?>