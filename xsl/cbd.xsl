<?xml version='1.0'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="/*"> 
      <cbd_dataset>
        <xsl:apply-templates/>
      </cbd_dataset>
  </xsl:template>

  <xsl:template match="/*/*">
      <xsl:copy-of select="."/>
  </xsl:template>
    
  <xsl:template match="/*/enterprises">
    <Enterprises>
      <xsl:variable name="entr" select="."/>
      <xsl:copy-of select="*"/>
      <xsl:if test="city_id">
        <cityname><xsl:value-of select="//city[city_id/text() = $entr/city_id/text()]/cityname"/></cityname>
      </xsl:if>
    </Enterprises>
  </xsl:template>
    
</xsl:stylesheet>
