<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="/">
    <article>
      <header>
        <xsl:apply-templates select="/tei:TEI/tei:teiHeader"/>
      </header>
      <xsl:apply-templates select="/tei:TEI/tei:text"/>
    </article>
  </xsl:template>
  
  <xsl:template match="tei:pb" name="pb">
    <xsl:variable name="facs" select="@facs"/>
    <xsl:choose>
      <xsl:when test="normalize-space($facs) != ''">
        <!-- https://gallica.bnf.fr/ark:/12148/bpt6k1526131p/f104.image -->
        <a class="pb" href="{$facs}" target="_blank">
          <span>
            <xsl:if test="translate(@n, '1234567890', '') = ''">p. </xsl:if>
            <xsl:value-of select="@n"/>
          </span>
          <img src="{substring-before($facs, '/ark:/')}/iiif/ark:/{substring-after($facs, '/ark:/')}/full/150,/0/native.jpg"/>
        </a>
      </xsl:when>
      <xsl:otherwise>
        <span class="pb">
          <xsl:text>[</xsl:text>
          <xsl:if test="translate(@n, '1234567890', '') = ''">p. </xsl:if>
          <xsl:value-of select="@n"/>
          <xsl:text>]</xsl:text>
        </span>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>


</xsl:transform>
