<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user   = lms_steam::get_current_user();

if ( !isset( $_GET["action"] ) || !isset( $_GET["id"] ) || !isset( $_GET["modifier"] ) || !isset( $_GET["where"] ) ) {
	include( "bad_link.php" );
	exit;
}

switch ( $_GET['action'] ) {
	case 'drop':
		$obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "id" ] );
		$koala_obj = koala_object::get_koala_object( $obj );
		$container = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "where" ], CLASS_CONTAINER );
		$koala_container = new koala_container( $container );
		if ( $_GET["modifier"] != "into" || !is_object( $obj ) || !is_object( $container ) )
			break;
		if ( $koala_container->accepts_object( $obj ) && $obj->move( $container ) ) {
			$access = $koala_obj->get_access_scheme();
			if ( $access ) {
				$root_creator = lms_steam::get_root_creator( $container );
				if ( is_object( $root_creator ) ) {
					$koala_creator = koala_object::get_koala_object( $root_creator );
					if ( is_object( $koala_creator ) && $koala_creator instanceof koala_group ) {
						$access_desc = $koala_obj->get_access_descriptions( $root_creator );
						if ( isset( $access_desc[$access]['members'] ) && isset( $access_desc[$access]['steam'] ) )
							$koala_obj->set_access( $access, $access_desc[$access]['members'], $access_desc[$access]['steam'], $koala_creator->get_members_group(), $koala_creator->get_staff_group(), $koala_creator->get_admins_group() );
					}
				}
			}
			$msg = gettext( "Placed '%OBJECT' into '%CONTAINER'." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		else {
			$msg = gettext( "Could not place '%OBJECT' into '%CONTAINER'." );
			$_SESSION[ "problem" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
	break;

	case 'drop-copy':
		$obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "id" ] );
		$koala_obj = koala_object::get_koala_object( $obj );
		$container = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "where" ], CLASS_CONTAINER );
		$koala_container = new koala_container( $container );
		if ( $_GET["modifier"] != "into" || !is_object( $obj ) || !is_object( $container ) )
			break;
		if ( !$koala_container->accepts_object( $obj ) ) {
			$msg = gettext( "Could not place '%OBJECT' into '%CONTAINER'." );
			$_SESSION[ "problem" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		$copy = steam_factory::create_copy( $GLOBALS["STEAM"]->get_id(), $obj );
		if ( !is_object( $copy ) ) {
			$msg = gettext( "Could not obtain a copy of '%OBJECT'." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		if ( $copy->move( $container ) ) {
			$access = $koala_obj->get_access_scheme();
			if ( $access ) {
				$root_creator = lms_steam::get_root_creator( $container );
				if ( is_object( $root_creator ) ) {
					$koala_creator = koala_object::get_koala_object( $root_creator );
					if ( is_object( $koala_creator ) && $koala_creator instanceof koala_group ) {
						$access_desc = $koala_obj->get_access_descriptions( $root_creator );
						if ( isset( $access_desc[$access]['members'] ) && isset( $access_desc[$access]['steam'] ) )
							$koala_obj->set_access( $access, $access_desc[$access]['members'], $access_desc[$access]['steam'], $koala_creator->get_members_group(), $koala_creator->get_staff_group(), $koala_creator->get_admins_group() );
					}
				}
			}
			//TODO: change access permissions for the target/source:
			$msg = gettext( "Obtained a copy of '%OBJECT' and placed it into '%CONTAINER'." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		else {
			try { if ( is_object( $copy ) ) $copy->delete(); } catch ( Exception $e ) { }
			$msg = gettext( "Could not obtain a copy of '%OBJECT' and place it into '%CONTAINER'." );
			$_SESSION[ "problem" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
	break;

	case 'drop-link':
	break;

	case 'take':
		$obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "id" ] );
		$koala_obj = koala_object::get_koala_object( $obj );
		$container = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "where" ], CLASS_CONTAINER );
		$koala_container = new koala_container( $container );
		if ( $_GET["modifier"] != "from" || !is_object( $obj ) || !is_object( $container ) )
			break;
		if ( $obj->move( $user ) ) {
			$msg = gettext( "'%OBJECT' has been picked up from '%CONTAINER' and placed into your clipboard." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		else {
			$msg = gettext( "Could not pick up '%OBJECT' from '%CONTAINER' and place it into your clipboard." );
			$_SESSION[ "problem" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
	break;

	case 'take-copy':
		$obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "id" ] );
		$koala_obj = koala_object::get_koala_object( $obj );
		$container = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET[ "where" ], CLASS_CONTAINER );
		$koala_container = new koala_container( $container );
		if ( $_GET["modifier"] != "from" || !is_object( $obj ) || !is_object( $container ) )
			break;
		$copy = steam_factory::create_copy( $GLOBALS["STEAM"]->get_id(), $obj );
		if ( !is_object( $copy ) ) {
			$msg = gettext( "Could not obtain a copy of '%OBJECT'." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		if ( $copy->move( $user ) ) {
			$msg = gettext( "A copy of '%OBJECT' has been placed into your clipboard." );
			$_SESSION[ "confirmation" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
		else {
			try { if ( is_object( $copy ) ) $copy->delete(); } catch ( Exception $e ) { }
			$msg = gettext( "Could not obtain a copy of '%OBJECT' and place it into your clipboard." );
			$_SESSION[ "problem" ] = str_replace( array( "%OBJECT", "%CONTAINER" ), array( $koala_obj->get_display_name(), $koala_container->get_display_name() ), $msg );
			header( "Location: " . $_SERVER[ "HTTP_REFERER" ] );
			exit;
		}
	break;

	case 'take-link':
	break;
}

// if we are still here, then something didn't work out:
include( 'bad_link.php' );

?>
