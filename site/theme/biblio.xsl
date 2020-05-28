<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:param name="href"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <li>
      <xsl:variable name="bibl" select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl[1]"/>
        <xsl:choose>
          <xsl:when test="$bibl">
            <a href="{$href}">
              <xsl:apply-templates select="$bibl/node()"/>
            </a>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>[teiHeader//bibl absent] </xsl:text>
            <a href="{$href}">
              <xsl:value-of select="$href"/>
            </a>
          </xsl:otherwise>
        </xsl:choose>
    </li>
  </xsl:template>

</xsl:transform>
