<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="tei_flow.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <!--
  type	code	length	title	pubdate	pubplace	publisher	idno	ptr	bibl
  -->
  <xsl:template match="/">
    <!-- type -->
    <xsl:variable name="type" select="substring-before(substring-after($filename, '_'), '_')"/>
    <xsl:value-of select="$type"/>
    <!-- code -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="$filename"/>
    <!-- length -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="string-length(normalize-space(/tei:TEI/tei:text|/tei:TEI/tei:sourceDoc))"/>
    <!-- title -->
    <xsl:value-of select="$tab"/>
    <xsl:variable name="title">
      <xsl:apply-templates select="(/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title)[1]/node()"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="normalize-space($title) != ''">
        <xsl:copy-of select="$title"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>[</xsl:text>
        <xsl:value-of select="substring-after(substring-after($filename, '_'), '_')"/>
        <xsl:text>]</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <!-- bibnote -->
    <xsl:value-of select="$tab"/>
    <xsl:apply-templates select="(/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl)[1]//tei:note/node()"/>
    <p>Responsables : </p><xsl:for-each select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:editionStmt/tei:respStmt">
      <span class="contributeurice"><xsl:value-of select="tei:name/text()"/>, <xsl:value-of select="tei:resp/text()"/> — </span>
    </xsl:for-each>
    <!-- ptr -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space((/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:ptr)[1]/@target)"/>
    <!-- bibl -->
    <xsl:value-of select="$tab"/>
    <xsl:apply-templates select="(/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl)[1]/node()"/>
  </xsl:template>
  
  <xsl:template name="old">
    <!-- pubdate -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space((/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:date)[1])"/>
    <!-- pubplace -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space((/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:pubPlace)[1])"/>
    <!-- publisher -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space((/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:publisher)[1])"/>
    <!-- idno -->
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space((/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:idno)[1])"/>

  </xsl:template>
  
  <!-- sortir les sauts de lignes du texte brut -->
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
