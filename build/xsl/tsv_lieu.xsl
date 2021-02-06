<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:variable name="lf">
    <xsl:text>&#10;</xsl:text>
  </xsl:variable>
  <xsl:variable name="tab">
    <xsl:text>&#9;</xsl:text>
  </xsl:variable>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <!--
  "code", "label", "parent_code", "settlement", "alt", "geo"
  -->
  <xsl:template match="/tei:listPlace">
    <root>
      <xsl:apply-templates select="tei:place"/>
    </root>
  </xsl:template>
  <xsl:template match="tei:place">
    <!-- code -->
    <xsl:value-of select="@xml:id"/>
    <xsl:value-of select="$tab"/>
    <!-- label -->
    <xsl:apply-templates select="tei:name[1]"/>
    <xsl:value-of select="$tab"/>
    <!-- parent_code -->
    <xsl:value-of select="parent::*/@xml:id"/>
    <xsl:value-of select="$tab"/>
    <!-- settlement -->
    <xsl:apply-templates select="tei:settlement"/>
    <xsl:value-of select="$tab"/>
    <!-- alt -->
    <xsl:apply-templates select="tei:name[2]"/>
    <xsl:value-of select="$tab"/>
    <!-- geo -->
    <xsl:apply-templates select="tei:geo"/>
    <xsl:value-of select="$lf"/>
    <xsl:apply-templates select="tei:place"/>
  </xsl:template>
  

</xsl:transform>
