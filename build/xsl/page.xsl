<?xml version="1.0" encoding="UTF-8"?>
<!--
Modèles partagés par les pages 
“document” (notice biblio) site/document/*.html 
“texte” (plein texte) site/texte/*.html 

  -->
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"  xmlns:tei="http://www.tei-c.org/ns/1.0"  exclude-result-prefixes="tei">
  <xsl:import href="tei_common.xsl"/>
  <xsl:template match="tei:titleStmt">
    <h1>
      <xsl:apply-templates select="tei:title/node()"/>
    </h1>
    <xsl:for-each select="tei:author[normalize-space(.) != '']">
      <div class="author">
        <xsl:apply-templates/>
      </div>
    </xsl:for-each>
    <xsl:for-each select="tei:editor[normalize-space(.) != '']">
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
    <xsl:for-each select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl/tei:note">
      <div class="publine">
        <xsl:apply-templates/>
      </div>
    </xsl:for-each>
  </xsl:template>

  <!-- Contributeurs à l’édition électronique -->
  <xsl:template match="tei:editionStmt">
    <p class="editionStmt">
      <text>Responsables : </text>
      <xsl:for-each select="tei:respStmt">
        <span class="respStmt">
          <xsl:apply-templates select="tei:name"/>
          <xsl:if test="tei:resp">
            <xsl:text>, </xsl:text>
            <xsl:apply-templates select="tei:resp"/>
          </xsl:if>
          <xsl:if test="position() != last()"> — </xsl:if>
        </span>
      </xsl:for-each>
      <xsl:text>.</xsl:text>
    </p>
  </xsl:template>

  <xsl:template match="tei:graphic">
    <xsl:variable name="id">
      <xsl:call-template name="id"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="contains(@url, '/iiif/')">
        <xsl:variable name="width">
          <xsl:choose>
            <xsl:when test="ancestor::sourceDoc">1140</xsl:when>
            <xsl:otherwise>600</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>       
        <a href="{@url}" target="_blank" class="iiif">
          <xsl:variable name="src">
            <xsl:value-of select="substring-before(@url, '/full/0/')"/>
            <xsl:text>/1140,/0/</xsl:text>
            <xsl:value-of select="substring-after(@url, '/full/0/')"/>
          </xsl:variable>
          <img src="{$src}" id="{$id}">
            <xsl:attribute name="alt">
              <xsl:choose>
                <xsl:when test="ancestor::tei:sourceDoc">
                  <xsl:value-of select="normalize-space($doctitle)"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="normalize-space(../tei:figDesc)"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
          </img>
        </a>
      </xsl:when>
      <xsl:otherwise>
        <img src="{$images}{@url}" alt="{normalize-space(.)}" id="{$id}">
          <xsl:if test="@style|@scale">
            <xsl:variable name="style">
              <xsl:if test="@scale &gt; 0 and @scale &lt; 1">
                <xsl:text>width: </xsl:text>
                <xsl:value-of select="floor(@scale * 100)"/>
                <xsl:text>%; </xsl:text>
              </xsl:if>
              <xsl:value-of select="@style"/>
            </xsl:variable>
            <xsl:attribute name="style">
              <xsl:value-of select="normalize-space($style)"/>
            </xsl:attribute>
          </xsl:if>
          <xsl:if test="@rend">
            <xsl:attribute name="class">
              <xsl:value-of select="@rend"/>
            </xsl:attribute>
          </xsl:if>
        </img>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="tei:pb_NO" name="pb">
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
