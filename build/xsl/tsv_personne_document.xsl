<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="tei_common.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <xsl:for-each select="/tei:TEI/tei:text//tei:persName 
      | /tei:TEI/tei:sourceDoc//tei:persName  
      | /tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt//tei:*[@key]
      ">
      <xsl:call-template name="split">
        <xsl:with-param name="role">
          <xsl:choose>
            <xsl:when test="self::tei:author">auteur</xsl:when>
            <xsl:when test="@role">
              <xsl:value-of select="normalize-space(@role)"/>
            </xsl:when>
          </xsl:choose>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="split">
    <xsl:param name="role" select="normalize-space(@role)"/>
    <xsl:variable name="role1" select="substring-before(concat($role, ' '), ' ')"/>
    <xsl:variable name="key" select="normalize-space(@key)"/>
    <xsl:value-of select="$key"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="$filename"/>
    <xsl:value-of select="$tab"/>
    <xsl:call-template name="id"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space(.)"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space($role1)"/>
    <xsl:value-of select="$lf"/>
    <xsl:if test="contains($role, ' ')">
      <xsl:call-template name="split">
        <xsl:with-param name="role" select="substring-after($role, ' ')"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

</xsl:transform>
