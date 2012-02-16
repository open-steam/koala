<?php
/**
 * Handles connection socket to steam server
 *
 * PHP versions 5
 * 
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Dominik Niehus <nicke@upb.de>
 */

defined("STEAM_SOCKET_TIMEOUT_DEFAULT") or define("STEAM_SOCKET_TIMEOUT_DEFAULT", 60);

class steam_connection {
	
	  
	// socket data
	protected $socket;
	protected $socket_status;
	protected $socket_timeout;

	// server data
	protected $steam_server_ip;
	protected $steam_server_port;
	
	// login information
	protected $login_data; //??
	protected $current_steam_user;
	protected $login_user_name;
	private   $login_passwd;
	protected $login_status; // 1=logged in, 0=logged out

	// request buffer
	protected $request_buffer;
	
	// internal mapping transaction ids and object ids
	protected $object_buffer;

	// internal transaction counter
	protected $transactionid;

	// internal request counter
	protected $sentrequests;
	protected static $globalRequests = 0;
	protected static $globalRequestsMap = array();
	protected static $globalRequestsTime= array();
	
	protected $login_arguments;
	
  	protected $root_room;
  	
  	protected $last_reboot;
  	protected $pike_version;
  	protected $database;
  	protected $steam_group;
 	protected $server_version;
 	protected $server_config;
	
	private static $instances = array();
	
	private function __construct($pServerIp, $pServerPort, $pLoginName, $pLoginPassword) {
		$this->init_variables();
		if ( ! empty( $pServerIp ) && ! empty( $pServerPort ) && ! empty( $pLoginName ) && ! empty( $pLoginPassword ) ) {
			$this->steam_server_ip = $pServerIp;
			$this->steam_server_port = $pServerPort;
			$this->login_user = $pLoginName;
			$this->login_passwd = $pLoginPassword;
		} else {
			if (empty( $pServerIp )) {
				throw new ParameterException( "pServeIp", "empty" );
			} else if ( empty( $pServerPort )) {
				throw new ParameterException( "pServerPort", "empty" );
			} else if ( empty( $pLoginName )) {
				throw new ParameterException( "pLoginName", "empty" );
			} else if ( empty( $pLoginPassword )) {
				throw new ParameterException( "pLoginPassword", "empty" );
			}
		}
	}
	
	public static function get_instance($id) {
		if (isset(self::$instances[$id])) {
			return self::$instances[$id];
		} else {
			echo "no connection found for id: " . $id;
			return null;
		}
	}
	
	public static function init($server_ip, $server_port, $login_name, $login_pw) {
		$id = $login_name . "@" . $server_ip;
		if (isset(self::$instances[$id])) {
			self::$instances[$id]->connect($server_ip, $server_port, $login_name, $login_pw);
			return self::$instances[$id];
		} else {
			$steam_connection = new steam_connection($server_ip, $server_port, $login_name, $login_pw);
			self::$instances[$id] = $steam_connection;
			$steam_connection->connect($server_ip,$server_port,$login_name, $login_pw);
			return $steam_connection;
		}
	}
	
	public function set_socket_timeout($timeout) {
		$this->socket_timeout = $timeout;
	}
	
	public function get_socket_timeout() {
		return $this->socket_timeout;
	}
	
	public function get_id() {
		return $this->login_user . "@" . $this->steam_server_ip;
	}
	
	/**
	 * Initialization of steam_connector
	 *
	 * Reset the connection, the login status and the command buffer.
	 *
	 */
	protected function init_variables()
	{
    	$this->sentrequests = 0;
		$this->transaction_id	= 1;
		$this->socket_status 	= FALSE;
		$this->login_status	= FALSE;
		$this->login_user_name  = "Anonymous";
		$this->request_buffer	= array();
		$this->object_buffer 	= array();
	}
	
	/**
 	* function read_socket:
 	* 
 	* @param $pLength
 	* 
 	* @return 
 	*/
	public function read_socket( $pLength )
	{	
		return @fread( $this->socket, $pLength );
	}

	/**
	 * function connect:
	 * 
	 * Establishes a connection to sTeam
	 *
	 * Uses fsockopen() to connect to a sTeam-server.
	 * If connection established, some class-variables are set
	 * describing login and socket status.
	 * 
	 * @param string  $pServerIP	IP or hostname of a sTeam-server
	 * @param integer $pServerPort	server's port for COAL-protocol
	 * @param string  $pLogin	user's login
	 * @param string  $pPassword	user's password
   * @param boolean $reconnect TRUE if only reconnect (reduces login overhead)
	 * @return socket-handler
	 */
	private function connect( $pServerIp, $pServerPort, $pLoginName, $pLoginPassword, $reconnect = FALSE )
	{
		$this->steam_server_ip = @gethostbyname( $pServerIp );
		if ( ! $this->steam_server_ip )
		{
			// Exception: steam-server unknown
			throw $this->exception( 100, $pServerIp, FALSE );
		}
		$this->steam_server_port = $pServerPort;
		$this->login_user_name = $pLoginName;

		$this->socket = @fsockopen(
				$this->steam_server_ip,
				$this->steam_server_port,
				$errno,
				$errstr
				);

		if ( ! $this->socket )
		{
			$this->socket_status = FALSE;
			// Exception: Could not connect...
			throw $this->exception( 110, $this->steam_server_ip . ":" . $this->steam_server_port, FALSE );
		}

		$this->socket_status = TRUE;

		$this->steam_server_ip		= $pServerIp;
		$this->steam_server_port	= $pServerPort;

		if ( trim( $pLoginName ) != "" && trim( $pLoginPassword ) != ""  )
		{
			try	
			{
				$this->login( $pLoginName, $pLoginPassword, $reconnect );
			}
			catch ( steam_exception $e )
			{
				$this->disconnect();
				throw $e;
			}
		}

		return $this->socket;
	}
	
	/**
	 * function disconnect:
	 * 
	 * Shuts down the socket connection
	 *
	 * Uses fclose on established socket 
	 * and sets the socket_status to FALSE.
	 */
	public function disconnect( )
	{
		if ( $this->socket )
		{
			@fclose( $this->socket );
		}
		$this->socket_status = FALSE;
	}

	/**
	 * function is_connected:
	 *
   * returns the socket status, TRUE if connector is connected to a steam
   * server, FALSE if not
	 *
	 * @return TRUE if connector is connected to a steam server 
   */
  function is_connected() {
   return $this->socket_status;
  }
  
	/**
	 * function get_socket_status:
	 * 
	 * Returns whether the connection is steablished or not.
	 * 
	 * @return boolean
	 */
	public function get_socket_status( )
	{
		return $this->socket_status;
	}
  
	/**
	 * function reconnect:
	 * 
	 * To reconnect the sTeam-server after a broken connection
	 *
	 * If the connection was established once, the informations
	 * about server, port, user and password are stored in class-variables.
	 * These are used to reconnect the server. reconnect() calls 
	 * connect() function.
	 *
	 * @return socket 
	 */
	public function reconnect()
	{
		if ( ! empty( $this->steam_server_ip ) && ! empty( $this->steam_server_port ) && ! empty( $this->login_user_name ) && ! empty( $this->login_user_pw )   )
		{
			try
			{
				$this->connect( 
						$this->steam_server_ip,
						$this->steam_server_port,
						$this->login_user_name,
						$this->login_user_pw,
            TRUE
					      );
			}
			catch ( steam_exception $e )
			{
				// not of my business...
				throw $e;
			}
		}
		else
		{
			// Exception: Could not reconnect
			throw $this->exception( 150 );
		}
	}

	/**
	 * function login:
	 * 
	 * The login procedure 
	 *
	 * The user logs in through an established connection.
	 * Credentials are checked by sTeam. If successful, an 
	 * object of type steam_user is given back; otherwise
	 * a FALSE is returned.
	 *
	 * @param string $pLogin	User's login name
	 * @param string $pPassword	User's password
   * @param boolean $relogin TRUE if only relogin (reduces login overhead)
	 * @return steam_user | FALSE
	 */
	public function login( $pLogin, $pPassword, $relogin = FALSE )
	{
		$request = new steam_request(
				$this->get_id(),
				$this->get_transaction_id(),
				steam_factory::get_object($this->get_id()),
				array( 
					$pLogin, 
					$pPassword, 
					$this->get_version(), 
					CLIENT_STATUS_CONNECTED 
				     ),
				  ($relogin?COAL_RELOGIN:COAL_LOGIN)
				);

		try
		{
			$result = $this->command( $request );
		}
		catch( steam_exception $e )
		{
			// not of my business
			$this->login_status = 0;
			return FALSE;
		}
		$this->login_status = ! ( $request->is_error() );
    /**
    $login_arguments contains the following:
    0  => user name
    1  => server version
    2  => last reboot
    3  => last login
    4  => pike version
    5  => database
    6  => root-room
    7  => group-all
    8  => map ( name->object ) containing modules
    9  => map ( int id->factory object )
    10 => map ( config name->config value )
     */
		if ( $this->login_status )
		{
			// Kann spaeter weg!!!
			if (!$relogin) $this->login_data	     = $result;
			if (!$relogin) $this->login_arguments	 = $result->get_arguments();
		      if (!$relogin) $this->server_version   = $this->login_arguments[1];
		      if (!$relogin) $this->last_reboot      = $this->login_arguments[2];
		      if (!$relogin) $this->pike_version     = $this->login_arguments[4];
		      if (!$relogin) $this->database         = $this->login_arguments[5];
		      if (!$relogin) $this->root_room        = $this->login_arguments[6];
		      if (!$relogin) $this->steam_group      = $this->login_arguments[7];
		      if (!$relogin) $this->server_config    = $this->login_arguments[10];
			$this->login_user_name	= $pLogin;
			$this->login_user_pw    = $pPassword;
			$this->current_steam_user	= steam_factory::get_object( $this->get_id(), $result->object->get_id(), CLASS_USER );
			return $this->current_steam_user;
		}
		else
		{
			$this->login_status = 0;
			return FALSE;
		}
	}
	
	/**
	 * function command:
	 * 
	 * Manages the transportation of commands through COAL-protocol
	 *
	 * This function sends a command throught an established socket
	 * to the sTeam-server and returns its answer. The command and 
	 * the answer is encapsulated in a steam_request-object. Look there
	 * for further information.
	 *
	 * @param steam_request $pRequest An encoded question
	 * @return steam_request Returns the question together with the answer got back from sTeam-Server
	 */
	public function command( $pRequest )
	{
		if ( get_class( $pRequest ) != "steam_request" )
		{
			// Exception
			throw new ParameterException( "pRequest", "steam_request" );
		}

		try
		{
			$result = $this->send_command( array( $pRequest) );
		}
		catch( steam_exception $e )
		{
			throw $e;
		}

		return $pRequest;
	}

	/**
	 * function send_command:
	 * 
	 * Pure communication function 
	 *
	 * Handles the sending- and receiving-procedure whilst the
	 * communication process between PHP and sTeam. It uses
	 * fwrite() and fread() on the established socket.
	 *
	 * @param string $pCommandBuffer Encoded command, see steam_request->encode() 
	 * @param integer $pCommandCounter Number of encoded command inside $pCommandBuffer
	 * @return string Encoded answer from sTeam-server
	 */
	public function send_command(array $pCommandBuffer ) {
		$startTime = microtime(TRUE);
		$orignalRequests = array();
		foreach ($pCommandBuffer as $key => $req) {
			$orignalRequests[$key] = clone $req;
		}
		// send command to steam-server
	isset($this->socket_timeout) ? stream_set_timeout($this->socket, $this->socket_timeout) : stream_set_timeout($this->socket, STEAM_SOCKET_TIMEOUT_DEFAULT);
    $this->sentrequests ++;
    self::$globalRequests ++;
    foreach ( $pCommandBuffer as $request ) {
      $encoded_request = $request->encode();
      $bytes = @fwrite(
          $this->socket,
          $encoded_request,
          strlen( $encoded_request )
          );
      if ( ! $bytes )
      {
        // Exception: Could not write through socket
        $info = stream_get_meta_data($this->socket);
    	fclose($this->socket);
      	if ($info['timed_out']) {
      		throw new steam_exception( $this->get_login_user_name(),  "Connection timed out! Could not write through socket request=" . $request->get_coalcommand(), 300 );
    	} else {
       		throw new steam_exception( $this->get_login_user_name(),  "Could not write through socket request=" . $request->get_coalcommand(), 300 );
    	}
      }
    }
		// get result
		$data = "";
    $cbsize = count( $pCommandBuffer );
    if (count($pCommandBuffer)>1) $flushing = TRUE;
    else $flushing = FALSE;
		for ( $i = 0 ; $i < $cbsize; $i++ )
		{
      $tidok = FALSE;
      while ( $tidok != TRUE ) {
        // read "-1" and size of package
        $buffer	= fread( $this->socket, 1 );
        $size	= fread( $this->socket, 4 );
  
        if (! $buffer || ! $size )
        {
          // Exception: Could not read from socket
            $info = stream_get_meta_data($this->socket);
	    	fclose($this->socket);
	      	if ($info['timed_out']) {
	      		throw new steam_exception( $this->get_login_user_name(),  "Connection timed out! Reading socket.", 300 );
	    	} else {
	       		throw new steam_exception( $this->get_login_user_name(),  "Reading socket.", 300 );
	    	}
        }
  
        $count = hexdec( bin2hex( $size ) ) - 5;
  
        $data_read = "";
  
        while( $count > 0 )
        {
          $data_buffer = @fread( $this->socket, $count );
          if ( ! $data_buffer )
          {
            throw new steam_exception( $this->get_login_user_name(), "Read empty data from socket commandbuffer=" . $pCommandBuffer , 300);
          }
          $data_length = strlen( $data_buffer );
          $count -= $data_length;
          if ( feof( $this->socket ) )
          {
            $count = 0;
          }
          $data_read .= $data_buffer;
        }
        
        $thetid = hexdec(bin2hex(substr($data_read, 0, 4)));
        $commandtid = $pCommandBuffer[ $i ]->get_transactionid();
        if ( $thetid == $commandtid ) {
          $tidok = TRUE;
          $res = $buffer . $size . $data_read;
       		if ( trim( $res ) == "" )
          {
            // Exception: Got no result back
            throw new steam_exception( $this->get_login_user_name(), "Got no result back. command=" . $command, 300 );
          }
          try {
            $pCommandBuffer[ $i ]->decode( $res, $flushing );
          } catch( Exception $exception ){
            throw new steam_exception($this->get_login_user_name(), $exception->get_message(), 300);
          }
        }
        else {
          // if tid < commandtid skipping may help
          if ( $thetid < $commandtid ) {
            error_log("steam_connector.php, send_command: command tid=" . $commandtid . " answer tid=". $thetid. " skipping result...");
          }
          // no chance, serious failure in data transfer
          else {
            throw new steam_exception( $this->get_login_user_name(), "Failure during data transfer commandbuffer=" . $pCommandBuffer, 300 );
          }
        }
      }
		}
		if (count($orignalRequests) == 1) {
			$request = $orignalRequests[0];
			$args = $request->get_arguments();
			$string = "object: " . (($request->get_object() instanceof steam_object) ? $request->get_object()->get_id() : "null") . "\t" ;
			$string .= "methode: " . ((is_array($args) && isset($args[0])) ?  $args[0] : "null") . "\t";
			$string .= "args: " . ((is_array($args) && isset($args[1])) ?  ((is_array($args[1]) && isset($args[1][0])) ? $args[1][0] : $args[1]) : "null") . "\t";
			(!API_DEBUG) or error_log($string);
			$args = $request->get_arguments();
			if (is_array($args) && isset($args[0])) {
				$method = $args[0];
			} else {
				$method = "problem";
			}
		} else {
			$method = "Sammelaufruf";
		}
		if ($method === "" || !is_string($method)) {
			$method = "Problem2";
		}
		$time = microtime(TRUE) - $startTime;
		if (isset(self::$globalRequestsMap[$method])) {
			self::$globalRequestsMap[$method]++;
			self::$globalRequestsTime[$method] = self::$globalRequestsTime[$method] + $time;
		} else {
			self::$globalRequestsMap[$method] = 1;
			self::$globalRequestsTime[$method] = $time;
		}
		return $pCommandBuffer;
	}
  
	/**
	 * function buffer_command:
	 * 
	 * Buffers encoded commands
	 * 
	 * Because of the procedure of connecting and logging into
	 * the server for single commands, 
	 * the communication process for multiple
	 * commands is faster if they can send as a bundle.
	 * This functions buffers single commands until the 
	 * sending-process.
	 *
	 * @param steam_request $pRequest
	 */
	private function buffer_command( $pRequest )
	{
		if ( get_class( $pRequest ) != "steam_request" )
		{
			throw new ParameterException( "pRequest", "steam_request" );
		}
		array_push($this->request_buffer, $pRequest);
	}

	/**
	 * function reset_buffer:
	 * 
	 * Reset command buffer
	 *
	 * This function initializes the command buffer
	 * and reset the command counter to zero.
	 */
	private function reset_buffer()
	{
		$this->request_buffer = array();
	}

	/**
	 * clears the command queue
	 *
	 * The function clears deletes all buffered commands from the command buffer 
	 *
	 */
	public function buffer_clear()
	{
    $this->request_buffer = array();
  }
  
	/**
	 * Sends the whole bundle of buffered commands to sTeam
	 *
	 * The function sends the command buffer to the sTeam-server,
	 * receives their answers and returns them as an array of
	 * steam_request objects.
	 *
	 * @return mixed array of steam_request
	 */
	public function buffer_flush()
	{
		// Only flush buffer if one or more commands are buffered
		if ( count( $this->request_buffer) > 0 )
		{
			// send command buffer...
			$data = $this->send_command(
					$this->request_buffer
					);
			      // construct result array with transaction id as key
			      foreach( $data as $answer ) {
			        $result[ $answer->get_transactionid() ] = $answer->get_arguments();
			      }
			$this->request_buffer = array();
		}
		else
		{
			$result = array();
		}

		// do set_values for all objects in object buffer
    	// (used in buffer_attributes_request)
		if ( count( $this->object_buffer ) > 0 )
		{
			while( list( $transaction_id, $object ) = each($this->object_buffer ) )
			{
				$attributes = $result[ $transaction_id ];
				$object->set_values( $attributes );
			}
		}
		$this->object_buffer = array();
		return $result;
	}

	/**
	 * function buffer_attributes_request:
	 * 
	 * @param steam_object $pObject object with unknown attributes
	 * @param mixed $pAttributes List of attributes'names
	 */
	public function buffer_attributes_request( $pObject, $pAttributes, $pSourceObjectID = 0 )
	{
			$object = ( $pSourceObjectID == 0 ) ? $pObject : new steam_object( $this, $pSourceObjectID );
			$transaction_id = $this->predefined_command(
				$object,
				"query_attributes",
				array( $pAttributes ),
				1
			);
			$this->object_buffer[ $transaction_id ] = $pObject;
			return $transaction_id;
	}

	/**
	 * function get_transaction_id:
	 * 
	 * increments the counter of transactions and
	 * returns the new number as an unique transaction id
	 *
	 * @return integer unique transaction id
	 */
	public function get_transaction_id()
	{
		$this->transactionid += 1;
		return $this->transactionid;
	}
	
	/**
	 * function get_module:
	 * 
	 * Returns server module
	 *
	 * @param string $pServerModule Name of the module 
	 * @return steam_object
	 */
	public function get_module( $pModule )
	{
    if (!isset( $this->login_arguments[ 8 ][ $pModule ] )) return 0;
		return $this->login_arguments[ 8 ][ $pModule ];
	}
	
	/**
	 * function get_factory:
	 * 
	 * Returns server factory
	 *
	 * @param string $pType Factory Type (e.g. CLASS_USER or CLASS_DOCUMENT)
	 * @return steam_object
	*/
	public function get_factory( $pType )
	{
    if (!isset( $this->login_arguments[ 9 ][ $pType ] )) return 0;
		return $this->login_arguments[ 9 ][ $pType ];
	}
	
	/**
	 * function predefined_command:
	 * 
	 * Defines COAL-commands in a convenient way
	 *
	 * This function translates the arguments into
	 * a COAL-command by means of steam_request.
	 * Either it returns an instance of steam_request,
	 * or - depending on the buffer argument - the
	 * transaction id in case of a buffered command.
	 *
	 * <b>ATTENTION: Function is needed when you want to implement
	 * new COAL-commands!</b>
	 *
	 * Examples:
	 *
	 *<code>
	 * $my_workroom = $steam_connection->predefined_command(
	 *		$steam_connection->get_current_steam_user(),
	 *		"query_attribute",
	 *		array( "USER_WORKROOM" ),
	 *		0
	 *	);
	 *</code>
	 *
	 *<code>
	 * $transaction_id = $steam_connection->predefined_command(
	 *		$my_workroom,
	 *		"get_inventory",
	 *		array(),
	 *		1
	 * 	);
	 *</code>
	 *
	 * @param steam_object $pObject 
	 * @param string $pMethod COAL-command, see sTeam-documentation for further informations
	 * @param mixed $pArgs Array of arguments, see sTeam-documentation for further informations
	 * @param boolean $pBuffer 0 = send now, 1 = buffer command and send later
	 * @return steam_request | integer Depending on the buffer argument either a steam_request instance or a unique transaction id is given back
	 */
	public function predefined_command( $pObject, $pMethod, $pArgs, $pBuffer )
	{
    $request = new steam_request( $this->get_id(), $this->get_transaction_id(), $pObject, array( $pMethod, $pArgs ) );

		if ( $pBuffer == 0 )
		{
      $request = $this->command( $request );
			return $request->get_arguments();
		}
		else
		{
			$this->buffer_command( $request );
			return $request->transactionid;
		}

	}
	
	public function get_version() {
		include_once('phpsteam_version.php');
		return PHPSTEAM_VERSION;
	}
	
	public function get_current_steam_user() {
		return $this->current_steam_user;
	}
	
	public function get_login_status() {
		return $this->login_status;
	}
	
	public function get_sentrequests() {
		return $this->sentrequests;
	}
	
	public function get_globalrequests() {
		return self::$globalRequests;
	}
	
	public function get_globalrequestsmap() {
		return self::$globalRequestsMap;
	}
	
	public function get_globalrequestsTime() {
		return self::$globalRequestsTime;
	}
	
	public function get_login_user_name() {
		return $this->login_user_name;
	}
	
	/** 
	 * function exception:
	 * 
	 * Returns new steam_exception regarding to the given code
	 *
	 * Table of error codes:
	 * 100 - server unknown
	 * 110 - could not connect to server
	 * 120 - no socket to steam-server
	 * 150 - could not reconnect
	 * 200 - no result from steam
	 *
	 * @param integer $pCode errorcode
   * @param Boolean $allow_backtrace Default is TRUE. Deliver FALSE if the backtrace may contain sensible data like username and password
	 * @param string $pDetails Some details about the error (optional)
	 * @return steam_exception Exception
	 */
	public function exception( $pCode, $pDetails = "", $allow_backtrace = TRUE )
	{
		if ( ! is_integer ( $pCode ) ) throw new ParameterException( "pCode", "integer" );

		switch ( $pCode )
		{
			case 100:
				$e = new steam_exception( 
						$this->get_login_user_name(), 
						"Server unknown: " . $pDetails,
						100,
            $allow_backtrace
						);
				break;

			case 110:
				$e = new steam_exception(
						$this->get_login_user_name(),
						"Could not connect to server: " . $pDetails,
						110,
            $allow_backtrace
						);
				break;

			case 120:
				$msg = "No socket to steam-server";
				if ( ! empty( $pDetails ) )
				{
					$msg .= ", " . $pDetails;
				}
				$e = new steam_exception(
						$this->get_login_user_name(),
						$msg,
						120,
            $allow_backtrace
						);
				break;

			case 150:
				$e = new steam_exception(
						$this->get_login_user_name(),
						"Could not reconnect",
						150,
            $allow_backtrace
						);
				break;

			case 200:
				$e = new steam_exception(
						$this->get_login_user_name(),
						"Got no result back from steam: " . $pDetails,
						200,
            $allow_backtrace
						);
				break;

			default:
				$e = new steam_exception();
				break;
		}
		$e->backtrace[ 0 ] = FALSE;
		return $e;
	}
	
	public function get_login_data()  {
		return $this->login_data;
	}
	
	public function get_server_version() {
		return $this->server_version;
	}
	
	/**
	 * function get_config_value:
	 *
	 * @param $pKey the key of the value to get
	 *
	 * @return
	 */
	function get_config_value( $pKey) {
		return $this->server_config[$pKey];
	}
	
	 /**
	 * function get_steam_group:
	 *
	 * returns the steam group
	 *
	 * @return steam_group steam group
	 */
	public function get_steam_group()
	{
		return $this->steam_group;
	}
	
	/**
	 * function list_server_modules:
	 *
	 * @return
	 */
	public function list_modules()
	{
		return array_keys($this->login_arguments[8]);
	}
	
	/**
	 * function get_database:
	 *
	 * returns the database handler of the server
	 *
	 * @return steam_object the database handler
	 */
	public function get_database()
	{
		return $this->database;
	}
	
	/**
	 * function get_pike_version:
	 *
	 * returns the pike version of the server
	 *
	 * @return string pike version
	 */
	public function get_pike_version()
	{
		return $this->pike_version;
	}
	
	public function get_last_reboot() {
		return $this->last_reboot;
	}
}
?>