<?php

require_once( PATH_EXTENSIONS . "tutorials/classes/koala_group_tutorial.class.php");
require_once( PATH_EXTENSIONS . "tutorials/classes/koala_container_tutorial.class.php");

class tutorials extends koala_extension
{
	static $PATH;
	static $version = "1.0.0";

	function __construct()
	{
		self::$PATH = PATH_EXTENSIONS . "tutorials/";
		parent::__construct(PATH_EXTENSIONS . "tutorials.xml");
	}

	static public function get_koala_object_for ( $steam_object, $type, $obj_type )
	{
		if ( $obj_type === "group_tutorial_koala" )
			return new koala_group_tutorial( $steam_object );
		if ( $type == CLASS_ROOM && is_object( $creator = $steam_object->get_creator() ) && $creator->get_type() == CLASS_GROUP && is_object( $creator_parent = $creator->get_parent_group() ) && $creator_parent->get_attribute( OBJ_TYPE ) === 'course_learners' ) {
			return new koala_container_tutorial( $steam_object );
		}
		return FALSE;
	}

	function get_display_name ()
	{
		return h( gettext( "tutorials" ) );
	}

	function get_display_description ()
	{
		return h( gettext( "support for managing tutorial groups" ) );
	}

	function get_headline ( $headline = array(), $context = "", $params = array() )
	{
		if ( $context != 'tutorials' && $context != 'tutorial' ) return FALSE;
		$headline[] = array( 'name' => gettext( 'Tutorials' ), 'link' => $headline[-1]['link'] . 'tutorials/' );
		if ( !isset( $params['tutorial'] ) ) return $headline;
		$headline[] = $params['tutorial']->get_link();
		return $headline;
	}

	function can_extend ( $koala_class )
	{
		if ( $koala_class == 'koala_group_course' || is_subclass_of( $koala_class, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'COURSE_TUTORIALS_ENABLED', 'TRUE' );
	}

	public function disable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'COURSE_TUTORIALS_ENABLED', 'FALSE' );
	}

	protected function is_enabled_for ( $koala_object )
	{
		$enabled = $koala_object->get_attribute("COURSE_TUTORIALS_ENABLED");
		if ( $enabled === "TRUE" )
			return TRUE;
		else
			return FALSE;
	}

	function get_menu ( $params = array() )
	{
		if ( !is_array($params) || !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$course = $params[ "owner" ];
		if ( !($course instanceof koala_group_course) )
			throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
		
		$ext_tutorials = lms_steam::get_extensionmanager()->get_extension( "tutorials" );
		if (($course->is_member( lms_steam::get_current_user() ) || $course->is_admin( lms_steam::get_current_user() )) && $ext_tutorials->is_enabled( $course ) )
		{
			return array(
				"name" => gettext( "Tutorials" ),
				"link" => $course->get_url() ."tutorials/"
			);
		}
		return array();
	}
	
	function get_context_menu ( $context, $params = array() )
	{
		if ( !is_array($params) || !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$course = $params[ "owner" ];
		if ( !($course instanceof koala_group_course) )
			throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
		
		$subcontext = $context;
		if ( isset( $params[ "subcontext" ] ) )
			$subcontext = $params[ "subcontext" ];
		
		$current_user   = lms_steam::get_current_user();
		$context_menu_entry = array();
		
		$path = $course->get_url();

		switch( $subcontext )
		{
		case "tutorials":
			if ( $course->is_admin( $current_user ) )
			{
				$context_menu_entry = array(
					array( "link" => $path . "tutorials/prefs", "name" => gettext("Preferences") ),
					array( "link" => $path . "tutorials/new" , "name" => gettext( "Create new tutorial" ) )
						
						);
			}
		break;
		case "tutorial":
			if ( ($course->is_member( $current_user )) || ($course->is_admin( $current_user )) )
			{
				if ( isset( $GLOBALS[ "tutorial" ] ) && $GLOBALS[ "tutorial" ]->get_workroom()->check_access_insert( $current_user ))
				{	
					if ( $course->is_admin( $current_user ) )
					{
						$tutorial = new koala_group_tutorial($GLOBALS["tutorial"]);
						
						if(($tutorial->is_moderated()) && (! $tutorial->is_password_protected()))
						{
							$context_menu_entry = array(
							  array( "link" => $path . "tutorials/" . $GLOBALS[ "tutorial" ]->get_id() . "/edit/", "name" => gettext( "Preferences") ),
							  array( "link" => $path . "tutorials/" . $GLOBALS[ "tutorial" ]->get_id() . "/delete/", "name" => gettext( "Delete") ),
							  array( "link" => PATH_URL ."upload.php?env=" . $GLOBALS[ "tutorial" ]->get_workroom()->get_id(), "name" => gettext( "Upload learning material" ) ),
							  array( "link" => PATH_URL . "group_add_member.php?group=" . $GLOBALS["tutorial"]->get_id(), "name" => gettext( "Add learner" )),
							  array( "link" => $path . "tutorials/" . $GLOBALS[ "tutorial" ]->get_id() . "/requests/", "name" => gettext( "Manage membership requests" ))
							);
						}
						else
						{
							$context_menu_entry = array(
							  array( "link" => $path . "tutorials/" . $GLOBALS[ "tutorial" ]->get_id() . "/edit/", "name" => gettext( "Preferences") ),
							  array( "link" => $path . "tutorials/" . $GLOBALS[ "tutorial" ]->get_id() . "/delete/", "name" => gettext( "Delete") ),
							  array( "link" => PATH_URL ."upload.php?env=" . $GLOBALS[ "tutorial" ]->get_workroom()->get_id(), "name" => gettext( "Upload learning material" ) ),
							  array( "link" => PATH_URL . "group_add_member.php?group=" . $GLOBALS["tutorial"]->get_id(), "name" => gettext( "Add learner" ))
							);						
						}
					}
					else if ( $course->is_member( $current_user ) )
					{
						$context_menu_entry = array(
						  array( "link" => PATH_URL ."upload.php?env=" . $GLOBALS[ "tutorial" ]->get_workroom()->get_id(), "name" => gettext( "Upload learning material" ) )
						);
					}
				}
			}
		break;
		}
		
		return $context_menu_entry;
	}
	
	function handle_path( $path, $owner = FALSE, $portal = FALSE )
	{
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );
		if ( !is_object( $owner ) || !( $owner instanceof koala_group_course ) )
			throw new Exception( "No owner (course) provided.", E_PARAMETER );
		
		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		//$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_name() . "." . $path[0]);
		$current_semester = $owner->get_semester();
		
		$backlink = $owner->get_url() . $this->get_path_name() . "/";
		//$backlink = PATH_URL . "extensions/" . $this->get_name() . "/" . $path[0] ."/" . $path[1] . "/" . $this->get_name() . "/";

		switch( TRUE)
		{
		case ( isset( $path[0] ) && $path[0] == "enable" ):
		case ( isset( $path[0] ) && $path[0] == "disable" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			$switch = $path[ 0 ];
			include( self::$PATH . "switch_tutorials.php" );
			exit;
		break;
			case( isset($path[0]) && $path[0] == "prefs" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			include( self::$PATH . "tutorials_prefs.php" );
			exit;
		break;
			case( isset($path[0]) && $path[0] == "new" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			include( self::$PATH . "tutorial_new.php" );
			exit;
		break;
		case( isset($path[0]) && !empty($path[0]) && isset($path[1]) && $path[1] == "delete" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			global $tutorial;
			if ( $tutorial = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
			{
				if ( ! $tutorial->check_access_write( $user ) )
				{
					include( "no_access.php" );
					exit;
				}
				include( self::$PATH . "tutorial_delete.php" );
				exit;
			}
			else
			{
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
		break;
		case( isset($path[0]) && !empty($path[0]) && isset($path[1]) && $path[1] == "edit" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			if ( $tutorial_steam_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
			{
				if ( ! $tutorial_steam_group->check_access_write( $user ) )
				{
					include( "no_access.php" );
					exit;
				}
				$tutorial = new koala_group_tutorial( $tutorial_steam_group );
				$backlink = $backlink . $path[0];
				include( self::$PATH . "tutorial_edit.php" );
				exit;
			}
			else
			{
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
		break;
		case( isset($path[0]) && !empty($path[0]) && isset($path[1]) && $path[1] == "requests" ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			if ( $group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
			{
				if ( ! $group->check_access_write( $user ) )
				{
					throw new Exception( gettext( "You have no write access for this tutorial" ), E_USER_RIGHTS );
				}
				$backlink = $backlink . $path[0];
				include( PATH_PUBLIC . "group_membership_requests.php" );
				exit;
			}
			else
			{
				include( "bad_link.php" );
				exit;
			}
		break;
		case( isset($path[0]) && !empty( $path[0] ) ):
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			global $tutorial;
			if ( is_object( $tutorial = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) ) )
			{
				if ( ! $tutorial->check_access_read( $user ) )
				{
					throw new Exception( gettext( "You have no read access for this tutorial" ), E_USER_RIGHTS );
				}
				include( self::$PATH . "tutorial.php" );
				exit;
			}
			else
			{
				include( PATH_PUBLIC . "bad_link.php" );
				exit;
			}
		break;
		default:
			if ( ! $portal_user->is_logged_in() )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$course = $owner;
			include( self::$PATH . "tutorials.php" );
		break;
		}
	}
	
	function get_wrapper_class($obj)
	{	
		return new koala_group_tutorial($obj);
	}
	
	function get_specific_backlink($obj)
	{
		return $obj->get_semester_group()->get_name() . "/" . $obj->get_course_group()->get_name() . "/tutorials/" . $obj->get_id();
	}

	function get_path_name()
	{
		return $this->get_name();
	}
	
	static function get_version() {
		return self::$version;
	}
}
?>
