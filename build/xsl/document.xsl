<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <xsl:value-of select="$filename"/>
    <xsl:value-of select="$tab"/>
    <xsl:variable name="type" select="substring-before(substring-after($filename, '_'), '_')"/>
    <xsl:value-of select="$type"/>
    <xsl:value-of select="$tab"/>
    <xsl:variable name="bibl">
      <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl[1]/node()"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="normalize-space($bibl) != ''">
        <xsl:copy-of select="$bibl"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>[</xsl:text>
        <xsl:value-of select="substring-after(substring-after($filename, '_'), '_')"/>
        <xsl:text>]</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="string-length(normalize-space(/tei:TEI/tei:text))"/>
  </xsl:template>
  <xsl:template match="text()">
    <xsl:variable name="text" select="translate(., ' ', '')"/>
    <xsl:if test="translate(substring($text, 1,1), concat(' ', $lf, $cr, $tab), '') = ''">
      <xsl:text> </xsl:text>
    </xsl:if>
    <xsl:value-of select="normalize-space($text)"/>
    <xsl:if test="translate(substring($text, string-length($text)), concat(' ', $lf, $cr, $tab), '') = ''">
      <xsl:text> </xsl:text>
    </xsl:if>
  </xsl:template>
</xsl:transform>
