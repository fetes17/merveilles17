<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:variable name="lf">
    <xsl:text>&#10;</xsl:text>
  </xsl:variable>
  <xsl:variable name="tab">
    <xsl:text>&#9;</xsl:text>
  </xsl:variable>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <!--
  "code", "label", "parent_code"
  -->
  <xsl:template match="/tei:superEntry">
    <root>
      <xsl:apply-templates select="tei:entry"/>
    </root>
  </xsl:template>
  <xsl:template match="tei:entry">
    <!-- code -->
    <xsl:value-of select="@xml:id"/>
    <xsl:value-of select="$tab"/>
    <!-- label -->
    <xsl:apply-templates select="tei:form[1]"/>
    <xsl:value-of select="$tab"/>
    <!-- parent_code -->
    <xsl:value-of select="parent::*/@xml:id"/>
    <xsl:value-of select="$lf"/>
    <xsl:apply-templates select="tei:entry"/>
  </xsl:template>
  

</xsl:transform>
