<?php

require_once( PATH_EXTENSIONS . "units_pointlist/classes/koala_container_pointlist.class.php");

class units_pointlist extends koala_unit
{
	private $course, $portal;
	static $version = "1.0.0";
	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;

	static public function get_koala_object_for( $steam_object, $type, $obj_type )
	{
		if ( strpos( $obj_type, "container_pointlist" ) === 0 && strpos( $obj_type, "container_pointlist_studinfo" ) !== 0)
			return new koala_container_pointlist( $steam_object, new units_pointlist( lms_steam::get_root_creator( $steam_object ) ) );
		return FALSE;
	}

	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_pointlist/";
		self::$DISPLAY_NAME = gettext("Pointlist");
		self::$DISPLAY_DESCRIPTION = gettext("A Pointlist may be used to store points of tutorial sheets for each participant of a course.");
		parent::__construct( PATH_EXTENSIONS . "units_pointlist.xml", $steam_object );
    $this->set_action_permissions(PERMISSION_ACTION_EDIT | PERMISSION_ACTION_DELETE);
	}

	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_POINTLIST_ENABLED', 'TRUE' );
	}

	public function disable_for( $koala_object )
	{
		$koala_object->set_attribute( 'UNITS_POINTLIST_ENABLED', 'FALSE' );
	}

	public function is_enabled_for( $koala_object )
	{
		//if ( ! units_base::is_enabled_for( $koala_object ) ) return FALSE;
		return $koala_object->get_attribute( 'UNITS_POINTLIST_ENABLED' ) === 'TRUE';
	}

	function get_context_menu( $context, $params = array() )
	{
		if ( !isset( $params[ "unit" ] ) )
			return array();
		$unit = $params[ "unit" ];
		if ( ! $unit instanceof koala_container_pointlist )
			return array();
		if ( !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		$owner = $params[ "owner" ];

		if ( isset( $params[ "container" ] ) )
			$container = koala_object::get_koala_object( $params[ "container" ] );
		else
			$container = $unit;
			if ( ( ! $container instanceof koala_container_pointlist ) )
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

    if(!isset($portal) || !is_object($portal)) {
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}

		if ( isset( $path[0] ) && is_numeric( $path[0] ) ) {
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_pointlist" )
				return;
			$koala_container = new koala_container_pointlist( $steam_unit, new units_pointlist( $owner->get_steam_object() ) );
			$unit = $koala_container;
		}
		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$html_handler = new koala_html_course( $owner );
		$html_handler->set_context( "units", array( "subcontext" => "unit", "owner" => $owner, "unit" => $unit ) );

    $user = lms_steam::get_current_user();
		$course = $owner;

    if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );
    if ( !is_object( $owner ) || !( $owner instanceof koala_group_course ) )
      throw new Exception( "No owner (course) provided.", E_PARAMETER );

    switch( TRUE)
    {
      case ( isset( $path[1] ) && $path[1] == "sheets_edit" ):
        include( PATH_EXTENSIONS . "units_pointlist/modules/units_pointlist_sheets_edit.php" );
        break;
      case ( isset( $path[1] ) && $path[1] == "export_excel" ):
        include( PATH_EXTENSIONS . "units_pointlist/modules/units_pointlist_excel.php" );
        break;
      default:
          include( PATH_EXTENSIONS . "units_pointlist/modules/units_pointlist.php" );
        break;
    }
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
      include($this->get_path() . "/modules/" . $this->get_name() . "_new.php" );
      return $unit_new_html;
    }

	function get_icon()
	{
		return PATH_URL . "cached/get_document.php?name=koala_unit_pointlist.png&type=objecticon&height=70";
	}

	function get_big_icon()
  {
		return PATH_URL . "cached/get_document.php?name=koala_unit_pointlist_big.png&type=objecticon&height=120";
  }

  function initialize_pointlist($values, $room, $basegroup)  {
    // empty wrapper
  }
  
  function display_memberdata($template, $id, $name, $firstname, $fullname, $count, $points, $max = FALSE, $bonus_1 = FALSE, $bonus_2 = FALSE) {
    if (!is_array($points)) $points = array();
    $sum = 0.0;
    $template->setCurrentBlock("BLOCK_MEMBER");
    $template->setVariable( "MEMBER_NAME", $name);
    $template->setVariable( "MEMBER_TITLE", $firstname . " " . $fullname);
    for ($i = 1; $i <= $count; $i++) {
    if ( !isset($points[$i]) ) $p = "";
      else $p = $points[$i];
      $sum += (float)str_replace(",", ".", $p);
      $template->setCurrentBlock("BLOCK_POINTS");
      $dp = trim($p) . ($max?" / " . $max[$i]:"");
      $template->setVariable("MEMBER_POINTS", $dp);
      $template->parse("BLOCK_POINTS");
    }
        // SUM
    $template->setCurrentBlock("BLOCK_POINTS");
    $template->setVariable("MEMBER_POINTS", $sum);
    $template->parse("BLOCK_POINTS");
    // BONUS
    $template->setCurrentBlock("BLOCK_POINTS");
    $bonustext= units_pointlist::calculate_bonus($sum, $bonus_1, $bonus_2);
    $template->setVariable("MEMBER_POINTS", $bonustext);
    $template->parse("BLOCK_POINTS");
    $template->parse("BLOCK_MEMBER");
  }
  
  function display_maxpoints_data($template, $count, $points, $bonus_1 = FALSE, $bonus_2 = FALSE) {
    if (!is_array($points)) $points = array();
    $sum_max = 0;
    $template->setCurrentBlock("BLOCK_MAXPOINTS_DATA");
    $template->setVariable("LABEL_MAXPOINTS", gettext("Max points"));
    for ($i = 1; $i <= $count; $i++) {
      if ( empty($points[$i]) ) $p = "";
      else $p = $points[$i];
      $sum_max += $p;
      $template->setCurrentBlock("BLOCK_MAXPOINT_DATA");
      $template->setVariable("VALUE_MAXPOINT", trim($p));
      $template->parse("BLOCK_MAXPOINT_DATA");
    }
      // SUM
    $template->setCurrentBlock("BLOCK_MAXPOINT_DATA");
    $template->setVariable("VALUE_MAXPOINT", ($sum_max?$sum_max:"&#160;"));
    $template->parse("BLOCK_MAXPOINT_DATA");
    // BONUS
    $bonus_head = ($bonus_1?$bonus_1:"") . (($bonus_1 && $bonus_2)?"/":"") . ($bonus_2?$bonus_2:"");
    $template->setCurrentBlock("BLOCK_MAXPOINT_DATA");
    $template->setVariable("VALUE_MAXPOINT", $bonus_head );
    $template->parse("BLOCK_MAXPOINT_DATA");
  }
  
  
  function display_memberdata_admin($template, $id, $name, $firstname, $fullname, $matriculation_number, $count, $points, $maxpoints, $write_mode = FALSE, $bonus_1 = FALSE, $bonus_2 = FALSE) {
    $sum = 0.0;
    if ($write_mode) $block = "BLOCK_POINTS_ADMIN";
    else             $block = "BLOCK_POINTS";
    if (!is_array($points)) $points = array();
    $template->setCurrentBlock("BLOCK_MEMBER");
    $template->setVariable( "MEMBER_NAME", $name . "<br /><small>(" . ($matriculation_number!=0?$matriculation_number:"n.a.") . ")</small>");
    $template->setVariable( "MEMBER_TITLE", $firstname . " " . $fullname);
    for ($i = 1; $i <= $count; $i++) {
      if ( !isset($points[$i]) ) $p = "";
      else $p = $points[$i];
      if(isset($points[$i])){
      	$sum += (float)str_replace(",", ".", $points[$i]);
      }
      $template->setCurrentBlock($block);
      $template->setVariable("MEMBER_POINTS", trim($p));
      if ($write_mode) {
        $template->setVariable("MEMBER_ID", $id);
        $template->setVariable("MEMBER_SHEETCOUNT", $i);
        $style = "text-align: right;";
        $check = check_point($points[$i], $maxpoints[$i]);
        if ($check !== POINT_OK) {
          $hint = "";
          $style .= " background-color: #D11E01; color: #ffffff;";
          switch ($check) {
            case POINTERROR_NOINT: $hint = gettext("Not a number");
                              break;
            case POINTERROR_LOWERZERO: $hint = gettext("Lower than zero");
                              break;
            case POINTERROR_GREATERMAX: $hint = gettext("Greater than max");
                              break;
            default: $hint = gettext("Invalid input");
                     break;
          };
          $template->setVariable("POINT_TITLE", $hint);
        }
        $template->setVariable("POINT_STYLE", "style='". $style . "'");
      }
      $template->parse($block);
    }
    // SUM
    $template->setCurrentBlock("BLOCK_POINTS");
    $template->setVariable("MEMBER_POINTS", $sum);
    $template->parse("BLOCK_POINTS");
    // BONUS
    $template->setCurrentBlock("BLOCK_POINTS");
    $bonustext= units_pointlist::calculate_bonus($sum, $bonus_1, $bonus_2);
    $template->setVariable("MEMBER_POINTS", $bonustext);
    $template->parse("BLOCK_POINTS");
    
    $template->parse("BLOCK_MEMBER");
  }

  function calculate_bonus($sum, $bonus_1, $bonus_2) {
    $bonustext = gettext("None");
    if ($bonus_1 && $sum >= $bonus_1) $bonustext = gettext("One step");
    if ($bonus_2 && $sum >= $bonus_2) $bonustext = gettext("Two steps");
    return $bonustext;
  }
  
  function extract_pointlist( &$data ) {
    $pointlist = array();
    $akey = "";
    foreach($data as $key => $value) {
      if ( preg_match( "#UNIT_POINTLIST_POINTS_([0-9]*)#", $key, $akey ) > 0 ) {
        $pointlist[$akey[1]] = $value;
      }
    }
    return $pointlist;
  }
  
  /**
   * Test if Matrikelnummer is valid 
   * matrikel modulo 11 is 0; if modulo is 1, the last digit (crc) is 0.
   * matrikels start with 3 or 6.
   * @author Studinfo Staff
   */
  function check_matriculation_number( $mnr ) {
    $first = substr($mnr, 0, 1);
    $prf   = substr($mnr, strlen($mnr)-1, 1);
    $mod   = $mnr % 11;
    return (($first==3 || $first==6) && ($mod==0 ? TRUE : ($mod==1 && $prf==0)));
  }
  
	static function get_version() {
		return self::$version;
	}
}
?>