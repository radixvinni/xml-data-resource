<?xml version='1.0'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> <!--  xml:space="preserve" ??-->
    <xsl:variable name="s" select="/page/request/s/text()"/>
   
    <xsl:template match="request[not(p)]"> 
      <!-- Start page -->
      <style>#toggler.icon-chevron-up:after {content:"Less";} #toggler.icon-chevron-down:after {content:"More";} #toggler:after { margin-left:16px; border-bottom: 1px dotted #999;} #toggler:hover:after {color:rgb(0, 136, 204);} #toggler {display: inline-block;vertical-align: top; margin:8px;cursor: pointer;} #filters {margin-top:10px}</style>
      <div class="container">
      <form  method="get" action="{$base_url}">
        <span class="input-prepend input-append form-search">
          <span class="add-on" style="background:white;"><i class="icon-search" style="margin-top: 3px;"></i></span>
          <input class="span8 search-query" name="q" id="searchInput" type="text" placeholder="Search" value="{q}"/>
          <button class="btn" type="submit">Search</button>
        </span><i id="toggler" class="icon-chevron-down"/>
        <div id="filters" class="controls controls-row" style="display:none;">
          <select multiple="" name="filter[id][]" id="test" class="populate select2-offscreen span3" tabindex="-1">
            <xsl:apply-templates select="document('db://test')//test" mode="option"/>
          </select>
        </div>
        <script>
$(document).ready(function() { 
    $('#toggler').click(function () { 
        $('#filters').toggle('1000');
        $(this).toggleClass('icon-chevron-up icon-chevron-down'); 
      }); 
    $("#test").select2({placeholder: "name"});
    <xsl:if test="count(filter/*)">
        $('#toggler').trigger('click');
        
      </xsl:if>
});
        </script>
      </form>
      
      <xsl:if test="error/text()='1'">
        <div class="alert alert-error">
          <button type="button" class="close" data-dismiss="alert">×</button>
          <strong>Error!</strong> Error during import.
        </div>
      </xsl:if>
      <xsl:if test="success/text()='1'">
        <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="alert">×</button>
          <strong>Yes!</strong> Operation successfull.
        </div>
      </xsl:if>
      <xsl:if test="not(q)">

        <xsl:variable name="ents" select="document('db://test?limit=50')"/>
        <xsl:variable name="max" select="$ents/data/@total div 50 - 1"/>
        <xsl:choose>
          <xsl:when test="count($ents//test)">
            <table class="table table-striped">
              <thead><tr><th>name</th></tr></thead>
              <tbody><xsl:apply-templates select="$ents//test" mode="list"/></tbody>
            </table>
          </xsl:when>
          <xsl:otherwise>
            <div class="alert alert-info"><em> Nothing here. Go <a href="{$base_url}?p=import">load something</a> </em></div>
          </xsl:otherwise>
        </xsl:choose>
        <ul class="pager">
          <xsl:if test="$s &gt; 0">
            <li class="previous">
              <a href="?s={-1+$request.s}"><xsl:text disable-output-escaping="yes">←</xsl:text>Previous</a>
            </li>
          </xsl:if>
          <xsl:if test="not($s) or $s &lt; $max">
          <li class="next">
            <a href="?s={1+$request.s}">Next <xsl:text disable-output-escaping="yes">→</xsl:text></a>
          </li>
          </xsl:if>
        </ul>
      </xsl:if>
        
      <xsl:if test="q">
      <xsl:variable name="ents" select="document('db://test?search%5B%5D=name&amp;merge=get')"/>
        <xsl:choose>
          <xsl:when test="count($ents//test)">
            <table class="table table-striped">
              <thead><tr><th>name</th></tr></thead>
              <tbody><xsl:apply-templates select="$ents//test" mode="list"/></tbody>
            </table>
          </xsl:when>
          <xsl:otherwise>
            <div class="alert alert-info"><em> Nothing found </em></div>
          </xsl:otherwise>
        </xsl:choose>
        
      </xsl:if>
      
      </div>
    </xsl:template>
    <xsl:template match="request[id]"> 
        <xsl:apply-templates select="document(concat('db://test?limit=1&amp;filter%5Bid%5D=',id))//test" mode="page"/>
    </xsl:template>

    <xsl:template match="request[p/text()='export']"> 
      <div class="container">
      <form name='filter' id='filter' action="{$base_url}api.php?method=export" method="POST">
      <legend>Export</legend>
      <table border='0' cellpadding='5'>
      <tr><td width='50%'>
	      <span align="center">Server</span>
	      </td>
	      <td><xsl:value-of select="//SOURCE_SRV"/></td>
      </tr>
      <tr><td  width='50%'>
	      <span align="center">DB</span>
	      </td>
	      <td><xsl:value-of select="//SOURCE_DB"/></td>
      </tr>
      <tr><td  width='50%'>
	      <span align="center">Target server</span>
	      </td>
	      <td><xsl:value-of select="//TARGET_SRV"/></td>
      </tr>
      <tr><td  width='50%'>
	      <span align="center">Target DB</span>
	      </td>
	      <td><xsl:value-of select="//TARGET_DB"/></td>
      </tr>
      <tr>	
       	<td align='left'><input type="submit" value="Ok" class='dialog_form_input' size='50'/></td>
        <td align='left'><input type="button" value="Cancel" class='dialog_form_input' size='50' onclick='window.location = "{$base_url}?"'/></td>
      </tr>
      </table>
      </form>
      </div>
    </xsl:template>
    <xsl:template match="import" mode="log">
      <p class="text-success">
        <xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: Loaded <xsl:value-of select="@in_size"/> bytes from <xsl:value-of select="@in_file"/>
      </p>
    </xsl:template>
    <xsl:template match="import[last()]" mode="log">
      <p class="text-success">
        <xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: Loaded <xsl:value-of select="@in_size"/> bytes from <a href="{$base_url}/data/dump.xml"><xsl:value-of select="@in_file"/></a></p>
    </xsl:template>
    <xsl:template match="error" mode="log">
      <p class="text-error"><xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: <xsl:apply-templates select="." mode="error_msg"/></p>
    </xsl:template>
    <xsl:template match="error[@in_file='0']" mode="error_msg">Error 0</xsl:template>
    <xsl:template match="error[@in_file='1']" mode="error_msg">Error 1</xsl:template>
    <xsl:template match="error[@in_file='2']" mode="error_msg">Error 2</xsl:template>
    <xsl:template match="load" mode="log">
      <p class="text-success">
        <xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: Loaded <xsl:value-of select="@in_size"/> bytes from archive <xsl:value-of select="@in_file"/>
      </p>
    </xsl:template>
    <xsl:template match="save" mode="log">
      <p class="text-info">
        <xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: Template <xsl:value-of select="@in_file"/> saved(<xsl:value-of select="@in_size"/> bytes)
      </p>
    </xsl:template>
    <xsl:template match="export" mode="log">
      <p class="text-success">
        <xsl:value-of select="@date"/> &#160;<xsl:value-of select="@user"/>: Export done
      </p>
    </xsl:template>
    
    <xsl:template match="request[p/text()='import']">
      <div class="container">
      <legend>Log</legend>
      <xsl:apply-templates select="document('../data/log.xml')/log/*" mode="log"/>
      <form name='import' id='import' action="{$base_url}api.php?method=import" method="POST" enctype="multipart/form-data">
      <legend>Import</legend>
      <table border='0' cellpadding='5'>
      <tr><td  width='40%'>
	      <span align="center">XML source</span>
	      </td>
	      <td><input type='file' name='file' id='file' size='60' class='file_input'/></td>
      </tr>
      <tr>	
        <td align='left'><input type="button" value="Cancel" class='dialog_form_input' size='50' onclick='window.location = "{$base_url}?"'/></td>
        <td align='left'><input type="submit" value="Ok" class='dialog_form_input' size='50'/></td>
      </tr>
      </table>
      </form>

      <form name='extract' id='extract' action="{$base_url}api.php?method=extract" method="POST" enctype="multipart/form-data">
      <legend>Load archive</legend>
      <table border='0' cellpadding='5'>
      <tr><td  width='40%'>
	      <span align="center">ZIP archive</span>
	      </td>
	      <td><input type='file' name='file' id='file' size='60' class='file_input'/></td>
      </tr>
      <tr>	
        <td align='left'><input type="button" value="Cancel" class='dialog_form_input' size='50' onclick='window.location = "{$base_url}?"'/></td>
        <td align='left'><input type="submit" value="Ok" class='dialog_form_input' size='50'/></td>
      </tr>
      </table>
      </form>

      </div>
    </xsl:template>

</xsl:stylesheet>

