<?php
include_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "admin" );
if ( !is_object($admin_group) || !$admin_group->is_member( $user ) ) {
	include( "no_access.php" );
	exit;
}

phpinfo();
?>
