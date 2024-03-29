<?xml version="1.0" encoding="UTF-8"?>
<!--
Interface de lecture d’un texte
Entrée : *_imp_*.xml, *_ms_*.xml (??? et je ne sais plus pourquoi des img)
Sortie : site/texte/*.html 
-->
<xsl:transform exclude-result-prefixes="tei" version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:tei="http://www.tei-c.org/ns/1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="tei_flow.xsl"/>
  <xsl:import href="tei_toc.xsl"/>
  <xsl:import href="tei_header.xsl"/>
  <xsl:import href="page.xsl"/>
  <xsl:output encoding="UTF-8" indent="yes" method="xml" omit-xml-declaration="yes"/>
  <xsl:key match="tei:persName[not(ancestor::tei:teiHeader)]" name="persName" use="normalize-space(@key)"/>
  <xsl:key match="tei:placeName[not(ancestor::tei:teiHeader)]" name="placeName" use="normalize-space(@key)"/>
  <xsl:key match="tei:tech[not(ancestor::tei:teiHeader)]" name="tech" use="normalize-space(@type)"/>
  <xsl:key match="tei:name[not(ancestor::tei:teiHeader)]" name="name" use="normalize-space(@key)"/>
  <xsl:key match="*[@ana][@ana != 'description']" name="ana" use="normalize-space(@ana)"/>
  <xsl:variable name="lieux" select="document('../../index/lieu.xml')//tei:place"/>
  <xsl:variable name="personnes" select="document('../../index/personne.xml')/*/*"/>
  <xsl:variable name="ana" select="document('../../index/ana.xml')/*/*"/>
  <xsl:variable name="techniques" select="document('../../index/technique.xml')/*/*"/>
  <xsl:template match="/">
    <article class="liseuse">
      <!-- le panneau d’exploration -->
      <div class="explorer" id="explorer">
        <p class="notice">
          <a class="notice" href="../document/{$filename}{$_html}" target="_blank" title="Retour à la notice">◀ Notice</a>
        </p>
        <xsl:call-template name="explorer"/>
      </div>
      <div id="milieu">
        <div class="bg-gray cartouche">
          <!-- les métadonnées du document -->
          <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt"/>
          <!-- les éditeurs électroniques, template surchargé dans page.xsl, au cas où on le voudrait dans page_document.xsl -->
          <xsl:apply-templates select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:editionStmt"/>
        </div>
        <div class="explorable" id="explorable">
          <xsl:apply-templates select="/tei:TEI/tei:text"/>
        </div>
      </div>
      <nav id="bookmarks">
        <mark class="toclone"> </mark>
      </nav>
    </article>
  </xsl:template>
  <xsl:template name="explorer">
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
      <xsl:with-param name="tag">name</xsl:with-param>
      <xsl:with-param name="label">Personnages</xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="taglist">
      <xsl:with-param name="tag">placeName</xsl:with-param>
      <xsl:with-param name="label">Lieux</xsl:with-param>
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
            <xsl:sort data-type="number" order="descending" select="count(key($tag, normalize-space(@key|@type)))"/>
            <xsl:call-template name="tr">
              <xsl:with-param name="tag" select="$tag"/>
              <xsl:with-param name="key" select="normalize-space(@key|@type)"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="$rows != ''">
      <details class="terms" id="{$tag}">
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
              <th title="Aller à la notice" width="40"/>
              <th class="term">
                <xsl:value-of select="$label"/>
              </th>
              <th class="nb" title="Occurrences">nb</th>
            </tr>
          </thead>
          <tbody>
            <xsl:copy-of select="$rows"/>
          </tbody>
        </table>
        <p> </p>
      </details>
    </xsl:if>
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
      <td>
        <xsl:if test="$key != ''">
          <a target="_blank" title="Aller à la notice">
            <xsl:attribute name="href">
              <xsl:choose>
                <xsl:when test="$tag = 'persName'">../personne/</xsl:when>
                <xsl:when test="$tag = 'ana'">../technique/</xsl:when>
                <xsl:when test="$tag = 'tech'">../technique/</xsl:when>
                <xsl:when test="$tag = 'placeName'">../lieu/</xsl:when>
              </xsl:choose>
              <xsl:value-of select="$key"/>
              <xsl:text>.html</xsl:text>
            </xsl:attribute>
            <xsl:text>◀</xsl:text>
          </a>
        </xsl:if>
      </td>
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
            <xsl:when test="$key = ''">
              <text>???</text>
            </xsl:when>
            <xsl:when test="$lieux[@xml:id = $key]/tei:name">
              <xsl:value-of select="$lieux[@xml:id = $key]/tei:name"/>
            </xsl:when>
            <xsl:when test="$personnes[@xml:id = $key]/tei:name">
              <xsl:value-of select="$personnes[@xml:id = $key]/tei:name"/>
            </xsl:when>
            <xsl:when test="$ana[@xml:id = $key]">
              <xsl:value-of select="$ana[@xml:id = $key]"/>
            </xsl:when>
            <xsl:when test="$techniques[@xml:id = $key]">
              <xsl:value-of select="$techniques[@xml:id = $key]"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>[</xsl:text>
              <i>
                <xsl:value-of select="$key"/>
              </i>
              <xsl:text>]</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </td>
      <td class="nb">
        <xsl:value-of select="$count"/>
      </td>
    </tr>
  </xsl:template>
  <xsl:template match="tei:note">
    <aside>
      <xsl:call-template name="atts"/>
      <xsl:apply-templates/>
    </aside>
  </xsl:template>
  <xsl:template match="tei:pb" name="pb">
    <xsl:variable name="facs" select="@facs"/>
    <xsl:choose>
      <xsl:when test="contains($facs, 'gallica.bnf.fr/ark:/')">
        <!-- https://gallica.bnf.fr/ark:/12148/bpt6k1526131p/f104.image -->
        <a class="pb facs" href="{$facs}" id="{@xml:id}" target="_blank">
          <span class="n">
            <xsl:if test="translate(@n, '1234567890', '') = ''">p. </xsl:if>
            <xsl:value-of select="@n"/>
          </span>
          <img data-bigger="{substring-before($facs, '/ark:/')}/iiif/ark:/{substring-after(substring-before(concat($facs, '.image'), '.image'), '/ark:/')}/full/700,/0/native.jpg" src="{substring-before($facs, '/ark:/')}/iiif/ark:/{substring-after(substring-before(concat($facs, '.image'), '.image'), '/ark:/')}/full/150,/0/native.jpg"/>
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
