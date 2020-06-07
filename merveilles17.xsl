<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="style/flow.xsl"/>
  <xsl:import href="style/toc.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="no"/>
  <!-- https://fetes17.github.io/files/ -->
  <xsl:variable name="theme">../style/</xsl:variable>
  <xsl:template match="/">
    <html>
      <xsl:call-template name="att-lang"/>
      <head>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" type="text/css" href="{$theme}teinte.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}merveilles17.css"/>
      </head>
      <body>
        <header id="header">
          <xsl:apply-templates select="/tei:TEI/tei:teiHeader"/>
        </header>
        <main>
          <xsl:apply-templates select="/tei:TEI/tei:text"/>
        </main>
        <aside id="aside">
          <xsl:call-template name="toc"/>
        </aside>
        <footer id="footer">
          
        </footer>
      </body>
    </html>
    
  </xsl:template>

</xsl:transform>
