<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="style/flow.xsl"/>
  <xsl:import href="style/toc.xsl"/>
  <xsl:key name="persName" match="tei:persName" use="normalize-space(@key)"/>
  <xsl:key name="placeName" match="tei:placeName" use="normalize-space(@key)"/>
  <xsl:key name="tech" match="tei:tech" use="normalize-space(@type)"/>
  <xsl:key name="name" match="tei:name" use="normalize-space(@key)"/>
  <xsl:key name="ana" match="*[@ana]" use="normalize-space(@ana)"/>
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
        <script src="{$theme}split.js">//</script>
      </head>
      <body>
        <div id="split">
          <aside id="aside">
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
            <div id="sommaire">
              <h2>Sommaire</h2>
              <xsl:call-template name="toc"/>
            </div>
            <div id="personnes">
              <h2>Personnes</h2>
              <ul>
                <xsl:for-each select="//tei:persName[count(. | key('persName', normalize-space(@key))[1]) = 1][not(ancestor::tei:teiHeader)]">
                  <xsl:sort select="normalize-space(@key)"/>
                  <xsl:variable name="key" select="normalize-space(@key)"/>
                  <li>
                    <xsl:attribute name="id">
                      <xsl:choose>
                        <xsl:when test="@key">
                          <xsl:value-of select="translate($key, $idfrom, $idto)"/>
                        </xsl:when>
                        <xsl:otherwise>persNamenokey</xsl:otherwise>
                      </xsl:choose>
                    </xsl:attribute>
                    <b>
                      <xsl:choose>
                        <xsl:when test="$key != ''">
                          <xsl:value-of select="@key"/>
                        </xsl:when>
                        <xsl:otherwise>
                          <text>@key ?</text>
                        </xsl:otherwise>
                      </xsl:choose>
                    </b>
                    <xsl:text> : </xsl:text>
                    <xsl:for-each select="key('persName', $key)">
                      <xsl:if test="position() != 1">, </xsl:if>
                      <a>
                        <xsl:attribute name="href">
                          <xsl:call-template name="href"/>
                        </xsl:attribute>
                        <xsl:choose>
                          <xsl:when test="normalize-space(.) != ''">
                            <xsl:value-of select="normalize-space(.)"/>
                          </xsl:when>
                          <xsl:otherwise>
                            <i>[vide]</i>
                          </xsl:otherwise>
                        </xsl:choose>
                      </a>
                    </xsl:for-each>
                    <xsl:text>.</xsl:text>
                  </li>
                </xsl:for-each>
              </ul>
            </div>
            <div id="lieux">
              <h2>Lieux</h2>
              <ul>
                <xsl:for-each select="//tei:placeName[count(. | key('placeName', normalize-space(@key))[1]) = 1][not(ancestor::tei:teiHeader)]">
                  <xsl:variable name="key" select="normalize-space(@key)"/>
                  <li>
                    <xsl:attribute name="id">
                      <xsl:choose>
                        <xsl:when test="$key != ''">
                          <xsl:value-of select="translate($key, $idfrom, $idto)"/>
                        </xsl:when>
                        <xsl:otherwise>technokey</xsl:otherwise>
                      </xsl:choose>
                    </xsl:attribute>
                    <b>
                      <xsl:choose>
                        <xsl:when test="@key">
                          <xsl:value-of select="@key"/>
                        </xsl:when>
                        <xsl:otherwise>
                          <text>@key ?</text>
                        </xsl:otherwise>
                      </xsl:choose>
                    </b>
                    <xsl:text> : </xsl:text>
                    <xsl:for-each select="key('placeName', $key)">
                      <xsl:if test="position() != 1">, </xsl:if>
                      <a>
                        <xsl:attribute name="href">
                          <xsl:call-template name="href"/>
                        </xsl:attribute>
                        <xsl:choose>
                          <xsl:when test="normalize-space(.) != ''">
                            <xsl:value-of select="normalize-space(.)"/>
                          </xsl:when>
                          <xsl:otherwise>
                            <i>[vide]</i>
                          </xsl:otherwise>
                        </xsl:choose>
                      </a>
                    </xsl:for-each>
                    <xsl:text>.</xsl:text>
                  </li>
                </xsl:for-each>
              </ul>
            </div>
            <div id="techniques">
              <h2>Techniques</h2>
              <ul>
                <xsl:for-each select="//tei:tech[count(. | key('tech', normalize-space(@type))[1]) = 1][not(ancestor::tei:teiHeader)]">
                  <xsl:variable name="key" select="normalize-space(@type)"/>
                  <li>
                    <xsl:attribute name="id">
                      <xsl:choose>
                        <xsl:when test="$key != ''">
                          <xsl:value-of select="translate($key, $idfrom, $idto)"/>
                        </xsl:when>
                        <xsl:otherwise>technokey</xsl:otherwise>
                      </xsl:choose>
                    </xsl:attribute>
                    <b>
                      <xsl:choose>
                        <xsl:when test="@type">
                          <xsl:value-of select="@type"/>
                        </xsl:when>
                        <xsl:otherwise>
                          <text>@key ?</text>
                        </xsl:otherwise>
                      </xsl:choose>
                    </b>
                    <xsl:text> : </xsl:text>
                    <xsl:for-each select="key('tech', $key)">
                      <xsl:if test="position() != 1">, </xsl:if>
                      <a>
                        <xsl:attribute name="href">
                          <xsl:call-template name="href"/>
                        </xsl:attribute>
                        <xsl:choose>
                          <xsl:when test="normalize-space(.) != ''">
                            <xsl:value-of select="normalize-space(.)"/>
                          </xsl:when>
                          <xsl:otherwise>
                            <i>[vide]</i>
                          </xsl:otherwise>
                        </xsl:choose>
                      </a>
                    </xsl:for-each>
                    <xsl:text>.</xsl:text>
                  </li>
                </xsl:for-each>
              </ul>
            </div>
            <div id="personnages">
              <h2>Personnages</h2>
              <ul>
                <xsl:for-each select="//tei:name[count(. | key('name', normalize-space(@key))[1]) = 1][not(ancestor::tei:teiHeader)] ">
                  <xsl:variable name="key" select="normalize-space(@key)"/>
                  <li>
                    <xsl:attribute name="id">
                      <xsl:choose>
                        <xsl:when test="$key != ''">
                          <xsl:value-of select="translate($key, $idfrom, $idto)"/>
                        </xsl:when>
                        <xsl:otherwise>namenokey</xsl:otherwise>
                      </xsl:choose>
                    </xsl:attribute>
                    <b>
                      <xsl:choose>
                        <xsl:when test="@key">
                          <xsl:value-of select="@key"/>
                        </xsl:when>
                        <xsl:otherwise>
                          <text>@key ?</text>
                        </xsl:otherwise>
                      </xsl:choose>
                    </b>
                    <xsl:text> : </xsl:text>
                    <xsl:for-each select="key('name', $key)">
                      <xsl:if test="position() != 1">, </xsl:if>
                      <a>
                        <xsl:attribute name="href">
                          <xsl:call-template name="href"/>
                        </xsl:attribute>
                        <xsl:choose>
                          <xsl:when test="normalize-space(.) != ''">
                            <xsl:value-of select="normalize-space(.)"/>
                          </xsl:when>
                          <xsl:otherwise>
                            <i>[vide]</i>
                          </xsl:otherwise>
                        </xsl:choose>
                      </a>
                    </xsl:for-each>
                    <xsl:text>.</xsl:text>
                  </li>
                </xsl:for-each>
              </ul>
            </div>
            <p> </p>
          </aside>
          <main id="main">
            <xsl:apply-templates select="/tei:TEI/tei:teiHeader"/>
            <xsl:apply-templates select="/tei:TEI/tei:text"/>
            <xsl:apply-templates select="/tei:TEI/tei:sourceDoc"/>
          </main>
        </div>
        <script src="{$theme}merveilles17.js">//</script>
      </body>
    </html>
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
