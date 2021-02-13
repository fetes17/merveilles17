<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:import href="tei_header.xsl"/>
  <xsl:import href="tei_flow.xsl"/>
  <xsl:import href="page.xsl"/>
  <xsl:output indent="yes" encoding="UTF-8" method="xml" omit-xml-declaration="yes"/>
  <xsl:key name="name" match="tei:name[not(ancestor::tei:teiHeader)]" use="normalize-space(@key)"/>
  <!--
  <xsl:param name="locorum"/>
  <xsl:variable name="place" select="document($locorum)/*/*"/>
  -->
  <xsl:variable name="ana" select="document('../../index/ana.xml')/*/*"/>
  <xsl:key name="placeName" match="tei:placeName" use="normalize-space(@key)"/>
  <xsl:key name="persName" match="tei:persName" use="normalize-space(@key)"/>
  <xsl:key name="tech" match="tei:tech" use="normalize-space(@type)"/>
  <xsl:key name="ana" match="*[@ana]" use="normalize-space(@ana)"/>
  <xsl:key name="when" match="tei:date[@when]" use="normalize-space(@when)"/>
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:variable name="type" select="substring-before(substring-after($filename, '_'), '_')"/>
  <xsl:template match="/">
    <article class="document">
      <div class="object_header">
        <div class="container">
          <xsl:choose>
            <xsl:when test="/tei:TEI/tei:sourceDoc">
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title"/>
              <xsl:apply-templates select="/*/tei:teiHeader[1]/tei:profileDesc[1]/tei:creation[1]"/>
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:*[not(self::tei:title)]"/>
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl[1]/tei:note"/>
              <xsl:call-template name="download"/>
              <xsl:apply-templates select="/tei:TEI/tei:sourceDoc"/>
              <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:notesStmt"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:variable name="length" select="string-length(normalize-space(/tei:TEI/tei:text))"/>
              <div class="row">
                <div class="col-9">
                  <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title"/>
                  <xsl:apply-templates select="/*/tei:teiHeader[1]/tei:profileDesc[1]/tei:creation[1]"/>
                  <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:*[not(self::tei:title)]"/>
                  <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl[1]/tei:note"/>
                  <xsl:call-template name="download"/>
                  <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:notesStmt"/>
                  <xsl:choose>
                    <xsl:when test="$length &lt; 700">
                      <xsl:apply-templates select="/tei:TEI/tei:text"/>
                    </xsl:when>
                    <xsl:otherwise>
                      <a href="../texte/{$filename}{$_html}" class="textofiche">
                        <div class="blurb">
                          <xsl:call-template name="ellipse">
                            <xsl:with-param name="node" select="/tei:TEI/tei:text/tei:body"/>
                            <xsl:with-param name="length" select="600"/>
                          </xsl:call-template>
                        </div>
                      </a>
                    </xsl:otherwise>
                  </xsl:choose>
                </div>
                <div class="col-3">
                  <a>
                    <xsl:choose>
                      <xsl:when test="$length &gt; 700">
                        <xsl:attribute name="href">
                          <xsl:text>../texte/</xsl:text>
                          <xsl:value-of select="$filename"/>
                          <xsl:value-of select="$_html"/>
                        </xsl:attribute>
                        <div  class="textofiche">
                          <span class="link">Texte intégral ▶</span>
                        </div>
                      </xsl:when>
                      <xsl:when test="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:ptr">
                        <xsl:attribute name="href">
                          <xsl:value-of select="(/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:ptr)[1]/@target"/>
                        </xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                      </xsl:when>
                    </xsl:choose>
                    <img src="S/{$filename},S.jpg"/>
                  </a>
                </div>
              </div>
            </xsl:otherwise>
          </xsl:choose>
        </div>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-9 doc_ventre"> 
            %personnes%
            <xsl:call-template name="themes"/>
          </div>
          <div class="col-3">
            %chrono%
            %lieux% 
            %techniques% 
          </div>
        </div>
      </div>
      <div class="bg-gray">
        <div class="container">
          %relations% 
        </div>
      </div>
    </article>
  </xsl:template>
  <!--  -->
  <xsl:template match="tei:titleStmt/tei:author | tei:titleStmt/tei:editor" priority="2">
    <a class="{local-name()}">
      <xsl:if test="@key">
        <xsl:attribute name="href">../personne/<xsl:value-of select="@key"/>.html</xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </a>
  </xsl:template>

  <xsl:template match="tei:sourceDesc/tei:bibl[1]/tei:note">
    <div class="publine">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="tei:titleStmt/tei:respStmt" priority="2">
    <a class="{local-name()}">
      <xsl:if test="tei:persName/@key">
        <xsl:attribute name="href">../personne/<xsl:value-of select="tei:persName/@key"/>.html</xsl:attribute>
      </xsl:if>
      <xsl:variable name="resp" select="normalize-space(tei:resp)"/>
      <xsl:if test="$resp != ''">
        <xsl:value-of select="$resp"/>
        <xsl:text>, </xsl:text>
      </xsl:if>
      <xsl:apply-templates select="tei:persName/node()"/>
    </a>
  </xsl:template>

  <!-- Techniques d’écriture -->
  <xsl:template name="anas">
    <xsl:if test="//*[@ana][@ana != 'description']">
      <div class="doc_ana">
        <h2>Techniques d’écriture</h2>
        <ul>
          <xsl:for-each select="//*[@ana][@ana != 'description'][count(. | key('ana', normalize-space(@ana))[1]) = 1]">
            <xsl:sort select="count(key('ana', @ana))" order="descending"/>
            <xsl:variable name="key" select="@ana"/>
            <li>
              <xsl:value-of select="$ana[@xml:id = $key]"/>
              <xsl:text> </xsl:text>
              <b>
                <xsl:text>(</xsl:text>
                <xsl:value-of select="count(key('ana', $key))"/>
                <xsl:text>)</xsl:text>
              </b>
            </li>
          </xsl:for-each>
        </ul>
      </div>
    </xsl:if>
  </xsl:template>
  <xsl:template name="themes">
    <xsl:variable name="tag">name</xsl:variable>
    <xsl:if test="//*[name() = $tag][not(ancestor::tei:teiHeader)]">
      <div id="doc_theme">
        <h2>personnages fictifs</h2>
        <xsl:for-each select="//*[name() = $tag][count(. | key($tag, normalize-space(@key|@type))[1]) = 1][not(ancestor::tei:teiHeader)]">
          <xsl:sort select="count(key($tag, @key|@type))" order="descending"/>
          <xsl:choose>
            <xsl:when test="position() &gt; 200"/>
            <xsl:when test="@key">
              <a href="#" class="theme">
                <xsl:value-of select="translate(@key, '_', ' ')"/>
              </a>
              <xsl:text> </xsl:text>
            </xsl:when>
          </xsl:choose>
        </xsl:for-each>
      </div>
    </xsl:if>
  </xsl:template>

  <!-- Ne pas sortir les descriptions d’images ici -->
  <xsl:template match="tei:sourceDoc//tei:figDesc"/>

  <xsl:template match="tei:ref">
    <!--
    <ref target="../xml/merveilles17_img_stockp22_030.xml">
    -->
    <xsl:variable name="target">
      <xsl:if test="contains(@target, 'merveilles17_')">
        <xsl:text>merveilles17_</xsl:text>
        <xsl:value-of select="substring-before(substring-after(@target, 'merveilles17_'), '.xml')"/>
      </xsl:if>
    </xsl:variable>   
    <a>
      <xsl:attribute name="href">
        <xsl:choose>
          <xsl:when test="$target != ''">
            <xsl:value-of select="$target"/>
            <xsl:text>.html</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="@target"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:apply-templates/>
    </a>
  </xsl:template>

  <xsl:template name="download">
    <xsl:for-each select="(/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc//tei:ptr)[1]">
      <div class="download">
        <a class="download" target="_blank">
          <xsl:attribute name="href">
            <xsl:value-of select="@target"/>
          </xsl:attribute>
          <!--
          <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
          -->
          <xsl:text>◀ Document source</xsl:text>
        </a>
      </div>
    </xsl:for-each>
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
