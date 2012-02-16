<?php
//TODO: extensions path als variable definieren!!!!!!!



class units_elearning extends koala_unit
{
	
	private $course;
	static $version = "1.7.1";
	private static $PATH, $DISPLAY_NAME, $DISPLAY_DESCRIPTION;
	private $cache;
	
	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "units_elearning/";
	    self::$DISPLAY_NAME = gettext("elearning course material");
		self::$DISPLAY_DESCRIPTION = gettext("Here you can add elearning material");
		parent::__construct( PATH_EXTENSIONS . "units_elearning.xml", $steam_object );
		if (!defined("PATH_TEMPLATES_UNITS_ELEARNING")) define( "PATH_TEMPLATES_UNITS_ELEARNING", PATH_EXTENSIONS . "units_elearning/templates/" );
		$this->cache = get_cache_function( "unit_elearning", 3600 );
	}
	
	public function enable_for ( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_ELEARNING_ENABLED', 'TRUE' );
	}
	
	public function disable_for( $koala_object ) {
		$koala_object->set_attribute( 'UNITS_ELEARNING_ENABLED', 'FALSE' );
	}
	
	public function is_enabled_for( $koala_object ) {
	 	return $koala_object->get_attribute( 'UNITS_ELEARNING_ENABLED' ) === 'TRUE';
	}
	
	function get_path_name() {
		return $this->get_Name();
	}
	
	function get_wrapper_class($obj) {
		
	}
	
	
	function can_extend ( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}
	
	//add the main menu
	function get_menu( $params = array() ) {
		$course = $params[ "owner" ];
		$room = $course->get_workroom();
		$units = $room->get_inventory();
		if (isset($units[0])) {        //TODO: Little Hack: only 1 unit supported
			$steam_unit = $units[0];
			$mediathek = elearning_mediathek::get_instance();
			$mediathek->set_unit($steam_unit);
			$elearning_course = elearning_mediathek::get_elearning_course_for_unit($steam_unit);
			$exam = $elearning_course->get_exam_by_type("final_exam");
			if ($exam->is_global_enabled() && elearning_user::get_instance(lms_steam::get_current_user()->get_name(), $course->get_id())->has_exam_enabled()){
				return array(
						    "name" => "Prüfung",
						    "link" => $course->get_url() . "units_elearning/"
							);
			} else return array();
		} else {
			return array();
		}

	}
	
	//add the context menu
	function get_context_menu( $context, $params = array() ) {
		$course = $params[ "owner" ];
		$room = $course->get_workroom();
		$units = $room->get_inventory();
		if (isset($units[0])) {        //TODO: Little Hack: only 1 unit supported
			$steam_unit = $units[0];
			$mediathek = elearning_mediathek::get_instance();
			$mediathek->set_unit($steam_unit);
			$elearning_course = elearning_mediathek::get_elearning_course_for_unit($steam_unit);
			$exam = $elearning_course->get_exam_by_type("final_exam");
			
			if ( is_array($params) && isset( $params[ "owner" ] ) ) {
				$course = $params[ "owner" ];
				$current_user = lms_steam::get_current_user();
				$is_admin = $course->is_admin( $current_user );
				
				$path = $course->get_url();
				if ( !($course instanceof koala_group_course) )
					throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
					
				if (lms_steam::is_koala_admin($current_user) && $context == "start"){
					return array(array(
						"name" => "Prüfung verwalten",
						"link" => "units_elearning/reporting/"
						));
				}
				if ($exam->is_global_enabled()){
					if ($course->is_staff($current_user) && $context == "members"){
							return array(array(
								"name" => "Prüfungsstatistik",
								"link" => "../units_elearning/chart/"
								));
					}
				} else {
					return array();
				}
			}
		} else {
			return array();
		}
		return array();
	}
	
	//TODO: move to abstract
	function get_icon()
	{
		return PATH_URL . "cached/get_document.php?name=koala_unit_extern.png&type=objecticon&height=70";
	}
	
	//TODO: move to abstract	
	function set_course( $course = FALSE )
	{
      return $this->course = $course;
    }
    
    function get_course() {
    	return $this->course;
    }
    
    //TODO: nice, but this should be done in superclass
    function get_html_unit_new()
	{
	  $course = $this->course;
	  //TODO:  ... _new.php should also extend a superclass
      include($this->get_path() . "/modules/" . $this->get_name() . "_new.php" );
      return $unit_new_html;
	}
	
	function handle_path( $path, $owner = FALSE, $portal = FALSE ) {
		$r="";
		foreach ($path as $s){
			$r .= $s;
		}
		if (is_string($path)) {
			$path = url_parse_rewrite_path($path);
		}
		
		//lms_portal::get_instance()->initialize(GUEST_NOT_ALLOWED);
		
		$steam_unit = "";
		$action = "";
		if ( isset( $path[0] ) && is_numeric( $path[0] ) ) {
			$steam_unit = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[0] );
			if ( is_object( $steam_unit ) && $steam_unit->get_attribute( "UNIT_TYPE" ) !== "units_elearning" )
				return;
			//TODO: cool. neues object von sich in sich ? Häh, geil!
			$unit = new units_elearning($owner->get_steam_object());
			$docextern = new koala_object_elearning( $steam_unit, $unit );
			if ( isset( $path[1] ) ) $action = $path[1];
		}

		$portal_user = lms_portal::get_instance()->get_user();
		$user   = lms_steam::get_current_user();
		$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		$current_semester = $owner->get_semester();
		$course = $owner;
		$this->set_course($course);

		$backlink = $owner->get_url() . $this->get_path_name() . "/";

		$html_handler = new koala_html_course( $owner );

 		if (count($path) > 0 && $path[0] == "reporting") {
			$first_unit = $this->get_elearning_unit();
			$exam = new exam($first_unit, $this->cache->call(array($first_unit, "get_attribute"), "ELEARNING_UNIT_ID"), $owner);
			if ($course->is_admin($user)) {
				$exam->render_reporting_html();
			} else {
				header("location:../../");
				exit;
			}
		} else if (count($path) > 0 && $path[0] == "" )  {	
			$first_unit = $this->get_elearning_unit();
			$exam = new exam($first_unit, $this->cache->call(array($first_unit, "get_attribute"), "ELEARNING_UNIT_ID"), $owner);
			if ($exam->get_exam()->is_global_enabled() && elearning_user::get_instance($user->get_name(), $course->get_id())->has_exam_enabled()) {
				$exam->render_html();
			} else {
				header("location:../");
				exit;
			}
		} else if (count($path) > 1 && $path[0] == "report" ) {
			$first_unit = $this->get_elearning_unit();
			$exam = new exam($first_unit, $this->cache->call(array($first_unit, "get_attribute"), "ELEARNING_UNIT_ID"), $owner);
			$steam_user = $this->cache->call("steam_factory::get_user", $GLOBALS[ "STEAM" ]->get_id(), $path[1]);
			if ($exam->get_exam()->is_global_enabled() && $course->is_staff($user) && $steam_user instanceof steam_user) {
				$exam->render_report_html($steam_user);
			} else {
				header("location:../");
				exit;
			}
		} else if (count($path) > 1 && $path[0] == "chart" ) {
			$first_unit = $this->get_elearning_unit();
			$exam = new exam($first_unit, $this->cache->call(array($first_unit, "get_attribute"), "ELEARNING_UNIT_ID"), $owner);
			if ($exam->get_exam()->is_global_enabled() && $course->is_staff($user)) {
				$exam->render_chart_html();
			} else {
				header("location:../");
				exit;
			}
		} else if (count($path) == 1 || (count($path) == 2 && $path[1]=="")) {
			$action="index";
			include( PATH_EXTENSIONS . 'units_elearning/modules/units_elearning.php' );
		}  else if (count($path) > 2 && $path[1] == "elearning") {
			$action="chapter";
			$chapter = $path[2];
			if (count($path) > 4 && $path[3] == "media") {
				$action="media";
				$media=$path[4];
			}
			include( PATH_EXTENSIONS . 'units_elearning/modules/units_elearning.php' );
		} else if (count($path) > 2 && $path[1] == "scripts") {
			$action="scripts";
			$scripts="";
			for ($i=1; $i<count($path); $i++) {
				$scripts .= "/" . $path[$i];
			}
			include( PATH_EXTENSIONS . 'units_elearning/modules/units_elearning.php' );
		} else if (count($path) > 1 && $path[1] == "directaccess") {
			$mediathek = elearning_mediathek::get_instance();
			$elearning_course = elearning_mediathek::get_elearning_course_for_unit($steam_unit);
			$da = new directaccess($elearning_course->get_id(), $course);
			$da->callfunction($_POST["case"]);
		} else {
			$url = "";
			$first=TRUE;
			foreach ($path as $s) {
				if ($first) {
					$first =FALSE;
				} else {
					$url .= "/" . $s;
				}
			}
			$url = "/packages/elearning_stahl_verkauf" . "/chapters/" . $_SESSION["chapter"] . $url;
			$steam_doc = $this->cache->call("steam_factory::get_object_by_name", $GLOBALS[ "STEAM" ]->get_id(), $url);
			if ($steam_doc instanceof steam_document) {
				echo $this->cache->call(array($steam_doc,"download"));
				exit;
			} else {
				error_log("Could not find: " . $url);
			}
		}
		return true;
	}
	
	function get_elearning_unit() {
		$course_workroom = $this->cache->call(array($this->get_course(), "get_workroom"));
		$units = $this->cache->call(array($course_workroom, "get_inventory"));
		return $units[0];
	}
	
	static function get_version() {
		return self::$version;
	}
	
	function get_member_info($user, $k_course) {		
		return elearning_user::get_instance($user->get_name(), $k_course->get_id())->get_status_HTML();
	}
	
	function get_filter_html($portal, $parent_id, $search_id) {
		$js = <<< END
		function filterStatus(element, parent_id, search_id) {
			var value = element.value;
			switch (value) {
				case "0":
						show_all_elements(parent_id);
						break;
				case "1":
						hide_elements("noch nie", parent_id, search_id);
						break;
				case "2":
						hide_elements("noch keine", parent_id, search_id);
						break;
				case "3":
						hide_elements("Prüfung bestanden", parent_id, search_id);
						break;
				case "4":
						hide_elements("Prüfung nicht bestanden", parent_id, search_id);
						break;
				default:
						show_all_elements(parent_id);
						break;
			}
		}
		
		function show_all_elements(parent_id) {
			document.getElementById("user_filter").value = "";
			
			var allHTMLTags=document.getElementsByTagName("*");
			for (var i=0; i<allHTMLTags.length; i++) {
				if (allHTMLTags[i].className==parent_id) {
					var row = allHTMLTags[i];
					var cells=(row.getElementsByTagName("td"));
			        for (var j=0; j<cells["length"];j++){
			        	try {
			           		cells[j].style.display="table-cell";
			           	} catch (e) {
			                cells[j].style.display="block";    //Internet Explorer
			           	}
			        }
				}
			}
		}
		
		function hide_elements(search, parent_id, search_id) {
			show_all_elements(parent_id);
			var allHTMLTags=document.getElementsByTagName("*");
			for (var i=0; i<allHTMLTags.length; i++) {
				if (allHTMLTags[i].className==search_id) {
					var searchElem = allHTMLTags[i];
					if (searchElem.innerHTML.toLowerCase().search(search.toLowerCase())==-1) {
						var cells=(searchElem.parentNode.getElementsByTagName('td'));
			           	for (var j=0; j<cells['length'];j++){
			            	cells[j].style.display='none';
			        	}
					}
				}
			}
		}
END;
		$portal->add_javascript_code("unit_elearning", $js);
		$html = "oder:
				<select onchange=\"filterStatus(this,'".$parent_id."','".$search_id."');\">
					<option value=\"0\" selected>Status beliebig</option>
					<option value=\"1\">Status &quot;noch nie angemeldet&quot;</option>
					<option value=\"2\">Status &quot;noch keine Prüfung abgelegt&quot;</option>
					<option value=\"3\">Status &quot;Prüfung bestanden&quot;</option>
					<option value=\"4\">Status &quot;Prüfung nicht bestanden&quot;</option>
				</select>";
		return $html;
	}
}
?>