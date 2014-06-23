<?php
if(!class_exists('gn_DataDisplay')):

class gn_DataDisplay extends gn_WebInterface{
var $localDir=null;
var $dtaKeyField ='id';
var $mode="records";
var $directLoad =false;
var $dtaCols;
var $dtaTable;
var $dtaHeaders;
var $dtaDirectRowLink='';
var $title ="Default Title";
var $description ="";
var $data;
var $debug = false;
var $paginate=true;
var $recordLimit=10;
var $filters = array();
var $xsl ="data-display.xsl";



function gn_DataDisplay () {
	$this->localDir=dirname(__FILE__);
	$this->xmlUtil = new gn_XMLUtils();
	$this->options= array();

}


function showdebug ($str) {
	if ($this->debug) {
		echo($str);
	}
}
function setLocalDir($dir) {
	$this->localDir=$dir;
	$this->xmlUtil->setLocalDir($dir);
}



function addDataHeaders() {
	$str='<tr>';

	foreach ($this->dtaHeaders as $key=>$header) {
		$str .='<th';
		$str .=" field='". $this->dtaCols[$key]."' >";
		$str .=$this->xmlUtil->escapeXml($header);
		$str .='</th>';
	}

	$str.='</tr>';
	return($str);
}

function filterQuote($value,$filter) {
	if ($filter->comparator=="like") {
		$value="%$value%";
	}

	if($filter->type=="text") {
		$value= "'".mysql_real_escape_string($value)."'";
	}

	else if($filter->type=="range") {
		$value= "'".mysql_real_escape_string($value)."'";
	}


	else if($filter->type=="daterange") {
		$date = new GnDate($value);
		$formattedDate= $date->format("Y-m-d");
		$value= "'".mysql_real_escape_string($formattedDate)."'";
	}

	return($value);
}

function addFilterCriteria($filter, $criteria) {

	if ($filter->type=="check") {
		if ($this->getDataParam($filter->name)) {
			$criteria[]=" $filter->yesrule ";
		}
		else {
			$criteria[]=" $filter->norule ";

		}
	}
	else if ($filter->type=="daterange") {
		$subcriteria = array();

		if  ($this->getDataParam($filter->name."_from")) {
		 $subcriteria[] =" $filter->field >= ". $this->filterQuote($this->getDataParam($filter->name."_from"), $filter);

		}


		if  ($this->getDataParam($filter->name."_to")) {
				 $subcriteria[] =" $filter->field <= ". $this->filterQuote($this->getDataParam($filter->name."_to"), $filter);
		}

		if (sizeof($subcriteria)>0) {
			$criteria[] =implode(" and ",$subcriteria);
		}

	}

	else if ($filter->type=="range") {
		$subcriteria = array();

		if  ($this->getDataParam($filter->name."_from")) {
		 $subcriteria[] =" $filter->field >= ". $this->filterQuote($this->getDataParam($filter->name."_from"), $filter);

		}


		if  ($this->getDataParam($filter->name."_to")) {
				 $subcriteria[] =" $filter->field <= ". $this->filterQuote($this->getDataParam($filter->name."_to"), $filter);
		}

		if (sizeof($subcriteria)>0) {
			$criteria[] =implode(" and ",$subcriteria);
		}

	}

	else {
		if ($this->getDataParam($filter->name)) {

		$criteria[]=" $filter->field $filter->comparator ". $this->filterQuote($this->getDataParam($filter->name), $filter);
		}
	}


	return ($criteria);
}




function addFiltersCriteria() {
	$criteria= array();

	foreach ($this->filters as $filter) {
		$criteria=$this->addFilterCriteria($filter, $criteria);
	}

	return ($criteria);
}
function buildCriteria() {
	$criteria=$this->addFiltersCriteria();

	if ($this->mode=="edit") {
		$val = $_GET[$this->dtaID];
		$criteria[]=sprintf(" id=%d ", $val);
	}

	if ($criteria) {
		return (implode(" and ",$criteria));
	}
}

function generateSelectSQL() {
	$sql="";
	$sql .=" from ";

	if ($this->dtaTable) {
		$sql .= $this->data->tableName($this->dtaTable);
	}
	elseif ($this->dtaSelectFrom) {
		$sql .= $this->dtaSelectFrom;

	}

	if ($criteria=$this->buildCriteria()) {
		$sql .=" where ";
		$sql .=$criteria;

	}

	return($sql);
}

function getSortField($index) {
	$field = $this->dtaCols[$index-1];

	if (strpos($field," as ")) {
		$field = substr($field, strpos($field," as ")+4);
	}

	$field= mysql_real_escape_string($field);
	return($field);


}
function generateSQL() {
	$sql="";
	//$offset = $_GET["offset"]?$_GET["offset"]:0;

	$sql .="select ";
	$sql .=$this->data->formatFieldList($this->dtaCols);
	$sql .=$this->generateSelectSQL();

	if ($this->getDataParam("sortField")) {
		$sql.= sprintf(" order by %s",$this->getSortField($this->getDataParam("sortField"))); // $_GET["sort"];
	}

	if ($this->getDataParam("sortDirection")) {
		$sql.= sprintf(" %s", mysql_real_escape_string($this->getDataParam("sortDirection"))); // $_GET["sort"];
	}

	return($sql);
}

// *********************************************************************

function writeOutput($str) {
	echo ($str);
}



function writeResultScroller ($totalRows, $recordsPerPage, $currentOffset, $offsetParamName){

	// Called by writePagedResult() to write links to previous and/or next result
	// pages, if required.

	$url =  $_SERVER["REQUEST_URI"]; //$this->getServerVariable('URL');
	/*
	//echo ("ResultScroller start URL".$url."<br>");

	$qs = $this->getServerVariable('QUERY_STRING');
	if($qs && strpos($url,"?")===false) $url.="?".$qs; // this seems to be different between Unix and Windows implementations -DS

	*/
	//echo ("ResultScroller modified URL".$url."<br>");

	$totalRows=$this->foundRows;

	$numPages = $this->roundUp($totalRows / $recordsPerPage);
	$lastOffset = ($numPages-1)*$recordsPerPage;
	$onFirstPage = ($currentOffset == 0) ? true : false;
	$onLastPage = ($currentOffset == $lastOffset) ? true : false;
	$currentPage = ($currentOffset/$recordsPerPage + 1);


	 $this->writeOutput("<div class='resultscroller'>");
	$this->writeScrollerItem("<<",  $this->appendParam($url, $offsetParamName, 0), !$onFirstPage, "First page..."); // "
	$this->writeOutput("&#160;");
	$this->writeScrollerItem("<",  $this->appendParam($url, $offsetParamName, $currentOffset-$recordsPerPage), !$onFirstPage, "Previous page...");
	$this->writeOutput("&#160;");

	for($i=0; $i<$numPages; ++$i){
		$this->writeScrollerItem($i+1, $this->appendParam($url, $offsetParamName, $i*$recordsPerPage), !($i+1==$currentPage));
		$this->writeOutput("&#160;");
	}

	$this->writeScrollerItem(">", $this->appendParam($url, $offsetParamName, $currentOffset+$recordsPerPage), !$onLastPage, "Next page...");
	$this->writeOutput("&#160;");
	$this->writeScrollerItem(">>", $this->appendParam($url, $offsetParamName, $lastOffset), !$onLastPage, "Last page...");

	$this->writeOutput("</div>\n");
}


function writeScrollerItem($text, $url, $active, $titleText=""){

	// Called by writeResultScroller(). Writes $text as a link or
	// as plain text depending on value of $active.

if($active) $this->writeScrollerLink($text, $url, $titleText);
	else $this->writeOutput("<span class='inactive'>".$this->htmlEncode($text)."</span>");
}

function writeScrollerLink($text, $url, $titleText){

	// Called by writeResultScroller(). Writes hyperlink to $url
	// with "title" attribute of $titleText.

	$this->writeOutput("<a ");
	if($titleText) $this->writeOutput("title='".$this->htmlEncode($titleText)."' ");
	$this->writeOutput("href='".$url."'>".$this->htmlEncode($text)."</a>");
}




// ***************************************************************************


function retrieveRecords () {
	$sql = $this->generateSQL();

	$offset = $this->getDataParam("offset")?$this->getDataParam("offset"):0;



	 if ($this->paginate) {
	 		$sql.=sprintf(" limit %d,%d", $offset,$this->recordLimit);
	 }

	 if ($this->records || $this->directLoad) {
	 	$records = $this->records;
	 	$this->foundRows = count($records);
	 }
	 else {
	 	$records= $this->data->get_results($sql);

	 	$this->foundRows = $this->data->db->get_var("select count(*) ".$this->generateSelectSQL());
	 }


	 return($records);
}

function getRowKey($record) {
	$array = get_object_vars($record);

	return ($array[$this->dtaKeyField]);
}

function addDbData() {
	$str='';
	$records = $this->retrieveRecords();

	foreach ($records as $record) {

	$rowkey = $this->getRowKey($record);


		$str.="<tr rowkey='$rowkey'>";
			foreach ($record as $key => $value) {
				$str.="<td fieldname='$key'>";
				$str.=$this->xmlUtil->escapeXml($value);
				$str.='</td>';
			}

		$str.='</tr>';
	}

	return($str);
}

function addOptions ($option="multiple") {
	 $str='';
	foreach($this->options as $label=>$link) {
		$str.="<option><name>$label</name><link>?page=$link</link></option>";
	}
	return ($str);
}


function getDataParam($name) {

	return mysql_real_escape_string($_GET[$name]);

}

function filterXML() {
	$xml="";
	foreach ($this->filters as $filter) {
		$xml.="<filter type='$filter->type' name='$filter->name' prompt='$filter->prompt' field='$filter->field'>";
		if ($filter->type =="daterange") {
			$xml.="<from>".$this->getDataParam($filter->name."_from")."</from>";
			$xml.="<to>".$this->getDataParam($filter->name."_to")."</to>";;

		}
		else {
			$xml.=$this->getDataParam($filter->name);
		}

		$xml.="</filter>";
	}

	//echo($xml);
		return ($xml);
}

function loadData() {
	//$xml = $this->xmlUtil->loadXML ($this->localDir."/xml/sample-data.php");
	//return($xml);

	$this->url = $_SERVER["REQUEST_URI"];

	$this->url = preg_replace("/&sort=[^&]*/",'',$this->url);

	$xml ='<?xml version="1.0"?>';
	$xml.='<gnData>';
	$xml.='<header>';
	$xml.='<sql>'.$this->xmlUtil->escapeXml($this->generateSQL()) .'</sql>';
	$xml.='<title>'.$this->xmlUtil->escapeXml($this->title) .'</title>';
//	$xml.='<description>'.$this->xmlUtil->escapeXml($this->description) .'</description>';
	$xml.='<description>'.$this->description .'</description>';
	$xml.='<url>'.$this->xmlUtil->escapeXml($this->url) .'</url>';
	$xml.='<page>'.$this->getDataParam("page") .'</page>';
	$xml.='<sortField>'.$this->getDataParam("sortField") .'</sortField>';
	$xml.='<sortDirection>'.$this->getDataParam("sortDirection") .'</sortDirection>';
	$xml.='<reportName>'.$this->reportName .'</reportName>';
	$xml.='<reportGenerator>'.$this->reportGenerator .'</reportGenerator>';




	$xml.=$this->filterXML();


	$xml.='<options>'.$this->addOptions() .'</options>';
	$xml.='<rowlink>'.$this->dtaRowLink .'</rowlink>';
	$xml.='<directrowlink>'.$this->dtaDirectRowLink .'</directrowlink>';

	$xml.='</header>';
	$xml.='<data>';

	$xml.='<table>';

	$xml.=$this->addDataHeaders();
	if ($option=="multiple") {
		$xml.=$this->addDbData();
	}
	else {
		$xml.=$this->addDbData();
	}

	$xml.='</table>';


	$xml.='</data>';
	$xml.='</gnData>';

	$xmldoc =$this->xmlUtil->parseXML($xml);


	return($xmldoc);
}




function loadXSL ($file) {
	$xml = $this->xmlUtil->loadXML ($this->localDir."/xsl/".$file);
	return ($xml);


}



function displayInterface() {
	if ($this->mode=="records") {
		$xsl = $this->loadXSL($this->xsl);
		$xml = $this->loadData();
		echo($this->xmlUtil->applyXSL ($xsl,$xml, $this->params));
	}
	else if ($this->mode=="edit") {
		$this->showdebug("Here");

			$xml = $this->loadData("single");
			$xsl = $this->loadXSL($this->xsl);
			echo($this->xmlUtil->applyXSL ($xsl,$xml));

	}
	else {
		$this->showdebug("Whoops");
	}


	$offset = $this->getDataParam("offset")?$this->getDataParam("offset"):0;


	$offset = sprintf("%d", $offset);

	if ($this->paginate) {
	if($this->foundRows > $this->recordLimit)
		$this->writeResultScroller ($this->foundRows, $this->recordLimit, $offset, "offset");
	}



}

}
endif;?>