<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="tei_common.xsl"/>
  <xsl:param name="filename"/>
  <xsl:output indent="yes" encoding="UTF-8" method="text" omit-xml-declaration="yes"/>
  <!-- 
  <ptr target="merveilles17_img_piefront.xml#piefront" type="include"/>
  -->
  <xsl:template match="/">
    <xsl:for-each select="/tei:TEI/tei:text//tei:ptr[@type = 'include']">
      <!-- src_code -->
      <xsl:value-of select="$filename"/>
      <xsl:value-of select="$tab"/>
      <!-- dst_code -->
      <xsl:variable name="dst">
        <xsl:variable name="prefix">merveilles17_</xsl:variable>
        <xsl:value-of select="$prefix"/>
        <!-- securiser, en cas de liens relatifs -->
        <xsl:value-of select="substring-before(substring-after(@target, $prefix), '.xml')"/>
      </xsl:variable>
      <xsl:value-of select="$dst"/>
      <xsl:value-of select="$lf"/>
    </xsl:for-each>
  </xsl:template>

</xsl:transform>
