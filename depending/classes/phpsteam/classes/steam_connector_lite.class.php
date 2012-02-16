<?php
/**
 * Central connection to sTeam through the COAL-protocol in a "light" variant.
 * 
 * This Connector- version is optimized to perform quick logins by using a 
 * special login variant. This variant is quicker than the login implemented in 
 * steam_connector but doesnt get so much information from the server during the 
 * login procedure. As a result, the modules and factories are not available
 * using steam_connector_lite.
 * The methods with changed behaviour throwing an exception like "Error: Method
 * is not supported by lite variant" to indicate that the lite- connector doesnt 
 * support all features.
 * 
 * PHP versions 5
 *
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author  Daniel Buese <dbuese@upb.de>, Alexander Roth <aroth@it-roth.de>, Henrik Beige <hebeige@gmx.de>, Dominik Niehus <nicke@upb.de>
 */

require_once( "steam_factory.class.php" );

/**
 * The steam_connector manages all socket functions.
 *
 * This class implements services like
 * - connect and disconnect to sTeam-server 
 * - send commands and get data back through the COAL-protocol
 * - login and logout, bounded to a specific user
 * - reconnect to sTeam after broken connection
 *
 * You need to have this class instanciated before working with sTeam.
 * Insofar, an object of steam_connector can be seen as the key to
 * access all other steam_objects. PHPsTeam is so easy like this:
 * <code>
 * $steam = new steam_connector( 
 * 		"steam.open-steam.org", 
 *		1900,  
 "aroth", 
 *		"secret_pw"
 *	);
 * $me = $steam->get_current_steam_user();
 * $my_workroom = $me->get_workroom();
 * $and_my_objects = $my_workroom->get_inventory();
 * </code>
 *
 * @package     PHPsTeam
 */
class steam_connector_lite extends steam_connector
{
	/**
	 * constructor of steam_connector:
	 * 
	 * The functions initializes some variables through init() when envoked.
	 * Arguments are all optional. If arguments are given, steam_connector
	 * tries to connect to the defined sTeam-server.
	 * Examples:
	 * - <code>$steam = new steam_connector( "steam.upb.de", 1900, "aroth", "secret" );</code>
	 * - <code>$steam = new steam_connector( "192.168.104.100", 1900 );</code>
	 * - <code>$steam = new steam_connector();</code>
	 *
	 * @param string  $pServerIP	IP or hostname of a sTeam-server
	 * @param integer $pServerPort	server's port for COAL-protocol
	 * @param string  $pLogin	user's login
	 * @param string  $pPassword	user's password
	 */
	public function __construct( $pServerIp = "", $pServerPort = "", $pLoginName = "", $pLoginPassword = "")
	{
		parent::__construct( $pServerIp, $pServerPort, $pLoginName, $pLoginPassword );
	}

	/**
	 * function get_login_data: 
	 * 
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   *
	 * @throws 
 	*/
	public function get_login_data()
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: get_login_data is not available using steam_connector_lite", 980 );
	}

	/**
	 * function get_root_room:
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
	 */
	public function get_root_room( )
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: get_root_room is not available using steam_connector_lite", 980 );
	}

	/**
	 * function get_module:
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   */
	public function get_module( $pServerModule )
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: get_module is not available using steam_connector_lite", 980 );
	}


	/**
	 * function get_factory:
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   */
  public function get_factory( $pType )
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: get_factory is not available using steam_connector_lite", 980 );
	}

	
	/**
	 * function get_server_module:
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   */
  public function get_server_factory( $pType )
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: get_server_factory is not available using steam_connector_lite", 980 );
	}
  
	/**
 	* function list_server_modules:
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   */
  public function list_server_modules()
	{
		throw new steam_exception( $this->get_login_user_name(), "Error: list_server_modules is not available using steam_connector_lite", 980 );
	}

  /**
   * method: send_mail($target, $subject, $message, $buffer = 0)
   *
   * throws a steam_exception because this method cannot be used using 
   * steam_connector_lite
   */
 function send_mail_from($target, $subject, $message, $from,  $buffer = FALSE, $mime_type = "text/html")
  {
		throw new steam_exception( $this->get_login_user_name(), "Error: send_mail_from is not available using steam_connector_lite", 980 );
  }
  
  /**
   * function get_config:
   * 
   * @param $pKey the key of the value to get
   * @param $pBuffer
   * 
   * @return 
   */
	function get_config_value( $pKey, $pBuffer = 0 ) {
		throw new steam_exception( $this->get_login_user_name(), "Error: set_config_value is not available using steam_connector_lite", 980 );
  }
}

?>