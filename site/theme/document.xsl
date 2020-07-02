<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <!--
  <xsl:import href="../../style/flow.xsl"/>
  -->
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:key name="persName" match="tei:persName" use="normalize-space(@key)"/>
  <xsl:key name="tech" match="tei:tech" use="normalize-space(@type)"/>
  <xsl:key name="ana" match="*[@ana]" use="normalize-space(@ana)"/>
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:template match="/">
    <div class="row align-items-start">
      <div class="col-9">
        <p>
          <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl"/>
        </p>
        <div class="content">
          <p/>
          <p>
            <xsl:call-template name="ellipse">
              <xsl:with-param name="node" select="/tei:TEI/tei:text/tei:body"/>
              <xsl:with-param name="length" select="300"/>
            </xsl:call-template>
          </p>
        </div>
        <div class="writing">
          <h2>Techniques d’écriture</h2>
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
        </div>
        <div>
          <h2>Documents liés</h2>
          Balises pour les liens ?
        </div>
        <div>
          <h2>Thèmes</h2>
          <ul>
            <li>Personnages fictifs ?</li>
            <li>Nuage de mots ? Attention, ne va pas du tout réagir de la même manière sur les documents longs ou courts.</li>
          </ul>
        </div>
      </div>
      <div class="col-3">
        <div>
          <h2>Événements</h2>
        </div>
        <div>
          <h2>Personnes</h2>
          <!-- Grouper par clé -->
          <xsl:for-each select="//tei:persName[count(. | key('persName', normalize-space(@key))[1]) = 1]">
            <xsl:sort select="normalize-space(@key)"/>
            <xsl:variable name="key" select="normalize-space(@key)"/>
            <div>
              <h3>
                <xsl:choose>
                  <xsl:when test="@key">
                    <xsl:value-of select="@key"/>
                  </xsl:when>
                  <xsl:otherwise>@key ?</xsl:otherwise>
                </xsl:choose>
              </h3>
              <xsl:for-each select="key('persName', $key)">
                <xsl:if test="position() != 1">, </xsl:if>
                <xsl:value-of select="."/>
              </xsl:for-each>
            </div>
          </xsl:for-each>
        </div>
        <div>
          <h2>Techniques</h2>
          <xsl:for-each select="//tei:tech[count(. | key('tech', normalize-space(@type))[1]) = 1]">
            <xsl:variable name="key" select="string(normalize-space(@type))"/>
            <div>
              <h3>
                <xsl:choose>
                  <xsl:when test="@type">
                    <xsl:value-of select="@type"/>
                  </xsl:when>
                  <xsl:otherwise>@type ?</xsl:otherwise>
                </xsl:choose>
              </h3>
              <xsl:for-each select="key('tech', $key)">
                <xsl:if test="position() != 1">, </xsl:if>
                <xsl:value-of select="."/>
              </xsl:for-each>
              <xsl:text>.</xsl:text>
            </div>
          </xsl:for-each>
        </div>
      </div>
    </div>
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
        <xsl:when test="$last = '/'">
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
  <xsl:template match="tei:p | tei:l" mode="txt">
    <xsl:apply-templates mode="txt"/>
    <xsl:text> / </xsl:text>
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


</xsl:transform>