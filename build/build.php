<?php

Merveilles17::init();
Merveilles17::copy();
Merveilles17::load();
Merveilles17::chrono();
Merveilles17::documents();
Merveilles17::lieux();
Merveilles17::personnes();
Merveilles17::techniques();
Merveilles17::corpus(); 
Merveilles17::control();
Merveilles17::homepage();



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

CREATE TABLE document_include (
  -- liens d’inclusion de documents
  id             INTEGER,               -- ! rowid auto
  src            INTEGER,               -- ! document.id renseigné a posteriori
  src_code       TEXT NOT NULL,         -- ! document.code extrait du document XML
  dst            INTEGER,               -- ! document.id renseigné a posteriori
  dst_code       TEXT NOT NULL,         -- ! document.code extrait du document XML
  PRIMARY KEY(id ASC)
);
CREATE INDEX document_include_src ON document_include(src);
CREATE INDEX document_include_dst ON document_include(dst);


CREATE TABLE lieu (
  -- <place>, répertoire des lieux 
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! @xml:id, code unique
  label          TEXT NOT NULL,         -- ! <name>[1], forme de référence
  parent         INTEGER,               -- ! identifiant de lieu parent (ou 0 si racine)
  parent_code    TEXT NOT NULL,         -- ! ../@xml:id, code provisoire
  path           TEXT NOT NULL,         -- ! ex : /france/paris/paris_chaillot_couvent
  geo            TEXT,                  -- ? <geo>, coordonnées carto
  settlement     TEXT,                  -- ? <settlement> commune, pour recherche
  alt            TEXT,                  -- ? <name>[2], forme alternative, pour recherche
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
  PRIMARY KEY(id ASC)
);
CREATE INDEX lieu_path ON lieu(path);
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
  label           TEXT NOT NULL,        -- ! forme d’autorité
  parent         INTEGER,               -- ! identifiant de lieu parent (ou 0 si racine)
  parent_code    TEXT NOT NULL,         -- ! ../@xml:id, code provisoire
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
CREATE INDEX personne_label ON personne(label);
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
  role           INT,                   -- ? sort
  role_code      TEXT,                  -- ? @role
  PRIMARY KEY(id ASC)
);
CREATE INDEX personne_document_doc ON personne_document(document, personne, role);
CREATE INDEX personne_document_personne ON personne_document(personne);
CREATE INDEX personne_document_document ON personne_document(document, personne_code);
CREATE INDEX personne_document_role ON personne_document(personne, role, document, anchor);
CREATE INDEX personne_document_docs ON personne_document(personne, role_code, document);
CREATE INDEX personne_document_occs ON personne_document(personne, role_code);

CREATE TABLE role (
  -- liste des roles
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  label          TEXT,                  -- ! forme d’autorité
  PRIMARY KEY(id ASC)
);
CREATE INDEX role_code ON role(code);

CREATE TABLE document_resp (
  -- Responsabilité de personnes sur un document <teiHeader>
  id             INTEGER,               -- ! rowid auto
  personne       INTEGER,               -- ! personne.id obtenu avec par personne.code
  personne_code  TEXT NOT NULL,         -- ! personne.code
  document       INTEGER,               -- ! document.id obtenu avec par document.code
  document_code  TEXT NOT NULL,         -- ! sera obtenu avec par document.code
  resp           TEXT,                  -- ! @role 
  PRIMARY KEY(id ASC)
);
CREATE INDEX document_resp_personne ON document_resp(personne, resp);
CREATE INDEX document_resp_pers ON document_resp(personne_code, resp);


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


CREATE TABLE corpus (
  -- Définition des corpus thématiques
  id             INTEGER PRIMARY KEY,
  code           TEXT UNIQUE NOT NULL,  -- identifiant technique
  titre          TEXT NOT NULL,         -- titre affiché
  description    TEXT,                  -- texte descriptif
  docs           INTEGER DEFAULT 0      -- nombre de documents (calculé)
);

CREATE TABLE corpus_document (
  -- Documents appartenant à chaque corpus
  id             INTEGER PRIMARY KEY,
  corpus         INTEGER NOT NULL,      -- corpus.id
  corpus_code    TEXT NOT NULL,         -- corpus.code (provisoire)
  document       INTEGER,               -- document.id (calculé)
  document_code  TEXT NOT NULL          -- document.code
);
CREATE INDEX corpus_document_corpus ON corpus_document(corpus);
CREATE INDEX corpus_document_document ON corpus_document(document);

  ";
  static private $doctype = array(
    "ms" => "Manuscrits",
    "imp" => "Imprimés",
    "img" => "Images",
  );
  
  static $role = array(
    "commanditaire" => "Commanditaire",
    "destinataire" => "Destinataire",
    "organisation" => "Organisation",
    "participant" => "Participant·e",
    "spectateur" => "Spectateur·rice",
    "convive" => "Convive",
    "acteur" => "Acteur·ice",
    "chanteur" => "Chanteur·euse",
    "danseur" => "Danseur·euse",
    "musicien" => "Musicien·ne",
    
    
    "artificier" => "Artificier·ère",
    "fournisseur" => "Fournisseur",
    "auteur" => "Auteur·rice",
    "imprimeur" => "Imprimeur·euse",
    // "dessinateur" => "Dessinateur·rice·s",
    "none" => "(non précisé)", 
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
      // echo $code, " ", $label, " ",  $start, " ",  $end, " ",  $lieu_code, " ", $parent, "\n";
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
    $file = self::$home."index/lieu.xml";
    $dom = new DOMDocument();
    $dom->load($file, LIBXML_BIGLINES | LIBXML_NOCDATA | LIBXML_NONET | LIBXML_NSCLEAN);
    $csv = "code\tlabel\tparent_code\tpath\tsettlement\talt\tgeo\n"; // ne pas oublier la première ligne
    $csv .= Build::transformDoc($dom, self::$home."build/xsl/tsv_lieu.xsl");
    self::tsv_insert("lieu", array("code", "label", "parent_code", "path", "settlement", "alt", "geo"), $csv);
    self::$pdo->exec("
      UPDATE lieu SET
        parent=(SELECT id FROM lieu AS l WHERE code=lieu.parent_code)
      ;
    ");

    /*
    // marche, mais trop lourd pour capter le lieu parent, passé en xslt ci-dessus
    $q = self::$pdo->prepare("INSERT INTO lieu (code, label, geo, settlement, alt) VALUES (?, ?, ?, ?, ?)");
    self::$pdo->beginTransaction();
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace("tei", "http://www.tei-c.org/ns/1.0");
    // ici on pourrait enregistrer automatiquement d’autres namespace
    $root = $dom->documentElement;
    foreach ($xpath->query('namespace::*', $root) as $node ) {
      echo $node->nodeName, " ", $node->nodeValue, "\n";
      if ($node->nodeName == 'xmlns') $xpath->registerNamespace("default", $node->nodeValue);
    }
    
    foreach ($xpath->query('//tei:place') as $place ) {
      $code = $place->getAttribute('xml:id');
      $label =$geo = $settlement = $alt = null;
      foreach ($place->childNodes as $node) {
        $name = $node->nodeName;
        switch($name) {
          case "#text":
          case "place":
            break;
          case "name":
            if ($label == null) $label = $node->nodeValue;
            else if ($alt == null) $alt = $node->nodeValue;
            break;
          case "settlement":
            $settlement = $node->nodeValue;
            break;
          case "geo":
            $geo = $node->nodeValue;
            break;
          
        }
      }
      $q->execute(array($code, $label, $geo, $settlement, $alt));
    }
    self::$pdo->commit();
    */
  }
  
  public static function load_technique()
  {
  
    $file = self::$home."index/technique.xml";
    $dom = new DOMDocument();
    $dom->load($file, LIBXML_BIGLINES | LIBXML_NOCDATA | LIBXML_NONET | LIBXML_NSCLEAN);
    $csv = "code\tlabel\tparent_code\n"; // ne pas oublier la première ligne
    $csv .= Build::transformDoc($dom, self::$home."build/xsl/tsv_technique.xsl");
    self::tsv_insert("technique", array("code", "label", "parent_code"), $csv);
    self::$pdo->exec("
      UPDATE technique SET
        parent=(SELECT id FROM technique AS t2 WHERE code=technique.parent_code)
      ;
    ");
  }

  public static function load_role()
  {
    $q = self::$pdo->prepare("INSERT INTO role (code, label) VALUES (?, ?)");
    self::$pdo->beginTransaction();
    foreach(self::$role as $code=>$label) {
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
    self::load_role();
    
    $document_cols = array("type", "code", "length", "title", "publine", "ptr", "bibl");
    
    // different generated files    
    $readme = "
# Merveilles de la Cour, les textes

[Documentation du schema](https://fetes17.github.io/merveilles17/merveilles17.html)

";
    $document = implode("\t", $document_cols)."\n";
    $lieu_document =           "lieu_code\tdocument_code\tanchor\toccurrence\tdesc\n";
    $technique_document = "technique_code\tdocument_code\tanchor\toccurrence\n";
    $personne_document =   "personne_code\tdocument_code\tanchor\toccurrence\trole_code\n";
    $document_include =   "src_code\tdst_code\n";
    $document_resp =   "document_code\tpersonne_code\tresp\n";
    
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
      $document_include .= Build::transformDoc($dom, self::$home."build/xsl/tsv_document_include.xsl", null, array('filename' => $dstname));
      $document_resp .= Build::transformDoc($dom, self::$home."build/xsl/tsv_document_resp.xsl", null, array('filename' => $dstname));
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
    self::tsv_insert("personne_document", array("personne_code", "document_code", "anchor", "occurrence", "role_code"), $personne_document);
    self::tsv_insert("document_include", array("src_code", "dst_code"), $document_include);
    self::tsv_insert("document_resp", array("document_code", "personne_code", "resp"), $document_resp);

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
        document=(SELECT id FROM document WHERE code=personne_document.document_code),
        role=(SELECT id FROM role WHERE code=personne_document.role_code)
      ;
      
      UPDATE document_resp SET
        personne=(SELECT id FROM personne WHERE code=document_resp.personne_code),
        document=(SELECT id FROM document WHERE code=document_resp.document_code)
      ;
      UPDATE document_include SET
        src=(SELECT id FROM document WHERE code=document_include.src_code),
        dst=(SELECT id FROM document WHERE code=document_include.dst_code)
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
      UPDATE personne_document SET
        role_code='none',
        role=(SELECT id FROM role WHERE code='none')
        WHERE role IS NULL
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

    $tsv = "document_code\tpersonne_code\toccurrence\n";
    $stmt = self::$pdo->prepare("SELECT document_code, personne_code, occurrence FROM personne_document WHERE personne IS NULL");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) $tsv .= implode("\t", $row)."\n";
    $stmt = self::$pdo->prepare("SELECT document_code, personne_code, resp FROM document_resp WHERE personne IS NULL");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) $tsv .= implode("\t", $row)."\n";
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
    $index .= '  <div class="container">'."\n";
    $index .= '    <div class="row">'."\n";
    $index .= '      <div class="col-9">'."\n";
    $index .= self::uldocs(null, null, "");
    $index .= '      </div>'."\n";
    $index .= '      <div class="col-3">'."\n";
    $chrono = Build::transform(self::$home."index/chronologie.xml", self::$home."build/xsl/chrono.xsl");
    $index .= preg_replace('@href="[^#]*#@', 'href="#', $chrono);
    $index .= '      </div>'."\n";
    $index .= '    </div>'."\n";
    $index .= '  </div>'."\n";
    $index .= '</article>'."\n";
    file_put_contents(self::$home."site/document/index".self::$_html, str_replace("%main%", $index, $template));
    
    $qid = self::$pdo->prepare("SELECT id FROM document WHERE code = ?");
    
    $q_lieu_doc = self::$pdo->prepare("SELECT lieu, lieu_code, COUNT(document_code) AS count FROM lieu_document WHERE document_code = ? GROUP BY lieu ORDER BY count DESC");
    $q_lieu = self::$pdo->prepare("SELECT label FROM lieu WHERE id = ?");
    
    $qtechniques = self::$pdo->prepare("SELECT technique.id, technique.code, technique.label, COUNT(document_code) AS count FROM technique, technique_document WHERE document_code = ? AND technique_document.technique = technique.id GROUP BY technique ORDER BY count DESC");
    
    // group by personne_code for unknown personne
    $q_pers_doc = self::$pdo->prepare("
    SELECT personne, personne_code, role, role_code, COUNT(*) AS count
      FROM personne_document, personne
      WHERE 
        document_code = ? 
        AND personne_document.personne = personne.id
      GROUP BY personne_document.role, personne_document.personne
      ORDER BY personne_document.role, personne.label
    "); // , COUNT(*) AS count
    $q_pers = self::$pdo->prepare("SELECT label FROM personne WHERE id = ?");
    // $qpersonnes = self::$pdo->prepare("SELECT personne.id, personne.code, personne.label, COUNT(document_code) AS count FROM personne, personne_document WHERE document_code = ? AND personne_document.personne = personne.id GROUP BY personne ORDER BY count DESC");
    
    
    $q_chrono = self::$pdo->prepare("SELECT chrono.* FROM chrono, chrono_document WHERE document = ? AND chrono_document.chrono = chrono.id;");
    $q_rel_chrono = self::$pdo->prepare("SELECT document.* FROM document, chrono_document WHERE chrono_document.document = document.id AND chrono_document.chrono = ?;");
    $q_rel_haspart = self::$pdo->prepare("SELECT document.* FROM document, document_include WHERE document_include.dst = document.id AND document_include.src = ?;");
    $q_rel_ispartof = self::$pdo->prepare("SELECT document.* FROM document, document_include WHERE document_include.src = document.id AND document_include.dst = ?;");
    $q_rel_author = self::$pdo->prepare("SELECT document.* FROM document, document_resp WHERE document_resp.document = document.id AND document_resp.resp = 'author' AND document_resp.personne_code IN (SELECT personne_code FROM document_resp WHERE document = ?) ORDER BY document.code;");
    $q_rel_printer = self::$pdo->prepare("SELECT document.* FROM document, document_resp WHERE document_resp.document = document.id AND document_resp.resp = 'imprimeur' AND document_resp.personne_code IN (SELECT personne_code FROM document_resp WHERE document = ?) ORDER BY document.code;");

    
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

      // Chrono
      $chrono_ids = array();
      $q_chrono->execute(array($docid));
      $chrono = '';
      $chrono .= '
<h2>Événements liés</h2>
<nav class="chrono">'."\n";
      while ($row = $q_chrono->fetch(PDO::FETCH_ASSOC)) {
        $chrono .= '<a class="caldate" href="index.html#'.$row['code'].'">';
        $chrono .= self::htchrono($row);
        $chrono .= '</a>'."\n";
        $chrono_ids[] = $row['id'];
      }
      $chrono .= '</nav>'."\n";
      $page = str_replace("%chrono%", $chrono, $page);

      // liste de personnes citées
      $q_pers_doc->execute(array($document_code));
      $personnes = '
<section id="doc_pers">
  <h2>Personnes</h2>'."\n\n";
      $count = 0;
      $role_code = null;
      while ($row = $q_pers_doc->fetch(PDO::FETCH_ASSOC)) {
        if ($role_code != $row['role_code']) {
          if (!is_null($role_code)) $personnes .= "</section>";
          $role_code = $row['role_code'];
          $personnes .= "\n<section class=\"persList\">\n  <h3>".self::$role[$role_code].'</h3>'."\n";
        }
        $q_pers->execute(array($row['personne']));
        list($label) = $q_pers->fetch();
        if (!$label) $label = '<i>['.$row['personne_code'].']</i>';
        $personnes .= '<li class="persName"><a href="../personne/'.$row['personne_code'].self::$_html.'">'.$label.'</a>';
        if ($row['count'] > 1) $personnes .= ' ('.$row['count'].')';
        $personnes .= '</li>'."\n";
        $count++;
      }
      $personnes .= '
  </section>
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
      
      $relations = '';
      // inclusions
      $docs = '';
      $q_rel_haspart->execute(array($docid));
      while ($row =  $q_rel_haspart->fetch(PDO::FETCH_ASSOC)) {
        $docs .= self::htdocument($row);
      }
      if ($docs) $relations .= "<h3>Documents inclus</h3>\n".$docs;
      $docs = '';
      $q_rel_ispartof->execute(array($docid));
      while ($row =  $q_rel_ispartof->fetch(PDO::FETCH_ASSOC)) {
        $docs .= self::htdocument($row);
      }
      if ($docs) $relations .= "<h3>Est inclus dans</h3>\n".$docs;
      
      // documents du même événement
      $zevent = "";
      foreach($chrono_ids as $id) {
        $q_rel_chrono->execute(array($id));
        while ($row =  $q_rel_chrono->fetch(PDO::FETCH_ASSOC)) {
          if ($row['id'] == $docid) continue;
          $zevent .= self::htdocument($row);
        }
      }
      if ($zevent)  $relations .= "<h3>Même événement</h3>\n".$zevent;
      
      $docs = '';
      $q_rel_author->execute(array($docid));
      while ($row =  $q_rel_author->fetch(PDO::FETCH_ASSOC)) {
        if ($row['id'] == $docid) continue;
        $docs .= self::htdocument($row);
      }
      if ($docs) $relations .= "<h3>Même auteur</h3>\n".$docs;
      $docs = '';
      $q_rel_printer->execute(array($docid));
      while ($row =  $q_rel_printer->fetch(PDO::FETCH_ASSOC)) {
        if ($row['id'] == $docid) continue;
        $docs .= self::htdocument($row);
      }
      if ($docs) $relations .= "<h3>Même imprimeur</h3>\n".$docs;

      
      if ($relations) $relations = "<section id=\"doc_rels\">\n<h2>Documents liés</h2>\n".$relations."\n</section>";
      $page = str_replace("%relations%", $relations, $page);
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
    $parent = self::$pdo->prepare("SELECT * FROM technique WHERE parent = ?");
    $template = str_replace("%relpath%", "../", self::$template);
    // boucler sur tous les termes
    $stmt = self::$pdo->prepare("SELECT * FROM technique ORDER BY docs DESC, code ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $href = "technique/".$row['code'].self::$_html;
      $page = '<div class="container">';
      $page .= '  <div class="row align-items-start">'."\n";
      $page .= '    <div class="col-9">'."\n";
      $page .= '      <h1>'.$row['label'].'</h1>'."\n";
      
      // techniques enfants
      $parent->execute(array($row['id']));
      $children = "";
      while ($child = $parent->fetch(PDO::FETCH_ASSOC)) {
        $children .= '      <li><a href="' . $child['code'] . '.html">'. $child['label'] .'</a></li>'."\n";
      }
      if ($children) {
        $page .= '      <section>'."\n";
        $page .= '        <h2>Techniques liées</h2>' . "\n";
        $page .= '        <ul>'."\n";
        $page .= $children;
        $page .= '        </ul>'."\n";
        $page .= '      </section>'."\n";
      }

      
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

/*
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
    }
    $stmt = null;    
    $index .= '
    </tbody>
  </table>
</div>
    ';
    */
    // passer les stats $row['docs'], $row['occs'] ?
    $index = Build::transform(self::$home."index/technique.xml", self::$home."build/xsl/technique.xsl");

    file_put_contents(self::$home."site/technique/index.html", str_replace("%main%", $index, $template));

  }

  /**
   * Générer les pages personnes
   */
  public static function personnes()
  {
    $qroles = self::$pdo->prepare("SELECT role_code, count(*) AS count FROM personne_document WHERE personne = ? GROUP BY role ORDER BY role");
    Build::rmdir(self::$home."site/personne/");
    Build::mkdir(self::$home."site/personne/");
    $template = str_replace("%relpath%", "../", self::$template);
    // créer les pages pour chaque personne
    $stmt = self::$pdo->prepare("SELECT * FROM personne");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $href = "personne/".$row['code'].self::$_html;
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
    <div class="row">
      <div class="col-9">
';
      
      $page .= '      <section class="rolist">'."\n";
      $page .= '        <h2>Documents liés</h2>'."\n";
      $page .= self::uldocs("personne", $row['id']);
      $page .= '      </section>'."\n";
      $page .= '
      </div>
      <div class="col-3">
        <nav class="roles">
    ';
      $qroles->execute(array($row['id']));
      while ($row = $qroles->fetch(PDO::FETCH_ASSOC)) {
        $role_code = $row['role_code'];
        $page .= '<a class="role" href="#'.$role_code.'">'.self::$role[$role_code].' ('.$row['count'].')</a>'."\n";
      }
          $page .= '
        </nav>
      </div>
    </div>
  </div>
</div>
';
      file_put_contents(self::$home."site/".$href, str_replace("%main%", $page, $template));
    }

    
    
    $index = '<div class="container">
<div class="row">
<div class="col-9">';
    
    // boucler sur les roles
    $qpers = self::$pdo->prepare("
    SELECT personne.*, role_code, COUNT(DISTINCT personne_document.document) AS role_docs, COUNT(*) as role_occs 
        FROM personne_document, personne 
        WHERE personne_document.personne = personne.id 
        GROUP BY personne, role
        ORDER BY personne.label;");

      $index .= '
';
      $index .= '
    <table class="sortable rolist">
      <thead>
        <tr>
          <th class="label" width="100%">Personne</th>
          <th title="Nombre de documents">Rôle</th>
          <th class="docs" title="Nombre de documents où une personne apparait en tenant ce rôle">docs.</th>
          <th class="occs" title="Nombre d’occurrences  où une personne apparait en tenant ce rôle">occs.</th>
        </tr>
      </thead>
      <tbody>
';
      $qpers->execute();
      while ($row = $qpers->fetch(PDO::FETCH_ASSOC)) {
        $date = '';
        if ($row['birth'] && $row['death']) $date = ' ('.$row['birth'].' – '.$row['death'].')';
        else if ($row['birth']) $date = ' ('.$row['birth'].' – ?)';
        else if ($row['death']) $date = ' (? – '.$row['birth'].')';
        if (!$row['label']) $row['label'] = '[<i>'.$row['code'].'</i>]';
        $code = $row['role_code'];
        $index .= '
        <tr class="'.$code.'">
          <td class="label"><a target="_blank" href="'.$row['code'].self::$_html.'">'.$row['label'].$date.'</a></td>
          <td class="role '.$code.'">'.self::$role[$row['role_code']].'</td>
          <td class="docs">'.$row['role_docs'].'</td>
          <td class="occs">'.$row['role_occs'].'</td>
        </tr>';

      }
      $index .= '
      </tbody>
    </table>
    <p> </p>
    ';
    
      $index .= '
    <p> </p>
  </div>
  <div class="col-3">
    <nav class="roles">
';
      foreach(self::$role as $code => $label) {
        $index .= '<a class="role" href="#'.$code.'">'.$label.'</a>'."\n";
      }
      $index .= '
    </nav>
  </div>
  </div>
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
    
    $index = "";
    $index .= '<div class="container">'."\n";
    $index .= '<h1>Lieux</h1>'."\n";
    // passer les stats $row['docs'], $row['occs'] ?
    $index .= Build::transform(self::$home."index/lieu.xml", self::$home."build/xsl/lieu.xsl");
    $index .= '<p> </p>'."\n";
    $index .= '</div>'."\n";
    file_put_contents(self::$home."site/lieu/index.html", str_replace("%main%", $index, $template));
    // 
    
    // boucler sur tous les termes
    $stmt = self::$pdo->prepare("SELECT * FROM lieu");
    $stmt->execute();
    $lieu =  self::$pdo->prepare("SELECT * FROM lieu WHERE id = ?");
    $children = self::$pdo->prepare("SELECT * FROM lieu WHERE parent = ? AND docs > 0");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $page  = '<div class="container">'."\n";
      $page .= '  <div class="row align-items-start">'."\n";
      $page .= '    <div class="col-9">'."\n";
      $lieu->execute(array($row['parent']));
      $mother = $lieu->fetch(PDO::FETCH_ASSOC);
      // lien lieu parent
      if ($mother) {
        $page .= '
<p class="notice">
  <a title="Notice mère" class="notice" href="'.$mother['code'].'.html">◀ '.$mother['label'].'</a>
</p>';
      }
      
      $page .= '      <h1 class="lieu">'.$row['label'].'</h1>'."\n";
      if ($row['geo']) {
        $place = "";
        if ($row['settlement']) $place .= $row['settlement'].", ";
        if ($row['alt']) $place .= $row['alt'];
        else $place .= $row['label'];
        $page .= '    <div><a target="_blank" href="https://www.google.com/maps/search/'.$place.'/@'.$row['geo'].'z">'.$row['geo'].'</a></div>'."\n";
      }
      // lieux enfants
      $children->execute(array($row['id']));
      $list = "";
      while ($child = $children->fetch(PDO::FETCH_ASSOC)) {
        $list .= '      <li><a href="' . $child['code'] . '.html">'. $child['label'] .'</a></li>'."\n";
      }
      if ($list) {
        $page .= '      <section>'."\n";
        $page .= '        <h2>Lieux liés</h2>' . "\n";
        $page .= '        <ul>'."\n";
        $page .= $list;
        $page .= '        </ul>'."\n";
        $page .= '      </section>'."\n";
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

  }

  /**
 * Génération des pages corpus
 */
/**
 * Génération des pages corpus
 */
public static function corpus()
{
  $csvfile = self::$home."index/corpus.csv";
  
  // Buffer pour les logs de debug
  $debug_log = "<html><head><meta charset='UTF-8'><title>Debug Corpus</title></head><body>";
  $debug_log .= "<h1>Debug Génération Corpus</h1>";
  $debug_log .= "<style>body{font-family:monospace;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";
  
  if (!file_exists($csvfile)) {
    $debug_log .= "<p class='error'>!! Pas de fichier corpus.csv, skip</p>";
    $debug_log .= "</body></html>";
    file_put_contents(self::$home."site/debug_corpus.html", $debug_log);
    echo "  !! Pas de fichier corpus.csv, skip\n";
    return;
  }
  
  $debug_log .= "<p class='success'>✓ Fichier CSV trouvé : $csvfile</p>";
  
  $corpus_insert = self::$pdo->prepare("INSERT INTO corpus (code, titre, description) VALUES (?, ?, ?)");
  $corpus_doc_insert = self::$pdo->prepare("INSERT INTO corpus_document (corpus_code, document_code) VALUES (?, ?)");
  
  self::$pdo->beginTransaction();
  
  $handle = fopen($csvfile, 'r');
  $first = true;
  $ligne_num = 0;
  
  while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
    $ligne_num++;
    
    if ($first) {
      $debug_log .= "<h2>En-têtes CSV (ligne 1)</h2>";
      $debug_log .= "<pre>" . print_r($row, true) . "</pre>";
      $debug_log .= "<p>Nombre de colonnes : " . count($row) . "</p>";
      $first = false;
      continue;
    }
    
    if (count($row) < 4) {
      $debug_log .= "<p class='error'>⚠️  Ligne $ligne_num : seulement " . count($row) . " colonnes</p>";
      $debug_log .= "<pre>" . print_r($row, true) . "</pre>";
      continue;
    }
    
    list($code, $titre, $description, $sql_where) = $row;
    
    $debug_log .= "<hr><h2>Corpus : $titre</h2>";
    $debug_log .= "<p><strong>Code:</strong> '$code'</p>";
    $debug_log .= "<p><strong>Description:</strong> $description</p>";
    $debug_log .= "<p><strong>SQL WHERE:</strong><br><code>$sql_where</code></p>";
    
    echo "  -- Corpus: $titre\n";
    
    $corpus_insert->execute(array($code, $titre, $description));
    $corpus_id = self::$pdo->lastInsertId();
    
    try {
      $sql = "SELECT code FROM document WHERE " . $sql_where;
      $debug_log .= "<p><strong>Requête SQL complète:</strong></p>";
      $debug_log .= "<pre>" . htmlspecialchars($sql) . "</pre>";
      
      $stmt = self::$pdo->query($sql);
      $count = 0;
      $examples = array();
      
      while ($doc = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $corpus_doc_insert->execute(array($code, $doc['code']));
        $count++;
        if ($count <= 5) {
          $examples[] = $doc['code'];
        }
      }
      
      if ($count > 0) {
        $debug_log .= "<p class='success'><strong>✓ $count documents trouvés</strong></p>";
        $debug_log .= "<p>Exemples (5 premiers) :</p><ul>";
        foreach ($examples as $ex) {
          $debug_log .= "<li>$ex</li>";
        }
        $debug_log .= "</ul>";
      } else {
        $debug_log .= "<p class='error'><strong>❌ 0 documents trouvés</strong></p>";
        
        // Tests de diagnostic
        $debug_log .= "<h3>Tests de diagnostic :</h3>";
        
        // Test 1 : Combien de documents au total ?
        $total = self::$pdo->query("SELECT COUNT(*) FROM document")->fetchColumn();
        $debug_log .= "<p>Total documents en base : $total</p>";
        
        // Test 2 : La sous-requête retourne-t-elle quelque chose ?
        if (strpos($sql_where, 'SELECT') !== false) {
          // Extraire la sous-requête (approximatif)
          preg_match('/IN \((SELECT.*?)\)(?:\s|$)/i', $sql_where, $matches);
          if (isset($matches[1])) {
            $subquery = $matches[1];
            try {
              $test_stmt = self::$pdo->query($subquery);
              $subresults = $test_stmt->fetchAll(PDO::FETCH_COLUMN);
              $debug_log .= "<p>Sous-requête retourne " . count($subresults) . " résultats</p>";
              if (count($subresults) > 0 && count($subresults) <= 10) {
                $debug_log .= "<pre>" . print_r($subresults, true) . "</pre>";
              }
            } catch (PDOException $e) {
              $debug_log .= "<p class='error'>Erreur sous-requête : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
          }
        }
      }
      
    } catch (PDOException $e) {
      $debug_log .= "<p class='error'><strong>❌ Erreur SQL :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
      echo "  !! Erreur SQL pour corpus '$code': " . $e->getMessage() . "\n";
    }
  }
  
  fclose($handle);
  self::$pdo->commit();
  
  // Mise à jour des références
  self::$pdo->exec("
    UPDATE corpus_document SET
      corpus=(SELECT id FROM corpus WHERE code=corpus_document.corpus_code),
      document=(SELECT id FROM document WHERE code=corpus_document.document_code)
    ;
    UPDATE corpus SET
      docs=(SELECT COUNT(*) FROM corpus_document WHERE corpus=corpus.id)
    ;
  ");
  
  // Afficher le résultat final
  $debug_log .= "<hr><h2>Résultat final</h2>";
  $result = self::$pdo->query("SELECT code, titre, docs FROM corpus ORDER BY titre");
  $debug_log .= "<table border='1' cellpadding='5'>";
  $debug_log .= "<tr><th>Code</th><th>Titre</th><th>Documents</th></tr>";
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $color = $row['docs'] > 0 ? 'success' : 'error';
    $debug_log .= "<tr class='$color'>";
    $debug_log .= "<td>{$row['code']}</td>";
    $debug_log .= "<td>{$row['titre']}</td>";
    $debug_log .= "<td><strong>{$row['docs']}</strong></td>";
    $debug_log .= "</tr>";
  }
  $debug_log .= "</table>";
  
  $debug_log .= "</body></html>";
  
  // Écrire le fichier de debug
  file_put_contents(self::$home."site/debug_corpus.html", $debug_log);
  echo "  ✓ Debug écrit dans site/debug_corpus.html\n";
  
  // Générer les pages HTML (reste du code inchangé)
  Build::mkdir(Build::rmdir(self::$home."site/corpus/"));
  $template = str_replace("%relpath%", "../", self::$template);
  
  $index = self::corpus_index();
  file_put_contents(self::$home."site/corpus/index.html", str_replace("%main%", $index, $template));
  
  $qcorpus = self::$pdo->prepare("SELECT * FROM corpus ORDER BY titre");
  $qcorpus->execute();
  
  while ($corpus = $qcorpus->fetch(PDO::FETCH_ASSOC)) {
    $page = self::corpus_page($corpus);
    file_put_contents(
      self::$home."site/corpus/".$corpus['code'].".html",
      str_replace("%main%", $page, $template)
    );
  }
}

  public static function homepage()
{
  echo "=== Génération homepage ===\n";
  
  $template = str_replace("%relpath%", "", self::$template);
  $html = file_get_contents(self::$home."build/pages/index.html");
  
  // Injecter la chronologie
  $chrono = Build::transform(self::$home."index/chronologie.xml", self::$home."build/xsl/chrono.xsl");
  $html = str_replace("%chrono%", $chrono, $html);
  
  // Injecter les corpus
  $corpus = self::corpus_accueil();
  $html = str_replace("%corpus%", $corpus, $html);
  
  file_put_contents(self::$home."site/index.html", str_replace("%main%", $html, $template));
  
  echo "Fichier écrit : site/index.html\n";
}

/**
 * Génère les tuiles corpus pour la page d'accueil (max 6)
 */
private static function corpus_accueil()
{
  // Vérifie que les corpus existent
  $count = self::$pdo->query("SELECT COUNT(*) FROM corpus")->fetchColumn();
  if ($count == 0) {
    return '';  // Pas de corpus → section vide
  }
  
  $html = '<div class="card-deck corpus">'."\n";
  
  // Récupère les 6 premiers corpus (par ordre alphabétique)
  $qcorpus = self::$pdo->prepare("SELECT * FROM corpus ORDER BY titre LIMIT 6");
  $qcorpus->execute();
  
  while ($row = $qcorpus->fetch(PDO::FETCH_ASSOC)) {
    // Cherche l'image du premier document du corpus
    $qimg = self::$pdo->prepare("
      SELECT document.code 
      FROM corpus_document, document 
      WHERE corpus_document.corpus = ? 
        AND corpus_document.document = document.id 
      LIMIT 1
    ");
    $qimg->execute(array($row['id']));
    $doc = $qimg->fetch(PDO::FETCH_ASSOC);
    
    // Utilise l'image du document ou image par défaut
    $img = $doc ? $doc['code'] : 'default';
    
    $html .= '  <a href="corpus/'.$row['code'].'.html" class="card corpus">'."\n";
    $html .= '    <img src="document/S/'.$img.',S.jpg" onerror="this.src=\'images/accueil/doctype_ms.jpg\'"/>'."\n";
    $html .= '    <div>'.$row['titre'].'</div>'."\n";
    $html .= '  </a>'."\n";
  }
  
  $html .= '</div>'."\n";
  
  return $html;
}

/**
 * Page index des corpus (tuiles comme sur l'accueil)
 */
private static function corpus_index()
{
  $html = '<div class="container">'."\n";
  $html .= '  <h1>Corpus thématiques</h1>'."\n";
  $html .= '  <p>Collections de documents organisées par thèmes, événements ou critères particuliers.</p>'."\n";
  $html .= '  <div class="card-deck corpus">'."\n";
  
  $qcorpus = self::$pdo->prepare("SELECT * FROM corpus ORDER BY titre");
  $qcorpus->execute();
  
  while ($row = $qcorpus->fetch(PDO::FETCH_ASSOC)) {
    // Chercher une image représentative (premier document du corpus)
    $qimg = self::$pdo->prepare("
      SELECT document.code 
      FROM corpus_document, document 
      WHERE corpus_document.corpus = ? 
        AND corpus_document.document = document.id 
      LIMIT 1
    ");
    $qimg->execute(array($row['id']));
    $doc = $qimg->fetch(PDO::FETCH_ASSOC);
    $img = $doc ? $doc['code'] : 'default';
    
    $html .= '    <a href="'.$row['code'].'.html" class="card corpus">'."\n";
    $html .= '      <img src="../document/S/'.$img.',S.jpg" onerror="this.src=\'../images/accueil/doctype_ms.jpg\'"/>'."\n";
    $html .= '      <div class="card-body">'."\n";
    $html .= '        <h5>'.$row['titre'].'</h5>'."\n";
    $html .= '        <p class="card-text"><small>'.$row['docs'].' documents</small></p>'."\n";
    $html .= '      </div>'."\n";
    $html .= '    </a>'."\n";
  }
  
  $html .= '  </div>'."\n";
  $html .= '</div>'."\n";
  
  return $html;
}

/**
 * Page détail d'un corpus (comme page lieu/personne)
 */
private static function corpus_page($corpus)
{
  $html = '<div class="container">'."\n";
  $html .= '  <div class="row align-items-start">'."\n";
  $html .= '    <div class="col-9">'."\n";
  $html .= '      <h1>'.$corpus['titre'].'</h1>'."\n";
  
  if ($corpus['description']) {
    $html .= '      <p class="lead">'.$corpus['description'].'</p>'."\n";
  }
  
  $html .= '      <section>'."\n";
  $html .= '        <h2>Documents ('.$corpus['docs'].')</h2>'."\n";
  
  // Réutiliser la fonction uldocs existante
  $html .= self::uldocs("corpus", $corpus['id']);
  
  $html .= '      </section>'."\n";
  $html .= '    </div>'."\n";
  
  // Colonne latérale avec stats
  $html .= '    <div class="col-3">'."\n";
  $html .= '      <section class="stats">'."\n";
  $html .= '        <h3>Statistiques</h3>'."\n";
  
  // Stats par type
  $qstats = self::$pdo->prepare("
    SELECT document.type, COUNT(*) as count 
    FROM corpus_document, document 
    WHERE corpus_document.corpus = ? 
      AND corpus_document.document = document.id 
    GROUP BY document.type
  ");
  $qstats->execute(array($corpus['id']));
  
  $html .= '        <ul class="list-unstyled">'."\n";
  while ($stat = $qstats->fetch(PDO::FETCH_ASSOC)) {
    $label = self::$doctype[$stat['type']];
    $html .= '          <li>'.$stat['count'].' '.$label.'</li>'."\n";
  }
  $html .= '        </ul>'."\n";
  
  // Lieux principaux
  $qlieux = self::$pdo->prepare("
    SELECT lieu.label, COUNT(DISTINCT corpus_document.document) as count
    FROM corpus_document, lieu_document, lieu
    WHERE corpus_document.corpus = ?
      AND corpus_document.document = lieu_document.document
      AND lieu_document.lieu = lieu.id
    GROUP BY lieu.id
    ORDER BY count DESC
    LIMIT 5
  ");
  $qlieux->execute(array($corpus['id']));
  
  if ($qlieux->rowCount() > 0) {
    $html .= '        <h4>Lieux principaux</h4>'."\n";
    $html .= '        <ul class="list-unstyled">'."\n";
    while ($lieu = $qlieux->fetch(PDO::FETCH_ASSOC)) {
      $html .= '          <li>'.$lieu['label'].' ('.$lieu['count'].')</li>'."\n";
    }
    $html .= '        </ul>'."\n";
  }
  
  $html .= '      </section>'."\n";
  $html .= '    </div>'."\n";
  $html .= '  </div>'."\n";
  $html .= '</div>'."\n";
  
  return $html;
}
  
  private static function uldocs($table=null, $id=null)
  {
    $qdocument = self::$pdo->prepare('SELECT * FROM document WHERE id = ?');
    $qchrono = self::$pdo->prepare('SELECT * FROM chrono WHERE id = ?');
    
    if ($table == 'lieu') {
      $qlieupath = self::$pdo->prepare('SELECT path FROM lieu WHERE id = ?');
      $qlieupath->execute(array($id));
      list($path) = $qlieupath->fetch();
      $sql = "
        SELECT DISTINCT 
          chrono_document.* 
          FROM chrono_document, lieu_document, lieu 
          WHERE 
            lieu.path LIKE :path
            AND lieu_document.lieu = lieu.id
            AND lieu_document.document = chrono_document.document 
          ORDER BY chrono_document.id;
      ";
      $stmt = self::$pdo->prepare($sql);
      $path .= '%';
      $stmt->bindParam(':path', $path, PDO::PARAM_STR);
    }
    else if ($table == 'corpus') {
    $sql = "
      SELECT DISTINCT chrono_document.* 
      FROM chrono_document, corpus_document 
      WHERE corpus_document.corpus = ? 
        AND corpus_document.document = chrono_document.document 
      ORDER BY chrono_document.id;
    ";
    $stmt = self::$pdo->prepare($sql);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    }
      
    else if ($table) {
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
      if ($table == 'personne') $html .= self::htdocument($qdocument->fetch(), $id);
      else if ($table == 'lieu') $html .= self::htdocument($qdocument->fetch(), null, true);
      else $html .= self::htdocument($qdocument->fetch());
    }
    $html .= "</section>\n";
    return $html;
  }

  private static function htchrono($row)
  {
    $out = '';
    $out .= '<span class="date">';
    $out .= '<b class="year">'.substr($row['start'], 0, 4).'</b>';
    if ($row['start'] != $row['end']) {
      if (strlen($row['start']) >= 7 && strlen($row['end']) >= 7) {
        $mois1 = substr($row['start'], 5, 2);
        $mois2 = substr($row['end'], 5, 2);
      }
      if (strlen($row['start']) >= 10 && strlen($row['end']) >= 10) {
        $j1 = substr($row['start'], 8, 2);
        $j2 = substr($row['end'], 8, 2);
        if ($mois1 == $mois2) $out .= ', <span clas="day">'.(int)$j1.'-'.(int)$j2.'</span> <span clas="month">'.Build::mois($mois1).'</span>';
        else $out .= ', <span clas="day">'.(int)$j1.'</span> <span clas="month">'.Build::mois($mois1).' – '.(int)$j2.' '.Build::mois($mois2).'</span>';
      }
      else {
        if ($mois1 == $mois2) $out .= ', <span clas="month">'.Build::mois($mois1).'</span>';
        else $out .= ', <span clas="month">'.Build::mois($mois1).' – '.Build::mois($mois2).'</span>';
      }
    } 
    else {
      if (strlen($row['start']) >= 10) $out .= ', <span clas="day">'.(int)substr($row['start'], 8, 2).'</span> <span clas="month">'.Build::mois(substr($row['start'], 5, 2)).'</span>';
      else if (strlen($row['start']) >= 7) $out .= ', <span clas="month">'.Build::mois(substr($row['start'], 5, 2)).'</span>';
    }
    $out .= '.</span>';
    $out .= '<div class="desc"><span class="lieu">';
    $out .= ' '.$row['lieu_label'].'.';
    $out .= '</span>
    <i class="title">';
    $out .= ' '.$row['label'].'.';
    $out .= '</i></div>';
    return $out;
  }

  private static function chronotitle($row, $html=true)
  {
    
  }

  private static function htdocument($row, $persid=null, $tech=false)
  {
    $rolist = array();
    if ($persid) {
      $qrole = self::$pdo->prepare("SELECT role_code FROM personne_document WHERE document = ? AND personne = ? GROUP BY role ORDER BY role");
      $qrole->execute(array($row['id'], $persid));
      while ($res = $qrole->fetch(PDO::FETCH_ASSOC)) {
        $rolist[] = $res['role_code'];
      }
    }
    
    $html = "\n";
    $html .= '<a target="_blank" class="document '.$row['type'];
    if (count($rolist)) $html .= ' '. implode(' ', $rolist);
    $html .= '" href="../document/'.$row['code'].self::$_html.'">'."\n";
    $html .= '<div class="coldoc">'."\n";
    
    $html .= '  <div class="vignette" style="background-image:url(\'../document/S/'.$row['code'].',S.jpg\');"></div>'."\n";
    // role ?
    if ($persid) {
      $html .= '  <div class="roles">'."\n";
      foreach ($rolist as $role_code) {
        $html .= '<div class="role '.$role_code.'">'.self::$role[$role_code].'</div>';
      }
      $html .= '  </div>'."\n";
    }
    $html .= '  <div class="bibl">'."\n";
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
    $html .= '</div>'."\n";
    if ($tech) {
      $techlist = "";
      $qtech = self::$pdo->prepare("
      SELECT technique.*
        FROM technique_document, technique
        WHERE
          technique_document.document = ?
          AND technique_document.technique = technique.id
        GROUP BY technique.id
      ");
      $qtech->execute(array($row['id']));
      $first = true;
      while($atech= $qtech->fetch()) {
        if ($first) $first = false;
        else $techlist .= ",\n";
        $techlist .= '<span class="tech">'.$atech['label'].'</span>';
      }
      if ($techlist) $html .= "<div>\n".$techlist.".\n</div>\n";
    }
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
      // index.html sera généré plus tard par homepage()
      continue;
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
    $dom->formatOutput = true;
    $dom->substituteEntities = true;
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
