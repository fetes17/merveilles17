<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="common.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <xsl:for-each select="/tei:TEI/tei:text//tei:placeName | /tei:TEI/tei:sourceDoc//tei:placeName">
      <xsl:variable name="key" select="normalize-space(@key)"/>
      <xsl:value-of select="$key"/>
      <xsl:value-of select="$tab"/>
      <xsl:value-of select="$filename"/>
      <xsl:value-of select="$tab"/>
      <xsl:call-template name="id"/>
      <xsl:value-of select="$tab"/>
      <xsl:value-of select="normalize-space(.)"/>
      <xsl:value-of select="$tab"/>
      <xsl:for-each select="ancestor::*[@ana='description'][1]">
        <xsl:apply-templates mode="title"/>
      </xsl:for-each>
      <xsl:value-of select="$lf"/>
      
    </xsl:for-each>
  </xsl:template>

</xsl:transform>
