<?php

require_once( PATH_EXTENSIONS . "units_pyramiddiscussion/classes/koala_container_pyramiddiscussion.class.php");

class units_pyramiddiscussion extends koala_unit
{
	private $course, $portal;
	static $version = "1.0.0";
	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;

	static public function get_koala_object_for ( $steam_object, $type, $obj_type )
	{
		if ( strpos( $obj_type, "container_pyramiddiscussion" ) === 0 )
			return new koala_container_pyramiddiscussion( $steam_object, new units_pyramiddiscussion( lms_steam::get_root_creator( $steam_object ) ) );
		return FALSE;
	}

	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_pyramiddiscussion/";
		self::$DISPLAY_NAME = gettext("Pyramid discussion");
		self::$DISPLAY_DESCRIPTION = gettext("A Pyramid discussion is a presentation form in which the achieved considerations of a discussion process get noted in form of a pyramid. You can use this unit type for carrying out such a discussion as part of your course. The creation dialog lets you choose a topic for the discussion, further description of the unit and the number of participants of the discussion.");
		parent::__construct( PATH_EXTENSIONS . "units_pyramiddiscussion.xml", $steam_object );
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
		$koala_object->set_attribute( 'UNITS_PYRAMIDDISCUSSION_ENABLED', 'TRUE' );
	}

	public function disable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_PYRAMIDDISCUSSION_ENABLED', 'FALSE' );
	}

	public function is_enabled_for ( $koala_object )
	{
		//if ( ! units_base::is_enabled_for( $koala_object ) ) return FALSE;
		return $koala_object->get_attribute( 'UNITS_PYRAMIDDISCUSSION_ENABLED' ) === 'TRUE';
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !isset( $params[ "unit" ] ) )
			return array();
		$unit = $params[ "unit" ];
		if ( ! $unit instanceof koala_container_pyramiddiscussion )
			return array();
		if ( !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$owner = $params[ "owner" ];

		if ( isset( $params[ "container" ] ) )
			$container = koala_object::get_koala_object( $params[ "container" ] );
		else
			$container = $unit;
			if ( ( ! $container instanceof koala_container_pyramiddiscussion ) )
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

		if ( isset( $path[0] ) && is_numeric( $path[0] ) ) {
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_pyramiddiscussion" )
				return;
			$koala_container = new koala_container_pyramiddiscussion( $steam_unit, new units_pyramiddiscussion( $owner->get_steam_object() ) );
			$unit = $koala_container;
		}

		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		//$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_name() . "." . $path[0]);
		$current_semester = $owner->get_semester();

		//$backlink = PATH_URL . "extensions/units/" . $path[0] ."/" . $path[1] . "/units/";
		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$html_handler = new koala_html_course( $owner );
		$html_handler->set_context( "units", array( "subcontext" => "unit", "owner" => $owner, "unit" => $unit ) );

		$course = $owner;
		include( PATH_EXTENSIONS . "units_pyramiddiscussion/modules/units_pyramiddiscussion.php" );
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

	function get_icon()
	{
		return PATH_URL . "cached/get_document.php?name=koala_unit_pyramiddiscussion.png&type=objecticon&height=70";
		//return PATH_URL . "get_document.php?pyramiddiscussion_unit.png";
	}

	function get_big_icon()
    {
		return PATH_URL . "cached/get_document.php?name=koala_unit_pyramiddiscussion_big.png&type=objecticon&height=120";
    	//return PATH_URL . "extensions/units_pyramiddiscussion/icons/pyramiddiscussion_unit_big.png";
    }

    function initialize_pyramiddiscussion($values, $room, $basegroup)
    {
    	$module = $GLOBALS[ "STEAM" ]->get_module("package:pyramiddiscussion");
    	$vars = array(
    				  'pyramiddiscussion_max' => $values["participants"],
    				  'pyramiddiscussion_title' => $values["dsc"],
    				  );

    	try{
    		$GLOBALS[ "STEAM" ]->predefined_command(
    											$module,
    											"initialize_pyramiddiscussion",
    											array($room, $basegroup, "text/plain", $vars),
    											0);



    	} catch(Exception $ex){throw $ex;}
    }
    
	static function get_version() {
		return self::$version;
	}
}
?>