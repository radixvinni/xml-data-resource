<?xml version='1.0'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:variable name="filter_id" select="/page/request/filter/id/*/text()"/>

    <xsl:template match="test" mode="list"> 
      <tr><td>
      <a href="{$base_url}?id={id}"><xsl:value-of select="name"/></a>
      </td></tr>
    </xsl:template>

    <xsl:template match="test" mode="option">
      <option value="{id}">
        <xsl:if test="id = $filter_id">
          <xsl:attribute name="selected">selected</xsl:attribute>
        </xsl:if>
        <xsl:value-of select="name"/>
      </option>
    </xsl:template>

    <xsl:template match="test" mode="page">
      <div class="container">
      <ul class="breadcrumb">
        <li><a href="{$base_url}?">List</a> <span class="divider">/</span></li>
        <li class="active"><xsl:value-of select="name"/></li>
      </ul>
      <legend><xsl:value-of select="name"/></legend>
      <dl class="dl-horizontal">
            <dt>Id</dt>
            <dd><xsl:value-of select="id"/></dd>
            <dt>Name</dt>
            <dd><xsl:value-of select="name"/></dd>
          </dl>
      </div>
    </xsl:template>
    
</xsl:stylesheet>
