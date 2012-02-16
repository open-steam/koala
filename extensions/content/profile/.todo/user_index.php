<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$login = $_GET[ "id" ];

if ( ! $user = steam_factory::username_to_object( $STEAM->get_id(), $login ) )
{
	include( "bad_link.php" );
	exit;
}

$backlink = PATH_URL . "user/" . $login . "/";

$path = url_parse_rewrite_path( $_GET[ "path" ] );
switch( TRUE)
{
	case ( $path[ 0 ] == "contacts" ):
		if (YOUR_CONTACTS) {
			$backlink .= "contacts/";
			include( "user_contacts.php" );
		} else {
			include( "bad_link.php" );
		}
		exit;
	break;

	case ( $path[ 0 ] == "groups" ):
	    if (YOUR_GROUPS) {
			$backlink .= "groups/";
			include( "user_groups.php" );
		} else {
			include( "bad_link.php" );
		}
		exit;
	break;

	case ( $path[ 0 ] == "clipboard" ):
		if (CLIPBOARD) {
			// only the user herself and the admins may view her clipboard:
			$current_user = lms_steam::get_current_user();
			if ( ($user->get_id() != $current_user->get_id()) && (!is_object($admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "Admin") ) || !$admin_group->is_member( $current_user )) ) {
				$portal->set_problem_description( gettext( "You are not permitted to view this folder." ) );
				$portal->show_html();
				exit;
			}
			$backlink .= "clipboard/";
			$documents_root = $user;
			$documents_path = isset( $path[ 1 ] ) ? array_slice( $path, 1 ) : array();
			include( "user_clipboard.php" );
		} else {
			include( "bad_link.php" );
		}
		exit;
	break;

	// Try the extensions:
	case ( isset( $path[0] ) && !empty( $path[0] ) ):
		$extension_manager = lms_steam::get_extensionmanager();
		$extension_manager->handle_path( $path, new koala_user( $user ), $portal );
	break;
}

if (YOUR_PROFILE) {
	include_once( "user_profile.php" );
} else {
	include( "bad_link.php" );
}

?>
