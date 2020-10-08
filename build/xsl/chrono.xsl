<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/tei:listEvent">
    <nav>
      <xsl:apply-templates select="*"/>
    </nav>
  </xsl:template>
  
  <xsl:template match="tei:event">
    <div class="event">
      <span class="year">
        <xsl:value-of select="substring(@when, 1, 4)"/>
      </span>
      <span class="day">
        <xsl:value-of select="number(substring(@when, 9, 2))"/>
      </span>
      <span class="month">
        <xsl:variable name="num" select="number(substring(@when, 6, 2))"/>
        <xsl:choose>
          <xsl:when test="$num = 1">janvier</xsl:when>
          <xsl:when test="$num = 2">février</xsl:when>
          <xsl:when test="$num = 3">mars</xsl:when>
          <xsl:when test="$num = 4">avril</xsl:when>
          <xsl:when test="$num = 5">mai</xsl:when>
          <xsl:when test="$num = 6">juin</xsl:when>
          <xsl:when test="$num = 7">juillet</xsl:when>
          <xsl:when test="$num = 8">août</xsl:when>
          <xsl:when test="$num = 9">septembre</xsl:when>
          <xsl:when test="$num = 10">octobre</xsl:when>
          <xsl:when test="$num = 11">novembre</xsl:when>
          <xsl:when test="$num = 12">décembre</xsl:when>
        </xsl:choose>
      </span>
    </div>
  </xsl:template>


</xsl:transform>
