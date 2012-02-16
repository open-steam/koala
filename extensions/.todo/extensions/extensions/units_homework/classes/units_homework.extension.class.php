<?php

require_once( PATH_EXTENSIONS . "units_homework/classes/koala_container_homework.class.php");

class units_homework extends koala_unit
{
	private $course, $portal;
	static $version = "1.0.0";
	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;

	static public function get_koala_object_for ( $steam_object, $type, $obj_type )
	{
		if ( $obj_type === "container_homework_unit_koala" ) {
			return new koala_container_homework( $steam_object, new units_homework( lms_steam::get_root_creator( $steam_object ) ) );
		}
		if ( $type == CLASS_CONTAINER && !is_string($obj_type) ) {
			$root_env = $steam_object->get_root_environment();
			$env = $steam_object->get_environment();
			if ( !is_object($env) || !is_object($root_env) || $root_env->get_id() != $env->get_id() )
				return FALSE;  // object is not directly in a workroom
			$root_creator = $root_env->get_creator();
			if ( is_object( $root_creator ) && $root_creator->get_attribute( OBJ_TYPE ) !== 'course_learners' )
				return FALSE;  // object is not in learners' workroom
			return new koala_container_homework( $steam_object, new units_homework( lms_steam::get_root_creator( $steam_object ) ) );
		}
		return FALSE;
	}

	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_homework/";
		self::$DISPLAY_NAME = gettext("Homework");
		self::$DISPLAY_DESCRIPTION = gettext("This unit gives the students the chance to upload their homework and get feedback from the staff. The staff has to upload the task and enter an enddate and a maximum number of participants.");
		parent::__construct(PATH_EXTENSIONS . "units_homework.xml", $steam_object);
    $this->set_action_permissions(PERMISSION_ACTION_EDIT | PERMISSION_ACTION_DELETE);
	}

	function can_extend ( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_HOMEWORK_ENABLED', 'TRUE' );
	}

	public function disable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_HOMEWORK_ENABLED', 'FALSE' );
	}

	public function is_enabled_for ( $koala_object )
	{
		//if ( ! units_base::is_enabled_for( $koala_object ) ) return FALSE;
		return $koala_object->get_attribute( 'UNITS_HOMEWORK_ENABLED' ) === 'TRUE';
	}

	function get_headline ( $headline = array(), $context = "", $params = array() )
	{
		if ( !isset( $params["unit"] ) ) return FALSE;
		$unit = koala_object::get_koala_object( $params["unit"] );
		$headline[] = $unit->get_link();
		return $headline;
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !isset( $params[ "unit" ] ) )
			return array();
		$unit = $params[ "unit" ];
		if ( $unit instanceof steam_object )
			$unit = koala_object::get_koala_object( $unit );
		if ( ! $unit instanceof koala_container_homework )
			return array();
		if ( !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$owner = $params[ "owner" ];

		if ( isset( $params[ "container" ] ) )
			$container = koala_object::get_koala_object( $params[ "container" ] );
		else
			$container = $unit;
		if ( ( ! $container instanceof koala_container_homework ) && ( get_class($container) !== 'koala_container' ) )
			return array();
		$context_menu = array();

		$subcontext = $context;
		if ( isset( $params[ "subcontext" ] ) )
			$subcontext = $params[ "subcontext" ];
		switch ( $subcontext ) {
			case "unit":
				return $container->get_context_menu( $context, $params );
			break;
		}
		return $context_menu;
	}

	function handle_path( $path, $owner = FALSE, $portal = FALSE )
	{
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );

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

		//$backlink = PATH_URL . "extensions/units/" . $path[0] ."/" . $path[1] . "/units/";
		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$action = "";
		if ( isset( $path[ 0 ] ) && is_numeric( $path[ 0 ] ) ) {
			if ( isset( $path[ 1 ] ) && is_numeric( $path[ 1 ] ) ) {
				$backlink .= $path[0] . "/";
				$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[ 1 ], CLASS_CONTAINER );
				$koala_container = new koala_container( $container, $backlink );
				$backlink .= $path[ 1 ] . "/";
				$unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
				if ( isset( $path[ 2 ] ) ) $action = $path[ 2 ];
			}
			else {
				$backlink .= $path[ 0 ] . "/";
				$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[ 0 ], CLASS_CONTAINER );
				$koala_container = new koala_container_homework( $container, new units_homework( $owner->get_steam_object() ) );
				$unit = $koala_container;
				if ( isset( $path[ 1 ] ) ) $action = $path[ 1 ];
			}
		}
		else {
			$container = $owner->get_workroom();
			$koala_container = new koala_container_workroom( $container, $backlink );
			$unit = $koala_container;
			if ( isset( $path[ 0 ] ) ) $action = $path[ 0 ];
		}

		if ( is_object( $unit ) && $unit->get_attribute( "UNIT_TYPE" ) !== "container_homework_unit_koala" && $unit->get_attribute( "UNIT_TYPE" ) !== "units_homework" ) {
			if ( ! $unit instanceof koala_container_homework && ! $this->get_koala_object_for( $unit->get_steam_object(), $unit->get_steam_object()->get_type(), $unit->get_attribute( OBJ_TYPE ) ) instanceof koala_container_homework )
				return FALSE;
		}

		$koala_container->set_obj_types_invisible( array( "container_wiki_koala", "room_wiki_koala", "KOALA_WIKI" ) );
		$html_handler = new koala_html_course( $owner );
		$html_handler->set_context( "units", array( "subcontext" => "unit", "owner" => $owner, "unit" => $unit, "container" => $container ) );

		switch( $action )
		{
			case "new-folder":
				$environment = $container;
				unset( $container );
				unset( $koala_container );
				include( "container_new.php" );
				exit;
			break;
			case "edit":
				if ( $container->get_id() == $unit->get_id() )
					include( self::$PATH . "modules/units_homework_edit.php" );
				else
					include( "container_edit.php" );
				exit;
			break;
			case "delete":
				include( "container_delete.php" );
				exit;
			break;
			case "new_homework":
				include( self::$PATH . "modules/units_homework_upload.php" );
				exit;
			break;
			case "feedback":
				include( self::$PATH . "modules/units_homework_feedback.php" );
				exit;
			break;
			case "points_tab":
				include( self::$PATH . "modules/units_homework_pointslist.php" );
				exit;
			break;
		}
		include( self::$PATH . "modules/units_homework.php" );
		return TRUE;
	}

	function get_wrapper_class($obj)
	{
	}

	function get_path_name()
	{
		return "units";
	}

	function get_display_name()
	{
		return self::$DISPLAY_NAME;
	}

	function get_display_description()
	{
		return self::$DISPLAY_DESCRIPTION;
	}

	function set_course( $course = FALSE )
	{
      return $this->course = $course;
    }

	function set_portal( $portal = FALSE )
	{
      return $this->portal = $portal;
    }

	function get_html_unit_new()
	{
      $course = $this->course;
      //$portal = $this->portal;
      include($this->get_path() . "/modules/" . $this->get_name() . "_new.php" );
      return $unit_new_html;
    }

    function handle_POST_data()
    {
    	$course = $this->course;
    	include($this->get_path() . "/modules/" . $this->get_name() . "_new_POST.php");
    }

    function get_icon()
    {
		//return PATH_URL . "cached/get_document.php?name=koala_unit_homework.png&type=objecticon&height=70";
    	return PATH_URL . "extensions/units_homework/icons/homework_unit.png";
    }

	function get_big_icon()
    {
		//return PATH_URL . "cached/get_document.php?name=koala_unit_homework_big.png&type=objecticon&height=120";
    	return PATH_URL . "extensions/units_homework/icons/homework_unit_big.png";
    }

	static public function get_access_descriptions( $grp ) {
		return koala_container_homework::get_access_descriptions( $grp );
	}
	
	static function get_version() {
		return self::$version;
	}
}
?>
