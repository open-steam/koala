<?php

class units_videostreaming extends koala_unit
{
	private $course;
	static $version = "1.0.0";
	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;
	
	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_videostreaming/";
		self::$DISPLAY_NAME = gettext("Videostreaming Unit");
		self::$DISPLAY_DESCRIPTION = gettext("Some useful text here.");
		parent::__construct( PATH_EXTENSIONS . "units_videostreaming.xml", $steam_object );
	}
	
	public function enable_for ( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_VIDEOSTREAMING_ENABLED', 'TRUE' );
	}
	
	public function disable_for( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_VIDEOSTREAMING_ENABLED', 'FALSE' );
	}
	
	public function is_enabled_for( $koala_object ) {
		return $koala_object->get_attribute( 'UNITS_VIDEOSTREAMING_ENABLED' ) === 'TRUE';
	}
	
	function get_path_name() {
		return $this->get_Name();
	}
	
	function get_wrapper_class($obj) {
		
	}
	
	static function get_version() {
		return self::$version;
	}
	
	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}
	
	function get_icon()
	{
		return PATH_URL . "cached/get_document.php?name=koala_unit_extern.png&type=objecticon&height=70";
	}
	
	function set_course( $course = FALSE )
	{
      return $this->course = $course;
    }
    
	function get_html_unit_new()
	{
      $course = $this->course;
      include($this->get_path() . "/modules/" . $this->get_name() . "_new.php" );
      return $unit_new_html;
    }
    
	function handle_path( $path, $owner = FALSE, $portal = FALSE ) {
		$r="";
		foreach ($path as $s){
			$r .= $s;
		}
		error_log("Path: ". $r);
		if (is_string($path)) {
			$path = url_parse_rewrite_path($path);
		}

		if (!isset($portal) || !is_object($portal)){
			$portal = lms_portal::get_instance();
			$portal->initialize(GUEST_NOT_ALLOWED);
		}
		
		$steam_unit = "";
		$action = "";
		if ( isset( $path[0] ) && is_numeric( $path[0] ) ) {
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_videostreaming" )
				return;
			//TODO: cool. neues object von sich in sich ? Häh, geil!
			$unit = new units_elearning($owner->get_steam_object());
			$docextern = new koala_object_elearning( $steam_unit, $unit );
			if ( isset( $path[1] ) ) $action = $path[1];
		}

		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		$current_semester = $owner->get_semester();
		$course = $owner;

		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$html_handler = new koala_html_course( $owner );
		
		
		
		if (count($path) > 1 && $path[1] == "config") {
			// sinnvolle aktionen hier
			$action="config";
			include( PATH_EXTENSIONS . 'units_videostreaming/modules/units_videostreaming.php' );
		} else {
			$action = "index";
			include( PATH_EXTENSIONS . 'units_videostreaming/modules/units_videostreaming.php' );
		}
	}
	
}

/*require_once( PATH_EXTENSIONS . "units_elearning/classes/koala_object_elearning.class.php");

class units_extern extends koala_unit
{
	private $course, $portal;

	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;

	static public function get_koala_object_for( $steam_object, $type, $obj_type )
	{
		if ( strpos( $obj_type, "docextern_unit_koala" ) === 0 )
			return new koala_object_docextern( $steam_object, new units_extern( lms_steam::get_root_creator( $steam_object ) ) );
		return FALSE;
	}

	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_extern/";
		self::$DISPLAY_NAME = gettext("External Resource");
		self::$DISPLAY_DESCRIPTION = gettext("You can use this unit type for providing external web resources in an course. In the preferences of this unit you can specify an external web link that will thereafter be accessable as unit content.");
		parent::__construct( PATH_EXTENSIONS . "units_extern.xml", $steam_object );
	}

	function can_extend ( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_EXTERN_ENABLED', 'TRUE' );
	}

	public function disable_for ( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_EXTERN_ENABLED', 'FALSE' );
	}

	public function is_enabled_for ( $koala_object )
	{
		//if ( ! units_base::is_enabled_for( $koala_object ) ) return FALSE;
		return $koala_object->get_attribute( 'UNITS_EXTERN_ENABLED' ) === 'TRUE';
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !isset( $params[ "unit" ] ) )
			return array();
		$unit = $params[ "unit" ];
		if ( ! $unit instanceof units_extern )
			return array();
		if ( !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$owner = $params[ "owner" ];

		if ( isset( $params[ "object" ] ) )
			$object = koala_object::get_koala_object( $params[ "object" ] );
		else
			$object = $unit;
		if ( ( ! $object instanceof koala_object_docextern ) )
			return array();
		$context_menu = array();

		$subcontext = $context;
		if ( isset( $params[ "subcontext" ] ) )
			$subcontext = $params[ "subcontext" ];
		switch ( $subcontext ) {
			case "unit":
				return $object->get_context_menu( $context, $params );
			break;
		}
		return $context_menu;
	}

	function handle_path( $path, $owner = FALSE, $portal = FALSE )
	{
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );

		if ( !isset($portal) || !is_object($portal) )
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}

		$action = '';
		if ( isset( $path[0] ) && is_numeric( $path[0] ) ) {
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_extern" )
				return;
			$unit = new units_extern( $owner->get_steam_object() );
			$docextern = new koala_object_docextern( $steam_unit, $unit );
			if ( isset( $path[1] ) ) $action = $path[1];
		}

		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		//$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_name() . "." . $path[0]);
		$current_semester = $owner->get_semester();
		$course = $owner;

		//$backlink = PATH_URL . "extensions/units/" . $path[0] ."/" . $path[1] . "/units/";
		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$html_handler = new koala_html_course( $owner );
		$html_handler->set_context( "units", array( 'subcontext' => 'unit', 'owner' => $owner, 'unit' => $unit, 'object' => $docextern ) );

		switch( $action )
		{
			case 'edit':
				include( self::$PATH . 'modules/units_extern_edit.php' );
				exit;
			break;
			case 'delete':
				include( self::$PATH . 'modules/units_extern_delete.php' );
				exit;
			break;
		}

		include( PATH_EXTENSIONS . 'units_extern/modules/units_extern.php' );
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
		return PATH_URL . "cached/get_document.php?name=koala_unit_extern.png&type=objecticon&height=70";
	}

	function get_big_icon()
    {
		return PATH_URL . "cached/get_document.php?name=koala_unit_extern_big.png&type=objecticon&height=120";
    }
}*/
?>
