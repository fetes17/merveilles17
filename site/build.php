<?php

Merveilles17::init();
Merveilles17::load();


/*



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
  bibl           TEXT NOT NULL,         -- ! référence bibliographique (html)
  PRIMARY KEY(id ASC)
);

CREATE TABLE lieu (
  -- répertoire des lieux
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme de référence
  coord          TEXT,                  -- ? coordonnées carto
  locality       TEXT,                  -- ? commune, pour recherche
  alt            TEXT,                  -- ? forme alternative, pour recherche
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
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
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  desc           TEXT,                  -- ? description, à tirer du contexte
  PRIMARY KEY(id ASC)
);

CREATE TABLE technique (
  -- répertoire des techniques
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme d’autorité
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
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
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  PRIMARY KEY(id ASC)
);

CREATE TABLE personne (
  -- répertoire des personnes
  id             INTEGER,               -- ! rowid auto
  code           TEXT UNIQUE NOT NULL,  -- ! code unique
  term           TEXT NOT NULL,         -- ! forme dans le texte
  docs           INTEGER,               -- ! nombre de documents,  calculé, pour tri
  occs           INTEGER,               -- ! nombre d’occurrences, calculé, pour tri
  PRIMARY KEY(id ASC)
);

CREATE TABLE personne_doc (
  -- Occurences d’un nom de personne dans un document
  id             INTEGER,               -- ! rowid auto
  personne       INTEGER,               -- ! personne.id obtenu avec par personne.code
  personne_code  TEXT NOT NULL,         -- ! personne.code
  doc            INTEGER,               -- ! doc.id obtenu avec par doc.code
  doc_code       TEXT NOT NULL,         -- ! sera obtenu avec par doc.code
  anchor         TEXT NOT NULL,         -- ! ancre dans le ficheir source
  occurrence     TEXT NOT NULL,         -- ! forme dans le texte
  role           TEXT,                  -- ? @role
  PRIMARY KEY(id ASC)
);


  ";
  static private $doctype = array(
      "arc" => "Archives",
      "gr" => "Gravures",
      "i" => "Imprimés",
      "image" => "Images",
      "ms" => "Manuscrits",
      "p" => "Périodiques",
    );

  
  public static function init()
  {
    self::$home = dirname(dirname(__FILE__)).'/';
    self::$sqlfile = self::$home."site/merveilles17.sqlite";
    // recreate sqlite base on each call
    self::$pdo = Build::sqlcreate(self::$sqlfile, self::$create);
    self::$tmpdir = sys_get_temp_dir()."/";
    // self::$template = file_get_contents($theme."template.html");
  }
  
  /**
   * Load dictionaries in database
   */
  public static function load()
  {
    self::tsv_insert("lieu", array("code", "term", "coord", "locality", "alt"), file_get_contents(self::$home."index/lieu.tsv"));
    return;
    
    // different generated files    
    $readme = "
# Merveilles de la Cour, les textes

[Documentation du schema](https://fetes17.github.io/merveilles17/merveilles17.html)

";
    $biblio = array();
    $lieu_doc =           "lieu_code\tdoc_code\tanchor\toccurrence\tdesc\n";
    $technique_doc = "technique_code\tdoc_code\tanchor\toccurrence\n";
    $personne_doc =   "personne_code\tdoc_code\tanchor\toccurrence\trole\n";
    
    // loop on all xml files, and do lots of work
    foreach (glob($home."xml/*.xml") as $srcfile) {
      echo basename($srcfile),"\n";
      $dom = Build::dom($srcfile);
      
      $readme .= "* [".basename($srcfile)."](https://fetes17.github.io/merveilles17/xml/".basename($srcfile).")\n";

      $dstname = basename($srcfile, ".xml");
      $dstfile = self::$home."site/".$dstname.".html";
      
      $biblio[$dstname] = Build::transformDoc($dom, $home."site/xsl/doc.xsl", null, array('name' => $dstname));
      $lieu_doc .= Build::transformDoc($dom, $home."site/xsl/lieu_doc.xsl", null, array('filename' => $dstname));
      
      /*      
      $main = Build::transformDoc($dom, $theme."document.xsl", null, array('filename' => $dstname, 'locorum' => $indexes['locorum']));
      file_put_contents($dstfile, str_replace("%main%", $main, $template));
      // data
      fwrite($fwplace, $place);
      $pers = Build::transformDoc($dom, $theme."pers.xsl", null, array('filename' => $dstname));
      fwrite($fwpers, $pers);
      $tech = Build::transformDoc($dom, $theme."tech.xsl", null, array('filename' => $dstname));
      fwrite($fwtech, $tech);
      */
    }
    file_put_contents($home."README.md", $readme);
    
    // fill biblio
    $sql = "INSERT INTO doc (code, type, bibl) VALUES (:code, :type, :bibl);";
        $stmt = self::$pdo->prepare($sql);
    $stmt->bindParam('code', $code);
    $stmt->bindParam('type', $type);
    $stmt->bindParam('bibl', $type);
    self::$pdo->beginTransaction();
    foreach ($biblio as $code => $bibl) {
      $type = explode('_', $code)[1];
    }
    self::$pdo->commit();

    
    
    file_put_contents($home."index/lieu_doc.tsv", $lieu_doc);
    $sql = "INSERT INTO lieu_doc (lieu_code, doc_code, anchor, occurrence, desc) VALUES (:lieu_code, :doc_code, :anchor, :occurrence, :desc);";
    $stmt = self::$pdo->prepare($sql);
    $stmt->bindParam('lieu_code', $lieu_code);
    $stmt->bindParam('doc_code', $doc_code);
    $stmt->bindParam('anchor', $anchor);
    $stmt->bindParam('locality', $locality);
    $stmt->bindParam('coord', $coord);

    
    
  }
  
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
      print_r($values);
      $stmt->execute($values);
    }
    self::$pdo->commit();
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
    if (!file_exists($dir = dirname($file))) {
      mkdir($dir, 0775, true);
      @chmod($dir, 0775);  // let @, if www-data is not owner but allowed to write
    }
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
