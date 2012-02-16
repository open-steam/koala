<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$myUser = lms_steam::get_current_user();
//$myUser->set_attribute("Test", (integer) -12);
echo "GO";
echo $myUser->get_attribute("Test") . "<br />";
echo PHP_INT_MAX . "<br /><br />";

//echo chr()

?>