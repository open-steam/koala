<?php

require_once( PATH_EXTENSIONS . "units_docpool/classes/koala_container_docpool.class.php");

class units_docpool extends koala_unit
{
	private $course, $portal;
	static $version = "1.0.0";

	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;

	static public function get_koala_object_for( $steam_object, $type, $obj_type )
	{
		if ( $obj_type === "container_docpool_unit_koala" ) {
			return new koala_container_docpool( $steam_object, new units_docpool( lms_steam::get_root_creator( $steam_object ) ) );
		}
		if ( $type == CLASS_CONTAINER && !is_string($obj_type) ) {
			$root_env = $steam_object->get_root_environment();
			$env = $steam_object->get_environment();
			if ( !is_object($env) || !is_object($root_env) || $root_env->get_id() != $env->get_id() )
				return FALSE;  // object is not directly in a workroom
			$root_creator = $root_env->get_creator();
			if ( is_object( $root_creator ) && $root_creator->get_attribute( OBJ_TYPE ) !== 'course_learners' )
				return FALSE;  // object is not in learners' workroom
			return new koala_container_docpool( $steam_object, new units_docpool( lms_steam::get_root_creator( $steam_object ) ) );
		}
		return FALSE;
	}

	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_docpool/";
		self::$DISPLAY_NAME = gettext("Document Pool");
		self::$DISPLAY_DESCRIPTION = gettext("This standard unit type provides a space for exchanging documents. As creator of the unit you can decide who may hav access to the materials and who may be able to post materials. If you choose to support units in your course this unit type will be <b>enabled by default</b>.");
		parent::__construct(PATH_EXTENSIONS . "units_docpool.xml", $steam_object);
	}

	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_DOCPOOL_ENABLED', 'TRUE' );
	}

	public function disable_for( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_DOCPOOL_ENABLED', 'FALSE' );
	}

	public function is_enabled_for( $koala_object )
	{
		//if ( ! units_base::is_enabled_for( $koala_object ) ) return FALSE;
		return $koala_object->get_attribute( 'UNITS_DOCPOOL_ENABLED' ) === 'TRUE';
	}

	function get_headline( $headline = array(), $context = "", $params = array() )
	{
		if ( !isset( $params["unit"] ) ) return FALSE;
		if ( !($params["unit"]->get_attribute("OBJ_TYPE") === "container_docpool_unit_koala") ) return FALSE;
		$unit = koala_object::get_koala_object( $params["unit"] );
		$headline[] = array("name"=>$unit->get_name());
		return $headline;
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !isset( $params[ "unit" ] ) )
			return array();
		$unit = $params[ "unit" ];
		if ( $unit instanceof steam_object )
			$unit = koala_object::get_koala_object( $unit );
		if ( ! $unit instanceof koala_container_docpool )
			return array();
		if ( !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$owner = $params[ "owner" ];

		if ( isset( $params[ "container" ] ) )
			$container = koala_object::get_koala_object( $params[ "container" ] );
		else
			$container = $unit;
		if ( ( ! $container instanceof koala_container_docpool ) && ( get_class($container) !== 'koala_container' ) )
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
				$koala_container = new koala_container_docpool( $container, new units_docpool( $owner->get_steam_object() ) );
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

		if ( is_object( $unit ) && $unit->get_attribute( "UNIT_TYPE" ) !== "container_docpool_unit_koala" && $unit->get_attribute( "UNIT_TYPE" ) !== "units_docpool" ) {
			if ( ! $unit instanceof koala_container_docpool && ! $this->get_koala_object_for( $unit->get_steam_object(), $unit->get_steam_object()->get_type(), $unit->get_attribute( OBJ_TYPE ) ) instanceof koala_container_docpool )
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
					include( self::$PATH . "modules/units_docpool_edit.php" );
				else
					include( "container_edit.php" );
				exit;
			break;
			case "delete":
				include( "container_delete.php" );
				exit;
			break;
		}
		include( "container_inventory.php" );
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
		return PATH_URL . "cached/get_document.php?name=koala_unit_docpool.png&type=objecticon&height=70";
    	//return PATH_URL . "extensions/units_docpool/icons/docpool_unit.png";
    }

	function get_big_icon()
    {
		return PATH_URL . "cached/get_document.php?name=koala_unit_docpool_big.png&type=objecticon&height=120";
    	//return PATH_URL . "extensions/units_docpool/icons/docpool_unit_big.png";
    }

	static public function get_access_descriptions( $grp ) {
		return koala_container_docpool::get_access_descriptions( $grp );
	}
  
	static function get_version() {
		return self::$version;
	}
}
?>
