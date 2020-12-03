<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="tei_flow.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:variable name="lieux" select="document('../../index/lieu.xml')/*/*"/>
  <xsl:template match="/tei:listEvent">
    <nav class="chrono">
      <xsl:apply-templates select="*"/>
    </nav>
  </xsl:template>
  <xsl:template match="tei:event">
    <xsl:variable name="date" select="@from | @when"/>
    <a class="caldate" href="document/index.html#{@xml:id}">
      <xsl:variable name="where" select="@where"/>
      <xsl:attribute name="title">
        <xsl:variable name="lieu" select="$lieux[@xml:id = $where]/tei:name"/>
        <xsl:if test="$lieu != ''">
          <xsl:value-of select="$lieu"/>
          <xsl:text>. </xsl:text>
        </xsl:if>
        <xsl:value-of select="normalize-space(tei:label)"/>
        <xsl:text>.</xsl:text>
      </xsl:attribute>
      <span class="year">
        <xsl:value-of select="substring($date, 1, 4)"/>
      </span>
      <xsl:variable name="num" select="number(substring($date, 9, 2))"/>
      <xsl:if test="$num &gt; 0">
        <span class="day">
          <xsl:value-of select="$num"/>
          <xsl:if test="tei:event and @to and number(substring($date, 6, 2)) = number(substring(@to, 6, 2))">
            <xsl:variable name="num2" select="number(substring(@to, 9, 2))"/>
            <xsl:if test="$num2 &gt; 0">
              <xsl:text>-</xsl:text>
              <xsl:value-of select="$num2"/>
            </xsl:if>
          </xsl:if>
        </span>
      </xsl:if>
      <xsl:variable name="month" select="number(substring($date, 6, 2))"/>
      <xsl:if test="$month &gt; 0">
        <span class="month">
          <xsl:choose>
            <xsl:when test="$month = 1">janvier</xsl:when>
            <xsl:when test="$month = 2">février</xsl:when>
            <xsl:when test="$month = 3">mars</xsl:when>
            <xsl:when test="$month = 4">avril</xsl:when>
            <xsl:when test="$month = 5">mai</xsl:when>
            <xsl:when test="$month = 6">juin</xsl:when>
            <xsl:when test="$month = 7">juillet</xsl:when>
            <xsl:when test="$month = 8">août</xsl:when>
            <xsl:when test="$month = 9">septembre</xsl:when>
            <xsl:when test="$month = 10">octobre</xsl:when>
            <xsl:when test="$month = 11">novembre</xsl:when>
            <xsl:when test="$month = 12">décembre</xsl:when>
          </xsl:choose>
        </span>
      </xsl:if>
    </a>
    <xsl:apply-templates select="tei:event"/>
  </xsl:template>
</xsl:transform>
