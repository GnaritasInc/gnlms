<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method = "html"    omit-xml-declaration = "yes" indent="yes"/>
<xsl:output encoding="utf-8"/>


<xsl:param name="csvlink"/>

  <xsl:template match="node()[local-name(.)!=''] | @*"  priority="-1">
   <xsl:element name="{local-name(.)}"> 
   <xsl:for-each select ="@*">
   	<xsl:attribute name="{local-name(.)}">
   		<xsl:value-of select="."/>
   	</xsl:attribute>
   	</xsl:for-each>
       <xsl:apply-templates select="node()"/>
    </xsl:element>
  </xsl:template>
  
  
  <xsl:template match="node() | @*" priority="-1">
    <xsl:copy>
      <xsl:apply-templates select="node() | @*"/>
    </xsl:copy>
  </xsl:template>
  
    <xsl:template match="@field"/>

  
  <xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>
  
  <xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <title><xsl:apply-templates select="//title/node()"/></title>
	<style type="text/css">
	/*<![CDATA[*/
	body {
	margin:0;
	padding:0;
	width:100%;
	font: 10pt/1.5em 'arial', helvetica, verdana, sans-serif;
	}
	
	#content {
	padding:20px;
	}
	
	h1 {
	color:#578fc2;
	font-size:14pt;
	margin-bottom:18px;
	}
	
	p, h2, h3, h4, h5 {
	margin-bottom:18px;
	}
	
	ul {
	margin:0 0 18px 0;
	padding:0 0 0 1em;
	}
	
	a {
	color:#4fac40;
	text-decoration:underline;
	}
	
	a:hover {
	text-decoration:none;
	}
	
	table {
	width:100%;
	margin-bottom:18px;
	border-collapse:collapse;
	}
	
	td {
	padding:5px;
	border:1px solid #cee2ef;
	}
	
	th {
	border:1px solid #cee2ef;
	background: #F0F7F8;
	padding:5px;
	text-transform:uppercase;
	font-weight:bold;
	text-align:left;
	}
		
	/*]]>*/
	</style>
  
  
  </head>
  <body>
    <div id="content">
      <h1><xsl:apply-templates select="//title/node()"/></h1>

   <xsl:apply-templates select="//table"/>
  
 
 
  	
  	<xsl:if test="$csvlink">
  	<ul><li><a>
  	<xsl:attribute name="href"><xsl:value-of select="$csvlink"/></xsl:attribute>
  	CSV Export</a></li></ul>
  	</xsl:if>
  	 	</div>
 
  <!--
  <div style="display:none">
  	<xsl:copy-of select="."/>
  	</div>
  -->
  	
  </body></html>

  </xsl:template>



  <xsl:template match="table">
    <xsl:copy>
          <tbody>
     <xsl:apply-templates select="node() | @*"/>
      </tbody>
    </xsl:copy>
  </xsl:template>











</xsl:stylesheet>