# Merveilles17, pack XSL

Les pages du site [Merveilles17](https://fetes17.github.io/merveilles17/site/) sont complexes.
Outre la maquette, elles agrègent notamment deux types de données en XML.

* [documents TEI](https://github.com/fetes17/merveilles17/tree/master/xml)
* [index extraits des documents TEI puis relus dans un format XML](https://github.com/fetes17/merveilles17/tree/master/index)

Ces sources XML sont toujours traitées avec les XSL de ce paquet (pas de xpath). Le process est piloté par un script PHP en ligne de commande 
[build.php](https://github.com/fetes17/merveilles17/blob/master/build/build.php).

Il y a trois types d’xslt.

* Transformations produisant du contenu
  * [page_document.xsl](page_document.xsl) — [documents TEI](https://github.com/fetes17/merveilles17/tree/master/xml) 
    -> [vue notice](https://fetes17.github.io/merveilles17/site/document/merveilles17_imp_ctb-perrault1670.html)
  * [page_texte.xsl](page_texte.xsl) — [documents TEI](https://github.com/fetes17/merveilles17/tree/master/xml) 
    -> [vue texte intégral](https://fetes17.github.io/merveilles17/site/texte/merveilles17_imp_ctb-perrault1670.html)
  * [page.xsl](page.xsl) importée par page_\*.xsl
  * [lieu.xsl](lieu.xsl) — [lieu.xml](../index/lieu.xml) -> [Lieux](https://fetes17.github.io/merveilles17/site/lieu/index.html)
  * [technique.xsl](technique.xsl) — [technique.xml](../index/technique.xml) -> [Techniques](https://fetes17.github.io/merveilles17/site/technique/index.html)
  * [chrono.xsl](chrono.xsl) — [chronologie.xml](https://github.com/fetes17/merveilles17/blob/master/index/chronologie.xml), 
    insérée comme navigation par ex dans [la liste des documents](https://fetes17.github.io/merveilles17/site/document/index.html)
* **tsv_\*.xsl** Transformations produisant des lignes TSV qui sont ensuite manipuélées par le pilote 
  [build.php](https://github.com/fetes17/merveilles17/blob/master/build/build.php)
* **tei_\*.xsl** transformations génériques tei -> html, importées
  * [tei_flow.xsl](tei_flow.xsl), `<tei:body>` -> `<html:article>`
  * [tei_header.xsl](tei_header.xsl), `<tei:teiHeader>` -> `<html:div>`
  * [tei_toc.xsl](tei_toc.xsl), `<tei:div>` -> `<html:nav>`
  * [tei_common.xsl](tei_common.xsl), modèles partagés par tei_*.xsl
  * [tei.rdfs](tei.rdfs), énoncés générés par tei_common.xsl
