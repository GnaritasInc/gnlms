<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method = "html"    omit-xml-declaration = "yes" indent="no"/>
<xsl:output encoding="utf-8"/>


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
  
  <xsl:template name="prompt">
  <xsl:choose>
  <xsl:when test="@prompt !=''"><xsl:value-of select="@prompt"/></xsl:when>
  <xsl:otherwise><xsl:value-of select="@field"/></xsl:otherwise>
  </xsl:choose>
  </xsl:template>
  
  
  <xsl:template match="filter[@type='text']">
	<tr class="form-field">
	<th scope="row" valign="top"><label><xsl:call-template name="prompt"/> is like</label></th>
	<td>
	<input type="text">
	<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
	<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
	</input>
	</td>
	<!--
	<td>
	<input type="submit" class="button" value="update"/></td>
	-->
	</tr>
  </xsl:template>

  <xsl:template match="filter[@type='daterange']">
	<tr class="form-field">
	<th scope="row" valign="top"><label><xsl:call-template name="prompt"/> (YYYY-MM-DD) between </label></th>
	<td>
	<input type="text">
	<xsl:attribute name="name"><xsl:value-of select="@name"/>_from</xsl:attribute>
	<xsl:attribute name="value"><xsl:value-of select="from"/></xsl:attribute>
	</input>
	
	and 
	
	<input type="text">
		<xsl:attribute name="name"><xsl:value-of select="@name"/>_to</xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="to"/></xsl:attribute>
	</input>
	</td>
	<!--
	<td>
	<input type="submit" class="button" value="update"/></td>
	-->
	</tr>
  </xsl:template>

  <xsl:template match="filter[@type='range']">
	<tr class="form-field">
	<th scope="row" valign="top"><label><xsl:value-of select="@field"/> between </label></th>
	<td>
	<input type="text">
	<xsl:attribute name="name"><xsl:value-of select="@name"/>_from</xsl:attribute>
	<xsl:attribute name="value"><xsl:value-of select="from"/></xsl:attribute>
	</input>
	and
	
	<input type="text">
		<xsl:attribute name="name"><xsl:value-of select="@name"/>_to</xsl:attribute>
		<xsl:attribute name="value"><xsl:value-of select="to"/></xsl:attribute>
	</input>
	</td>
		<!--
		<td>
		<input type="submit" class="button" value="update"/></td>
	-->
	</tr>
  </xsl:template>


  <xsl:template match="filter[@type='check']">
	<tr class="form-field">
        <th scope="row" valign="top"><label><xsl:value-of select="@prompt"/></label></th>
	<td> <input type="checkbox">
	<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
	<xsl:attribute name="value">1</xsl:attribute>
	<xsl:if test="normalize-space(.)!=''"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
	</input>
	</td>
	<!--
	<td>
	<input type="submit" class="button" value="update"/></td>
	-->
	</tr>
  </xsl:template>

<xsl:template match="/">
	<script type="text/javascript">
	function setSort(pos, text) {
		if (document.getElementById("sortField").value ==pos) {
		
		if (document.getElementById("sortDirection").value=="asc") {
			document.getElementById("sortDirection").value = "desc";
		}
		else {
			document.getElementById("sortDirection").value = "asc";
		}
		
			
		}
		document.getElementById("sortField").value = pos;
		
		document.forms["gnSortForm"].submit();
	}
	

	function setOffset(pos) {

		document.getElementById("gnDisplayOffset").value = pos;
		
		document.forms["gnSortForm"].submit();
	}	
	
	</script>
	
	<div id="wpbody"><div class="wrap">

	<h2><xsl:value-of select="//title"/></h2>
<xsl:apply-templates select="//description"/>
	<!--<b>SQL</b>: <xsl:apply-templates select="//sql"/> -->
	<form id="gnSortForm" method="get">
	<input type="hidden" name="sortField" id="sortField">
	<xsl:attribute name="value"><xsl:value-of select="//sortField"/></xsl:attribute></input>
	<input type="hidden" name="sortDirection" id="sortDirection">
	<xsl:attribute name="value"><xsl:value-of select="//sortDirection"/></xsl:attribute></input>
	<input type="hidden" name="page" id="page">
	<xsl:attribute name="value"><xsl:value-of select="//page"/></xsl:attribute></input>	
	

	
	<table class="form-table" border="1" style="border:1px solid black;">
	<xsl:apply-templates select="//filter"/>
	
	
	</table>
	<p class="submit"><input onclick="document.forms['gnSortForm'].submit();" type="submit"  class="button" value="Update Filters"/></p>
	<br class="clear" />
	</form>

	<xsl:apply-templates select="//table"/>
	<xsl:apply-templates select="//options"/>
	
	<div style="display:none">
	<xsl:copy-of select="."/>
	</div>
	
	</div></div>

</xsl:template>

<xsl:template match="@fieldname"  priority="1"/>
<xsl:template match="@field" priority="1"/>
<xsl:template match="@rowkey" priority="1"/>


<xsl:template match="options">
    <ul>
    <xsl:apply-templates/>
    </ul>
   </xsl:template>


<xsl:template match="option">
    <li><xsl:element name="a">
    <xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>
    <xsl:value-of select="name"/>
    </xsl:element></li>
   </xsl:template>

<!--
<table class="form-table">
    <tr class="form-field">
        <th scope="row" valign="top"><label for="foo">Foo</label></th>
        <td><input name="foo" id="foo" type="text" /></td>
    </tr>
</table>
<p class="submit"><input class="button" name="submit" value="Update" type="submit" /></p>
<br class="clear" />

-->
  <xsl:template match="table">
    <xsl:copy>
    	<!--<xsl:attribute name="class">form-table</xsl:attribute>-->
    	<xsl:attribute name="border">1</xsl:attribute>
    	<xsl:attribute name="width">100%</xsl:attribute>
    	<xsl:attribute name="style">border:1px solid black;border-collapse:collapse;</xsl:attribute>
      <xsl:apply-templates select="node() | @*"/>
    </xsl:copy>
    <br class="clear" />

  </xsl:template>


<xsl:template match="tr">
    <xsl:copy>
       <!-- <xsl:attribute name="class">form-field</xsl:attribute>-->
      	<xsl:apply-templates/>
    </xsl:copy>

</xsl:template>



<xsl:template match="tr[1]">
    <xsl:copy>
      	<xsl:apply-templates/>
    </xsl:copy>

</xsl:template>

<xsl:template match="th">
    <xsl:copy>
        <xsl:attribute name="scope">row</xsl:attribute>
        <xsl:attribute name="valign">top</xsl:attribute>

	<a>
	<xsl:attribute name="href">#</xsl:attribute>
	<xsl:attribute name="onclick">setSort(<xsl:value-of select="position()"/>,'<xsl:value-of select="@field"/>');</xsl:attribute>

    <xsl:apply-templates select="node() | @*"/>
    </a>
    </xsl:copy>
   </xsl:template>


<xsl:template match="td[@fieldname='eventclass']/text()">
<xsl:if test="normalize-space(.)='0'">On Campus</xsl:if>
<xsl:if test="normalize-space(.)='1'">Off Campus</xsl:if>
</xsl:template>

<xsl:template match="td">
    <xsl:copy>
        	<xsl:attribute name="style">border:1px solid black;</xsl:attribute>

    	<xsl:choose>
    		<xsl:when test="false">
      			<xsl:apply-templates select="node() | @*"/>
      		</xsl:when>
      		<xsl:otherwise>
      		<a>
      			<xsl:attribute name="href">?page=<xsl:value-of select="//rowlink"/>&amp;id=<xsl:value-of select="parent::tr/@rowkey"/></xsl:attribute>
      			<xsl:apply-templates select="node() | @*"/>
      		</a>
      		</xsl:otherwise>
      	</xsl:choose>
    </xsl:copy>

</xsl:template>




</xsl:stylesheet>