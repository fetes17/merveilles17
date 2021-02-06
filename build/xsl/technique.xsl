<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/tei:superEntry">
    <div class="container">
      <h1>Techniques</h1>
      <nav class="technique">
        <ul>
          <xsl:apply-templates select="*"/>
        </ul>
      </nav>
    </div>
    <p> </p>
  </xsl:template>
  <xsl:template match="tei:entry">
    <li class="entry">
      <a href="{@xml:id}.html">
        <xsl:apply-templates select="tei:form[1]"/>
      </a>
      <xsl:if test="tei:entry">
        <ul>
          <xsl:apply-templates select="tei:entry"/>
        </ul>
      </xsl:if>
    </li>
  </xsl:template>
</xsl:transform>
