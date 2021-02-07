<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="tei_common.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <!-- 
        <author key="benserade">Benserade Isaac de (1613-1691)</author>
  -->
  <xsl:template match="/">
    <xsl:for-each select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt//*[@key]">
      <!-- document_code -->
      <xsl:value-of select="$filename"/>
      <xsl:value-of select="$tab"/>
      <!-- person_code -->
      <xsl:value-of select="normalize-space(@key)"/>
      <xsl:value-of select="$tab"/>
      <!-- resp -->
      <xsl:choose>
        <xsl:when test="@role">
          <xsl:value-of select="@role"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="local-name()"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="$lf"/>
    </xsl:for-each>
  </xsl:template>

</xsl:transform>
