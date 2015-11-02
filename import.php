<?php
require_once('config.php');


class Importer {

  public static function log($act, $in_file='', $size=0) {
    $xml = simplexml_load_file(dirname(__FILE__)."/data/log.xml");
    if($xml===FALSE) $xml = simplexml_load_string("<log/>");
    
    $child = $xml->addChild($act);
    $child->addAttribute("in_file", $in_file);
    $child->addAttribute("in_size", $size);
    $child->addAttribute("user", $_SERVER['REMOTE_USER']);
    $child->addAttribute("date", date('d.m.y, H:i:s'));
    $child->addAttribute("ip", $_SERVER['REMOTE_ADDR']);
    $xml->asXML(dirname(__FILE__)."/data/log.xml") or die("error. change permissions for data directory");
  }
  public static function run($f) {
    $xml = simplexml_load_file($f);
    if($xml===FALSE) return FALSE;
    global $ekos_host, $ekos_db, $ekos_user, $ekos_pwd, $user_maclabel;
    $conn_pg = pg_connect ("host='$ekos_host' dbname=$ekos_db user=$ekos_user password=$ekos_pwd")
      or die ("error on connection to $ekos_db");
    if ($user_maclabel)
      pg_query($conn_pg,"set ac_session_maclabel = '".$user_maclabel."';") 
        or die('<error>unable to set session maclabel</error>');
    Importer::_run($xml, $conn_pg);
    return TRUE;
  }
  public static function cbd_run() {
    
    $proc = new XSLTProcessor;
    $proc->importStyleSheet(DOMDocument::load('xsl/cbd.xsl'));
    global $registerPHPFunctions;
    if ($registerPHPFunctions)
            $proc->registerPHPFunctions($registerPHPFunctions);
    $cbd_xml = $proc->transformToDoc(DOMDocument::load('db://?all-data'));
    
    $xml = simplexml_import_dom($cbd_xml) or die('import failed');
    
    global $cbd_host, $cbd_db, $cbd_user, $cbd_pwd;
    $conn_pg = pg_connect ("host='$cbd_host' dbname=$cbd_db user=$cbd_user password=$cbd_pwd")
    or die ("error on connection to $cbd_db");
    
    Importer::_run($xml, $conn_pg);
  }
  public static function _run($xml, $conn_pg) {
    global $user_maclabel;
    $table=array();
    $data=array();
    foreach ($xml->children() as $k=>$v) {
      if(!isset($table[$k])) $table[$k]=array();
      if(!isset($data[$k])) $data[$k]=array();
      $assoc = array();
      foreach ($v->children() as $r=>$c) {
        $d=(string)$c;
        if($d!=='')
        if(is_numeric($d)) { 
          $d = floatval($d);
          if (!isset($table[$k][$r]))  $table[$k][$r] = 'float';
        }
        else $table[$k][$r] = 'varchar(255)';
        if($d!=='')$assoc[$r] = "'".pg_escape_string($d)."'";
      }
      $data[$k][] = $assoc;
    }
    //ooo, how much memory is used here...

    foreach($table as $k => $v) {
      $mod = "";
      if ($user_maclabel)
        $mod .= "ALTER TABLE {$k} SET MAC TO NULL; ALTER TABLE {$k} DISABLE COLUMN MACS;";
      
      $mod .= "DROP TABLE IF EXISTS {$k}; CREATE TABLE {$k} ( ";
      foreach($v as $col => $typ){
        if ($col != 'maclabel')
        $mod .= "\"{$col}\" {$typ},";
      }
      $mod .= "CHECK(TRUE))";
      $mod .= ($_POST['mac_records']?" WITH (MACS = true)":"").";";
      if ($_POST['mac_columns']) $mod .= "ALTER TABLE {$k} ENABLE COLUMN MACS;";
      pg_query($conn_pg, $mod)
                  or die ("error on query ".pg_last_error($conn_pg));
    }
    foreach($data as $k => $t) {
      pg_query($conn_pg, "BEGIN;");
      foreach($t as $v) {
        $ins = "INSERT INTO {$k} ( ". implode(",", array_keys($v)) . ") VALUES (". implode(",", array_values($v)) . ");";
        pg_query($conn_pg, $ins)
                    or die ("error on query ".pg_last_error($conn_pg));
      }
      pg_query($conn_pg, "ANALYZE {$k};");
      pg_query($conn_pg, "COMMIT;");
    
    }
  }
}

