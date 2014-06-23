<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method = "text" omit-xml-declaration = "yes" indent="no"/>
<xsl:output encoding="windows-1252"/>
<xsl:strip-space elements = "td"/>

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
  
  
  <xsl:template match="/">
  <xsl:apply-templates select="//table//tr"/>
  </xsl:template>



<xsl:template match="tr">
<xsl:variable name="newline"><xsl:text>
</xsl:text></xsl:variable>
<xsl:apply-templates select="td|th"/><xsl:value-of select="$newline"/>
</xsl:template>

<xsl:template match="tr[not (following-sibling::tr)]">
<xsl:variable name="newline"><xsl:text>
</xsl:text></xsl:variable>
<xsl:apply-templates select="td|th"/><xsl:value-of select="$newline"/>
</xsl:template>


<xsl:template match="td | th"><xsl:variable name="remove">,"</xsl:variable>"<xsl:value-of select="translate(.,$remove,'')"/>",</xsl:template>

<xsl:template match="td[count(following-sibling::node())=0]"><xsl:variable name="remove">,"</xsl:variable>"<xsl:value-of select="translate(.,$remove,'')"/>"</xsl:template>
<xsl:template match="th[count(following-sibling::node())=0]"><xsl:variable name="remove">,"</xsl:variable>"<xsl:value-of select="translate(.,$remove,'')"/>"</xsl:template>





</xsl:stylesheet>