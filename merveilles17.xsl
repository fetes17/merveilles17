<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="build/xsl/liseuse.xsl"/>
  <!-- https://fetes17.github.io/files/ -->
  <xsl:variable name="theme">../build/theme/</xsl:variable>
  <xsl:template match="/">
    <html>
      <xsl:call-template name="att-lang"/>
      <head>
        <meta charset="UTF-8"/>
        <link href="https://fonts.googleapis.com/css2?Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&amp;family=Titillium+Web:ital,wght@0,200;0,400;0,600;1,200;1,400;1,600&amp;display=swap" rel="stylesheet"/>
        <link rel="stylesheet" type="text/css" href="{$theme}teinte.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}sortable.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}split.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}merveilles17.css"/>
      </head>
      <body>
        <div id="split">
          <aside id="aside">
            <xsl:call-template name="explorer"/>
          </aside>
          <main id="main">
            <xsl:apply-templates select="/tei:TEI/tei:teiHeader"/>
            <xsl:apply-templates select="/tei:TEI/tei:text"/>
            <xsl:apply-templates select="/tei:TEI/tei:sourceDoc"/>
          </main>
        </div>
        <div id="bookmarks">
          <mark class="toclone"/>
        </div>
        <script src="{$theme}split.js">//</script>
        <script src="{$theme}merveilles17.js">//</script>
        <script src="{$theme}sortable.js">//</script>
      </body>
    </html>
  </xsl:template>

</xsl:transform>
