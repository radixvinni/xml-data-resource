<?xml version='1.0'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:set="http://exslt.org/sets" extension-element-prefixes="set" xml:space="preserve">
    <xsl:template match="request[admin]">
      <link rel="stylesheet" href="{$base_url}css/codemirror.css"/>
      <style type="text/css" media="screen">
      #editor, .CodeMirror { 
        width:100%;
        height:550px;
        margin-top: 10px;
        display:inline-block;
      }
      </style>
      <div class="container-fluid">
      
      <form class="row-fluid" name='admin' action="{$base_url}api.php?method=save" method="POST">

      <div class="span2">

            <ul class="nav nav-tabs nav-stacked">
                  <xsl:apply-templates select="document('func://list_xsl')" mode="admin_menu"/>
            </ul>
      </div>
      <div class="span10">
            <xsl:if test="$success='1'">
              <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Saved!</strong> Templated saved.
              </div>
            </xsl:if>
             <input type="hidden" name="f" value="{admin/text()}"/>
             <div class="btn-group">
              <input type="submit" value="Save" class='btn'/>
              <input type="button" value="Cancel" class="btn" onclick='window.location = "{$base_url}?"'/>
            </div>
            <textarea name="contents" cols="50" rows="100" id="editor"><xsl:if test="admin/text() != ''"><xsl:value-of select="document(concat('func://escape?f=',admin/text()))"/></xsl:if></textarea>
      </div>
      </form>
      </div>

      <script src="{$base_url}js/codemirror.min.js" type="text/javascript" charset="utf-8"></script>
      <script src="{$base_url}js/xml.js" type="text/javascript" charset="utf-8"></script>
      <script>
           var myCodeMirror = CodeMirror.fromTextArea(document.getElementById("editor"),{
    lineNumbers: true,
    mode: "xml"
  });
      </script>
    </xsl:template>

    <xsl:template match="data/*" mode="admin_menu">
              <li><xsl:if test="$admin = text()"><xsl:attribute name="class">active</xsl:attribute></xsl:if>
              <a href="{$base_url}?admin={text()}"><xsl:value-of select="." /></a></li>
    </xsl:template>
    <xsl:template match="data/*[starts-with(text(),'.')]" mode="admin_menu"/>
    
    <xsl:template match="request[table]">
      <xsl:variable name="query" select="document(concat('db://',table,'?limit=20'))"/>
      <xsl:variable name="data" select="$query/data/*"/>
      <xsl:variable name="max" select="$query/data/@total div 20 - 1"/>
        <div class="container">
          <div class="row">
              <xsl:variable name="cols" select="document(concat('db://',table,'?describe'))/data/col"/>
              <h3>Columns</h3>
              <table class="table">
                <tr>
                  <th>Column</th>
                </tr>
                <xsl:for-each select="$cols">
                  <tr>
                    <td><xsl:value-of select="attname"/></td>
                  </tr>
                </xsl:for-each>
              </table>
            <xsl:if test="count($data) = 0">
              <center><i>Nothing to show</i></center>
            </xsl:if>
            <xsl:if test="count($data)">
            <h3>Records</h3>
            <table class="table">
              <tr>
                <th><xsl:value-of select="name($data/*[1])"/></th>
                <th><xsl:value-of select="name($data/*[2])"/></th>
              </tr>
              <xsl:for-each select="$data">
                <tr>
                  <td><xsl:value-of select="*[1]"/></td>
                  <td><xsl:value-of select="*[2]"/></td>
                </tr>
              </xsl:for-each>
            </table>
            </xsl:if>
          </div>
        </div>
            
    </xsl:template>
    <xsl:template match="request[table='all']">
      <xsl:variable name="data" select="document('db://')/data/*"/>
      <xsl:if test="count($data) = 0">
        <center><i>Nothing to show</i></center>
      </xsl:if>
      <xsl:if test="count($data)">
        <div class="container">
          <table class="table row">
            <tr><th>Table</th><th>Record count</th></tr>
            <xsl:for-each select="set:distinct($data)">
              <tr>
                <td><a href="{$base_url}?table={relname}&amp;colmacs={relusecolmacs}">Table <xsl:value-of select="relname"/></a></td>
                <td><xsl:value-of select="reltuples"/></td>
              </tr>
            </xsl:for-each>
          </table>
        </div>
 
      </xsl:if>
      
    </xsl:template>

    <xsl:template name="model-body">
    </xsl:template>
    <xsl:template name="model-body-null">
    </xsl:template>

</xsl:stylesheet>

