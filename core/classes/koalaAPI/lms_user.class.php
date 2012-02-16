<?php
require_once( PATH_LIB . "cache_handling.inc.php" );
require_once( PATH_LIB . "encryption_handling.inc.php" );

class lms_user
{
	protected $login;	// string
	protected $password;	// md5 hash
	protected $attributes = array();  // sTeam-attributes
	protected $logged_in  = FALSE;	// boolean
	protected $current_role; 

	public function __construct( $login = STEAM_GUEST_LOGIN, $password = STEAM_GUEST_PW )
	{
		$this->login = $login;
		$this->set_password( $password );
	}

	public function login( $login = "", $password = "" )
	{
		if ( empty( $login ) )
		{
			$login = $this->login;
			$password = $this->get_password();
		}
		else
		{
			if ( empty( $password ) )
			{
				throw new Exception(
						"Password not given ($login).",
						E_USER_LOGIN
						);
			}
			$this->login = $login;
			$this->set_password( $password );
		}

		if ( lms_steam::is_connected() )
		{
			lms_steam::disconnect();
		}

		lms_steam::connect( STEAM_SERVER, STEAM_PORT, $login, $password );
		if ( ! lms_steam::is_logged_in() )
		{
			return FALSE;
		}

		// ASSIGN COMMON ATTRIBUTES
		$this->logged_in = TRUE;
		
		// INITIALIZE ATTRIBUTES
		$this->init_attributes();

		// INITIALIZE NETWORKING_PROFILE
		$steam_user = lms_steam::get_current_user();
		if ( ! $steam_user->get_attribute( "LLMS_NETWORKING_PROFILE" ) instanceof steam_object )
		{
			$profile = new lms_networking_profile( $steam_user );
			$profile->initialize();
			logging::write_log( LOG_MESSAGES, "REGISTRATION\t" . $login );
		}

		// NEW ENTRY IN LOGFILE
 		logging::write_log( LOG_MESSAGES, "LOGIN\t\t" . $login );
		$_SESSION[ "last_login" ] = $steam_user->get_attribute( "LMS_LAST_LOGIN" );
		$steam_user->set_attribute( "LMS_LAST_LOGIN", time() );
		return TRUE;
	}
	
	public function init_attributes()
	{
		$steam_user = lms_steam::get_current_user();
		$attributes = array(
				"USER_EMAIL",
				"USER_FIRSTNAME",
				"USER_FULLNAME",
				"USER_LANGUAGE",
				"USER_LAST_LOGIN",
				"USER_RSS_FEEDS",
				"OBJ_ICON"
				);
		$this->attributes = $steam_user->get_attributes( $attributes );
		$icon = $this->attributes[ "OBJ_ICON" ];
		if ( $icon instanceof steam_document )
		{
			$this->attributes[ "OBJ_ICON" ] = $icon->get_id();
		}
		else
		{
			$this->attributes[ "OBJ_ICON" ] = 0;
		}		
	}

	public function get_forename()
	{
		return $this->get_attribute( "USER_FIRSTNAME" );
	}

	public function get_surname()
	{
		return $this->get_attribute( "USER_FULLNAME" );
	}

	public function get_email()
	{
		return $this->get_attribute( "USER_EMAIL" );
	}

	public function get_last_login( $format = "%x" )
	{
		return strftime( $format, $this->get_attribute( "USER_LAST_LOGIN" ) );
	}

	public function get_attribute( $attribute_name )
	{

		if ( ! is_array( $this->attributes ) || ! array_key_exists( $attribute_name, $this->attributes ) )
		{
			throw new Exception( "Attribute does not exist: " . $attribute_name . ".", E_PARAMETER );
		}
		return $this->attributes[ $attribute_name ];
	}

	public function set_password( $password )
	{
		$this->password = encrypt( $password, ENCRYPTION_KEY );
	}

	public function get_password()
	{
		return decrypt( $this->password, ENCRYPTION_KEY );
	}

	public function get_login()
	{
		return $this->login;
	}

	public function is_logged_in()
	{
		return $this->logged_in;
	}

	public function logout()
	{
		$this->logged_in = FALSE;
		// NEW ENTRY IN LOGFILE
		logging::write_log( LOG_MESSAGES, "LOGOUT\t\t" . $this->login );
	}
	
	public static function get_user_image_url($width = 0, $height = 0, $user_icon = NULL) {
		if ($user_icon == NULL) {
			$steam_user = lms_steam::get_current_user();
			$user_icon = $steam_user->get_attribute("OBJ_ICON");
		}
		if (!$user_icon instanceof steam_document) {
			$user_icon = steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), "/images/doctypes/user_unknown.jpg");
		}
		$path = PATH_URL . "download/image/" . $user_icon->get_id();
		if ($width > 0 && $height > 0) {
			return $path . "/$width/$height";
		} else {
			//invalid width or height. ignore it
			return $path;
		}
	}


}
?>
