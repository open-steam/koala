<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}

$users = steam_factory::get_group($GLOBALS['STEAM']->get_id(), "steam")->get_members();
foreach ($users as $user) {
	if ($user->get_name() != "root" && $user->get_name() != "service" && $user->get_name() != "postman"){
		echo "deleting " . $user->get_name() . "<br>";
		$user->delete();
	}
}

?>