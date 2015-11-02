<?php

$ekos_host = "localhost";
$ekos_db = "test";
$ekos_user = "postgres";
$ekos_pwd = "";

$cbd_host = "localhost";
$cbd_db = "central";
$cbd_user = "postgres";
$cbd_pwd = "";

$registerPHPFunctions = false;
$admin_group = "root";

$user_name = $_SERVER['REMOTE_USER'];
$user_info = posix_getpwnam($user_name);
$user_group =  posix_getgrgid($user_info['gid']);

//astra linux maclabel - ignore this
if (posix_access('/etc/security/msec_mac/'.$user_info['uid'], POSIX_R_OK)) {
  $user_maclabel = file_get_contents('/etc/security/msec_mac/'.$user_info['uid']);
  $user_maclabel = explode(':',$user_maclabel);
  $user_maxcategory = trim(array_pop($user_maclabel));
  $user_maxlevel = array_pop($user_maclabel);
  $user_maclabel = '{'.$user_maxlevel.','.$user_maxcategory.'}';
}
else $user_maclabel = "";

?>
