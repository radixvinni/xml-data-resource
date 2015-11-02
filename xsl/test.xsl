<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" encoding="UTF-8" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />
    <xsl:param name="base_url" select="'http://localhost'"/>
    <xsl:template match="/"> 
        <html>
            <head> 
                <title>XSL Test</title>
            </head> 
            <body>
                <xsl:apply-templates/> 
            </body>
        </html> 
    </xsl:template>
    <xsl:template match="page"> 
        <p>Hello, <xsl:value-of select="USER_NAME"/>!</p>
    </xsl:template>
</xsl:stylesheet>
