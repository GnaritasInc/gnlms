<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method = "html"    omit-xml-declaration = "yes" indent="no"/>
<xsl:output encoding="iso-8859-1"/>


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
  



</xsl:stylesheet>