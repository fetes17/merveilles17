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

</xsl:transform>
