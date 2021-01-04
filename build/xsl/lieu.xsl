<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/tei:listPlace">
    <nav class="place">
      <ul>
        <xsl:apply-templates select="*"/>
      </ul>
    </nav>
  </xsl:template>
  <xsl:template match="tei:place">
    <li class="place">
      <a href="{@xml:id}.html">
        <xsl:apply-templates select="tei:name[1]"/>
      </a>
      <xsl:if test="tei:place">
        <ul>
          <xsl:apply-templates select="tei:place"/>
        </ul>
      </xsl:if>
    </li>
  </xsl:template>
</xsl:transform>
