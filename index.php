<?php
require_once('render.php');
require_once('db.php');
require_once('custom.php');

BaseStream::registerStream('db');
BaseStream::registerStream('func');

$params = array(
  'base_url' => "http://" . $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . dirname($_SERVER["REQUEST_URI"]. '?') . '/',
  'logout_url' => "http://logout@" . $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'] . dirname($_SERVER["REQUEST_URI"]. '?') . '/',
  'request_url' => $_SERVER['QUERY_STRING'],
  'request' => $_REQUEST,
  'SOURCE_SRV' => $ekos_host,
  'SOURCE_DB' => $ekos_db,
  'TARGET_SRV' => $cbd_host,
  'TARGET_DB' => $cbd_db,
  'USER_NAME' => $user_name,
  'USER_GROUP' => $user_group['name'],
  'USER_GECOS' => $user_info['gecos'],
  'USER_MACLABEL' => $user_maclabel
);
if(isset($_GET['debug']))
  HTMLRenderer::render('xsl/test.xsl', $params, $params);
else
  HTMLRenderer::render('xsl/index.xsl', $params, $params);

?>
