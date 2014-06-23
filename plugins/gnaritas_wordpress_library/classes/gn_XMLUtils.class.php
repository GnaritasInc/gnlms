<?php
if(!class_exists('gn_XMLUtils')):

class gn_XMLUtils {
var $localDir=null;


function gn_XMLUTils () {
	$this->localDir=dirname(dirname(__FILE__));

}

function setLocalDir($dir) {
	$this->localDir=$dir;
}


function escapeXml($str) {
	// htmlspecialchars may do most of what we need
	$str = htmlspecialchars ($str); // does &, <, >, and "

	// Don't double escape

	$str = preg_replace("/&amp;#/", "&#", $str);
	$str = preg_replace("/&amp;amp;/", "&amp;", $str);

	return $str;
}




function applyXSLToString($xslFile, $xmlString) {
   /*
   $fileBase      = '';
	$xsl = domxml_xslt_stylesheet_file ("$localDir/xsl/" . $xslFile);
	@$inputdom = domxml_open_mem ($xmlString);

	$result =  $xsl->process($inputdom);
	print $xsl->result_dump_mem($result);
	*/

	$inputdom = $this->parseXML($xmlString);
	$xslDom = $this->loadXML($this->localDir."/xsl/" . $xslFile);

	echo($this->applyXSL($xslDom, $inputdom));
}

function parseXML2($xmlString) {
	@$inputdom = domxml_open_mem ($xmlString);
	return ($inputdom);
}


function parseXML($xmlString) {
	$xmlDOM = new DOMDocument();
	$xmlDOM->loadXML($xmlString);
	return ($xmlDOM);
}

function applyXSLtoDOM($xslDOM,$xmlDOM, $params= false) {
	$xsl = new XSLTProcessor();
	$xsl->importStylesheet($xslDOM);
	if ($params) {
			foreach ($params as $param => $value) {
				$xsl->setParameter("", $param, $value);
			}
	}
	$result =  $xsl->transformToDOC($xmlDOM);

	return ($result);
}

function applyXSL($xslDOM,$xmlDOM, $param = false) {
	$xsl = new XSLTProcessor();
	$xsl->importStylesheet($xslDOM);

	if ($param) {
			foreach ($param as $name => $value) {

		    $xsl->setParameter('', $name, $value);
			}
	}
	$result =  $xsl->transformToXML($xmlDOM);

	return ($result);
}

function applyLocalXSL($xslFileName, $xmlDOM) {
	$xslDOM = new DOMDocument();

	$xslDOM->load($this->localDir."/xsl/" . $xslFileName);

	return ($this->applyXSL($xslDOM,$xmlDOM));
}

function applyLocalXSLtoDOM($xslFileName, $xmlDOM, $params = false) {
	$xslDOM = new DOMDocument();
	$xslDOM->load($this->localDir."/xsl/" . $xslFileName);
	return ($this->applyXSLtoDOM($xslDOM,$xmlDOM, $params));
}

function loadXML($path) {
	$xmlDOM = new DOMDocument();
	$xmlDOM->load($path); //."?pageid=" .$pageid . "&data=" . $data);
	return($xmlDOM);
}

}
endif;?>