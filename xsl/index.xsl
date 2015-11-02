<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" encoding="UTF-8" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />
    <xsl:param name="base_url" select="'http://localhost/'"/>
    <xsl:param name="request.s" select="0"/>
    <xsl:variable name="p" select="/page/request/p/text()"/>
    <xsl:variable name="admin" select="/page/request/admin/text()"/>
    <xsl:variable name="table" select="/page/request/table/text()"/>
    <xsl:variable name="success" select="/page/request/success/text()"/>
    <xsl:template match="/page"> 
        <html>
            <head> 
                <link href="{$base_url}css/bootstrap.min.css" rel="stylesheet" media="screen"/>
                <link href="{$base_url}css/select2.css" rel="stylesheet" media="screen"/>
                <link href="{$base_url}css/select2-bootstrap.css" rel="stylesheet" media="screen"/>
                <script src="{$base_url}js/jquery.min.js"></script>
                <script src="{$base_url}js/bootstrap.min.js"></script>
                <script src="{$base_url}js/select2.min.js"></script>
                <script src="{$base_url}js/select2_locale_ru.js"></script>
                <style>
                .bordered li a {display:block;padding-top: 6px;background-color:#eee;color:black;
                padding-bottom: 6px;margin-top: 2px;margin-bottom: 2px;padding-right: 12px;padding-left: 12px;margin-right: 6px;
                line-height: 14px;font-weight:bold;font-size: 13px;}
                .bordered .dropdown li {display:block;padding-top: 6px;background-color:#eee;color:black;width: 124px;
                padding-bottom: 6px;margin-top: 2px;margin-bottom: 2px;padding-right: 12px;padding-left: 12px;margin-right: 6px;margin-left: 6px;
                line-height: 14px;font-size: 13px;}
                #logout {padding:0;width: 148px;}
                #logout a {margin:0}
                .bordered li a:hover {background-color: #e0e0e0;}
                .bordered li.active a {color: white;background-color: rgb(0, 136, 204);}
                .bordered li {float: left; line-height: 20px;}
                .bordered::before, .bordered:after {clear: both;display:table;line-height: 0;content: "";}
                .footer {text-align:center;}
                </style>
            </head> 
            <body>
                <div class="navbar-fixed-top" style="position: relative;">
                  <ul class="nav bordered" style="padding:5px;border-bottom:3px double #eee;">
                    <li>
                      <a href="{$base_url}?">Back</a>
                    </li>
                    <li class="pull-right dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-target="#" id="logoutMenu"><i class="icon icon-user"/></a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="logoutMenu">
                        <li><b>user: </b> <xsl:value-of select="//USER_NAME"/></li>
                        <li><b>group: </b> <xsl:value-of select="//USER_GROUP"/></li>
                        <li><b>info: </b> <xsl:value-of select="//USER_GECOS"/></li>
                        <li><b>maclabel: </b> <xsl:value-of select="//USER_MACLABEL"/></li>
                        <li id="logout"><a tabindex="-1" href="{$logout_url}">Logout</a></li>
                      </ul>
                    </li>
                    <xsl:if test="//USER_GROUP = 'root'">
                      <li class="pull-right">
                        <xsl:if test="$admin != ''"><xsl:attribute name="class">active pull-right</xsl:attribute></xsl:if>
                        <a href="{$base_url}?admin=data.xsl"><i class="icon icon-cog"/></a>
                      </li>
                      <li class="pull-right">
                        <xsl:if test="$table != ''"><xsl:attribute name="class">active pull-right</xsl:attribute></xsl:if>
                        <a href="{$base_url}?table=all"><i class="icon icon-lock"/></a>
                      </li>
                      <li class="pull-right">
                        <xsl:if test="$p = 'import'"><xsl:attribute name="class">active pull-right</xsl:attribute></xsl:if>
                        <a href="{$base_url}?p=import">Import XML</a>
                      </li>
                      <li class="pull-right">
                        <xsl:if test="$p = 'export'"><xsl:attribute name="class">active pull-right</xsl:attribute></xsl:if>
                        <a href="{$base_url}?p=export">Export to DB</a>
                      </li>
                    </xsl:if>
                  </ul>
                </div>
                  <xsl:apply-templates select="request"/>
            </body>
        </html> 
    </xsl:template>
    <xsl:include href = "request.xsl"/>
    <xsl:include href = "data.xsl"/>
    <xsl:include href = "admin.xsl"/>
</xsl:stylesheet>
