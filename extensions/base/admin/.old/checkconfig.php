<?php
// no direct call
if (!defined('_VALID_KOALA')) {
	header("location:/");
	exit;
}
include_once( "../etc/koala.conf.php" );
echo "<h1>Prüfe die Konfiguration</h1>";



echo "Gastanmeldung......";
$steam = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
$GLOBALS[ "STEAM" ] = $steam;
$steam_user = $steam->get_current_steam_user();
echo $steam_user->get_name();
echo "<span style=\"color:green;font-size:small\">erfolgreich</span><br />";

    if( !$steam || !$steam->get_login_status() )
    {
        print("No server connection!");
	    exit();
    }
    
echo "connection data<br />";
echo "server: " . STEAM_SERVER . ":" . STEAM_PORT . "<br />";

echo "PublicGroups: " . steam_factory::groupname_to_object( $steam->get_id(), "PublicGroups" )->get_id() . "<br />";
echo "PrivGroups: " . steam_factory::groupname_to_object( $steam->get_id(), "PrivGroups" )->get_id() . "<br />";
echo "Faculties: " . steam_factory::groupname_to_object( $steam->get_id(), "Faculties" )->get_id() . "<br />";
echo "Courses: " . steam_factory::groupname_to_object( $steam->get_id(), "Courses" )->get_id() . "<br />";

echo "Prüfe STEAM_PUBLIC_GROUP.......";
if (defined("STEAM_PUBLIC_GROUP") && STEAM_PUBLIC_GROUP != "") {
	check_steam_group(STEAM_PUBLIC_GROUP);
//	try {
//		$steam_public_group = steam_factory::get_object($steam->get_id(), STEAM_PUBLIC_GROUP);
//	} catch (Exception $e) {
//		echo "<span style=\"color:red;font-size:small\">STEAM_PUBLIC_GROUP falsch</span><br />";
//		$steam_public_group = steam_factory::groupname_to_object( $steam->get_id(), "PublicGroups" );
//		if ($steam_public_group != 0 && $steam_public_group instanceof steam_group) {
//			echo "STEAM_PUBLIC_GROUP should be: " . $steam_public_group->get_id();
//		} else {
//			echo "create a public group e.g. PublicGroups and set id to STEAM_PUBLIC_GROUP in config file";
//		}
//		exit;
//	}
	echo "<span style=\"color:green;font-size:small\">erfolgreich</span><br />";
} else {
	echo "<span style=\"color:red;font-size:small\">STEAM_PUBLIC_GROUP nicht gesetzt</span><br />";
	exit;
}

function check_steam_group ( $group_name, $parent_group_name = NULL, $description = NULL, $fix = FALSE ) {
	$parent_group = NULL;
	if ( is_string( $parent_group_name ) ) {
		$parent_group = steam_factory::get_group( $GLOBALS[ "STEAM" ]->get_id(), $parent_group_name );
		if ( !is_object( $parent_group ) ) {
			echo( "Error: could not find parent group '" . $parent_group_name . "' for group '" . $group_name . "'.\n" );
			return FALSE;
		}
	}
	$group_fullname = ( is_string( $parent_group_name ) ? $parent_group_name . "." : "" ) . $group_name;
	$group = steam_factory::get_group( $GLOBALS[ "STEAM" ]->get_id(), $group_fullname );
	if ( is_object( $group ) )
		return $group;
	if ( !$fix ) {
		echo "Error";
		return FALSE;
	}
	$group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $group_name, $parent_group, NULL, $description );
	if ( is_object( $group ) )
		echo( "Created group '" . $group_fullname . "'.\n" );
	else
		echo( "Error: could not create group '" . $group_fullname . "'.\n" );
	return $group;
}

?>