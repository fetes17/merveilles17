<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:param name="name"/>
  <xsl:param name="href" select="concat($name, '.html')"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <li>
      <xsl:text>[</xsl:text>
      <xsl:value-of select="substring-after(substring-after($name, '_'), '_')"/>
      <xsl:text>] </xsl:text>
      <xsl:variable name="bibl">
        <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl[1]/node()"/>
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="normalize-space($bibl) != ''">
          <a href="{$href}">
            <xsl:copy-of select="$bibl"/>
          </a>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>teiHeader//bibl ??? </xsl:text>
          <a href="{$href}">
            <xsl:value-of select="$href"/>
          </a>
        </xsl:otherwise>
      </xsl:choose>
    </li>
  </xsl:template>

</xsl:transform>
