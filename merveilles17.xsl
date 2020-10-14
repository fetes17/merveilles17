<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="build/xsl/page_texte.xsl"/>
  <xsl:variable name="theme">../build/theme/</xsl:variable>
  <xsl:template match="/">
    <html>
      <xsl:call-template name="att-lang"/>
      <head>
        <meta charset="UTF-8"/>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&amp;family=Titillium+Web:ital,wght@0,200;0,400;0,600;1,200;1,400;1,600&amp;display=swap" rel="stylesheet"/>
        <link rel="stylesheet" type="text/css" href="{$theme}teinte.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}sortable.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}split.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}merveilles17.css"/>
      </head>
      <body>
        <div style="width: 1200px; margin-left: auto; margin-right: auto; display: flex;">
          <aside id="explorer" style="flex: 0 0 33%;">
            <xsl:call-template name="explorer"/>
          </aside>
          <div id="explorable" style="flex: 0 0 66%;">
            <xsl:apply-templates select="/tei:TEI/tei:text"/>
          </div>
        </div>
        <div id="bookmarks">
          <mark class="toclone"/>
        </div>
        <script src="{$theme}merveilles17.js">//</script>
        <script src="{$theme}sortable.js">//</script>
      </body>
    </html>
  </xsl:template>

</xsl:transform>
