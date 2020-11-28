<?php

Merveilles17::init();
Merveilles17::copy();
Merveilles17::load();
Merveilles17::chrono();
Merveilles17::documents();
Merveilles17::lieux();
Merveilles17::personnes();
Merveilles17::techniques();
Merveilles17::control();



class Merveilles17
{
  /** extension for links */
  static $_html = '.html';
  /** SQLite link */
  static public $pdo;
  /** Home directory of project, absolute */
  static $home;
  /** Database absolute path */
  static private $sqlfile;
  /** HTML template */
  static private $template;
  /** SQL to create database */
  static private $create = "
PRAGMA encoding = 'UTF-8';
PRAGMA page_size = 8192;

CREATE TABLE document (
  -- répertoire des documents
  id             INTEGER,               -- ! rowid auto
  type           INTEGER,               -- ! type de document
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  length         INTEGER,               -- ! taille en caractères (auto)
  title          TEXT NOT NULL,         -- ! titre
  publine        TEXT,                  -- ! complément bibliographique au titre
  ptr            TEXT NOT NULL,         -- ! url de la source numérique
  bibl           TEXT NOT NULL,         -- ! référence bibliographique (html)
  personne_count INTEGER,               -- ! nombre de personnes citées (auto)
  lieu_count     INTEGER,               -- ! nombre de lieux (auto)
  tech_count     INTEGER,               -- ! nombre de techniques (auto)
  PRIMARY KEY(id ASC)
);
CREATE INDEX document_type ON document(type, code);

CREATE TABLE lieu (
  -- répertoire des lieux
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  label          TEXT NOT NULL,         -- ! forme de référence
  coord          TEXT,                  -- ? coordonnées carto
  settlement     TEXT,                  -- ? commune, pour recherche
  alt            TEXT,                  -- ? forme alternative, pour recherche
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
  PRIMARY KEY(id ASC)
);
CREATE INDEX lieu_occs ON lieu(occs, code);
CREATE INDEX lieu_docs ON lieu(docs, code);

CREATE TABLE lieu_document (
  -- Occurences d’un lieu dans un document
  id             INTEGER,               -- ! rowid auto
  lieu           INTEGER,               -- ! lieu.id obtenu avec par lieu.code
  lieu_code      TEXT NOT NULL,         -- ! lieu.code
  document       INTEGER,               -- ! document.id obtenu avec par document.code
  document_code  TEXT NOT NULL,         -- ! sera obtenu avec par document.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le fichier source
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  desc           TEXT,                  -- ? description, à tirer du contexte
  PRIMARY KEY(id ASC)
);
CREATE INDEX lieu_document_document ON lieu_document(document);
CREATE INDEX lieu_document_lieu ON lieu_document(lieu);


CREATE TABLE technique (
  -- répertoire des techniques
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  label           TEXT NOT NULL,         -- ! forme d’autorité
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
  PRIMARY KEY(id ASC)
);
CREATE INDEX technique_occs ON technique(occs, code);
CREATE INDEX technique_docs ON technique(docs, code);

CREATE TABLE technique_document (
  -- Occurences d’un technique dans un document
  id             INTEGER,               -- ! rowid auto
  technique      INTEGER,               -- ! technique.id obtenu avec par technique.code
  technique_code TEXT NOT NULL,         -- ! technique.code
  document       INTEGER,               -- ! document.id obtenu avec par document.code
  document_code  TEXT NOT NULL,         -- ! sera obtenu avec par document.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le fichier source
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  PRIMARY KEY(id ASC)
);
CREATE INDEX technique_document_document ON technique_document(document);
CREATE INDEX technique_document_technique ON technique_document(technique);


CREATE TABLE personne (
  -- répertoire des personnes
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  label          TEXT,                  -- ! forme d’autorité
  gender         TEXT,                  -- M male, F femme
  birth          TEXT,                  -- date de naissance
  death          TEXT,                  -- date de mort
  databnf        TEXT,                  -- autorité BNF
  wikipedia      TEXT,                  -- URL wikipedia
  isni           TEXT,                  -- code ISNI
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
  PRIMARY KEY(id ASC)
);
CREATE INDEX personne_occs ON personne(occs, code);
CREATE INDEX personne_docs ON personne(docs, code);

CREATE TABLE personne_document (
  -- Occurences d’un nom de personne dans un document
  id             INTEGER,               -- ! rowid auto
  personne       INTEGER,               -- ! personne.id obtenu avec par personne.code
  personne_code  TEXT NOT NULL,         -- ! personne.code
  document       INTEGER,               -- ! document.id obtenu avec par document.code
  document_code  TEXT NOT NULL,         -- ! sera obtenu avec par document.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le ficheir source
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  role           TEXT,                  -- ? @role
  PRIMARY KEY(id ASC)
);
CREATE INDEX personne_document_personne ON personne_document(personne);
CREATE INDEX personne_document_document ON personne_document(document, personne_code);

CREATE TABLE chrono (
  -- chronologie
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  label          TEXT NOT NULL,         -- ! nom de l’événement
  start          TEXT NOT NULL,         -- ! date de début
  end            TEXT NOT NULL,         -- ! date de fin
  lieu           INTEGER,               -- ! id du lieu de l’événement
  lieu_code      TEXT NOT NULL,         -- ! code de lieu de l’événement
  lieu_label     TEXT,                  -- ! label du lieu de l’événement
  parent         INTEGER,               -- ? si enfant d’événement
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  PRIMARY KEY(id ASC)
);

CREATE TABLE chrono_document (
  -- lien d’un événement à des documents
  id             INTEGER,               -- ! rowid auto
  chrono         INTEGER,               -- ! événement de la chrono
  document       INTEGER,               -- ! id du document référencé, dsera obtenu par document.code
  document_code  TEXT NOT NULL,         -- ! code du document référencé
  PRIMARY KEY(id ASC)
);
CREATE INDEX chrono_document_chrono ON chrono_document(chrono);
CREATE INDEX chrono_document_document ON chrono_document(document);


  ";
  static private $doctype = array(
    "ms" => "Manuscrits",
    "imp" => "Imprimés",
    "img" => "Images",
  );

  
  public static function init()
  {
    self::$home = dirname(dirname(__FILE__)).'/';
    self::$sqlfile = self::$home."site/merveilles17.sqlite";
    self::$template = file_get_contents(self::$home."build/template.html");
    // vider site avant de créer la base sqlite
    Build::rmdir(self::$home."site", true);
    // recreate sqlite base on each call
    self::$pdo = Build::sqlcreate(self::$sqlfile, self::$create);
  }
  
  /**
   * Chronologie, après avoir renseigné la table des lieux
   */
  public static function chrono()
  {
    $chrono = self::$pdo->prepare("INSERT INTO chrono (code, label, start, end, lieu_code, parent) VALUES (?, ?, ?, ?, ?, ?)");
    $chrono_document = self::$pdo->prepare("INSERT INTO chrono_document (chrono, document_code) VALUES (?, ?)");
    $dom = Build::dom(self::$home."index/chronologie.xml");
    $eventList = $dom->getElementsByTagNameNS ('http://www.tei-c.org/ns/1.0', 'event');
    $parent = null;
    self::$pdo->beginTransaction();
    foreach ($eventList as $event) {
      $code = $event->getAttribute("xml:id");
      $start = $event->getAttribute("from");
      if (!$start) $start = $event->getAttribute("when");
      $end = $event->getAttribute("to");
      if (!$end) $end = $start;
      $lieu_code = $event->getAttribute("where");
      if ($event->parentNode->nodeName != 'event') $parent = null;
      foreach ($event->childNodes as $node) {
        if ($node->nodeName != 'label') continue;
        $label = preg_replace('@\s+@', ' ', trim($node->textContent));
      }
      // si n’est pas un event enfant, pas de parent
      if ($event->parentNode->nodeName != 'event') $parent = null;
      echo $code, " ", $label, " ",  $start, " ",  $end, " ",  $lieu_code, " ", $parent, "\n";
      $chrono->execute(array($code, $label, $start, $end, $lieu_code, $parent));
      $eventid = self::$pdo->lastInsertId();
      foreach ($event->childNodes as $node) {
        if ($node->nodeName != 'ref') continue;
        $document_code = $node->getAttribute("target");
        if (!$document_code) continue;
        $document_code = pathinfo ($document_code, PATHINFO_FILENAME);
        $chrono_document->execute(array($eventid, $document_code));
      }
      // peut avoir des enfants
      if ($event->parentNode->nodeName != 'event') $parent = $eventid;
    }
    self::$pdo->commit();
    self::$pdo->exec("
      UPDATE chrono SET
        lieu=(SELECT id FROM lieu WHERE code=chrono.lieu_code),
        lieu_label=(SELECT label FROM lieu WHERE code=chrono.lieu_code)
      ;
      UPDATE chrono_document SET
        document=(SELECT id FROM document WHERE code=chrono_document.document_code)
      ;
    ");
    self::$pdo->exec("
      INSERT INTO chrono_document (document, document_code) SELECT id, code FROM document WHERE id NOT IN (SELECT document FROM chrono_document WHERE chrono IS NOT NULL);
    ");
  }
  
  /**
   *
   */
  public static function load_personne()
  {
    $qpersonne = self::$pdo->prepare("INSERT INTO personne (code, label, gender, birth, death, databnf, wikipedia, isni) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $listPerson = simplexml_load_file(self::$home."index/personne.xml");
    self::$pdo->beginTransaction();
    foreach ($listPerson->person as $person) {
      $code = $person->attributes('xml', true)->id;
      $gender = $person->sex;
      $label = (string)$person->name;
      if (!$label) $label = null;
      $birth = $person->birth['when'];
      if (!$birth) $birth = null;
      $death = $person->death['when'];
      if (!$death) $death = null;
      $databnf = $wikipedia = $isni = null;
      foreach ($person->identifier as $identifier) {
        $type = $identifier['type'];
        if($type == 'databnf') $databnf = $identifier;
        elseif ($type == 'wikipedia') $wikipedia = $identifier;
        elseif ($type == 'isni') $isni = $identifier;
      }
      $qpersonne->execute(array($code, $label, $gender, $birth, $death, $databnf, $wikipedia, $isni));
    }
    self::$pdo->commit();
  }

  public static function load_lieu()
  {
    $q = self::$pdo->prepare("INSERT INTO lieu (code, label, coord, settlement, alt) VALUES (?, ?, ?, ?, ?)");
    $root = simplexml_load_file(self::$home."index/lieu.xml");
    self::$pdo->beginTransaction();
    foreach ($root->place as $record) {
      $code = $record->attributes('xml', true)->id;
      $label = (string)$record->name[0];
      if (!$label) $label = null;
      $alt = (string)$record->name[1];
      if (!$alt) $alt = null;
      $coord = $record->geo;
      if (!$coord) $coord = null;
      $settlement = (string)$record->settlement;
      if(!$settlement) $settlement = null;
      $q->execute(array($code, $label, $coord, $settlement, $alt));
    }
    self::$pdo->commit();
  }
  
  public static function load_technique()
  {
    $q = self::$pdo->prepare("INSERT INTO technique (code, label) VALUES (?, ?)");
    $root = simplexml_load_file(self::$home."index/technique.xml");
    self::$pdo->beginTransaction();
    foreach ($root->term as $record) {
      $code = $record->attributes('xml', true)->id;
      $label = (string)$record;
      $q->execute(array($code, $label));
    }
    self::$pdo->commit();
  }
  
  /**
   * Load dictionaries in database
   */
  public static function load()
  {
    self::load_personne();
    self::load_lieu();
    self::load_technique();
    
    $document_cols = array("type", "code", "length", "title", "publine", "ptr", "bibl");
    
    // different generated files    
    $readme = "
# Merveilles de la Cour, les textes

[Documentation du schema](https://fetes17.github.io/merveilles17/merveilles17.html)

";
    $document = implode("\t", $document_cols)."\n";
    $lieu_document =           "lieu_code\tdocument_code\tanchor\toccurrence\tdesc\n";
    $technique_document = "technique_code\tdocument_code\tanchor\toccurrence\n";
    $personne_document =   "personne_code\tdocument_code\tanchor\toccurrence\trole\n";
    
    // loop on all xml files, and do lots of work
    foreach (glob(self::$home."xml/*.xml") as $srcfile) {
      echo "  -- ".basename($srcfile),"\n";
      $dom = Build::dom($srcfile);
      
      $readme .= "* [".basename($srcfile)."](https://fetes17.github.io/merveilles17/xml/".basename($srcfile).")\n";

      $dstname = basename($srcfile, ".xml");
      $dstfile = self::$home."site/".$dstname.self::$_html;
      
      
      $line = Build::transformDoc($dom, self::$home."build/xsl/tsv_document.xsl", null, array('filename' => $dstname));
      $line = str_replace(' xmlns="http://www.w3.org/1999/xhtml"', '', $line);
      $document .= $line;
      
      // extract index terms from document by xsl s tsv lines to insert in database
      // could be one day in xpath
      $lines = Build::transformDoc($dom, self::$home."build/xsl/tsv_personne_document.xsl", null, array('filename' => $dstname));
      $personne_document .= $lines;
      $technique_document .= Build::transformDoc($dom, self::$home."build/xsl/tsv_technique_document.xsl", null, array('filename' => $dstname));
      $lieu_document .= Build::transformDoc($dom, self::$home."build/xsl/tsv_lieu_document.xsl", null, array('filename' => $dstname));
    }
    file_put_contents(self::$home."README.md", $readme);

    // enregistrer fichiers tsv 
    file_put_contents(self::$home."site/data/document.tsv", $document);
    file_put_contents(self::$home."site/data/lieu_document.tsv", $lieu_document);
    file_put_contents(self::$home."site/data/technique_document.tsv", $technique_document);
    file_put_contents(self::$home."site/data/personne_document.tsv", $personne_document);

    // charger les tsv en base
    self::tsv_insert("document", $document_cols, $document);
    self::tsv_insert("lieu_document", array("lieu_code", "document_code", "anchor", "occurrence", "desc"), $lieu_document);
    self::tsv_insert("technique_document", array("technique_code", "document_code", "anchor", "occurrence"), $technique_document);
    self::tsv_insert("personne_document", array("personne_code", "document_code", "anchor", "occurrence", "role"), $personne_document);

    // mise à jour des index 
    self::$pdo->exec("
      UPDATE lieu_document SET
        lieu=(SELECT id FROM lieu WHERE code=lieu_document.lieu_code),
        document=(SELECT id FROM document WHERE code=lieu_document.document_code)
      ;
      UPDATE technique_document SET
        technique=(SELECT id FROM technique WHERE code=technique_document.technique_code),
        document=(SELECT id FROM document WHERE code=technique_document.document_code)
      ;
      UPDATE personne_document SET
        personne=(SELECT id FROM personne WHERE code=personne_document.personne_code),
        document=(SELECT id FROM document WHERE code=personne_document.document_code)
      ;
    ");
    

    // différents comptes (autre transaction)
    self::$pdo->exec("
      UPDATE lieu SET
        occs=(SELECT COUNT(*) FROM lieu_document WHERE lieu=lieu.id),
        docs=(SELECT COUNT(DISTINCT document) FROM lieu_document WHERE lieu=lieu.id)
      ;
      UPDATE technique SET
        occs=(SELECT COUNT(*) FROM technique_document WHERE technique=technique.id),
        docs=(SELECT COUNT(DISTINCT document) FROM technique_document WHERE technique=technique.id)
      ;
      UPDATE personne SET
        occs=(SELECT COUNT(*) FROM personne_document WHERE personne=personne.id),
        docs=(SELECT COUNT(DISTINCT document) FROM personne_document WHERE personne=personne.id)
      ;
    ");
    

  }
  
  public static function control()
  {
    // Index de contrôle
    $stmt = self::$pdo->prepare("SELECT document_code, lieu_code, occurrence FROM lieu_document WHERE lieu IS NULL");
    $stmt->execute();
    $tsv = "document_code\tlieu_code\toccurrence\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      $tsv .= implode("\t", $row)."\n";
    }
    file_put_contents(self::$home."site/data/lieu_orphelins.tsv", $tsv);    

    $stmt = self::$pdo->prepare("SELECT document_code, personne_code, occurrence FROM personne_document WHERE personne IS NULL");
    $stmt->execute();
    $tsv = "document_code\tpersonne_code\toccurrence\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      $tsv .= implode("\t", $row)."\n";
    }
    file_put_contents(self::$home."site/data/personne_orphelins.tsv", $tsv);    
  }

  public static function documents()
  {
    Build::mkdir(Build::rmdir(self::$home."site/document/"));
    Build::mkdir(Build::rmdir(self::$home."site/document/S/"));
    Build::mkdir(Build::rmdir(self::$home."site/texte/"));
    foreach (glob(self::$home."couv/S/*.jpg") as $srcfile) {
      copy($srcfile, self::$home.'site/document/S/'.basename($srcfile));
    }
    
    $template = str_replace("%relpath%", "../", self::$template);
    
    $index = '';
    $index .= '<article id="docs">'."\n";
    $index .= '
  <header class="doctype">
    <div class="ms">
      <span>Manuscrits</span>
      <img src="../images/biblio/doctype_ms.jpg"/>
    </div>
    <div class="imp">
      <span>Imprimés</span>
      <img src="../images/biblio/doctype_imp.jpg"/>
    </div>
    <div class="img">
      <span>Images</span>
      <img src="../images/biblio/doctype_img.jpg"/>
    </div>
  </header>
    ';
    $index .= '<div class="container">'."\n";
    $index .= self::uldocs(null, null, "");
    $index .= '</div>'."\n";
    $index .= '</article>'."\n";
    file_put_contents(self::$home."site/document/index".self::$_html, str_replace("%main%", $index, $template));
    
    $qid = self::$pdo->prepare("SELECT id FROM document WHERE code = ?");
    
    $q_lieu_doc = self::$pdo->prepare("SELECT lieu, lieu_code, COUNT(document_code) AS count FROM lieu_document WHERE document_code = ? GROUP BY lieu ORDER BY count DESC");
    $q_lieu = self::$pdo->prepare("SELECT label FROM lieu WHERE id = ?");
    
    $qtechniques = self::$pdo->prepare("SELECT technique.id, technique.code, technique.label, COUNT(document_code) AS count FROM technique, technique_document WHERE document_code = ? AND technique_document.technique = technique.id GROUP BY technique ORDER BY count DESC");
    
    // group by personne_code for unknown personne
    $q_pers_doc = self::$pdo->prepare("SELECT personne, personne_code, COUNT(*) AS count FROM personne_document  WHERE document_code = ? GROUP BY personne_code ORDER BY count DESC, personne_code");
    $q_pers = self::$pdo->prepare("SELECT label FROM personne WHERE id = ?");
    // $qpersonnes = self::$pdo->prepare("SELECT personne.id, personne.code, personne.label, COUNT(document_code) AS count FROM personne, personne_document WHERE document_code = ? AND personne_document.personne = personne.id GROUP BY personne ORDER BY count DESC");
    
    foreach (glob(self::$home."xml/*.xml") as $srcfile) {
      $document_code = basename($srcfile, ".xml");
      $qid->execute(array($document_code));
      list($docid) = $qid->fetch();
      $dom = Build::dom($srcfile);
      /* liseuse */
      $page = Build::transformDoc($dom, self::$home."build/xsl/page_texte.xsl", null, array('filename' => $document_code));      
      file_put_contents(self::$home.'site/texte/'.$document_code.self::$_html, str_replace('%main%', $page, $template));
      /* notice de document */
      $page = Build::transformDoc($dom, self::$home."build/xsl/page_document.xsl", null, array('filename' => $document_code));
      $page = str_replace(" § ", "\n<br/>", $page);

      // liste de personnes citées
      $q_pers_doc->execute(array($document_code));
      $personnes = '
<section id="doc_pers">
  <h2>Personnes</h2>
  <ul>';
      $count = 0;
      while ($row = $q_pers_doc->fetch(PDO::FETCH_ASSOC)) {
        $q_pers->execute(array($row['personne']));
        list($label) = $q_pers->fetch();
        if (!$label) $label = '<i>['.$row['personne_code'].']</i>';
        $personnes .= '<li><a href="../personne/'.$row['personne_code'].self::$_html.'">'.$label.'</a>';
        if ($row['count'] > 1) $personnes .= ' ('.$row['count'].')';
        $personnes .= '</li>'."\n";
        $count++;
      }
      $personnes .= '
  </ul>
</section>
';
      if (!$count) $personnes= '';
      $page = str_replace("%personnes%", $personnes, $page);

      
      // liste de lieux
      $q_lieu_doc->execute(array($document_code));
      $lieux = '
<section id="doc_place">
  <h2>Lieux</h2>
  <ul>
      ';
      $count = 0;
      while ($row = $q_lieu_doc->fetch(PDO::FETCH_ASSOC)) {
        $q_lieu->execute(array($row['lieu']));
        list($label) = $q_lieu->fetch();
        if (!$label) $label = '<i>['.$row['lieu_code'].']</i>';
        $lieux .= '<li><a href="../lieu/'.$row['lieu_code'].self::$_html.'">'.$label.'</a> ('.$row['count'].')</li>'."\n";
        $count++;
      }
      $lieux .= '
  </ul>
</section>
';
      if (!$count) $lieux = '';
      $page = str_replace("%lieux%", $lieux, $page);
      
      // liste de techniques
      $qtechniques->execute(array($document_code));
      $techniques = '
<section id="doc_tech">
  <h2>Techniques</h2>
  <ul>
';
      $count = 0;
      while ($row = $qtechniques->fetch(PDO::FETCH_ASSOC)) {
        $techniques .= '<li><a href="../technique/'.$row['code'].self::$_html.'">'.$row['label'].'</a> ('.$row['count'].')</li>'."\n";
        $count++;
      }
      $techniques .= '
  </ul>
</section>
';
      if (!$count) $techniques = "";
      $page = str_replace("%techniques%", $techniques, $page);
      
      
      file_put_contents(self::$home.'site/document/'.$document_code.self::$_html, str_replace('%main%', $page, $template));
    }

  
  }

  /**
   * Générer les pages techniques
   */
  public static function techniques()
  {
    Build::rmdir(self::$home."site/technique/");
    Build::mkdir(self::$home."site/technique/");
    $template = str_replace("%relpath%", "../", self::$template);
    $index = '
<div class="container">
  <table class="sortable">
    <thead>
      <tr>
        <th class="label">Technique</th>
        <th class="docs" title="Nombre de documents">documents</th>
        <th class="occs" title="Nombre d’occurrences">occurrences</th>
      </tr>
    </thead>    
    <tbody>
';
    // boucler sur tous les termes
    $stmt = self::$pdo->prepare("SELECT * FROM technique ORDER BY docs DESC, code ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $href = "technique/".$row['code'].self::$_html;
      $index .= '
    <tr>
      <td class="label"><a href="'.$row['code'].self::$_html.'">'.$row['label'].'</a></td>
      <td class="docs">'.$row['docs'].'</td>
      <td class="occs">'.$row['occs'].'</td>
    </tr>
';
      $page = '<div class="container">';
      $page .= '  <div class="row align-items-start">'."\n";
      $page .= '    <div class="col-9">'."\n";
      $page .= '      <h1>'.$row['label'].'</h1>'."\n";
      $page .= '      <section>'."\n";
      $page .= '        <h2>Documents liés</h2>'."\n";
      $page .= self::uldocs("technique", $row['id']);
      $page .= '      </section>'."\n";
      
      $page .= '    </div>'."\n";
      $page .= '    <div class="col-3">'."\n";
      $page .= '    </div>'."\n";
      $page .= '  </div>'."\n";
      $page .= '</div>'."\n";
      file_put_contents(self::$home."site/".$href, str_replace("%main%", $page, $template));
    }
    $stmt = null;    
    $index .= '
    </tbody>
  </table>
</div>
    ';
    file_put_contents(self::$home."site/technique/index.html", str_replace("%main%", $index, $template));

  }

  /**
   * Générer les pages personnes
   */
  public static function personnes()
  {
    Build::rmdir(self::$home."site/personne/");
    Build::mkdir(self::$home."site/personne/");
    $template = str_replace("%relpath%", "../", self::$template);
    $index = '
<div class="container">
  <table class="sortable">
    <thead>
      <tr>
        <th class="label">Personne</th>
        <th class="birth" title="Date de naissance">Naissance</th>
        <th class="death" title="Date de mort">Mort</th>
        <th class="docs" title="Nombre de documents">documents</th>
        <th class="occs" title="Nombre d’occurrences">occurrences</th>
      </tr>
    </thead>    
    <tbody>
';
    // boucler sur tous les termes
    $stmt = self::$pdo->prepare("SELECT * FROM personne ORDER BY docs DESC, code ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $href = "personne/".$row['code'].self::$_html;
      if (!$row['label']) $row['label'] = '[<i>'.$row['code'].'</i>]';
      $index .= '
      <tr>
        <td class="label"><a href="'.$row['code'].self::$_html.'">'.$row['label'].'</a></td>
        <td class="birth">'.$row['birth'].'</td>
        <td class="death">'.$row['death'].'</td>
        <td class="docs">'.$row['docs'].'</td>
        <td class="occs">'.$row['occs'].'</td>
      </tr>
';
      $page = '';
      $page .= '
<div class="object_header">
  <div class="container">
      ';
      $dates = '';
      if ($row['birth'] && $row['death']) $dates = ' ('.$row['birth'].' – '.$row['death'].')';
      else if ($row['birth']) $dates = ' ('.$row['birth'].' – ?)';
      else if ($row['death']) $dates = ' (? – '.$row['death'].')';
      $page .= '    <h1>'.$row['label'].$dates.'</h1>'."\n";
      // $page .= '<p>Courte notice ? à ajouter à personne.csv</p>'."\n";
      if ($row['wikipedia'] || $row['databnf'] || $row['isni']) {
        $page .= '<ul>'."\n";
        if ($row['wikipedia']) $page .= '  <li><a href="'.$row['wikipedia'].'" target="_new">wikipedia</a></li>'."\n";
        if ($row['databnf']) $page .= '  <li><a href="'.$row['databnf'].'" target="_new">databnf</a></li>'."\n";
        if ($row['isni']) $page .= '  <li>ISNI : <a href="http://isni.org/isni/'.strtr($row['isni'], 
        array(' '=>'', ' '=> '')).'" target="_new">'.$row['isni'].'</a></li>'."\n";
        $page .= '</ul>'."\n";
      }
      $page .= '
  </div>
</div>
<div class="object_documents">
  <div class="container">
';
      
      $page .= '      <section>'."\n";
      $page .= '        <h2>Documents liés</h2>'."\n";
      $page .= self::uldocs("personne", $row['id']);
      $page .= '      </section>'."\n";
      $page .= '
  </div>
</div>
';
      file_put_contents(self::$home."site/".$href, str_replace("%main%", $page, $template));
    }
    $stmt = null;    
    $index .= '
    </tbody>
  </table>
</div>
    ';
    file_put_contents(self::$home."site/personne/index.html", str_replace("%main%", $index, $template));

  }

  
  /**
   * Générer les pages lieux
   */
  public static function lieux()
  {
    Build::rmdir(self::$home."site/lieu/");
    Build::mkdir(self::$home."site/lieu/");
    $template = str_replace("%relpath%", "../", self::$template);
    $index = '
<div class="container">
  <table class="sortable">
    <thead>
      <tr>
        <th class="label">Lieu</th>
        <th class="docs" title="Nombre de documents">documents</th>
        <th class="occs" title="Nombre d’occurrences">occurrences</th>
      </tr>
    </thead>    
    <tbody>
';
    // boucler sur tous les termes
    $stmt = self::$pdo->prepare("SELECT * FROM lieu ORDER BY docs DESC, code ");
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $index .= '
      <tr>
        <td class="label"><a href="'.$row['code'].self::$_html.'">'.$row['label'].'</a></td>
        <td class="docs">'.$row['docs'].'</td>
        <td class="occs">'.$row['occs'].'</td>
      </tr>
';
      $page  = '<div class="container">'."\n";
      $page .= '  <div class="row align-items-start">'."\n";
      $page .= '    <div class="col-9">'."\n";
      $page .= '      <h1>'.$row['label'].'</h1>'."\n";
      if ($row['coord']) {
        $place = "";
        if ($row['settlement']) $place .= $row['settlement'].", ";
        if ($row['alt']) $place .= $row['alt'];
        else $place .= $row['label'];
        $page .= '    <div><a target="_blank" href="https://www.google.com/maps/search/'.$place.'/@'.$row['coord'].'z">'.$row['coord'].'</a></div>'."\n";
      }
      $page .= '      <section>'."\n";
      $page .= '        <h2>Documents liés</h2>'."\n";
      $page .= self::uldocs("lieu", $row['id']);
      $page .= '      </section>'."\n";
      
      $page .= '    </div>'."\n";
      $page .= '    <div class="col-3">'."\n";
      $page .= '    </div>'."\n";
      $page .= '  </div>'."\n";
      $page .= '</div>'."\n";
      file_put_contents(self::$home."site/lieu/".$row['code'].'.html', str_replace("%main%", $page, $template));
    }
    $stmt = null;
    
    $index .= '
    </tbody>
  </table>
</div>
    ';
    file_put_contents(self::$home."site/lieu/index.html", str_replace("%main%", $index, $template));
    

  }
  
  private static function uldocs($table=null, $id=null)
  {
    $qdocument = self::$pdo->prepare('SELECT * FROM document WHERE id = ?');
    $qchrono = self::$pdo->prepare('SELECT * FROM chrono WHERE id = ?');
    if($table) {
      $sql = "SELECT DISTINCT chrono_document.* FROM chrono_document, %table%_document WHERE %table%_document.%table% = ? AND %table%_document.document = chrono_document.document ORDER BY chrono_document.id;";
      $sql = str_replace('%table%', $table, $sql);
      $stmt = self::$pdo->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_INT);
    }
    else {
      $sql = "SELECT * FROM chrono_document;";
      $stmt = self::$pdo->prepare($sql);
    }
    $stmt->execute();
    
    $html = "";
    $chrono = null;
    $first = true;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if ($row['chrono'] == $chrono);
      else if ($row['chrono']) {
        if ($first) $first = false;
        else $html .= "</section>\n";
        $qchrono->execute(array($row['chrono']));
        $chron = $qchrono->fetch();
        $html .= "\n".'<section class="event" id="'.$chron['code'].'">'."\n";
        $html .= "\n  <div><h3>".self::htchrono($chron)."</h3></div>";
      }
      else {
        if ($first) $first = false;
        else $html .= "</section>\n";
        $html .= '<section class="event">'."\n";
        $html .= "  <div><h3>Autres événements</h3></div>\n";
      }
      $chrono = $row['chrono'];
      $qdocument->execute(array($row['document']));
      $html .= self::htdocument($qdocument->fetch());
    }
    $html .= "</section>\n";
    return $html;
  }

  private static function htchrono($row, $html=true)
  {
    $out = '';
    if ($html) $out .= '<b class="date">';
    $out .= substr($row['start'], 0, 4);
    if ($row['start'] != $row['end']) {
      if (strlen($row['start']) >= 7 && strlen($row['end']) >= 7) {
        $mois1 = substr($row['start'], 5, 2);
        $mois2 = substr($row['end'], 5, 2);
      }
      if (strlen($row['start']) >= 10 && strlen($row['end']) >= 10) {
        $j1 = substr($row['start'], 8, 2);
        $j2 = substr($row['end'], 8, 2);
        if ($mois1 == $mois2) $out .= ', '.(int)$j1.'-'.(int)$j2.' '.Build::mois($mois1);
        else $out .= ', '.(int)$j1.' '.Build::mois($mois1).' – '.(int)$j2.' '.Build::mois($mois2);
      }
      else {
        if ($mois1 == $mois2) $out .= ', '.Build::mois($mois1);
        else $out .= ', '.Build::mois($mois1).' – '.Build::mois($mois2);
      }
    } else {
      if (strlen($row['start']) >= 10) $out .= ', '.(int)substr($row['start'], 8, 2).' '.Build::mois(substr($row['start'], 5, 2));
      else if (strlen($row['start']) >= 7) $out .= ', '.Build::mois(substr($row['start'], 5, 2));
    }
    if ($html) $out .= '</b><span class="lieu">';
    $out .= ' '.$row['lieu_label'].'.';
    if ($html) $out .= '</span>
    <i class="title">';
    $out .= ' '.$row['label'].'.';
    $out .= '</i>';
    return $out;
  }

  private static function chronotitle($row, $html=true)
  {
    
  }
    
  private static function htdocument($row)
  {
    $html = '';
    $html .= '<a class="document '.$row['type'].'" href="../document/'.$row['code'].self::$_html.'">'."\n";
    
    $html .= '  <div class="vignette" style="background-image:url(\'../document/S/'.$row['code'].',S.jpg\');"></div>'."\n";
    $html .= '  <div>'."\n";
    $html .= '    <div class="title">'.$row['title'].'</div>'."\n";
    $html .= '    <div class="publine">';
    /*
    $html .= $row['pubplace'];
    if ($row['publisher']) $html .= ', '.$row['publisher'];
    $html .= ' – ';
    if ($row['pubdate']) $html .= $row['pubdate'];
    else $html .= $row['idno'];
    */
    $html .= $row['publine'];
    $html .= '</div>'."\n";
    $html .= '  </div>'."\n";
    $html .= '</a>'."\n";
    return $html;
  }

  /**
   * Charger une table avec des lignes tsv
   */  
  private static function tsv_insert($table, $cols, $lines)
  {
    $count = count($cols);
    $sql = "INSERT INTO ".$table." (".implode(", ", $cols).") VALUES (?".str_repeat (', ?', $count - 1).");";
    
    
    $stmt = self::$pdo->prepare($sql);
    $first = true;
    self::$pdo->beginTransaction();
    foreach (explode("\n", $lines) as $l){
      if (!$l) continue;
      if ($first) { // skip first line
        $first = false;
        continue;
      }
      $values = array_slice(explode("\t", $l), 0, $count);
      $stmt->execute($values);
    }
    self::$pdo->commit();
  }
  
  /**
   * Copy ressources to site
   */
  public static function copy()
  {
    Build::rcopy(self::$home."build/images", self::$home."site/images");
    Build::rcopy(self::$home."build/theme", self::$home."site/theme");
    $template = str_replace("%relpath%", "", self::$template);
    // copy static page
    foreach (glob(self::$home."build/pages/*.html") as $srcfile) {
      $html = file_get_contents($srcfile);
      $basename = basename($srcfile);
      if ($basename == 'index.html') {
        $chrono = Build::transform(self::$home."index/chronologie.xml", self::$home."build/xsl/chrono.xsl");
        $html = str_replace("%chrono%", $chrono, $html);
      }
      file_put_contents(self::$home."site/".$basename, str_replace("%main%", $html, $template));
    }
    Build::mkdir(self::$home."site/data");
  }

}

/**
 * Different tools to build html sites
 */
class Build
{
  /** XSLTProcessors */
  private static $transcache = array();
  /** get a temp dir */
  private static $tmpdir;


  static function mois($num)
  {
    $mois = array(
      1 => 'janvier',
      2 => 'février',
      3 => 'mars',
      4 => 'avril',
      5 => 'mai',
      6 => 'juin',
      7 => 'juillet',
      8 => 'août',
      9 => 'septembre',
      10 => 'octobre',
      11 => 'novembre',
      12 => 'décembre',
    );
    return $mois[(int)$num];
  }
  
  /**
   * get a pdo link to an sqlite database with good options
   */
  static function pdo($file, $sql)
  {
    $dsn = "sqlite:".$file;
    // if not exists, create
    if (!file_exists($file)) return self::sqlcreate($file, $sql);
    else return self::sqlopen($file, $sql);
  }
    
  /**
   * Open a pdo link
   */
  static private function sqlopen($file)
  {
    $dsn = "sqlite:".$file;
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA temp_store = 2;");
    return $pdo;
  }
  
  /**
   * Renew a database with an SQL script to create tables
   */
  static function sqlcreate($file, $sql)
  {
    if (file_exists($file)) unlink($file);
    self::mkdir(dirname($file));
    $pdo = self::sqlopen($file);
    @chmod($sqlite, 0775);
    $pdo->exec($sql);
    return $pdo;
  }

  /**
   * Get a DOM document with best options
   */
  static function dom($xmlfile) {
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput=true;
    $dom->substituteEntities=true;
    $dom->load($xmlfile, LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_NOWARNING);
    return $dom;
  }
  /**
   * Xsl transform from xml file
   */
  static function transform($xmlfile, $xslfile, $dst=null, $pars=null)
  {
    return self::transformDoc(self::dom($xmlfile), $xslfile, $dst, $pars);
  }

  static public function transformXml($xml, $xslfile, $dst=null, $pars=null)
  {
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput=true;
    $dom->substituteEntities=true;
    $dom->loadXml($xml, LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_NOWARNING);
    return self::transformDoc($dom, $xslfile, $dst, $pars);
  }

  /**
   * An xslt transformer with cache
   * TOTHINK : deal with errors
   */
  static public function transformDoc($dom, $xslfile, $dst=null, $pars=null)
  {
    if (!is_a($dom, 'DOMDocument')) {
      throw new Exception('Source is not a DOM document, use transform() for a file, or transformXml() for an xml as a string.');
    }
    $key = realpath($xslfile);
    // cache compiled xsl
    if (!isset(self::$transcache[$key])) {
      $trans = new XSLTProcessor();
      $trans->registerPHPFunctions();
      // allow generation of <xsl:document>
      if (defined('XSL_SECPREFS_NONE')) $prefs = XSL_SECPREFS_NONE;
      else if (defined('XSL_SECPREF_NONE')) $prefs = XSL_SECPREF_NONE;
      else $prefs = 0;
      if(method_exists($trans, 'setSecurityPreferences')) $oldval = $trans->setSecurityPreferences($prefs);
      else if(method_exists($trans, 'setSecurityPrefs')) $oldval = $trans->setSecurityPrefs($prefs);
      else ini_set("xsl.security_prefs",  $prefs);
      $xsldom = new DOMDocument();
      $xsldom->load($xslfile);
      $trans->importStyleSheet($xsldom);
      self::$transcache[$key] = $trans;
    }
    $trans = self::$transcache[$key];
    // add params
    if(isset($pars) && count($pars)) {
      foreach ($pars as $key => $value) {
        $trans->setParameter(null, $key, $value);
      }
    }
    // return a DOM document for efficient piping
    if (is_a($dst, 'DOMDocument')) {
      $ret = $trans->transformToDoc($dom);
    }
    else if ($dst != '') {
      self::mkdir(dirname($dst));
      $trans->transformToURI($dom, $dst);
      $ret = $dst;
    }
    // no dst file, return String
    else {
      $ret =$trans->transformToXML($dom);
    }
    // reset parameters ! or they will kept on next transform if transformer is reused
    if(isset($pars) && count($pars)) {
      foreach ($pars as $key => $value) $trans->removeParameter(null, $key);
    }
    return $ret;
  }
  
  /**
   * A safe mkdir dealing with rights
   */
  static function mkdir($dir)
  {
    if (is_dir($dir)) return $dir;
    if (!mkdir($dir, 0775, true)) throw new Exception("Directory not created: ".$dir);
    @chmod(dirname($dir), 0775);  // let @, if www-data is not owner but allowed to write
    return $dir;
  } 

  /**
   * Recursive deletion of a directory
   * If $keep = true, keep directory with its acl
   */
  static function rmdir($dir, $keep = false) {
    $dir = rtrim($dir, "/\\").DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) return $dir; // maybe deleted
    if(!($handle = opendir($dir))) throw new Exception("Read impossible ".$file);
    while(false !== ($filename = readdir($handle))) {
      if ($filename == "." || $filename == "..") continue;
      $file = $dir.$filename;
      if (is_link($file)) throw new Exception("Delete a link? ".$file);
      else if (is_dir($file)) self::rmdir($file);
      else unlink($file);
    }
    closedir($handle);
    if (!$keep) rmdir($dir);
    return $dir;
  }
  
  
  /**
   * Recursive copy of folder
   */
  static function rcopy($srcdir, $dstdir) {
    $srcdir = rtrim($srcdir, "/\\").DIRECTORY_SEPARATOR;
    $dstdir = rtrim($dstdir, "/\\").DIRECTORY_SEPARATOR;
    self::mkdir($dstdir);
    $dir = opendir($srcdir);
    while(false !== ($filename = readdir($dir))) {
      if ($filename[0] == '.') continue;
      $srcfile = $srcdir.$filename;
      if (is_dir($srcfile)) self::rcopy($srcfile, $dstdir.$filename);
      else copy($srcfile, $dstdir.$filename);
    }
    closedir($dir);
  }

}


 ?>
