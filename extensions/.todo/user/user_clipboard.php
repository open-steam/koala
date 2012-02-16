<?php

/*
 * The following variables *must* be set before including this file:
 * * $user : steam_user whose clipboard shall be displayed
 * * $documents_path : rest of the path that is relevant for the clipboard
 *     (e.g. everything after /user/root/clipboard/) as an array of path
 *     elements.
 * * $documents_root : steam_container that is the root of the document path
 *     hierarchy, e.g. $user (for the clipboard)
 * * $portal : a valid lms_portal instance
 * 
 * The following variables *may* be set before including this file:
 * * $container_icons : if set to FALSE, then no icons will be displayed for
 *      the inventory objects, otherwise the icons from the open-sTeam backend
 *      will be displayed.
 */

$current_user = lms_steam::get_current_user();

$cache = get_cache_function( $user->get_name(), 86400 );

$action = "";
if ( isset( $documents_path[ 0 ] ) && is_numeric( $documents_path[ 0 ] ) ) {
	$backlink .= $documents_path[ 0 ] . "/";
	$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$documents_path[ 0 ], CLASS_CONTAINER );
	$koala_container = koala_object::get_koala_object( $container );
	if ( isset( $documents_path[ 1 ] ) ) $action = $documents_path[ 1 ];
}
else {
	$container = $documents_root;
	$koala_container = new koala_container_clipboard( $user );
	if ( isset( $documents_path[ 0 ] ) ) $action = $documents_path[ 0 ];
}

$html_handler = new koala_html_user( $user );
$html_handler->set_context( "clipboard", array( "koala_container" => $koala_container ) );

//$link_path = $koala_container->get_link_path();

switch ( $action ) {
	case "new-folder":
		$environment = $container;
		unset( $container );
		unset( $koala_container );
		include( "container_new.php" );
		exit;
	break;
	case "edit":
		include( "container_edit.php" );
		exit;
	break;
	case "delete":
		include( "container_delete.php" );
		exit;
	break;
}

include( "container_inventory.php" );

?>
