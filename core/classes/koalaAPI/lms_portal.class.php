<?php

define( "GUEST_ALLOWED", 	TRUE );
define( "GUEST_NOT_ALLOWED", 	FALSE);
define( "OFFLINE", 	FALSE);
define( "ONLINE", 	TRUE);
define( "PROTOTYPE_ENABLED", TRUE);
define( "PROTOTYPE_DISABLED", FALSE);

class lms_portal
{
	private $template;
	private $lms_user;
	private $environment;
  	private $guest_allowed = -1;
  	private $prototype_enabled = true;
  	private $init_done = false;
  	private $offline_status;
  	
  	private static $instance;

	private function __construct()
	{
    // Convenience code to eliminate multiple portal instances
    //$bt = debug_backtrace();
    //error_log("Constructed portal in" . $bt[0]["file"]);
		$this->environment = FALSE;
		$this->template = new HTML_TEMPLATE_IT();

        if ( ! file_exists( PORTAL_TEMPLATE ) )
        {
            throw new Exception( "Template does not exist: " . PORTAL_TEMPLATE . ".", E_CONFIGURATION );
        }

        $this->template->loadTemplateFile( PORTAL_TEMPLATE );
		
		ob_start();
		if (headers_sent()) {
			//throw new Exception( "HTTP headers already sent.", E_INVOCATION );
		} else {
			header( "Content-Type: text/html; charset=" . CHARSET );
		}
	}
	
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new lms_portal();
		}
		return self::$instance;
	}
	
	public static function is_instance() {
		if (self::$instance == null) {
			return false;
		} else {
			return true;
		}
	}
	
	public function set_prototype_enabled($prototype_enabled) {
		$this->prototype_enabled = $prototype_enabled;
	}
	
	public function get_prototype_enabled() {
		return $this->prototype_enabled;
	}

  public function is_guest_allowed() {
    return $this->guest_allowed;
  }

  public function set_guest_allowed( $allowed ) {
    if ($allowed !== $this->guest_allowed) $this->init_login( $allowed );
  }

  public function init_login($guest_allowed = FALSE, $offline = FALSE) {
		if ( isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in() )
		{
			$this->lms_user = $_SESSION[ "LMS_USER" ];
		}
		else
		{
			if ( ! $guest_allowed && !$offline )
			{
				throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
			}
			$this->lms_user = new lms_user( STEAM_GUEST_LOGIN, STEAM_GUEST_PW );
		}
    $this->guest_allowed = $guest_allowed;
  }

	public function initialize( $guest_allowed = FALSE, $offline = FALSE)
	{
		if ($this->init_done) {
			return;
		}
		
        $this->init_login($guest_allowed, $offline);

		// LOG OUT ON POST-EVENT
		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "sign_out" )
        {
			$this->set_confirmation( gettext( "You are logged out." ) );
			$this->logout();
		}

		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "search" )
		{

		}

        if (!$offline) lms_steam::connect( STEAM_SERVER, STEAM_PORT, $this->lms_user->get_login(), $this->lms_user->get_password() );
        
        
		// DISCLAIMER HANDLING
        if (DISCLAIMER && isset($GLOBALS['STEAM']) && $this->lms_user->get_login() != "guest") {
        	$steam_user = $GLOBALS['STEAM']->get_current_steam_user();
        	if ($steam_user instanceof steam_user) {
				$user_disclaimer = $steam_user->get_attribute("USER_ACCEPTED_DISCLAIMER");
				if ($user_disclaimer === 0 || !$user_disclaimer === "TRUE") {
					if (strpos($_SERVER[ 'REQUEST_URI' ], "disclaimer_local.php") == null ) {
						throw new Exception( "Disclaimer must be accepted.", E_USER_DISCLAIMER );
					}
				}
        	}
		}
		
		// CHANGE PASSWORD
		if (CHANGE_PASSWORD && isset($GLOBALS['STEAM']) && isset($GLOBALS['STEAM'])) {
			$steam_user = $GLOBALS['STEAM']->get_current_steam_user();
        	if ($steam_user instanceof steam_user) {
				$user_generated_password = $steam_user->get_attribute("USER_GENERATED_PASSWORD");
				if ($user_generated_password != "") {
					if (strpos($_SERVER[ 'REQUEST_URI' ], "usermanagement") == null && strpos($_SERVER[ 'REQUEST_URI' ], "disclaimer_local.php") == null) {
						throw new Exception( "Change Password.", E_USER_CHANGE_PASSWORD);
					}
				}
        	}
		}

		// CHOOSE RIGHT LANGUAGE AND SET LOCALES FOR GETTEXT
		language_support::choose_language();
		
		// SET LOGO URL
		$this->template->setVariable( "LOGO_PATH_URL", PATH_URL );

		// SET STYLEPATH AND ADDITIONAL HEADERS
		$this->template->setVariable( "STYLE_PATH", PATH_STYLE );
		$this->template->setVariable( "STANDARD_STYLE_PATH", PATH_URL );

		// LOAD JAVA-SCRIPTS
		// $this->add_javascript( PATH_JAVASCRIPT . "bbcode.js?version=".KOALA_VERSION );
		// $this->add_javascript( PATH_JAVASCRIPT . "javascript_minimized.js?version=".KOALA_VERSION );
		
		$this->template->setVariable( "PATH_JAVASCRIPT", PATH_JAVASCRIPT );
		$this->template->setVariable( "KOALA_VERSION", KOALA_VERSION);
		
		// GENERATE HTML FOR STATUS-DIV
        $this->set_status( $offline );

		// SET CONFIRMATION
		$this->set_confirmation();

        // SET ERROR
        $this->set_problem_description("");

        // Set default page title
	    $this->set_page_title("");
	    
	    $this->template->setVariable("DEVELOPER_MODE", DEVELOPMENT_MODE);
	    $this->template->setVariable("PATH_URL", PATH_URL);

	    // SET USER ID FOR JAVASCRIPTS
	    $this->template->setVariable("USER_LOGIN", $this->lms_user->get_login());


      // LANGUAGE
      if ( isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ]->is_logged_in() && isset($GLOBALS["STEAM"]) && is_object($GLOBALS["STEAM"]->get_current_steam_user())) {
        $ulang = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_LANGUAGE");
        if (!is_string($ulang) || $ulang === "0") $ulang = LANGUAGE_DEFAULT_STEAM;
        $languages = array(
          "english" => array("name" => gettext("English"), "icon" => "flag_gb.gif", "lang_key" => "en_US"),
          "german"  => array("name" => gettext("German"), "icon" => "flag_de.gif", "lang_key" => "de_DE")
        );
        if (!array_key_exists($ulang, $languages)) {
          $ulang = LANGUAGE_DEFAULT_STEAM;
        }
        /*
        $this->template->setCurrentBlock("PORTAL_LANGUAGES");
        $this->template->setVariable("PORTAL_LANGUAGES_REDIRECT", $_SERVER["REQUEST_URI"] );
        $this->template->setVariable("PORTAL_LANGUAGES_ACTION", PATH_URL . "?action=switch_language");
        foreach( $languages as $key => $language) {
          $this->template->setCurrentBlock("LANGUAGE");
          $this->template->setVariable("LABEL_LANGUAGE_LABEL", "language_" . $key);
          $this->template->setVariable("LANGUAGE_ICON", PATH_URL . "styles/" . STYLE . "/images/" . $language["icon"]);
          $this->template->setVariable("LABEL_LANGUAGE", $language["name"]);
          $this->template->setVariable("LANGUAGE_VALUE", $key);
          if ( $ulang == $key ) {
            $this->template->setVariable("LANGUAGE_CHECKED", "checked=\"checked\"");
          }
          $this->template->parse("LANGUAGE");
        }
        $this->template->parse("PORTAL_LANGUAGES");
		*/
      	}
	
	$this->template->setVariable( "COPYRIGHT_INFO", "&copy; " . strftime( "%Y" ) . " " . secure_gettext( COPYRIGHT_NAME ) );
	$this->template->setVariable( "IMPRESSUM_INFO", " | " . " <a href='". PATH_URL . "imprint/'>" . gettext( "Imprint" ) . "</a>" );
	(DISCLAIMER) ? $this->template->setVariable( "SECURITY_INFO", " | " . " <a href='". PATH_URL . "disclaimer_static.php'>" . "Nutzerordnung" . "</a>" ) : "";
//    	$this->template->setVariable( "SECURITY_INFO", " | " . " <a target='_blank' href='".PATH_URL."html/datenschutzerklaerung.html' >Datenschutzerkl&auml;rung</a>" );
//    	$this->template->setVariable( "POLICY_INFO", " | " . " <a target='_blank' href='".PATH_URL."html/policy.html' >Policy</a>" );

//	$this->template->setVariable( "FUNDING_INFO", " | " . str_replace( "%NAME", " <a href='http://www.bmbf.de' target='_blank'>BMBF</a>", gettext( "funded by %NAME" ) ) );
//	$this->template->setVariable( "POWERED_BY_INFO", " | " . str_replace( "%NAME", " <a href='http://www.open-steam.org' target='_blank'>open-sTeam</a>", gettext( "powered by %NAME" ) ) );
		logging::write_log( LOG_MESSAGES, "RELOAD\t" . $this->lms_user->get_login() );
		
		$this->init_done = true;
	}

	private function add_body_params( $params = "" )
	{
		$this->template->setVariable( "BODY_PARAMS", $params );
	}

	public function add_css_style($css = "") {
		if ($css != "") {
			$this->template->setCurrentBlock("HEAD_CUSTOM_CSS");
			$this->template->setVariable("HEAD_CUSTOM_CSS_STYLE", $css);
			$this->template->parse("HEAD_CUSTOM_CSS");
		}
	}
	
	public function add_css_style_link($href = "") {
		if ($href != "") {
			$this->template->setCurrentBlock("HEAD_CUSTOM_CSS_LINK");
			$this->template->setVariable("HEAD_CUSTOM_CSS_STYLE_LINK", $href);
			$this->template->parse( "HEAD_CUSTOM_CSS_LINK" );
		}
	}
	
	private function add_javascript($caller_name, $src ="", $sourcecode ="" )
	{
		$this->template->setCurrentBlock( "HEAD_JAVASCRIPT_BLOCK" );
		$this->template->setVariable( "HEAD_JAVASCRIPT_CALLER", $caller_name );
		if ( !empty($src) )
			$this->template->setVariable( "HEAD_JAVASCRIPT_SRC", "src=\"$src\"" );
		$this->template->setVariable( "HEAD_JAVASCRIPT_SOURCECODE", $sourcecode );
		$this->template->parse( "HEAD_JAVASCRIPT_BLOCK" );
	}
	
	public function add_javascript_src($caller_name, $src = "") {
		$this->add_javascript($caller_name, $src);
	}

	public function add_javascript_code($caller_name, $code = "") {
		$this->add_javascript($caller_name, "", $code);
	}
	
	public function add_javascript_onload($caller_name, $code) {
		$js_function_name = $this->get_rand_letters(15);
		
		$js = <<< END
function $js_function_name() {
		$code
	}	
END;
		$this->template->setCurrentBlock( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION_CALL" );
		$this->template->setVariable( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION_CALLER", $caller_name);
		$this->template->setVariable( "HEAD_JAVASCRIPT_ONLOAD_FUNCION_NAME", $js_function_name . "();" );
		$this->template->parse( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION_CALL" );
		
		$this->template->setCurrentBlock( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION" );
		$this->template->setVariable( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION_CALLER", $caller_name);
		$this->template->setVariable( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION_CODE", $js );
		$this->template->parse( "HEAD_JAVASCRIPT_ONLOAD_FUNCTION" );
		
		$this->add_body_params("onLoad=\"onload_body();\"");
	}
	
	public function add_javascript_actionhandler_for_class() {
		
	}
	
	public function add_javascript_actionhandler_for_id() {
		
	}
	
	private function getUniqueCode($length = ""){	
		$code = md5(uniqid(rand(), true));
		if ($length != "") return substr($code, 0, $length);
		else return $code;
	}
	
	function get_rand_letters($length)
	{
	  if($length>0) 
	  { 
	  $rand_id="";
	   for($i=1; $i<=$length; $i++)
	   {
	   mt_srand((double)microtime() * 1000000);
	   $num = mt_rand(1,26);
	   $rand_id .= $this->assign_rand_value($num);
	   }
	  }
	return $rand_id;
	} 
	
function assign_rand_value($num)
{
// accepts 1 - 36
  switch($num)
  {
    case "1":
     $rand_value = "a";
    break;
    case "2":
     $rand_value = "b";
    break;
    case "3":
     $rand_value = "c";
    break;
    case "4":
     $rand_value = "d";
    break;
    case "5":
     $rand_value = "e";
    break;
    case "6":
     $rand_value = "f";
    break;
    case "7":
     $rand_value = "g";
    break;
    case "8":
     $rand_value = "h";
    break;
    case "9":
     $rand_value = "i";
    break;
    case "10":
     $rand_value = "j";
    break;
    case "11":
     $rand_value = "k";
    break;
    case "12":
     $rand_value = "l";
    break;
    case "13":
     $rand_value = "m";
    break;
    case "14":
     $rand_value = "n";
    break;
    case "15":
     $rand_value = "o";
    break;
    case "16":
     $rand_value = "p";
    break;
    case "17":
     $rand_value = "q";
    break;
    case "18":
     $rand_value = "r";
    break;
    case "19":
     $rand_value = "s";
    break;
    case "20":
     $rand_value = "t";
    break;
    case "21":
     $rand_value = "u";
    break;
    case "22":
     $rand_value = "v";
    break;
    case "23":
     $rand_value = "w";
    break;
    case "24":
     $rand_value = "x";
    break;
    case "25":
     $rand_value = "y";
    break;
    case "26":
     $rand_value = "z";
    break;
    case "27":
     $rand_value = "0";
    break;
    case "28":
     $rand_value = "1";
    break;
    case "29":
     $rand_value = "2";
    break;
    case "30":
     $rand_value = "3";
    break;
    case "31":
     $rand_value = "4";
    break;
    case "32":
     $rand_value = "5";
    break;
    case "33":
     $rand_value = "6";
    break;
    case "34":
     $rand_value = "7";
    break;
    case "35":
     $rand_value = "8";
    break;
    case "36":
     $rand_value = "9";
    break;
  }
return $rand_value;
}
	
	public function login( $login, $password, $request = "" )
	{
		if ( ! isset( $this->lms_user ) )
		{
			// PORTAL NOT INITIALIZED YET
			$this->initialize();
		}
		if ( ! $this->lms_user->login( $login, $password ) )
		{
			return FALSE;
		}
		$_SESSION[ "LMS_USER" ] = $this->lms_user;
        language_support::choose_language( lms_steam::get_user_language() );
		if ( empty ( $request ) )
		{
			header( "Location: " . PATH_URL . "home/"  );
		}
		else
		{
			header( "Location: " . PATH_SERVER . $request );
		}
	}

	public function logout()
	{
		if ( ! isset( $this->lms_user ) )
		{
			// PORTAL NOT INITIALIZED YET
			$this->initialize(GUEST_NOT_ALLOWED);
		}
		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache->clean( $this->lms_user->get_login() );
		$this->lms_user->logout();
		$_SESSION = array();
		session_destroy();
		ob_end_clean();
		header( "Location: " . PATH_URL  );
	}

	public function get_user()
	{
		return $this->lms_user;
	}
	
	public function get_icon_bar_html($is_logged_in) {
		if ( $is_logged_in ) {
                        if (SESSION_RESTORE_PORTAL_DATA) {
                            if (isset($_SESSION["icon_bar"])) {
                                $koala_html_menu = new koala_html_menu($_SESSION["icon_bar"]);
                                return $koala_html_menu->get_html(); 
                            } else {
                                return "";
                            }
                        }
			$user = lms_steam::get_current_user();
			$koala_html_menu = new koala_html_menu();
			if (USERMANAGEMENT) {
				$dataAccess = new sTeamServerDataAccess();
			 	if (lms_steam::is_koala_admin($user) || $dataAccess->isCustomerAdmin($user->get_id())) {
					$koala_html_menu->add_menu_entry( array( "name" => "<img title=\"Benutzerverwaltung\" src=\"/styles/standard/images/usermanagement.gif\" />", "link" => PATH_URL . "usermanagement/admin" ) );
			 	}
			 }
			// SERVER
			if(SERVERMENU && lms_steam::is_koala_admin($user) ) {
				$configImageUrl = PATH_URL . "styles/standard/images/config_16.gif";
				$koala_html_menu->add_menu_entry( array( "name" => "<img title=\"Server\" src=\"{$configImageUrl}\" />", "menu" => array(
					array( "name" => gettext( "Server information" ), "link" => PATH_URL . "admin_server.php" ),
					array( "name" => gettext( "koaLA extensions information" ), "link" => PATH_URL . "extensions_index.php" ),
					array( "name" => gettext( "Semester administration" ), "link" => PATH_URL . "admin_semester_statistics.php" ),
					(IMPORT_COURSE_FROM_PAUL) ? array( "name" => gettext( "PAUL Synchronisation" ), "link" => PATH_URL . "admin_paul.php" ) : "",
					(USERMANAGEMENT) ? array( "name" => gettext( "Usermanagement" ), "link" => PATH_URL . "usermanagement/index.php" ) : "",
					(SERVERMONITOR) ?array( "name" => gettext( "Server Monitor" ), "link" => PATH_URL . "serverstats.php" ) : "",
					(KOALAADMINTOOLS) ?array("name" => gettext("koaLA Admintools"), "link"=>PATH_URL."admin/"):""
				) ) );
			}
			$extensions = ExtensionMaster::getInstance()->getExtensionByType("IIconBarExtension");
			foreach ($extensions as $extension) {
				$entries = $extension->getIconBarEntries();
					if (isset($entries) && is_array($entries)) {
					foreach ($entries as $entry) {
						$koala_html_menu->add_menu_entry($entry);
					}
				}
			}
			return $koala_html_menu->get_html();
		}
		return "";
	}

	public static function get_menu_html( $cacheid, $is_logged_in )
	{
		if ( $is_logged_in )
		{
                        if (SESSION_RESTORE_PORTAL_DATA) {
                            if (isset($_SESSION["menu"])) {
                                $koala_html_menu = new koala_html_menu($_SESSION["menu"]);
                                return $koala_html_menu->get_html(); 
                            } else {
                                return "<div id='menu'></div>";
                            }
                        }
                        $koala_html_menu = new koala_html_menu();
			$user = lms_steam::get_current_user();
			// HOME
			// removed for version 1_5  
			//$koala_html_menu->add_menu_entry( array( "name" => gettext( "Home" ), "link" => PATH_URL ) );
			
			// YOU

			if(YOU_MENU){
				    $koala_html_menu->add_menu_entry( array( "name" => ((MENU_YOU) ? gettext( "You" ): $user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME")), "link" => PATH_URL . "desktop/", "menu" => array(
					//YOU SUBMENU
					(YOUR_DESKTOP) ? array( "name" => ((MENU_YOU) ? gettext( "Your desktop" ) : "Schreibtisch"), "link" => PATH_URL . "desktop/" ) : "",
					(YOUR_PORTFOLIO) ? array( "name" => ((MENU_YOU) ? "Mein Portfolio" : "Portfolio"), "link" => PATH_URL . "portfolio/") : "",
					//(YOUR_NEWS) ? array( "name" => gettext( "Your news" ), "link" => PATH_URL . "desktop/news/" ) : "",
					//(YOUR_MAILBOX) ? array( "name" => gettext( "Your mailbox" ), "link" => PATH_URL . "messages.php" ) : "",
					//(YOUR_CALENDER) ? array( "name" => gettext( "Your calendar" ), "link" => PATH_URL . "desktop/calendar/" ) : "",
					(YOUR_DOCUMENTS) ? array( "name" => ((MENU_YOU) ? gettext( "Your documents" ) : "Dokumente"), "link" => PATH_URL . "explorer/" ) : "",
					(YOUR_BOOKMARKS) ? array( "name" => ((MENU_YOU) ? gettext( "Meine Lesezeichen" ) : "Lesezeichen"), "link" => PATH_URL . "bookmarks/") : "",
                                        
                                        //not used
                                        //(YOUR_SCHOOLBOOKMARKS) ? array( "name" => ((MENU_YOU) ? gettext( "Meine Schul-Lesezeichen" ) : "Schul-Lesezeichen"), "link" => PATH_URL . "school/") : "",
					
                                        (YOUR_CONTACTS) ? array( "name" => ((MENU_YOU) ? gettext( "Your contacts" ) : "Kontakte"), /*"link" => PATH_URL . "contacts/" . $user->get_name() . "/" */) : "",
					(YOUR_MOKODESK && ($user->get_attribute("LARS_DESKTOP") !== 0)) ? array( "name" => ((MENU_YOU) ? gettext( "Mein MokoDesk" ) : "MokoDesk"), "link" => MOKODESK_URL) : "",
					
                                        (YOUR_DOCUMENTS) ? array( "name" => ((MENU_YOU) ? gettext( "Einstellungen2" ) : "Favoriten"), "link" => PATH_URL . "favorite/index/" ) : "",
					
                                        (YOUR_PROFILE) ? array( "name" => ((MENU_YOU) ? gettext( "Your profile" ) : "Profil"), "link" => PATH_URL . "user/index/" . $user->get_name() . "/") : "",
					
                                        //not implemented yet
                                        (YOUR_PROFILE) ? array( "name" => ((MENU_YOU) ? gettext( "Configuration" ) : "Einstellungen"), "link" => PATH_URL . "user/config/" ) : ""
					
					/*array( "name" => gettext( "Comments You've Made" ), "link" => PATH_URL . "desktop/comments/" ),
					koala_html_menu::get_separator(),
					array( "name" => gettext( "Edit your profile" ), "link" => PATH_URL . "profile_edit.php" ),
					array( "name" => gettext( "Set your buddy icon" ), "link" => PATH_URL . "profile_icon.php" ),
					array( "name" => gettext( "Your mail preferences" ), "link" => PATH_URL . "messages_prefs.php" )
					*/
				) ) );
			}
			
			// COURSES
			if (YOUR_COURSES) {
				$scg = null;
				if (defined("STEAM_COURSES_GROUP")) {
					$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
				}
				if ($scg instanceof steam_group) {
					$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER );
		      		if (!is_object($current_semester))
		         		throw new Exception( "cant find current_semester. please check setting of CURRENT_SEMESTER in koala.def.php", E_CONFIGURATION );
		      		$cache = get_cache_function( $user->get_name() );
		      		$courses = $cache->call( "lms_steam::user_get_booked_courses", $user->get_id() );
					//COURSES SUBMENU
					$submenu = array(
						(YOUR_COURSES) ? array( "name" => gettext( "Your courses" ), "link" => PATH_URL . SEMESTER_URL ."/?filter=booked" ) : "",
						(ALL_COURSES && (!ADMIN_ONLY_ALL_COURSES || (ADMIN_ONLY_ALL_COURSES && lms_steam::is_koala_admin($user)))) ? array( "name" => gettext( "Browse courses" ), "link" => PATH_URL . SEMESTER_URL ."/" ) : "",
					);
					if ( count( $courses) > 0 ) $submenu[] = koala_html_menu::get_separator();
					foreach( $courses as $course )
						$submenu[] = array( "name" => $course[ "COURSE_NAME" ], "link" => $course[ "COURSE_LINK" ] );	
					if(COURSES_MENU) {
						if (ADD_COURSE) {
							$koala_html_menu->add_menu_entry( array( "name" => gettext( "Courses" ), "link" => PATH_URL . SEMESTER_URL . "/?filter=booked", "menu" => $submenu ) );
						} else {
							$koala_html_menu->add_menu_entry( array( "name" => gettext( "Courses" ), "menu" => $submenu ) );
						}
					}
				}
			}
	
			// CONTACTS
			if(CONTACTS_MENU){
				$koala_html_menu->add_menu_entry( array( "name" => gettext( "Contacts" ), "link" => PATH_URL . "user/" . $user->get_name() . "/contacts/", "menu" => array(
					// SUBMENUS CONTACTS
					(YOUR_CONTACTS) ? array( "name" => gettext( "Contact list" ), "link" => PATH_URL . "user/" . $user->get_name() . "/contacts/" ) : "",
					(PROFILE_VISITORS) ? array( "name" => gettext( "Visitors of your profile" ), "link" => PATH_URL . "profile_visitors.php" ) : "",
					(USER_SEARCH) ? koala_html_menu::get_separator() : "",
					(USER_SEARCH) ? array( "name" => gettext( "Find people" ), "link" => PATH_URL . "search/people/" ) : ""
				) ) );
			}
			
			// GROUPS
			if (YOUR_GROUPS) {
				$submenu = array(
					// SUBMENUS GROUPS
					(YOUR_GROUPS) ? array( "name" => gettext( "Your groups" ), "link" => PATH_URL . "user/" . $user->get_name() . "/groups/" ) : "",
					(BROWSE_GROUPS) ? array( "name" => gettext( "Browse groups" ), "link" => PATH_URL . "groups/" ) : "",
					//(CREATE_GROUP) ? koala_html_menu::get_separator() : ""
					(CREATE_GROUPS) ? array( "name" => gettext( "Create group" ), "link" => PATH_URL . "groups_create.php" ) : ""				
				);
				$cache = get_cache_function( $user->get_name(), 86400 );
			    $groups = $cache->call( "lms_steam::user_get_groups", $user->get_name(), FALSE );
			    usort( $groups, "sort_objects" );     
				if ( count( $groups) > 0 ) $submenu[] = koala_html_menu::get_separator();
				foreach( $groups as $usergroup )
					$submenu[] = array( "name" => $usergroup[ "OBJ_NAME" ], "link" => $usergroup[ "GROUP_LINK" ] );
				if(GROUPS_MENU)
					$koala_html_menu->add_menu_entry( array( "name" => gettext( "Groups" ), "link" => PATH_URL . "user/" . $user->get_name() . "/groups/", "menu" => $submenu ) );
			}
			// additional platform menus
			$menus = json_decode(PLATFROM_MENUS, true);
			if (!is_array($menus)) {
				$menus = array();
			}
			foreach ($menus as $menu) {
				$koala_html_menu->add_menu_entry($menu);
			}
			
			$extensions = ExtensionMaster::getInstance()->getExtensionByType("IMenuExtension");
			foreach ($extensions as $extension) {
				$entries = $extension->getMenuEntries();
				if (isset($entries) && is_array($entries)) {
					foreach ($entries as $entry) {
						$koala_html_menu->add_menu_entry($entry);
					}
				}
			}
			
			// EXTRAS removed for Version 1_5
			/*
			$koala_html_menu->add_menu_entry( array( "name" => gettext( "Extras" ), "link" => PATH_URL . "downloads/", "menu" => array(
				// SUBMENUS EXTRAS
				array( "name" => gettext( "Downloads" ), "link" => PATH_URL . "downloads/" ),
				array( "name" => gettext( "More information"), "link" => PATH_URL ),
				koala_html_menu::get_separator(),
				array( "name" => gettext( "Help"), "link" => HELP_URL )
			) ) );
			*/
		}
		else {
			//removed for version 1_5
			//$koala_html_menu->add_menu_entry( array( "name" => gettext( "Home" ), "link" => PATH_URL ) );
			//$koala_html_menu->add_menu_entry( array( "name" => gettext( "Sign in" ), "link" => PATH_URL . "sign_in.php" ) );
			//$koala_html_menu->add_menu_entry( array( "name" => gettext( "Downloads" ), "link" => PATH_URL . "downloads/" ) );
			//$koala_html_menu->add_menu_entry(array("name" => " ", "link" => "#"));
			return "<div id='menu'></div>";
		}
		return $koala_html_menu->get_html();
	}

	public function set_environment ( $environment )
	{
		$this->environment = $environment;
	}

	private function set_status($offline = FALSE)
	{
		$this->offline_status = $offline;
		if ( !$offline && ! isset( $this->lms_user ) )
		{
			// PORTAL NOT INITIALIZED YET
			$this->initialized();
		}
		
        if ( !$offline && $this->lms_user->is_logged_in() )
		{
			$this->template->setVariable( "CURRENT_DATE", strftime( "%d.%m.%Y" ) ); // set current date
			$cache = get_cache_function( $this->lms_user->get_login(), 600 );
            $this->template->setCurrentBlock( "STATUS_SIGNED_IN_BLOCK" );
            $this->template->setVariable( "SIGNED_IN_TEXT", gettext( "Signed in as" ) );
			$this->template->setVariable( "SIGNED_IN_LOGIN_HOME", PATH_URL . "user/index/" . $this->lms_user->get_login() . "/" );
			$this->template->setVariable( "SIGNED_IN_LOGIN_NAME", $this->lms_user->get_login() );
			
			
			//switch for de-activating unread mails on the start page is MAILBOX_SHOW_UNREAD_ON_STARTPAGE
			$messages_unread_string = "";
			
			//$isForwarded = $this->lms_user->get_attribute( "USER_FORWARD_MSG");
			
			if (YOUR_MAILBOX && ( MAILBOX_SHOW_UNREAD_ON_STARTPAGE)){
			$this->template->setVariable( "MESSAGES_URL", PATH_URL . "messages.php" );
			$this->template->setVariable( "INBOX", gettext( "mailbox" )  );
			//$no_messages_unread = $cache->call( "lms_steam::user_count_unread_mails", $this->lms_user->get_login() );
			$no_messages_unread = databaseAccess::getUnreadMails($this->lms_user->get_login());
			if ( $no_messages_unread > 0 )
			{
				$this->template->setVariable( "MESSAGES_UNREAD", $no_messages_unread . "" );
			    $messages_unread_string.= "(".$no_messages_unread." ".gettext("unread").")";
			}
			else
            {
				$messages_unread_string.= "(0 ".gettext("unread").")";
			}
			
			$this->template->setVariable( "MESSAGES_UNREAD_LABEL", gettext( "unread" ) );
			$this->template->setVariable( "CHECK_MAIL_TEXT", gettext( "Check your mail." ) );
			$this->template->setVariable( "MESSAGES_UNREAD", $messages_unread_string);
			}
			
			
			if (YOUR_MAILBOX && !MAILBOX_SHOW_UNREAD_ON_STARTPAGE) {
			$this->template->setVariable( "MESSAGES_URL", PATH_URL . "messages.php"  );
			$this->template->setVariable( "INBOX", gettext( "mailbox" )  );
			$messages_unread_string = "";
			$this->template->setVariable( "CHECK_MAIL_TEXT", gettext( "Check your mail." ) );
			$this->template->setVariable( "MESSAGES_UNREAD", $messages_unread_string);
			}
			
			
			if (defined("HELP_URL") && HELP_URL != "") {
        		$this->template->setCurrentBlock("BLOCK_HELP");
      			$this->template->setVariable( "HELP_TEXT", gettext( "Help" ) );
        		$this->template->setVariable( "HELP_URL", HELP_URL);
        		$this->template->parse("BLOCK_HELP");
      		}
		
			$this->template->setVariable( "SIGN_OUT_URL", URL_SIGNOUT );
			$this->template->setVariable( "SIGN_OUT_TEXT", gettext( "Sign out" ) );
			$this->template->setVariable( "SEARCH_DSC", gettext( "Enter keywords" ) );
			$this->template->setVariable( "SEARCH_LABEL", gettext( "Search" ) );
            $this->template->parse( "STATUS_SIGNED_IN_BLOCK" );
		}
		else
		{
			/*$this->template->setCurrentBlock( "STATUS_GUEST_BLOCK" );
			$this->template->setVariable( "NOT_SIGNED_IN_TEXT", gettext( "You aren't signed in." ) );
			$this->template->setVariable( "STATUS_PATH_URL", PATH_URL );
			if (defined("HELP_URL") && HELP_URL != "") {
        		$this->template->setCurrentBlock("BLOCK_HELP");
      			$this->template->setVariable( "HELP_TEXT", gettext( "Help" ) );
        		$this->template->setVariable( "HELP_URL", HELP_URL);
        		$this->template->parse("BLOCK_HELP");
      		}
			$this->template->setVariable( "SIGN_IN_TEXT", gettext( "Sign In" ) );
			$this->template->parse( "STATUS_GUEST_BLOCK" );*/
		}
	}

    public function set_problem_description( $description = "", $solution = "" )
    {
        if ( ! empty( $_SESSION[ "error" ] ) )
        {
            $this->template->setCurrentBlock( "PROBLEM_BLOCK" );
            $this->template->setVariable( "PROBLEM_DESCRIPTION", $_SESSION[ "error" ] );
            $this->template->parse( "PROBLEM_BLOCK" );
            $_SESSION[ "error" ] = "";
        }
        elseif ( $description && $description != "")
	{
		$this->template->setCurrentBlock( "PROBLEM_BLOCK" );
		$this->template->setVariable( "PROBLEM_DESCRIPTION", $description );
		$this->template->setVariable( "PROBLEM_SOLUTION", $solution );
		$this->template->parse( "PROBLEM_BLOCK" );
	}
    }

	public function set_confirmation( $confirmation_text = "" )
	{
		if ( ! empty( $_SESSION[ "confirmation" ] ) )
		{
			$this->template->setCurrentBlock( "BLOCK_CONFIRMATION" );
			$this->template->setVariable( "CONFIRMATION_TEXT", $_SESSION[ "confirmation" ] );
			$this->template->parse( "BLOCK_CONFIRMATION" );
			$_SESSION[ "confirmation" ] = "";
		}
		elseif( $confirmation_text )
		{
			$this->template->setCurrentBlock( "BLOCK_CONFIRMATION" );
			$this->template->setVariable( "CONFIRMATION_TEXT", $confirmation_text );
			$this->template->parse( "BLOCK_CONFIRMATION" );
		}
	}

	/**
	 * Sets a pagination header for browsing through a list of items if necessary.
	 *
	 * @param object $template
	 * @param int $max_items
	 * @param int $no_items
	 * @param string $result_text the words %START, %END and %TOTAL will be replaced
	 *   by the corresponding pagination numbers. Note that the pagination header
	 *   (and thus the $result_text) will only be shown if pagination is necessary.
	 * @param string $uri_params
	 * @return int start index
	 */
	public static function get_paginator($max_items, $no_items, $result_text = "", $uri_params = "")
	{
		$template = new HTML_Template_IT();
		$template->loadTemplatefile(PATH_EXTENSIONS . "system/frame/ui/html/pageiterator.template.html");
		if ( $no_items <= $max_items )
		{
			$result = array();
			$result["startIndex"] = 0;
			$result["html"] = "";
			return $result;
		}
		$pages = ceil( $no_items / $max_items );
		$current_page = ( ! empty( $_GET[ "page" ] ) ) ? $_GET[ "page" ] : 1;
		if ( $current_page < 1 || $current_page > $pages )
		{
			$current_page = 1;
		}

		$link = '';
		$link .= ( empty( $uri_params ) ) ? "?page=" : $uri_params . "&page=" ;
		$template->setCurrentBlock( "BLOCK_PAGES" );
		if ( $current_page > 1 )
		{
            self::set_page( $template, "<a href='$link" . ( $current_page - 1 ) . "'>&laquo; " . gettext( "Prev" ) . "</a>" );
		}
		else
		{
			self::set_page( $template, "<span class='first'>&laquo; " . gettext( "Prev" ) . "</span>" );
		}

		$min = ( $current_page - 5 < 1 ) ? 1 : $current_page - 5;
		if ( $min > 1 ) {
		    self::set_page( $template, "| ..." );
		}
		for ( $i = $min; $i < $current_page; $i++ ) {
		    self::set_page( $template, "| <a href=\"$link$i\">$i</a>" );
		}
		self::set_page( $template, "| <span class=\"current\">$i</span>" );

		$max = ( $current_page + 5 > $pages ) ? $pages : $current_page + 5;
		for ( $i = $current_page + 1; $i <= $max; $i++ ) {
		    self::set_page( $template, "| <a href=\"$link$i\">$i</a>" );
		}
		if ( $pages > $max ) {
		    self::set_page( $template, "| ..." );
		}

		if ( $current_page != $pages )
		{
			self::set_page( $template, "| <a href=\"$link" . ($current_page + 1 ) . "\" class=\"next\">" . gettext( "Next" ) . " &raquo;</a>" );

		}
		else
		{
			self::set_page( $template, "| <span class=\"last\">" . gettext( "Next" ) . " &raquo;</span>" );
		}

		$start_index = ( $current_page - 1 ) * $max_items;
		$end_index = $start_index + $max_items - 1;
		if ( $end_index > $no_items ) $end_index = $no_items - 1;

		if ( empty( $result_text ) ) $result_text = gettext( '(%TOTAL items total)' );
        $template->setVariable( 'RESULTS', str_replace( array( '%START', '%END', '%TOTAL' ), array( $start_index+1, $end_index+1, $no_items ), $result_text ) );

		$template->parse( "BLOCK_PAGES" );
		$result = array();
		$result["startIndex"] = $start_index;
		$result["html"] = $template->get();
		return $result;
	}

	public function get_paginator_start ( $max_items )
	{
		$current_page = ( ! empty( $_GET[ "page" ] ) ) ? $_GET[ "page" ] : 1;
		if ( $current_page < 1 ) return 0;
		else return ($current_page - 1) * $max_items;
	}

	private static function set_page( $template, $string )
	{
		$template->setCurrentBlock( "BLOCK_PAGE" );
		$template->setVariable( "PAGE", $string );
		$template->parse( "BLOCK_PAGE" );
	}

	public function set_rss_feed( $rss_link, $rss_name, $rss_dsc )
	{
		$this->template->setCurrentBlock( "BLOCK_RSS_FEED" );
		$this->template->setVariable( "RSS_TITLE", $rss_name );
		$this->template->setVariable( "RSS_LINK", $rss_link );
		$this->template->parse( "BLOCK_RSS_FEED" );
		$this->template->setCurrentBlock( "FEEDS_BLOCK" );
			$this->template->setVariable( "RSS_STYLE_PATH", PATH_STYLE );
			$this->template->setVariable( "RSS_FEED_LINK", $rss_link );
			$this->template->setVariable( "RSS_FEED_NAME", $rss_name );
			$this->template->setVariable( "RSS_FEED_DSC", $rss_dsc );
		$this->template->parse( "FEEDS_BLOCK" );
	}

	public function get_html()
	{
		return $this->template->get();
	}

	public function show_html()
	{
		// GENERATE HTML FOR MENU
	    if ($this->offline_status) {
	    	$html_menu = $this->get_menu_html( "guest", FALSE );
	      	$this->template->setVariable( "MENU_HTML", $html_menu );
	    } else {
	      $cache = get_cache_function( $this->lms_user->get_login(), 600 );
	      $html_menu = $cache->call("lms_portal::get_menu_html", $this->lms_user->get_login(), $this->lms_user->is_logged_in());
	      $this->template->setVariable( "MENU_HTML", $html_menu );
	      
	      $html_icon_bar = lms_portal::$instance->get_icon_bar_html($this->lms_user->is_logged_in());
	      $this->template->setVariable( "ICON_BAR_HTML", $html_icon_bar );
	    }
		
		if ($this->prototype_enabled) {
			$this->template->setCurrentBlock('HEAD_JAVASCRIPT_PROTOTYPE');
			$this->template->setVariable( "PATH_JAVASCRIPT_2", PATH_JAVASCRIPT);
			$this->template->setVariable( "KOALA_VERSION_2", KOALA_VERSION);
			$this->template->parse('HEAD_JAVASCRIPT_PROTOTYPE');
		}		
		try {
			while (ob_get_level() > 0) {
				ob_end_flush();
			}
		} catch (Exception $e) {
			
		}
		if ($_SESSION["STATISTICS_LEVEL"] > 0) {
			// output number of open-sTeam requests:
			$this->template->setVariable( "STATISTICS_REQUESTS", " | " . (isset($GLOBALS["STEAM"]) ? $GLOBALS["STEAM"]->get_request_count() : "nc") . " " . gettext( "server requests" ) );
			// output time taken to produce page:
			if ($_SESSION["STATISTICS_LEVEL"] > 1 && isset( $GLOBALS["page_time_start"] ) ) {
				$this->template->setVariable( "STATISTICS_PAGETIME", " | " . gettext( "page took" ) . " " . round((microtime(TRUE) - $GLOBALS["page_time_start"]) * 1000 ) . " ms" );
			}
		}
		
		if (JAVASCRIPT_SECURITY) {
			define("SHOW_SECURITY_PROBLEMS", FALSE);
			//find body
			preg_match_all("/(<body.*?<\\/body>)/is",$this->template->get(),$b_result);
			
			//logging script
			preg_match_all("/(<script.{0,100})/is",$b_result[1][0],$r_script);
			$scripts = $r_script[1];
			foreach ($scripts as $script) {
				logging::write_log(LOG_SECURITY, "found script in " . $_SERVER["SCRIPT_NAME"] . " user:" . $this->lms_user->get_login() . "\n\t\t" . $script . "...");
				if (SHOW_SECURITY_PROBLEMS) {
					echo "<pre style=\"color:red;\">" . "found script " . htmlspecialchars($script) . "</pre>";
				}
			}
			//loggin link
			preg_match_all("/(<link.{0,100})/is",$b_result[1][0],$r_link);
			$links = $r_link[1];
			foreach ($links as $link) {
				logging::write_log(LOG_SECURITY, "found link in " . $_SERVER["SCRIPT_NAME"] . " user:" . $this->lms_user->get_login() . "\n\t\t" . $link . "...");
				if (SHOW_SECURITY_PROBLEMS) {
					echo "<pre style=\"color:red;\">" . "found link " . htmlspecialchars($link) . "</pre>";
				}
			}
			//remove <script
			$clean_body = str_replace("<script", "", $b_result[1][0]);
			//remove <link
			$clean_body = str_replace("<link", "", $clean_body);
			$clean_html = preg_replace("/(<body.*?<\\/body>)/is",$clean_body,$this->template->get());
			
			//remove <... on...="..." onload, onclick, etc.
			preg_match_all("/<body[^>]*>(.*)<\\/body>/is", $clean_html, $b_result);
			$body_content = $b_result[1][0];
			preg_match_all("/<[^>]*(\\s(on\\w*=((\"[^\"]*\")|('[^']*'))))+/is", $body_content, $on_result);
			$ons = $on_result[1];
			foreach ($ons as $on) {
				logging::write_log(LOG_SECURITY, "found on*** in " . $_SERVER["SCRIPT_NAME"] . " user:" . $this->lms_user->get_login() . "\n\t\t" . $on);
				if (SHOW_SECURITY_PROBLEMS) {
					echo "<pre style=\"color:red;\">" . "found on*** " . htmlspecialchars($on) . "</pre>";
				}
			}
			$body_content = preg_replace("/<[^>]*\\s(on\\w*=((\"[^\"]*\")|('[^']*')))/is", "", $body_content);
			preg_match_all("/(<body[^>]*>)/is", $clean_html, $r);
			$body_start = $r[1][0];
			$clean_html = preg_replace("/<body[^>]*>.*<\\/body>/is", $body_start . $body_content . "</body>", $clean_html);
			
			return print $clean_html;
		} else {
			return $this->template->show();
		}
		
	}

	public function set_page_title( $title = "" )
	{
		($title == "") ? $this->template->setVariable( "PORTAL_TITLE", PLATFORM_TITLE ) : $this->template->setVariable( "PORTAL_TITLE", PLATFORM_TITLE . " - " . $title );
	}

	public function set_headline2( $headline )
	{
		if ( is_array( $headline ) )
		{
			$new_headline = "<h4><a href=\"" . $headline[ "link" ] . "\">" . $headline[ "name" ] . "</a></h4>";
			$this->template->setVariable( "HEADLINE2", $new_headline );
		}
		else
		{
			$new_headline .= "<h4>$headline</h4>";
		}
	}

	public function set_page_main( $headline, $main_html = "")
	{
		if ( is_array( $headline ) )
		{
			$new_headline  = "<h1>";
			$slash = "";
			foreach( $headline as $sub_path )
			{
				$new_headline .= $slash . " ";
				if ( empty( $sub_path[ "link" ] ) )
				{
					$new_headline .= $sub_path[ "name" ];
				}
				else
				{
					$new_headline .= "<a href=\"" . $sub_path[ "link" ] . "\" style=\"text-decoration: none;\">" . $sub_path[ "name" ]. "</a>";
				}
				$slash = " / ";
			}
			$new_headline .= "</h1>";
		}
		elseif( ! empty( $headline ) )
		{
            $new_headline = "<h1>$headline</h1>";
        }
        if ( !empty( $new_headline ) ) $this->template->setVariable( "HEADLINE1", $new_headline );
        $this->template->setVariable( "MAIN_HTML", $main_html );
	}

	public function __destruct()
	{
		lms_steam::disconnect();
	}

}
?>