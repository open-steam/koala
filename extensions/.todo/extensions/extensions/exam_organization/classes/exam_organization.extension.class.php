<?php
/*
 * info
 * 
 * @author Marcel Jakoblew
 */
class exam_organization extends koala_extension{
	
	
	static $PATH = "exam_organization";
	
	static $version = "1.0.0";
	
	private static $DISPLAY_NAME, $DISPLAY_DESCRIPTION;
	
	function __construct( $steam_object = FALSE )
	{
		self::$PATH = PATH_EXTENSIONS . "exam_organization/";
		self::$DISPLAY_NAME = "Prüfungsorganisation";
		self::$DISPLAY_DESCRIPTION = gettext("exam organization");
		parent::__construct(PATH_EXTENSIONS . "exam_organization.xml", $steam_object);
		require("exam_organization_conf.php");
	}
	
	//abstract extendes methods
	
	/*
	public function enable(){
		//return true;
	}
	
	public function disable(){
		//return false;
	}
	*/
	
	public function enable_for( $koala_object ){
		//hier wird der extension beim aufruf des kurs erstellen skriptes der aufrufende kurs übergeben
		$koala_object->set_attribute( 'UNITS_ORGANIZATION_ENABLED', 'TRUE' );
	}
	
	public function disable_for( $koala_object ){
		//hier wird der extension beim aufruf des kurs erstellen skriptes der aufrufende kurs übergeben
		$koala_object->set_attribute( 'UNITS_ORGANIZATION_ENABLED', 'FALSE' );
	}
	
	
	public function is_enabled_for( $koala_object ){
		return $koala_object->get_attribute( 'UNITS_ORGANIZATION_ENABLED' ) === 'TRUE';
	}
	
	public function is_disabled_for( $koala_object ){
		return $koala_object->get_attribute( 'UNITS_ORGANIZATION_ENABLED' ) === 'FALSE';
	}
	
	public function get_path_name(){
		return $this->get_Name();
	}
	
	public function get_wrapper_class( $obj ){
		
	}
	
	//set a function to a path
	function handle_path( $path, $owner = FALSE, $portal = FALSE  ) {
		$course = $owner; //the course was given by the owner parameter
		$isAdmin = $course->is_admin( lms_steam::get_current_user() );
		
		//parse the url path into an array
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );
		
		
		if ( !is_object( $owner ) || !( $owner instanceof koala_group_course ) )
			throw new Exception( "No owner (course) provided.", E_PARAMETER );
		
		//choose and save last exam term	
		if (isset($path[0])){
			switch ($path[0]) {
				case "exam1":
			        $course->set_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED",1);
			        break;
			    case "exam2":
			        $course->set_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED",2);
			        break;
			    case "exam3":
			        $course->set_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED",3);
			        break;
			    default://do nothing
			}
		}
		
		$examTerm = $course->get_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED");
		if ($examTerm!=1 && $examTerm!=2 && $examTerm!=3){
			$course->set_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED",1);
			$examTerm = 1;
		}
		
		/*
		//test case
		if (isset($path[0]) && $path[0]=="test"){include( self::$PATH . "test.php" );die();}
		if (isset($path[0]) && $path[0]=="setup"){include( self::$PATH . "setup_exam.php" );die();}
		if (isset($path[0]) && $path[0]=="cleanup"){include( self::$PATH . "cleanup_database.php" );die();}
		*/
		
		//security check - do no advanced functions if not admin
		if (!$isAdmin) {include( self::$PATH . "exam_organization.php" );exit();}
		
		//check if password is set
		$examObject = exam_organization_exam_object_data::getInstance($course);
		if($examObject->isSetMasterPassword()){
			if (isset($_SESSION["password"]) && $examObject->checkMasterPassword($_SESSION["password"])){
				//correct pw entered
			} else {
				//enter password dialog
				include_once( self::$PATH . "initialize_portal.php");
		 		include( self::$PATH . "enter_password.php" );
			}
		} else {
			//no password set for this course
		}
		
		if ($path[0]=="newexam") {
			include_once( self::$PATH . "initialize_portal.php");
		 	include( self::$PATH . "new_term.php" );
		}
		
		if ($path[0]=="removeexam") {
			include_once( self::$PATH . "initialize_portal.php");
		 	include( self::$PATH . "remove_last_term.php" );
		}
		
		if ($path[0]=="geticon") {
			include_once( self::$PATH . "initialize_portal.php");
		 	include( self::$PATH . "get_icon.php" );exit(0);
		}
		
		if ($path[0]=="directaccess") {
			include_once( self::$PATH . "initialize_portal.php");
		 	include( self::$PATH . "ajax_update_data.php" );exit(0);
		}
		
		if ($path[0]=="password") {
			include_once( self::$PATH . "initialize_portal.php");
		 	include( self::$PATH . "setup_password.php" );
		}
		
		if (isset($path[1])){
			switch($path[1]){
				case "show_and_import_participants":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "show_and_import_participants.php" );die();
				case "show_participants_and_points":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "show_participants_and_points.php" );die();
				case "setplaces":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "set_places_for_participants.php");break;
				case "resetplaces":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "reset_places_for_participants.php");break;
				case "createplaceslist":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "create_places_list.php" );die();
				case "createparticipantslist":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "create_participants_list_by_name.php" );die();
				case "createparticipantslistbyname":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "create_participants_list_by_name.php" );die();
				case "createparticipantslistbyplace":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "create_participants_list_by_place.php" );die();
				case "createlabels":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "create_exam_labels.php" );die();
				case "importbonus":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "import_bonus_points.php" );break;
				case "examkey":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_examkey.php" );die();
				case "assignments":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_assignments.php" );die();
				case "time":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_date_time.php" );die();
				case "date":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_date_time.php" );die();
				case "room":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_room.php" );die();
				case "enterpoints":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "input_exam_results.php" );die();
				case "excelexamoffice":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "export_results_examoffice_excel.php" );die();
				case "excelexamoffice_hislsf":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "export_results_examoffice_excel_hislsf.php" );die();
				case "excelstatistics":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "export_results_and_statistics_excel.php" );die();
				case "pdfstatistics":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "export_results_and_statistics_pdf.php" );die();
				case "freetext":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "setup_exam_freetext.php" );break;
				case "deletedata":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "delete_term_data.php" );break;
				case "deleteterm":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "delete_term.php" );break;
				case "mail":
					include_once( self::$PATH . "initialize_portal.php");
					include( self::$PATH . "mail.php" );break;
				
				default: //do nothing
			}
		}
		include( self::$PATH . "exam_organization.php" );
		return TRUE;
	 }
	
	 
	// test einrag create course dialog
	function can_extend( $koala_class_name )
	{
		if ( $koala_class_name == 'koala_group_course' || is_subclass_of( $koala_class_name, 'koala_group_course' ) )
			return TRUE;
		return FALSE;
	}
	
	
	
	
	
	// --- koala user interface integration ---
	
	/*
	function get_headline( $headline = array(), $context = "", $params = array() )
	{
		//if ( !isset( $params["unit"] ) ) return FALSE;
		//$unit = koala_object::get_koala_object( $params["unit"] );
		//$headline[] = $unit->get_link();
		//return $headline;
		echo "get Headline aufruf<br />";
		//exit(0);
		//return Array ("Bla" => "blo");
		//return "Nerd";
		
	}
	*/
	
	//add the main menu
	function get_menu( $params = array() ) {
		if ( is_array($params) && isset( $params[ "owner" ] ) ) {
			$course = $params[ "owner" ];
			if ( !($course instanceof koala_group_course) )
				throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
			return array(
				    "name" => gettext("Exam organization"),
				    "link" => $course->get_url() . "exam_organization/"
					);
		}
		return array();
	}

	//add the context menu
	function get_context_menu( $context, $params = array() ) {
		if ($context!="exam_organization") return array();
		
		if ( is_array($params) && isset( $params[ "owner" ] ) ) {
			$course = $params[ "owner" ];
			$current_user = lms_steam::get_current_user();
			$is_admin = $course->is_admin( $current_user );
			
			$path = $course->get_url();
			if ( !($course instanceof koala_group_course) )
				throw new Exception( "The 'owner' param is not a koala_group_course.", E_PARAMETER );
			if ($is_admin){ //role admin
				return array(
					array( "name" => gettext("Add exam term"), "link" => $path . "exam_organization/newexam" ),
					array( "name" => gettext("Remove last exam term"), "link" => $path . "exam_organization/removeexam" ),
					array( "name" => gettext("Set/change password"), "link" => $path . "exam_organization/password" )
					);
			} else { //role student
				return array();
			}
			
		}
		return array();
	 }
	 
	function get_display_name(){
		return self::$DISPLAY_NAME;
	}
	
	static function get_version() {
		return self::$version;
	}
}
?>