<?php
require_once( PATH_EXTENSIONS . "units_mediathing/classes/koala_object_mediathing.class.php");

class units_mediathing extends koala_unit
{
	
	private $course;
	static $version = "1.0.0";
	private static $PATH, $DISPLAY_NAME;
	
	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_mediathing/";
		self::$DISPLAY_NAME = gettext("Mediathing Erweiterung");
		parent::__construct( PATH_EXTENSIONS . "units_mediathing.xml", $steam_object );
	}
	
	public function enable_for ( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_MEDIATHING_ENABLED', 'TRUE' );
	}
	
	public function disable_for( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_MEDIATHING_ENABLED', 'FALSE' );
	}
	
	public function is_enabled_for( $koala_object ) {
		return $koala_object->get_attribute( 'UNITS_MEDIATHING_ENABLED' ) === 'TRUE';
	}
	
	function get_path_name() {
		return $this->get_Name();
	}
	
	function get_wrapper_class($obj) {
		
	}
	
	//TODO: move to abstract
	function get_icon()
	{
		return PATH_URL . "cached/get_document.php?name=koala_unit_extern.png&type=objecticon&height=70";
	}
	
	function get_display_name()
	{
		return self::$DISPLAY_NAME;
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
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_mediathing" )
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
		
		
		
		if (count($path) > 1 && $path[1] == "create_exit") {
			// sinnvolle aktionen hier
			$action="create_exit";
			include( PATH_EXTENSIONS . 'units_mediathing/modules/units_mediathing.php' );
		} else {
			$action = "index";
			include( PATH_EXTENSIONS . 'units_mediathing/modules/units_mediathing.php' );
		}
	}
	
	
	static public function get_koala_object_for( $steam_object, $type, $obj_type )
	{
		if ( $obj_type === "mediathing_unit_koala" ) {
			return new koala_object_mediathing( $steam_object, new units_mediathing( lms_steam::get_root_creator( $steam_object ) ) );
		}
		return FALSE;
	}
	
//	function get_context_menu( $context, $params = array() ) {
//		if (isset($params["subcontext"])) {
//			if ($params["subcontext"] == "index") {
//				return array(array( "name" => gettext("configuration"), "link" => "./config/" ));
//			} else if ($params["subcontext"] == "config") {
//				return array(array( "name" => gettext("back"), "link" => ".." ));
//			}
//		}
//	}
	
	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}
	
	static function get_version() {
		return self::$version;
	}
	
}
?>
