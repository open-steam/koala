<?php
require_once( "../etc/koala.conf.php");
ini_set('memory_limit', '1024M');
  $steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW );
  $steam_user->login();
  echo "loadin g users\n";
  
  $group_steam = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "steam", 0 );
  
  $users = $group_steam->get_members();
  $num = count($users);
  echo $num . " users loading\n";
  
  
  $count = 0;
  foreach($users as $user) {
  	$name = $user->get_full_name();
  	$count++;
  	echo ($count / $num ) * 100 . "%\n";
  }

?>