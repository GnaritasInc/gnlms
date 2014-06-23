<?php

if(!class_exists('gn_DateDisplay')):


class gn_DateDisplay  {

	var $previousMonthLink;
	var $range;

	function gn_DateDisplay() {
		$range=false;
	}

	function getEventListXml () {
		// In Real Life (tm) this will perhaps accept start date and
		// end date params, connect to the database and return an
		// xml string representing data for events in the specified range (or
		// maybe the empty string of no events found).
		// For now will hard-code made-up events for demonstration purposes.


		$str = "<events>";

		$str .= getEventXml("2008-06-11", "15:00", "Some event", "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.");
		$str .= getEventXml("2008-06-14", "12:00", "Some other event", "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat." );
		$str .= getEventXml("2008-06-12", "15:00", "Cat Spay-a-thon", "Prevent your cats from breeding. hte only responsiblething for pet owners to do.");
		$str .= getEventXml("2008-07-04", "21:00", "Fireworks on the Charles", "Listen to the Boston Pops on teh Esplanade, etc...");

		$str .= "</events>";

		return $str;

	}

	function getEventXml ($date, $time, $title, $description) {
		$str = "<event>";
		$str .= "<date>".$date."</date>";
		$str .= "<time>".$time."</time>";
		$str .= "<title>".$title."</title>";
		$str .= "<description>".$description."</description>";
		$str .= "</event>\n";

		return $str;
	}

	function myLoc () {
		$pattern="/.date=\\d+-\\d+-\\d+/";

		return(preg_replace($pattern,"",$_SERVER["REQUEST_URI"]));
	}


function addArg() {
	if (strstr($this->myLoc(),"?")) {
		$addArg="&";
	} else {
		$addArg="?";
	}
	return ($addArg);
}

	function getMonthXml ($month, $year, $selected) {
		// $month is between 1 and 12, not 0 and 11 as in JavaScript

		$addArg=$this->addArg();

		$str = "<table>";

		$now = new gn_Date();
		$today = new gn_Date($now->format("Y-m-d"));
		$selectedDate = new gn_Date($selected);

		// initialize to first of the month
		$date = new gn_Date($year."-".$month."-1");

		$prevMonth = new gn_Date($date->format ("Y-m-d"). "-1 month"); //new gn_Date($year."-".($month - 1)."-1");

		$nextMonth = new gn_Date($date->format ("Y-m-d"). "+1 month"); //new gn_Date($year."-".($month +1)."-1");

		$prevMonthLinkAndClass = 'href="'.$this->myLoc().$addArg.'date='.$prevMonth->format("Y-m-d") .'" ';
		$nextMonthLinkAndClass = 'href="'.$this->myLoc().$addArg.'date='.$nextMonth->format("Y-m-d") .'" ';

		if ($this->range) {
			$lowRange = new gn_Date($now->format ("Y-m-d")."-".$this->range);
			$highRange = new gn_Date($now->format ("Y-m-d")."+".$this->range);

			$prevMonthLinkAndClass = ($prevMonth->valueOf() > $lowRange->valueOf())?  $prevMonthLinkAndClass: (" class='disabled' href='#' ");
			$nextMonthLinkAndClass = ($nextMonth->valueOf() < $highRange->valueOf())?  $nextMonthLinkAndClass: (" class='disabled' href='#' ");
		}



		$firstOfMonth = new gn_Date($year."-".$month."-1");


		$str .= "<caption>";
		$str.='<a '. $prevMonthLinkAndClass .' title="previous month" class="nav">&laquo;</a> ';
		$str .=$date->format("F Y");
		$str .=' <a '.$nextMonthLinkAndClass .' title="next month" class="nav">&raquo;</a>';
		$str.="</caption>";

		// get current weekday
		$wday = $date->getDatePart("wday");


		// set back to previous Sunday, if necessary
		$date->modify("-".($wday)." day");


		$str .= $this->getWeekdayHeaderRow();


		while($date->valueOf() < $firstOfMonth->valueOf() || $date->getDatePart("mon") == $month){
			$str .= $this->getWeekRow($date, $firstOfMonth,$today,$selectedDate);
		}

		$str .= "</table>";

		return $str;

	}


	function getWeekdayHeaderRow () {
		// could modify to optionally return short or long weekday names (or different languages?)
		// returning hard-coded English short names for now

		$weekdays = array ("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
		$weekdaysShort = array ("S", "M", "T", "W", "T", "F", "S");
		$weekdaysLong = array ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

		$str = "<tr>";
		for($i=0; $i < count($weekdays); ++$i){
			$str.= "<th scope='col' abbr='".$weekdays[$i] ."' title='".$weekdaysLong[$i] ."'>".$weekdaysShort[$i]."</th>";
		}
		$str .= "</tr>";

		return $str;

	}

	function getAnchorClass ($date) {
		return ("showTip L".$date->format("Ymd"));
	}

	function getWeekRow ($date, $month, $today,$selectedDate) {
		$str = "<tr>";

		$addArg=$this->addArg();


		for($i=1; $i<=7; ++$i){
			$str .= "<td id='".$date->format("Y-m-d")."' class='".$this->getCellClass($date, $month, $today,$selectedDate)."'>";
			$str.="<a href='";
			$str.=$this->myLoc().$addArg.'date='.$date->format("Y-m-d");
			$str.="' class='". $this->getAnchorClass($date) ."'>";
			$str.=$date->getDatePart("mday");
			$str.="</a>";
			$str.="</td>";
			$date->modify("+1 day");
		}

		$str .= "</tr>";

		return $str;
	}

	function getCellClass ($date, $firstOfMonth, $today,$selectedDate) {
		$nextMonth =new gn_Date($firstOfMonth->format("Y-m-d"));
		$nextMonth->modify("+1 month");

		$cssClass =  array();


		if($date->valueOf() < $today->valueOf()){
			$cssClass[]= "past";
		}
		if($date->valueOf() < $firstOfMonth->valueOf()){
			$cssClass[]= "previous";
		}
		if($date==$today){
			$cssClass[]= "today";
		}
		if ($date==$selectedDate) {
			$cssClass[]= "selected";
		}
		if($date->getDatePart("mon") == $firstOfMonth->getDatePart("mon")){
			$cssClass[]= "current";
		}
		if($date->valueOf() >= $nextMonth->valueOf()){
			$cssClass[]= "following";
		}
		return (implode(" ",$cssClass));
	}

}

endif;

?>