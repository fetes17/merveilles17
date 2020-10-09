<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="../../style/teiHeader.xsl"/>
  <xsl:import href="../../style/flow.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:param name="locorum"/>
  <xsl:variable name="place" select="document($locorum)/*/*"/>
  <xsl:key name="placeName" match="tei:placeName" use="normalize-space(@key)"/>
  <xsl:key name="persName" match="tei:persName" use="normalize-space(@key)"/>
  <xsl:key name="tech" match="tei:tech" use="normalize-space(@type)"/>
  <xsl:key name="ana" match="*[@ana]" use="normalize-space(@ana)"/>
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:template match="/">
    <article class="document">
      <div id="doc_bibl">
        <div class="container">
          <div class="row">
            <div class="col-9">
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt"/>
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:notesStmt"/>
              <div class="blurb">
                <xsl:choose>
                  <xsl:when test="/tei:TEI/tei:sourceDoc">
                    <xsl:apply-templates select="/tei:TEI/tei:sourceDoc"/>
                  </xsl:when>
                  <xsl:otherwise>
                    <xsl:call-template name="ellipse">
                      <xsl:with-param name="node" select="/tei:TEI/tei:text/tei:body"/>
                      <xsl:with-param name="length" select="300"/>
                    </xsl:call-template>
                  </xsl:otherwise>
                </xsl:choose>
                <div>
                  <a class="texte" href="#">Accéder au texte intégral</a>
                </div>
              </div>
              <xsl:for-each select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:idno">
                <div>
                  <a class="download">
                    <xsl:attribute name="href">
                      <xsl:value-of select="."/>
                    </xsl:attribute>
                    <xsl:text>Document source</xsl:text>
                  </a>
                </div>
              </xsl:for-each>
            </div>
            <div class="col-3">
              <img src="{$filename}.jpg"/>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-9">
            <div class="doc_ana">
              <h2>Techniques d’écriture</h2>
              <xsl:for-each select="//*[@ana][@ana != 'description'][count(. | key('ana', normalize-space(@ana))[1]) = 1]">
                <xsl:sort select="normalize-space(@ana)"/>
                <xsl:variable name="key" select="normalize-space(@ana)"/>
                <div>
                  <h3>
                    <xsl:value-of select="@ana"/>
                  </h3>
                  <xsl:for-each select="key('ana', $key)">
                    <p>
                      <xsl:call-template name="ellipse">
                        <xsl:with-param name="node" select="."/>
                        <xsl:with-param name="length" select="100"/>
                      </xsl:call-template>
                    </p>
                  </xsl:for-each>
                </div>
              </xsl:for-each>
            </div>
            <div>
              <h2>Documents liés</h2> 
              <p>Sur quoi lier ?</p>
            </div>
            <div>
              <h2>Thèmes</h2>
              <xsl:variable name="names">
                <xsl:for-each select="//*[@ana][count(. | key('ana', normalize-space(@ana))[1]) = 1]">
                  <xsl:sort select="normalize-space(@ana)"/>
                  <xsl:variable name="key" select="normalize-space(@ana)"/>
                  <div>
                    <h3>
                      <xsl:value-of select="@ana"/>
                    </h3>
                    <xsl:for-each select="key('ana', $key)">
                      <p>
                        <xsl:call-template name="ellipse">
                          <xsl:with-param name="node" select="."/>
                          <xsl:with-param name="length" select="100"/>
                        </xsl:call-template>
                      </p>
                    </xsl:for-each>
                  </div>
                </xsl:for-each>
              </xsl:variable>
              <ul>
                <li>Personnages fictifs ?</li>
                <li>Nuage de mots ? Attention, ne va pas du tout réagir de la même manière sur les documents longs ou courts.</li>
              </ul>
            </div>
          </div>
          <div class="col-3">
            <div id="doc_chrono">
              <h2>Dates</h2>
              <ul>
                <xsl:for-each select="/tei:TEI/tei:text//tei:date | /tei:TEI/tei:sourceDoc//tei:date">
                  <xsl:sort select="@when"/>
                  <li>
                    <xsl:choose>
                      <xsl:when test="@when">
                        <xsl:value-of select="@when"/>
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:for-each select="@*">
                          <xsl:value-of select="name()"/>
                          <xsl:text>=</xsl:text>
                          <xsl:value-of select="."/>
                        </xsl:for-each>
                      </xsl:otherwise>
                    </xsl:choose>
                  </li>
                </xsl:for-each>
              </ul>
            </div>
            <div id="doc_place">
              <h2>Lieux</h2>
              %lieux%
            </div>
            <div id="doc_pers">
              <h2>Personnes</h2>
              %personnes%
            </div>
            <div id="doc_tech">
              <h2>Techniques</h2>
              %techniques%
            </div>
          </div>
        </div>
      </div>
    </article>
  </xsl:template>
  <xsl:template match="tei:titleStmt">
    <h1>
      <xsl:apply-templates select="tei:title/node()"/>
    </h1>
    <xsl:for-each select="tei:author">
      <div class="author">
        <xsl:apply-templates/>
      </div>
    </xsl:for-each>
    <xsl:for-each select="tei:editor">
      <div class="editor">
        <xsl:value-of select="@role"/>
        <xsl:text> </xsl:text>
        <a href="../personne/{@key}{$_html}">
          <xsl:apply-templates/>
        </a>
      </div>
    </xsl:for-each>
    <time class="date">
      <xsl:value-of select="/tei:TEI/tei:teiHeader/tei:profileDesc/tei:creation/tei:date"/>
    </time>
  </xsl:template>
  <xsl:template name="ellipse">
    <xsl:param name="node"/>
    <xsl:param name="length"/>
    <xsl:variable name="text">
      <xsl:variable name="txt1">
        <xsl:apply-templates select="$node" mode="txt"/>
      </xsl:variable>
      <xsl:variable name="txt2">
        <xsl:value-of select="normalize-space($txt1)"/>
      </xsl:variable>
      <xsl:variable name="len2" select="string-length($txt2)"/>
      <xsl:variable name="last" select="substring($txt2, $len2)"/>
      <xsl:choose>
        <xsl:when test="$last = '§'">
          <xsl:value-of select="normalize-space(substring($txt2, 1, $len2 - 1))"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$txt2"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="string-length($text) &lt;= $length">
        <xsl:value-of select="$text"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="less" select="10"/>
        <xsl:value-of select="substring($text, 1, $length - $less)"/>
        <xsl:value-of select="substring-before(concat(substring($text,$length - $less+1, $length), ' '), ' ')"/>
        <xsl:text> […]</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="*" mode="txt">
    <xsl:apply-templates mode="txt"/>
  </xsl:template>
  <xsl:template match="tei:p | tei:l | tei:head" mode="txt">
    <xsl:apply-templates mode="txt"/>
    <xsl:text disable-output-escaping="yes"> § </xsl:text>
  </xsl:template>
  <xsl:template match="tei:note" mode="txt"/>
  <xsl:template name="pbgallica">
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

  <!--
  <xsl:template match="tei:bibl">
    <div class="bibl">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="tei:title">
    <cite class="title">
      <xsl:apply-templates/>
    </cite>
  </xsl:template>

  <xsl:template match="tei:figure">
    <figure>
      <xsl:apply-templates/>
    </figure>
  </xsl:template>

  <xsl:template match="tei:figDesc">
    <figcaption>
      <xsl:apply-templates/>
    </figcaption>
  </xsl:template>

  <xsl:template match="tei:graphic">
    <img src="{@url}"/>
  </xsl:template>

  <xsl:template match="*">
    <xsl:text>&lt;</xsl:text>
    <xsl:value-of select="name()"/>
    <xsl:text>&gt;</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>&lt;/</xsl:text>
    <xsl:value-of select="name()"/>
    <xsl:text>&gt;</xsl:text>
  </xsl:template>
  -->
</xsl:transform>
