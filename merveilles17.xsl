<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <!--
  <xsl:import href="../Teinte/xsl/flow.xsl"/>
  <xsl:import href="../Teinte/xsl/toc.xsl"/>
  <xsl:import href="../Teinte/xsl/teiHeader.xsl"/>
  -->
  <xsl:import href="style/flow.xsl"/>
  <xsl:import href="style/toc.xsl"/>
  <xsl:import href="style/teiHeader.xsl"/>
  <xsl:key name="persName" match="tei:persName[not(ancestor::tei:teiHeader)]" use="normalize-space(@key)"/>
  <xsl:key name="placeName" match="tei:placeName[not(ancestor::tei:teiHeader)]" use="normalize-space(@key)"/>
  <xsl:key name="tech" match="tei:tech[not(ancestor::tei:teiHeader)]" use="normalize-space(@type)"/>
  <xsl:key name="name" match="tei:name[not(ancestor::tei:teiHeader)]" use="normalize-space(@key)"/>
  <xsl:key name="ana" match="*[@ana][@ana != 'description']" use="normalize-space(@ana)"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="no"/>
  <!-- https://fetes17.github.io/files/ -->
  <xsl:variable name="theme">../style/</xsl:variable>
  <xsl:template match="/">
    <html>
      <xsl:call-template name="att-lang"/>
      <head>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" type="text/css" href="{$theme}teinte.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}sortable.css"/>
        <link rel="stylesheet" type="text/css" href="{$theme}merveilles17.css"/>
      </head>
      <body>
        <div id="split">
          <aside id="aside">
            <!--
            <div id="navaside">
              <a href="#sommaire">Sommaire</a>
              <xsl:text> | </xsl:text>
              <a href="#personnes">Personnes</a>
              <xsl:text> | </xsl:text>
              <a href="#lieux">Lieux</a>
              <xsl:text> | </xsl:text>
              <a href="#techniques">Techniques</a>
              <xsl:text> | </xsl:text>
              <a href="#personnages">Personnages</a>
            </div>
            -->
            <xsl:variable name="toc">
              <xsl:call-template name="toc"/>
            </xsl:variable>
            <xsl:if test="//tei:div/tei:head">
              <details id="sommaire">
                <summary>Sommaire</summary>
                <xsl:call-template name="toc"/>
              </details>
            </xsl:if>
            <xsl:call-template name="taglist">
              <xsl:with-param name="tag">persName</xsl:with-param>
              <xsl:with-param name="label">Personnes</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="taglist">
              <xsl:with-param name="tag">placeName</xsl:with-param>
              <xsl:with-param name="label">Lieux</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="taglist">
              <xsl:with-param name="tag">name</xsl:with-param>
              <xsl:with-param name="label">Personnages</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="taglist">
              <xsl:with-param name="tag">tech</xsl:with-param>
              <xsl:with-param name="label">Techniques</xsl:with-param>
            </xsl:call-template>
            <xsl:call-template name="taglist">
              <xsl:with-param name="tag">ana</xsl:with-param>
              <xsl:with-param name="label">Écriture</xsl:with-param>
            </xsl:call-template>
            <p> </p>
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
  
  <xsl:template name="taglist">
    <xsl:param name="tag"/>
    <xsl:param name="label"/>
    <xsl:variable name="rows">
      <xsl:choose>
        <xsl:when test="$tag = 'ana'">
          <xsl:for-each select="//*[@ana][@ana != 'description'][count(. | key($tag, normalize-space(@ana))[1]) = 1][not(ancestor::tei:teiHeader)]">
            <xsl:sort select="normalize-space(@ana)"/>
            <xsl:call-template name="tr">
              <xsl:with-param name="tag" select="$tag"/>
              <xsl:with-param name="key" select="normalize-space(@ana)"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="//*[name() = $tag][count(. | key($tag, normalize-space(@key|@type))[1]) = 1][not(ancestor::tei:teiHeader)]">
            <xsl:sort select="normalize-space(@key|@type)"/>
            <xsl:call-template name="tr">
              <xsl:with-param name="tag" select="$tag"/>
              <xsl:with-param name="key" select="normalize-space(@key|@type)"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="$rows != ''">
      <details id="{$tag}" class="terms">
        <summary>
          <xsl:value-of select="$label"/>
          <!--
          <a class="persName">
            <xsl:attribute name="href">
              <xsl:for-each select="//tei:persName[1][not(ancestor::tei:teiHeader)]">
                <xsl:call-template name="href"/>
              </xsl:for-each>
            </xsl:attribute>
          </a>
            -->
        </summary>
        <table class="sortable" data-sort="1">
          <thead>
            <tr>
              <th>
                <xsl:value-of select="$label"/>
              </th>
              <th title="Occurrences">nb</th>
            </tr>
          </thead>
          <tbody>
            <xsl:copy-of select="$rows"/>
          </tbody>
        </table>
      </details>
    </xsl:if>
  </xsl:template>
  
  <!-- 
  <graphic url="https://gallica.bnf.fr/iiif/ark:/12148/btv1b10527887j//f17/full/full/0/native.jpg"/>
  -->
  <xsl:template match="tei:graphic">
    <xsl:choose>
      <xsl:when test="contains(@url, '/iiif/')">
        <picture>
          <xsl:variable name="src">
            <xsl:value-of select="substring-before(@url, '/full/0/')"/>
            <xsl:text>/500,/0/</xsl:text>
            <xsl:value-of select="substring-after(@url, '/full/0/')"/>
          </xsl:variable>
          <img src="{$src}"/>
        </picture>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-imports/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="tr">
    <xsl:param name="tag"/>
    <xsl:param name="key"/>
    <xsl:variable name="count">
      <xsl:choose>
        <xsl:when test="$tag = 'placeName'">
          <xsl:value-of select="count(//tei:placeName[@key][@key != ''][starts-with(@key, $key)][not(ancestor::tei:teiHeader)])"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="count(key($tag, $key))"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>   
    <tr>
      <td class="term">
        <a>
          <xsl:attribute name="id">
            <xsl:choose>
              <xsl:when test="$key != ''">
                <xsl:value-of select="translate($key, $idfrom, $idto)"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="concat($tag, $nokey)"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:attribute name="data-tag">
            <xsl:value-of select="$tag"/>
          </xsl:attribute>
          <xsl:if test="$count = 1">
            <xsl:attribute name="href">
              <xsl:call-template name="href"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:choose>
            <xsl:when test="$key != ''">
              <xsl:value-of select="$key"/>
            </xsl:when>
            <xsl:otherwise>
              <text>???</text>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </td>
      <td class="nb">
        <xsl:value-of select="$count"/>
      </td>
    </tr>

  </xsl:template>
  
  <xsl:template match="tei:pb" name="pb">
    <xsl:variable name="facs" select="@facs"/>
    <xsl:choose>
      <xsl:when test="false() and normalize-space($facs) != ''">
        <!-- https://gallica.bnf.fr/ark:/12148/bpt6k1526131p/f104.image -->
        <a class="pb" href="{$facs}" target="_blank">
          <span>
            <xsl:if test="translate(@n, '1234567890', '') = ''">p. </xsl:if>
            <xsl:value-of select="@n"/>
          </span>
          <img src="{substring-before($facs, '/ark:/')}/iiif/ark:/{substring-after(substring-before(concat($facs, '.image'), '.image'), '/ark:/')}/full/150,/0/native.jpg"/>
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
