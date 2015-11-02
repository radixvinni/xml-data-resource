<?php
require_once('import.php');

function error($code) {
  header("Location: index.php?error={$code}");
  Importer::log("error",$code);
}
function query($sql) {
    global $ekos_host, $ekos_db, $ekos_user, $ekos_pwd, $user_maclabel;
    $conn_pg = pg_connect ("host='$ekos_host' dbname=$ekos_db user=$ekos_user password=$ekos_pwd")
      or die ("error on connection to $ekos_db");
    if ($user_maclabel) {
      pg_query($conn_pg,"set ac_session_maclabel = '".$user_maclabel."';") 
        or die('<error>unable to set session maclabel</error>');
      pg_query($conn_pg, $sql) 
        or die("<error>unable to run query: {$sql}</error>");
    }
}
  

if($user_group['name']!==$admin_group) { error(0);exit(); }

$method = $_GET['method'];

if ($method == 'import'):
  
      if($_FILES['file']['error']==UPLOAD_ERR_OK)
      {
          if (move_uploaded_file($_FILES['file']['tmp_name'], dirname(__FILE__)."/data/dump.xml")) {
            if (Importer::run(dirname(__FILE__)."/data/dump.xml"))
            {  Importer::log("import", $_FILES['file']['name'], $_FILES['file']['size']);
               header('Location: index.php?success=1');
            } else error(1);
          } else error(2);
      } else    error(3);

elseif ($method == 'extract'):
  
      if($_FILES['file']['error']==UPLOAD_ERR_OK)
      {
        if(in_array($_FILES['file']['type'], array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'))) {  
          if (move_uploaded_file($_FILES['file']['tmp_name'], dirname(__FILE__)."/data/data.zip")) {
            $zip = new ZipArchive();
            $x = $zip->open(dirname(__FILE__)."/data/data.zip");
            if ($x === true) {
                  $zip->extractTo(dirname(__FILE__)."/data");
                  $zip->close();
                  Importer::log("load", $_FILES['file']['name'], $_FILES['file']['size']);
                  header('Location: index.php?success=1');
            } else    error(4);
          } else    error(5);
        } else    error(6);
      } else    error(7);

elseif ($method == 'export'):
  
      require_once('import.php');
      require_once('db.php');
      BaseStream::registerStream('db');
      Importer::cbd_run();
      Importer::log("export");
      header('Location: index.php?success=1');
      
elseif ($method == 'save'):

      if (strpos($_POST['f'], '/') !== false)
          die('Not allowed!');

      $content = $_POST['contents'];
      $f = $_POST['f']; 
      file_put_contents('xsl/'.$f,stripslashes($content)) or error(8);
      Importer::log("save",$f,strlen($content));
      header("Location: index.php?admin={$f}&success=1");

elseif ($method == 'chmac_t'):
      if($_POST['inputNull'])
        query("ALTER TABLE {$_POST['inputTable']} SET MAC TO NULL");
      else
        query("ALTER TABLE {$_POST['inputTable']} SET MAC TO '{{$_POST['inputLevel']},{$_POST['inputCategory']}}'");
      header("Location: {$_SERVER['HTTP_REFERER']}&success=1");

elseif ($method == 'chmac_c'):
      if($_POST['inputNull'])
        query("ALTER TABLE {$_POST['inputTable']} ALTER COLUMN {$_POST['inputColumn']} SET MAC TO NULL");
      else
        query("ALTER TABLE {$_POST['inputTable']} ALTER COLUMN {$_POST['inputColumn']} SET MAC TO '{{$_POST['inputLevel']},{$_POST['inputCategory']}}'");
      header("Location: {$_SERVER['HTTP_REFERER']}&success=1");

elseif ($method == 'chmac_r'):
      query("UPDATE {$_POST['inputTable']} SET maclabel = '{{$_POST['inputLevel']},{$_POST['inputCategory']}}' WHERE {$_POST['inputColumn']} = {$_POST['inputId']}");
      header("Location: {$_SERVER['HTTP_REFERER']}&success=1");

else:
  error(0);

endif;
?>
