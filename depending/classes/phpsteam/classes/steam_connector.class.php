<?php
/**
 * Central connection to sTeam through the COAL-protocol
 *
 * All steam_objects need to have a connection to a sTeam-server.
 * Thus, a reference of this class is assigned to every instance
 * of steam_object or one of its subclasses through steam_factory::get_object().
 *
 * PHP versions 5
 *
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author  Henrik Beige <hebeige@gmx.de>, Alexander Roth <aroth@it-roth.de>, Daniel Buese <dbuese@upb.de>, Dominik Niehus <nicke@upb.de>
 */

defined("LOW_API_CACHE") or define("LOW_API_CACHE", true);
defined("API_DEBUG") or define("API_DEBUG", false);
require_once( "steam_factory.class.php" );
require_once( "steam_connection.class.php" );

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
 *		1900, "aroth",
 *		"secret_pw"
 *	);
 * $me = $steam->get_current_steam_user();
 * $my_workroom = $me->get_workroom();
 * $and_my_objects = $my_workroom->get_inventory();
 * </code>
 *
 * @package     PHPsTeam
 */
class steam_connector implements Serializable
{
	private $ServerIp;
	private $ServerPort;
	private $LoginName;

	private static $instances = array();

	/**
	 * constructor of steam_connector:
	 *
	 * The functions initializes some variables through init() when envoked.
	 * Arguments are all optional. If arguments are given, steam_connector
	 * tries to connect to the defined sTeam-server.
	 * Examples:
	 * - <code>$steam = new steam_connector( "steam.upb.de", 1900, "aroth", "secret" );</code>
	 *
	 * @param string  $pServerIP	IP or hostname of a sTeam-server
	 * @param integer $pServerPort	server's port for COAL-protocol
	 * @param string  $pLogin	    user's login
	 * @param string  $pPassword	user's password
	 */
	private function __construct($pServerIp, $pServerPort, $pLoginName, $pLoginPassword)
	{
		if ( ! is_string( $pServerIp ) ) throw new ParameterException( "pServerIp", "string" );
		if ( ! is_integer( $pServerPort ) ) throw new ParameterException( "pServerPort", "integer" );
		if ( ! is_string( $pLoginName ) ) throw new ParameterException( "pLoginName", "string" );
		if ( ! is_string( $pLoginPassword ) ) throw new ParameterException( "pLoginPassword", "string" );
		$this->ServerIp = $pServerIp;
		$this->ServerPort = $pServerPort;
		$this->LoginName = $pLoginName;
		self::$instances [$this->get_id()] = $this;
		steam_connection::init($pServerIp, $pServerPort, $pLoginName, $pLoginPassword);
	}

	public static function connect($pServerIp, $pServerPort, $pLoginName, $pLoginPassword) {
		if (!isset(self::$instances[$pLoginName . "@" . $pServerIp])) {
			return new self($pServerIp, $pServerPort, $pLoginName, $pLoginPassword);
		} else {
			return self::$instances[$pLoginName . "@" . $pServerIp];
		}
	}
	
	public static function get_instance($id) {
		if (!is_string($id)) throw new ParameterException( "id", "string" );
		if (isset(self::$instances[$id])) {
			return self::$instances[$id];
		} else {
			new Exception("no steam connector found for id: " . $id);
			return null;
		}
	}

	public function serialize() {
		return serialize(array($this->ServerIp, $this->ServerPort, $this->LoginName));
	}

	public function unserialize($data) {
		$values = unserialize($data);
		$this->ServerIp = $values[0];
		$this->ServerPort = $values[1];
		$this->LoginName = $values[2];
	}

	public function get_id() {
		return $this->LoginName . "@" . $this->ServerIp;
	}

	/**
	 * function get_login_status:
	 *
	 * Returns if the user is logged in or not.
	 *
	 * @return boolean
	 */
	public function get_login_status()
	{
		return steam_connection::get_instance($this->get_id())->get_login_status();
	}

	/**
	 * function get_login_data:
	 *
	 * @return
	 */
	public function get_login_data()
	{
		return steam_connection::get_instance($this->get_id())->get_login_data();
	}

	/**
	 * function get_current_steam_user:
	 *
	 * Returns the user who is logged in
	 *
	 * @return steam_user
	 */
	public function get_current_steam_user()
	{
		return steam_connection::get_instance($this->get_id())->get_current_steam_user();
	}

	/**
	 * function disconnect:
	 *
	 * Shuts down the socket connection
	 *
	 * Uses fclose on established socket
	 * and sets the socket_status to FALSE.
	 */
	public function disconnect()
	{
		steam_connection::get_instance($this->get_id())->disconnect();
	}

	/**
	 * function get_server:
	 *
	 * Returns the server's IP-address
	 *
	 * The steam_connector stores the IP-address
	 * of the sTeam-server used for the last successfull
	 * connection. This address is given back.
	 *
	 * @return string
	 */
	public function get_server()
	{
		return steam_connection::get_instance($this->get_id())->get_steam_server_ip();
	}

	/**
	 * function get_last_reboot:
	 *
	 * @return int
	 */
	public function get_last_reboot()
	{
		return steam_connection::get_instance($this->get_id())->get_last_reboot();
	}

	/**
	 * function get_port:
	 *
	 * Returns the server's port
	 *
	 * The steam_connector stores the port
	 * of the sTeam-server used for the last successfull
	 * connection. This port is given back.
	 *
	 * @return integer
	 */
	public function get_port()
	{
		return steam_connection::get_instance($this->get_id())->get_steam_server_port();
	}

	/**
	 * function get_root_room:
	 *
	 * Returns root-room
	 *
	 * @return steam_object
	 */
	public function get_root_room()
	{
		return steam_connection::get_instance($this->get_id())->get_root_room();
	}
	
	/**
	 * function get_server_module:
	 * 
	 * Returns server module
	 *
	 * @param string $pServerModule Name of the module 
	 * @return steam_object
	 */
	/*public function get_server_module( $pServerModule )
	{
		//True
    if (!isset( $this->login_arguments[ 8 ][ $pServerModule ] )){
    	echo "hund";die;
    	return 0;
    }
    
    switch ( $pServerModule ) {
      case "package:searchsupport":
          return new searchsupport( $this->login_arguments[ 8 ][ $pServerModule ] );
          break;
      case "groups":
          return new module_groups( $this->login_arguments[ 8 ][ $pServerModule ] );
          break;
      case "searching":
      	//compare_versions doesn't exist, login_arguments doesn't exist, 
      	// if ( $this->compare_versions( $this->server_version, '2.9.4' ) < 0 ) break;
          return new searching(steam_connection::get_instance($this->get_id())->get_module($pServerModule));
          break;
    }

		return $this->login_arguments[ 8 ][ $pServerModule ];
	}*/

	/**
	 * function get_module:
	 *
	 * Returns server module
	 *
	 * @param string $pServerModule Name of the module
	 * @return steam_object
	 */
	public function get_module( $pServerModule )
	{
		return steam_connection::get_instance($this->get_id())->get_module($pServerModule); 
	}

	public function get_service_manager()
	{
		return $this->get_module("ServiceManager");
	}

	public function is_service( $pServiceName )
	{
		$mngr = $this->get_service_manager();
		return steam_connection::get_instance($this->get_id())->predefined_command(
		$mngr,
			"is_service",
		array( $pServiceName ),
		0
		);
	}

	public function call_service( $pServiceName, $pParams )
	{
		$mngr = $this->get_service_manager();
		return steam_connection::get_instance($this->get_id())->predefined_command(
		$mngr,
			"call_service_async",
		array( $pServiceName, $pParams ),
		0
		);
	}

	public function get_services()
	{
		$mngr = $this->get_service_manager();
		return steam_connection::get_instance($this->get_id())->predefined_command(
		$mngr,
			"get_services",
		array(),
		0
		);
	}

	/**
	 * function get_factory:
	 *
	 * Returns server factory
	 *
	 * @param string $pType Factory Type (e.g. CLASS_USER or CLASS_DOCUMENT)
	 * @return steam
	 */
	public function get_factory( $pType )
	{
		return steam_connection::get_instance($this->get_id())->get_factory($pType);
	}

	public function list_modules()
	{
		return steam_connection::get_instance($this->get_id())->list_modules();
	}

	public function get_steam_group()
	{
		return steam_connection::get_instance($this->get_id())->get_steam_group();
	}

	public function get_database()
	{
		return steam_connection::get_instance($this->get_id())->get_database();
	}

	public function get_pike_version()
	{
		return steam_connection::get_instance($this->get_id())->get_pike_version();
	}

	public function set_socket_timeout($timeout) {
		steam_connection::get_instance($this->get_id())->set_socket_timeout($timeout);
	}

	/**
	 * function get_server_version:
	 *
	 * returns the server version
	 *
	 * @return string server version
	 */
	public function get_server_version()
	{
		return steam_connection::get_instance($this->get_id())->get_server_version();
	}

	/**
	 * function get_request_count:
	 *
	 * returns the new number of requests sent to the steam server
	 *
	 * @return integer request count
	 */
	public function get_request_count()
	{
		return steam_connection::get_instance($this->get_id())->get_sentrequests();
	}
	
	public function get_globalrequest_count() {
		return steam_connection::get_instance($this->get_id())->get_globalrequests();
	}
	
	public function get_globalrequest_map() {
		return steam_connection::get_instance($this->get_id())->get_globalrequestsmap();
	}
	
	public function get_globalrequest_time() {
		return steam_connection::get_instance($this->get_id())->get_globalrequeststime();
	}
	

	/**
	 * function upload:
	 *
	 * @param $pPath
	 * @param $pContent
	 *
	 * @return
	 */
	public function upload($pPath, $pContent )
	{
		$request = new steam_request(
		$this,
		$this->get_transaction_id(),
		$this->get_current_steam_user(),
		array( $pPath, strlen( $pContent ) ),
		COAL_FILE_UPLOAD
		);
		$command = $this->command( $request );
		fwrite( $this->socket, $pContent, strlen( $pContent ) );
		$new_object = $command->arguments[ 0 ];
		return $new_object;
	}

	/**
	 * function install_package:
	 *
	 * Installs an SPM package on the server. The package needs to be a valid
	 * steam_document, so you will have to upload the package file first
	 * before you pass it to this function.
	 *
	 * @param steam_document $spm_package the package object to install
	 * @return boolean TRUE if the package was installed, FALSE on failure
	 */
	public function install_package ( $spm_package )
	{
		if ( !is_object( $spm_package ) || !($spm_package instanceof steam_document) )
		throw new Exception( "No valid SPM package provided." );
		$spm_module = $this->get_module( "SPM" );
		if ( !is_object( $spm_module ) )
		throw new Exception( "Could not get the SPM module.\n" );
		$root_room = steam_factory::get_object_by_name( $this->get_id(), "/" );
		if ( !is_object( $root_room ) )
		throw new Exception( "Could not find the root-room.\n" );
		$result = $this->predefined_command( $spm_module, "install_spm", array( $spm_package, $root_room ), 0 );
		return is_array( $result );
	}

	/**
	 * method: send_mail($target, $subject, $message, $buffer = 0)
	 *
	 * send an email using the smtp module of the server directly. the message
	 * send by this command will be delivered to the target address directly and
	 * will not be stored in the messaging system of the steam server.
	 *
	 * @param string $target string containing the email- address of the recipient
	 * @param string $subject subject of the message
	 * @param string $from string containing the email address of the sender
	 * @param string $message message body/content in the specified mime type
	 * (html by default)
	 * @param string $mime_type mime type of the message to send e.g.
	 * "text/plain". If not specified explicitely the mime type is "text/hmtl" by
	 * default
	 * @param boolean $buffer optional TRUE if command should be buffered or FALSE
	 * to sent immidiately
	 * @return = see predefined_command
	 */
	function send_mail_from($target, $subject, $message, $from,  $buffer = FALSE, $mime_type = "text/html")
	{
		if(!is_string($target) || !is_string($subject) || !is_string($message))
		return false;
		return $this->predefined_command($this->get_module("smtp") , "send_mail", array($target, $subject, $message, $from, 0, $mime_type), $buffer);
	}

	function get_config_value( $pKey) {
		return steam_connection::get_instance($this->get_id())->get_config_value($pKey);
	}

	/**
	 * function get_login_user_name:
	 *
	 * @return String name of the user where this connector is connected to steam
	 */
	function get_login_user_name() {
		return steam_connection::get_instance($this->get_id())->get_login_user_name();
	}

	/**
	 * Convenience Method to be able to convert strings to quoted printable using
	 * mail methods of the PHPsTeam API, e.g. steam_user::mail()
	 * L: note $encoding that is uppercase
	 * L: also your PHP installation must have ctype_alpha, otherwise write it
	 * yourself
	 */
	function quoted_printable_encode($string, $encoding='UTF-8') {
		// use this function with headers, not with the email body as it misses word wrapping
		$len = strlen($string);
		$result = '';
		$enc = false;
		for($i=0;$i<$len;++$i) {
			$c = $string[$i];
			if (ctype_alpha($c))
			$result.=$c;
			else if ($c==' ') {
				$result.='_';
				$enc = true;
			} else {
				$result.=sprintf("=%02X", ord($c));
				$enc = true;
			}
		}
		//L: so spam agents won't mark your email with QP_EXCESS
		if (!$enc) return $string;
		return '=?'.$encoding.'?q?'.$result.'?=';
	}

	public function predefined_command( $pObject, $pMethod, $pArgs, $pBuffer ) {
  		return steam_connection::get_instance($this->get_id())->predefined_command($pObject, $pMethod, $pArgs, $pBuffer);
  	}
  
  	public function buffer_flush() {
  		return steam_connection::get_instance($this->get_id())->buffer_flush();
  	}
  
  	public function get_socket_status() {
  		return steam_connection::get_instance($this->get_id())->get_socket_status();
  	}
  
  	public function exception( $pCode, $pDetails = "", $allow_backtrace = TRUE ) {
  		return steam_connection::get_instance($this->get_id())->exception( $pCode, $pDetails, $allow_backtrace);
  	}
  	
  	public function buffer_attributes_request( $pObject, $pAttributes, $pSourceObjectID = 0 ){
  		return steam_connection::get_instance($this->get_id())->buffer_attributes_request( $pObject, $pAttributes, $pSourceObjectID);
  	}
  	public function get_transaction_id(){
  		return steam_connection::get_instance($this->get_id())->get_transaction_id();
  	}
  	public function command($pRequest){
  		return steam_connection::get_instance($this->get_id())->command($pRequest);
  	}
  	
  	public function read_socket($pLength) {
  		return steam_connection::get_instance($this->get_id())->read_socket($pLength);
  	}
} 
?>