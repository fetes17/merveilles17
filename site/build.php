<?php

Merveilles17::init();


/*

foreach (glob($home."site/pages/*.html") as $srcfile) {
  $dstfile = $home."site/".basename($srcfile);
  echo "$dstfile\n";
  $main = file_get_contents($srcfile);
  $html = str_replace("%main%", $main, $template);
  file_put_contents($dstfile, $html);
}



$fwpers = fopen($home."index/pers.tsv", "w");
fwrite($fwpers, "@key\t@role\tpersName\tfichier\n");
$fwplace = fopen($home."index/place.tsv", "w");
fwrite($fwplace, "clé\tentrée\toccurrence\tfichier\n");
$fwtech = fopen($home."index/tech.tsv", "w");
fwrite($fwtech, "@type\ttech\tfichier\n");
$biblio = array();



foreach (glob($home."xml/*.xml") as $srcfile) {
  $dstname = basename($srcfile, ".xml");
  fwrite($fwreadme, "* [".basename($srcfile)."](https://fetes17.github.io/merveilles17/xml/".basename($srcfile).")\n");

  
  $dstfile = $home."site/".$dstname.".html";
  echo basename($srcfile),"\n";
  $dom = Build::dom($srcfile);
  
  $biblio[$dstname] = Build::transformDoc($dom, $theme."biblio.xsl", null, array('name' => $dstname));
  
  $main = Build::transformDoc($dom, $theme."document.xsl", null, array('filename' => $dstname, 'locorum' => $indexes['locorum']));
  file_put_contents($dstfile, str_replace("%main%", $main, $template));
  // data
  $place = Build::transformDoc($dom, $theme."place.xsl", null, array('filename' => $dstname, 'locorum' => $indexes['locorum']));
  fwrite($fwplace, $place);
  $pers = Build::transformDoc($dom, $theme."pers.xsl", null, array('filename' => $dstname));
  fwrite($fwpers, $pers);
  $tech = Build::transformDoc($dom, $theme."tech.xsl", null, array('filename' => $dstname));
  fwrite($fwtech, $tech);
}


$doctype = array(
  "arc" => "Archives",
  "gr" => "Gravures",
  "i" => "Imprimés",
  "ms" => "Manuscrits",
  "p" => "Périodiques",
);

$html = array();
$last = null;
$first = true;
foreach ($biblio as $key => $value) {
  $type = explode('_', $key)[1];
  if ($type != $last) {
    if ($first) $first = false;
    else $html[] = "</div>";
    $html[] = "<div>";
    $html[] = "<h2>";
    if (isset($doctype[$type])) $html[] = $doctype[$type];
    else $html[] = $type;
    $html[] = "</h2>";
    $last = $type;
  }
  $html[] = $value;
}
$html[] = "</div>";

file_put_contents($home."site/biblio.html", str_replace("%main%", implode("\n", $html), $template));
*/

class Merveilles17
{
  /** SQLite link */
  static public $pdo;
  /** Home directory of project, absolute */
  static $home;
  /** Database absolute path */
  static private $sqlfile;
  /** get a temp dir */
  static $tmpdir;
  /** SQL to create database */
  static private $create = "
PRAGMA encoding = 'UTF-8';
PRAGMA page_size = 8192;

CREATE TABLE doc (
  -- répertoire des documents
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  type           INTEGER,               -- ! type de document
  title          TEXT NOT NULL,         -- ! titre text brut
  bibl           TEXT NOT NULL,         -- ! référence bibliographique (html)
  PRIMARY KEY(id ASC)
);

CREATE TABLE lieu (
  -- répertoire des lieux
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme de référence
  alt            TEXT,                  -- ? forme alternative, pour recherche
  locality       TEXT,                  -- ? commune, pour recherche
  coord          TEXT,                  -- ? coordonnées carto
  docs           INTEGER,               -- ! number of documents, calculated, for sorting
  occs           INTEGER,               -- ! number of occurrences, calculated, for sorting
  PRIMARY KEY(id ASC)
);

CREATE TABLE lieu_doc (
  -- Occurences d’un lieu dans un document
  id             INTEGER,               -- ! rowid auto
  lieu           INTEGER,               -- ! lieu.id obtenu avec par lieu.code
  lieu_code      TEXT NOT NULL,         -- ! lieu.code
  doc            INTEGER,               -- ! doc.id obtenu avec par doc.code
  doc_code       TEXT NOT NULL,         -- ! sera obtenu avec par doc.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le fichier source
  text           TEXT NOT NULL,         -- ! forme dans le texte
  desc           TEXT,                  -- ? description, à tirer du contexte
  PRIMARY KEY(id ASC)
);

CREATE TABLE technique (
  -- répertoire des techniques
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme d’autorité
  PRIMARY KEY(id ASC)
);

CREATE TABLE technique_doc (
  -- Occurences d’un technique dans un document
  id             INTEGER,               -- ! rowid auto
  technique      INTEGER,               -- ! technique.id obtenu avec par technique.code
  technique_code TEXT NOT NULL,         -- ! technique.code
  doc            INTEGER,               -- ! doc.id obtenu avec par doc.code
  doc_code       TEXT NOT NULL,         -- ! sera obtenu avec par doc.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le fichier source
  text           TEXT NOT NULL,         -- ! forme dans le texte
  PRIMARY KEY(id ASC)
);

CREATE TABLE personne (
  -- répertoire des personnes
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme dans le texte
  PRIMARY KEY(id ASC)
);

CREATE TABLE personne_doc (
  -- Occurences d’un nom de personne dans un document
  id             INTEGER,              -- ! rowid auto
  personne       INTEGER,              -- ! personne.id obtenu avec par personne.code
  personne_code  TEXT NOT NULL,        -- ! personne.code
  doc            INTEGER,              -- ! doc.id obtenu avec par doc.code
  doc_code       TEXT NOT NULL,        -- ! sera obtenu avec par doc.code
  anchor         TEXT NOT NULL,        -- ! ancre dans le ficheir source
  text           TEXT NOT NULL,        -- ! forme dans le texte
  role           TEXT,                 -- ? @role
  PRIMARY KEY(id ASC)
);


  ";
  
  public static function init()
  {
    self::$home = dirname(dirname(__FILE__)).'/';
    self::$sqlfile = self::$home."site/merveilles17.sqlite";
    self::$pdo = Build::sqlite(self::$sqlfile, self::$create);
    self::$tmpdir = sys_get_temp_dir()."/";
    // self::$template = file_get_contents($theme."template.html");
  }
  
  /**
   * Load dictionaries in database
   */
  public static function load()
  {
    $srcfile = $home."index/lieu.tsv";
    $handle = fopen($srcfile, "r");
    $sql = "INSERT INTO lieu (code, term, alt, locality, coord) VALUES (:code, :term, :alt, :locality, :coord);";
    $stmt = self::$pdo->prepare($sql);
    $stmt->bindParam('code', $code);
    $stmt->bindParam('term', $term);
    $stmt->bindParam('alt', $alt);
    $stmt->bindParam('locality', $locality);
    $stmt->bindParam('coord', $coord);
    fgets($handle); // jump first line    
    while ($line = fgets($handle)) {
      list($code, $term, $alt, $locality, $country, $coord) = explode("\t", $line);
      $stmt->execute();
    }
    return;
    
    // loop on all xml files, and do lots of work
    
    $readme = "
# Merveilles de la Cour, les textes

[Documentation du schema](https://fetes17.github.io/merveilles17/merveilles17.html)

";
    $tech_doc = "code\tlieu_code\toccurrence\tfichier\n";
    
    foreach (glob($home."xml/*.xml") as $srcfile) {
      echo basename($srcfile),"\n";
      $dom = Build::dom($srcfile);
      
      $readme .= "* [".basename($srcfile)."](https://fetes17.github.io/merveilles17/xml/".basename($srcfile).")\n";

      $dstname = basename($srcfile, ".xml");
      $dstfile = self::$home."site/".$dstname.".html";
      
      $biblio[$dstname] = Build::transformDoc($dom, $theme."biblio.xsl", null, array('name' => $dstname));

      /*      
      $main = Build::transformDoc($dom, $theme."document.xsl", null, array('filename' => $dstname, 'locorum' => $indexes['locorum']));
      file_put_contents($dstfile, str_replace("%main%", $main, $template));
      // data
      $place = Build::transformDoc($dom, $theme."place.xsl", null, array('filename' => $dstname, 'locorum' => $indexes['locorum']));
      fwrite($fwplace, $place);
      $pers = Build::transformDoc($dom, $theme."pers.xsl", null, array('filename' => $dstname));
      fwrite($fwpers, $pers);
      $tech = Build::transformDoc($dom, $theme."tech.xsl", null, array('filename' => $dstname));
      fwrite($fwtech, $tech);
      */
    }
  }

}

class Build
{
  /** XSLTProcessors */
  private static $transcache = array();

  
  public function __construct($conf)
  {
  
  }
  
  /**
   * get a pdo link to an sqlite database with good options
   */
  static function sqlite($file, $sql)
  {
    $dsn = "sqlite:".$file;
    // if not exists, create
    if (!file_exists($file)) {
      if (!file_exists($dir = dirname($file))) {
        mkdir($dir, 0775, true);
        @chmod($dir, 0775);  // let @, if www-data is not owner but allowed to write
      }
      $pdo = new PDO($dsn);
      @chmod($sqlite, 0775);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->exec($sql);
    }
    else {
      $pdo = new PDO($dsn);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    // temp table in memory
    $pdo->exec("PRAGMA temp_store = 2;");
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
      if (!is_dir(dirname($dst))) {
        if (!@mkdir(dirname($dst), 0775, true)) exit(dirname($dst)." impossible à créer.\n");
        @chmod(dirname($dst), 0775);  // let @, if www-data is not owner but allowed to write
      }
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
}


 ?>
