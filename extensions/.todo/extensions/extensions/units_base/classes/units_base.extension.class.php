<?php

require_once( PATH_EXTENSIONS . "units_base/classes/unitmanager.class.php" );

class units_base extends koala_extension
{ 
	static $PATH;
	static $version = "1.0.0";

	function __construct()
	{
		self::$PATH = PATH_EXTENSIONS . "units_base/";
		parent::__construct(PATH_EXTENSIONS . "units_base.xml");
	}

	function get_display_name()
	{
		return h( gettext( "units" ) );
	}

	function get_display_description()
	{
		return h( gettext( "support for different kinds of units" ) );
	}

	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for( $koala_object )
	{
		$koala_object->set_attribute( 'COURSE_UNITS_ENABLED', 'TRUE' );
	}

	public function disable_for( $koala_object )
	{
		$koala_object->set_attribute( 'COURSE_UNITS_ENABLED', 'FALSE' );
	}

	protected function is_enabled_for( $koala_object )
	{
		$enabled = $koala_object->get_attribute("COURSE_UNITS_ENABLED");
		if ( $enabled === "TRUE" )
			return TRUE;
		else
			return FALSE;
	}

	function get_menu( $params = array() )
	{
		if ( is_array($params) && isset( $params[ "owner" ] ) ) {
			$course = $params[ "owner" ];
			if ( !($course instanceof koala_group_course) )
				throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
			return array(
				    "name" => gettext( "Units" ),
				    "link" => $course->get_url() . "units/"
					);
		}
		return array();
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !is_array($params) || !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$course = $params[ "owner" ];
		if ( !($course instanceof koala_group_course) )
			throw new Exception( "The 'owner' param is not an koala_group_course.", E_PARAMETER );
		
		$subcontext = $context;
		if ( isset( $params[ "subcontext" ] ) )
			$subcontext = $params[ "subcontext" ];
		
		$current_user   = lms_steam::get_current_user();
		$context_menu = array();
		
		$learners_workroom = $course->get_workroom();
		
		//$path = PATH_URL . "extensions/units/" . $course->get_semester()->get_name() ."/" . $course->get_name() . "/";
		$path = $course->get_url();
		
		switch( $subcontext )
		{
		case "units":
			if ( ($course->is_member( $current_user )) || ($course->is_admin( $current_user )) )
			{
				if ( $learners_workroom->check_access_insert( $current_user ) )
				{
					$context_menu[] = array(
						"link" => $path . "units/new",
						"name" => gettext( "Create new unit" )
					);
				}
			}
		break;
		case "unit":	
			if ( ($course->is_member( $current_user )) || ($course->is_admin( $current_user )) )
			{
				if ( isset( $GLOBALS[ "unit" ] ) && $GLOBALS[ "unit" ]->get_steam_object()->check_access_write( $current_user ) )
				{
					$context_menu[] = array(
						"link" => $path . "/units/" . $GLOBALS[ "unit" ]->get_id() . "/edit/",
						"name" => gettext( "Preferences")
					);
					//TODO: might also need to check for move permission (not only write) for deleting unit:
					$context_menu[] = array(
						"link" => $path .  "/units/" . $GLOBALS[ "unit" ]->get_id() . "/delete/",
						"name" => gettext( "Delete")
					);
				}
			}
		break;
		}
		
		return $context_menu;
	}

	function handle_path( $path, $owner = FALSE, $portal = FALSE )
	{
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );
		if ( !is_object( $owner ) )
			throw new Exception( "No owner provided.", E_PARAMETER );
		
	if(!isset($portal) || !is_object($portal))
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}
		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		//$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_name() . "." . $path[0]);
		$current_semester = $owner->get_semester();
		
		$backlink = $owner->get_url() . $this->get_path_name() . "/";
		//$backlink = PATH_URL . "extensions/units/" . $path[0] ."/" . $path[1] . "/units/";
		switch( TRUE )
		{
		case( isset($path[0]) && $path[0] == "new" ):
    
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			include( self::$PATH . "unit_new.php" );
			exit;
		break;

		case( isset($path[0]) && is_numeric($path[0]) && isset($path[1]) && $path[1] == "edit" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( ! is_object( $steam_unit ) ) {
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
			global $unit;
			$unit = koala_object::get_koala_object( $steam_unit );
			if ( ! is_object( $unit ) )
			{
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
			if ( ! $steam_unit->check_access_write( $user ) )
			{
				throw new Exception( gettext( "You have no write access for this unit" ), E_USER_RIGHTS );
			}
			$backlink = $backlink . $path[0] . "/";
			include( self::$PATH . "unit_edit.php" );
			exit;
		break;

		case( isset($path[0]) && is_numeric($path[0]) && isset($path[1]) && $path[1] == "delete" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( ! is_object( $steam_unit ) ) {
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
			global $unit;
			$unit = koala_object::get_koala_object( $steam_unit );
			if ( ! is_object( $unit ) )
			{
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
			if ( ! $steam_unit->check_access_write( $user ) )
			{
				throw new Exception( gettext( "You have no write access for this unit" ), E_USER_RIGHTS );
			}
			$backlink = $backlink . $path[0] . "/";
			include( self::$PATH . "unit_delete.php" );
			exit;
		break;

		case( isset($path[0]) && ! empty( $path[0] ) ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			if ( !is_numeric($path[0]) ) return FALSE;  // not a unit id
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( !is_object($steam_unit) ) return FALSE;  // not a unit
			$unit_type = $steam_unit->get_attribute( 'UNIT_TYPE' );
			if ( !is_string( $unit_type ) || empty( $unit_type ) ) return FALSE;  // not a valid unit
			$unit_extension = unitmanager::create_unitmanager( $owner )->get_unittype( $unit_type );
			if ( !is_object( $unit_extension ) ) return FALSE;  // no matching unit extension
			if ( $unit_extension->handle_path( $path, $owner, $portal ) ) exit;
			else return FALSE;
		break;

		default:
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			include( self::$PATH . "units.php" );
			exit;
		break;
		}
		return FALSE;
	}

	function get_wrapper_class($obj)
	{
	}

	function get_path_name()
	{
		return "units";
	}
	
	static function get_version() {
		return self::$version;
	}
}
?>
