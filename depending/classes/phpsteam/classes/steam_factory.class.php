<?php
/**
 * Bundle of factory services inside the PHPsTeam-package
 *
 * All types of objects in the metaphor of virtual knowledge
 * rooms need a connection to the sTeam-server; most of their
 * functionality is implemented there. Therefore, all PHPsTeam-objects
 * need to retain a reference to an instance of steam_connector.
 *
 * Every instance of a steam_object or a subclass of steam_object
 * should be derived from the steam_factory-class by envoking
 * the method new_object.
 *
 * In addition, this class offers several factory-services more.
 *
 * PHP versions 5
 *
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author  Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 *
 */

/**
 * includes
 */
require_once( "steam_types.conf.php" );
require_once( "steam_attributes.conf.php" );


/**
 * steam_factory
 *
 * We use this class mainly to deal with instances of steam_objects.
 * Actually, there is no need to instantiate this class.
 * Use its functions as class-functions, e.g.
 * <code>
 * $my_container = steam_factory::new_object( $steam, $obj_id, CLASS_CONTAINER );
 * $my_inventory = $my_container->get_inventory();
 * </code>
 *
 * @package PHPsTeam
 */
class steam_factory
{
	
	private static $objectCache = array();
	private static $userLookupCache = array();
	private static $groupLookupCache = array();
	private static $pathLookupCache = array();
	
	/**
	 * function get_object:
	 *
	 * returns an instance of steam_object or one of its subclasses
	 *
	 * Please read the file's description of steam_factory.class.php;
	 * due to the purpose that steam_factory should be the central class
	 * for managing all instances of steam_objects, you should envoke
	 * this function for any new steam_object.
	 *
	 * @param steam_connection	the connection to steam is essential
	 * @param int pID if you have an object-id, place it here (optional)
	 * @param int pType see steam_types.conf.php for the type definitions (optional)
	 * @return steam_object new instance of a subclass of steam_object, depending on the type definition parameter
	 */
	public static function get_object( $pSteamConnectorID, $pID = 0, $pType = FALSE )
	{
		if (intval($pID) <= 0) { return 0; }
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		
		$globalID = $pID . "@" . $pSteamConnectorID;
		if (LOW_API_CACHE && isset(self::$objectCache[$globalID])) {
			$object = self::$objectCache[$globalID];
			if ($pType !== FALSE) {
				if (($object->get_type() & $pType)) {
					//error_log("wrong object type. expected $pType got {$object->get_type()} (id: {$pID})");
					//throw new Exception("wrong object type. expected $pType got {$object->get_type()} (id: {$pID})");
					return $object;
				}
			}
			return $object;
		}
		
		if ( $pType === FALSE )
		{
			$obj = new steam_object( $pSteamConnectorID, $pID );
			$pType = steam_connection::get_instance($pSteamConnectorID)->predefined_command(
			$obj,
				"get_object_class",
			array(),
			0
			);
		}
		switch( TRUE )
		{
			case ( ( $pType  & CLASS_USER )  == CLASS_USER ):
				$obj = new steam_user( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_GROUP ) == CLASS_GROUP ):
				$obj = new steam_group( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_CALENDAR ) == CLASS_CALENDAR ):
				$obj = new steam_calendar( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_ROOM ) == CLASS_ROOM ):
				$obj = new steam_room( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_TRASHBIN ) == CLASS_TRASHBIN ):
				$obj = new steam_trashbin( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType  & CLASS_CONTAINER ) == CLASS_CONTAINER ):
				$obj = new steam_container( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_EXIT ) == CLASS_EXIT ):
				$obj = new steam_exit( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_DOCEXTERN ) == CLASS_DOCEXTERN ):
				$obj = new steam_docextern( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_LINK ) == CLASS_LINK ):
				$obj = new steam_link( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_DOCWIKI ) == CLASS_DOCWIKI ):
				$obj = new steam_wiki( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_DOCUMENT ) == CLASS_DOCUMENT ):
				$obj = new steam_document( $pSteamConnectorID, $pID );
				break;
			case ( ($pType & CLASS_DATE ) == CLASS_DATE ):
				$obj = new steam_date( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_MESSAGEBOARD ) == CLASS_MESSAGEBOARD ):
				$obj = new steam_messageboard( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_SCRIPT ) == CLASS_SCRIPT ):
				$obj = new steam_script( $pSteamConnectorID, $pID );
				break;
			case ( ( $pType & CLASS_DATABASE ) == CLASS_DATABASE ):
				$obj = new steam_database( $pSteamConnectorID, $pID );
				break;
			default:
				$obj = new steam_object( $pSteamConnectorID, $pID, $pType );
				break;
		}
		//cache in
		self::$objectCache[$globalID] = $obj;
		return $obj;
	}
	
	public static function prefetch($pSteamConnectorID, $pObject, $pIventory = false, $pDepanding = false, $pBuffer = 0) {
		$clientSupport = steam_connection::get_instance($pSteamConnectorID)->get_module("package:clientsupport");
		if (is_object($clientSupport)) {
			$objectData = $GLOBALS['STEAM']->predefined_command($clientSupport, "query_object_data", array($pObject, $pIventory, $pDepanding), $pBuffer);
			$objects = $objectData["objects"];
			foreach ($objects as $id => $object) {
				(!API_DEBUG) or error_log("prefetched: " . $id);
				$steam_object = self::get_object($pSteamConnectorID, $id, $object["object_class"]);
				$steam_object->set_values($object["attributes"]);
				$steam_object->set_prefetched();
				(!API_DEBUG) or error_log(count($steam_object->get_values(), true));
				if ($steam_object instanceof steam_user) {
					self::setUserCache($object["attributes"]["OBJ_NAME"],$steam_object);
				} else if ($steam_object instanceof steam_group) {
					self::setGroupCache($object["attributes"]["OBJ_NAME"],$steam_object);
				}
			}
		}
	}

	/**
	 * function path_to_object:
	 *
	 * Returns an object instance by its full filepath
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-Server
	 * @param string $pPath Full path to object
	 * @return steam_object an instance of the object
	 */
	public static function path_to_object( $pSteamConnectorID, $pPath, $pBuffer = 0 )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_connector::get_instance($pSteamConnectorID)->predefined_command(
		steam_connector::get_instance($pSteamConnectorID)->get_module( "filepath:tree" ),
				"path_to_object",
		array( $pPath ),
		$pBuffer
		);
	}

	/**
	 * function get_object_by_name:
	 *
	 * Alias for public static function path_to_object
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-Server
	 * @param string $pPath Full path to object
	 * @return steam_object an instance of the object
	 */
	public static function get_object_by_name( $pSteamConnectorID, $pPath, $pBuffer = 0 )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::path_to_object( $pSteamConnectorID, $pPath, $pBuffer );
	}

	/**
	 * function get-user:
	 *
	 * Returns a steam_user instance by its login-name
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pUserName user's login name
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return steam_user the user object
	 */
	public static function get_user( $pSteamConnectorID, $pUserName,  $pBuffer = 0)
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::username_to_object( $pSteamConnectorID, $pUserName, $pBuffer);
	}

	/**
	 * function username_to_object:
	 *
	 * Returns a steam_user instance by its login-name
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pUserName user's login name
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return steam_user the user object
	 */
	public static function username_to_object( $pSteamConnectorID, $pUserName, $pBuffer = 0)
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		if (!isset(self::$userLookupCache[$pUserName])) {
			$result = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
										steam_connector::get_instance($pSteamConnectorID)->get_module("users"),
												"lookup",
										array( $pUserName ),
										$pBuffer
					   );
			if ($result instanceof steam_user) {
				self::$userLookupCache[$pUserName] = $result;
			}
		} else {
			$result = self::$userLookupCache[$pUserName];
		}
		return $result;
	}

	/**
	 * function get-user:
	 *
	 * Returns a steam_user instance by its login-name
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pGroupName group name
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return steam_user the user object
	 */
	public static function get_group( $pSteamConnectorID, $pGroupName,  $pBuffer = 0)
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::groupname_to_object( $pSteamConnectorID, $pGroupName, $pBuffer);
	}

	/**
	 * function groupname_to_object:
	 *
	 *@param $pSteamConnector
	 *@param $pGroupName
	 *@param Boolean $pBuffer Send now or buffer request?
	 *
	 *@return
	 */
	public static function groupname_to_object( $pSteamConnectorID, $pGroupName, $pBuffer = 0 ) {
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		if (!isset(self::$groupLookupCache[$pGroupName])) {
			$result = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
								steam_connector::get_instance($pSteamConnectorID)->get_module( "groups" ),
										"lookup",
								array( $pGroupName ),
								$pBuffer
					   );
			if ($result instanceof steam_group) {
				self::$groupLookupCache[$pGroupName] = $result;
			}
		} else {
			$result = self::$groupLookupCache[$pGroupName];
		}
		return $result;
	}

	/**
	 * function load_attributes:
	 *
	 * Sets the values of several object instances in order to
	 * the given attributes
	 * <code>
	 * steam_factory::load_attributes( $steam_con, $inventory, new array(
	 *	"SMT_COURSE_ID",
	 *	"SMT_COURSE_NAME",
	 *	"SMT_COURSE_SUPERVISOR",
	 *	"SMT_COURSE_DESCRIPTION" )
	 * );
	 * </code>
	 *
	 * @param steam_connector $pSteamConnector A connection to a sTeam-server
	 * @param mixed $pObjects The object instances with the unknown attributes
	 * @param mixed $pAttributes An array of unknown attribute names
	 */
	public static function load_attributes( $pSteamConnectorID, $pObjects, $pAttributes )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$tids = array();
		foreach( $pObjects as $object )
		{
			$tids[ $object->get_id() ] = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
			$object,
				"query_attributes",
			array($pAttributes),
			1
			);
		}
		$result = steam_connector::get_instance($pSteamConnectorID)->buffer_flush();

		foreach( $pObjects as $object )
		{
			$i = 0;
			$attributes = array();
			foreach( $pAttributes as $key ) {
				$attributes[$key] = $result[ $tids[$object->get_id()] ][ $i ];
				$i++;
			}
			$object->set_values( $attributes );
		}
	}

	/**
	 * function load_attributes:
	 *
	 * Sets the values of several object instances in order to
	 * the given attributes
	 * <code>
	 * steam_factory::load_attributes( $steam_con, $inventory, new array(
	 *	"SMT_COURSE_ID",
	 *	"SMT_COURSE_NAME",
	 *	"SMT_COURSE_SUPERVISOR",
	 *	"SMT_COURSE_DESCRIPTION" )
	 * );
	 * </code>
	 *
	 * @param steam_connector $pSteamConnector A connection to a sTeam-server
	 * @param mixed $pObjects The object instances with the unknown attributes
	 * @param mixed $pAttributes An array of unknown attribute names
	 */
	public static function get_attributes( $pSteamConnectorID, $pObjects, $pAttributes ) {
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$tids = array();
		foreach( $pObjects as $object )
		{
			$tids[ $object->get_id() ] = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
			$object,
				"query_attributes",
			array($pAttributes),
			TRUE
			);
		}
		$result = steam_connector::get_instance($pSteamConnectorID)->buffer_flush();

		$res = array();
		foreach( $pObjects as $object )
		{
			$i = 0;
			$res[$object->get_id()] = array();
			foreach( $pAttributes as $key ) {
				$res[$object->get_id()][$key] = $result[ $tids[$object->get_id()] ][ $i ];
				$i++;
			}
		}
		return $res;
	}


	/**
	 * function create_copy
	 *
	 * @param $pSteamConnector
	 * @param $pObject
	 * @param $pBuffer
	 *
	 * @return
	 */
	public static function create_copy( $pSteamConnectorID, $pObject, $pBuffer = 0 )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		if ( ($pObject->get_type() & CLASS_CONTAINER) || ($pObject->get_type() & CLASS_ROOM) )
		{
			$copy_recursively = TRUE;
		}
		else
		{
			$copy_recursively = FALSE;
		}
		return steam_connector::get_instance($pSteamConnectorID)->predefined_command(
		$pObject,
			"duplicate",
		array( $copy_recursively ),
		$pBuffer
		);
	}

	/**
	 * function create_object:
	 *
	 * Creates a new object inside the virtual space managed by sTeam
	 * @param $pSteamConnector
	 * @param $pName
	 * @param $pClass
	 * @param $pEnviroment
	 * @param $pArguments
	 *
	 * @return steam_object New steam_object
	 */
	public static function create_object( $pSteamConnectorID, $pName, $pClass, $pEnvironment = FALSE, $pArguments = array() )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$modules = steam_connector::get_instance($pSteamConnectorID)->get_login_data()->get_arguments();
		$steam_factory = $modules[ 9 ][ $pClass ];
		$arguments = array_merge(
		array("name" => $pName),
		$pArguments
		);
		$obj = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
		$steam_factory,
			"execute",
		$arguments,
		0
		);
		if ( $pEnvironment != FALSE )
		{
			$obj->move( $pEnvironment );
		}
		return $obj;
	}

	/**
	 * function create_group:
	 *
	 * Creates a new group
	 * @param steam_connector $pSteamConnector Connection to sTeam
	 * @param string $pName Name of the group
	 * @param steam_group $pParentGroup The group, where all future members are in
	 * @return steam_group An instance of the new group
	 */
	public static function create_group( $pSteamConnectorID, $pName, $pParentGroup, $pEnvironment = FALSE, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$pParentGroup->drop_subGroupsLookupCache();
		return steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_GROUP,
		$pEnvironment,
		array( "parentgroup" => $pParentGroup,
				"attributes" => array(OBJ_DESC => $pDescription) )
		);
	}

	/**
	 * function create_container:
	 *
	 * Creates a new container
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pName container's name
	 * @param steam_container $pEnvironment room or container where the new container should be created in
	 * @return steam_container
	 */
	public static function create_container( $pSteamConnectorID, $pName, $pEnvironment, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_CONTAINER,
		$pEnvironment,
		array( "attributes" => array(OBJ_DESC => $pDescription) )
		);
	}

	/**
	 * function create_room:
	 *
	 * Creates a new room
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pName room's name
	 * @param steam_room $pEnvironment room, where the new room should be created in
	 * @return steam_room
	 */
	public static function create_room( $pSteamConnectorID, $pName, $pEnvironment, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_ROOM,
		$pEnvironment,
		array( "attributes" => array(OBJ_DESC => $pDescription) )
		);
	}

	public static function create_calendar( $pSteamConnectorID, $pName, $pEnvironment, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_CALENDAR,
		$pEnvironment,
		array( "attributes" => array( OBJ_DESC => $pDescription ) )
		);
	}
	/**
	 * function create_link:
	 *
	 * Creates a new link to an object
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pObject Original object to which the link should refer to
	 * @param boolean $pBuffer send command now or later
	 * @return steam_link
	 */
	public static function create_link( $pSteamConnectorID, $pObject, $pBuffer = 0 )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$modules = steam_connector::get_instance($pSteamConnectorID)->get_login_data()->get_arguments();
		$steam_factory = $modules[ 9 ][ CLASS_LINK ];
		if (($pObject->get_type() & CLASS_LINK) == CLASS_LINK )
		{
			$pObject = $pObject->get_source_object();
		}
		$obj = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
		$steam_factory,
			"execute",
		array(
				"name" => $pObject->get_name(), 
				"link_to" => $pObject 
		),
		$pBuffer
		);
		return $obj;
	}

	public static function create_exit( $pSteamConnectorID, $pObject, $pBuffer = 0 )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$modules = steam_connector::get_instance($pSteamConnectorID)->get_login_data()->get_arguments();
		$steam_factory = $modules[ 9 ][ CLASS_EXIT ];
		if ( $pObject->get_type() & CLASS_EXIT == CLASS_EXIT )
		{
			$pObject = $pObject->get_source_object();
		}
		$obj = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
		$steam_factory,
			"execute",
		array(
				"name" => $pObject->get_name(), 
				"exit_to" => $pObject 
		),
		$pBuffer
		);
		return $obj;
	}

	/**
	 * function create_messageboard
	 *
	 * @param $pSteamConnector
	 * @param $pName
	 * @param $pEnviroment
	 * @param $pDescription
	 *
	 * @return
	 */
	public static function create_messageboard( $pSteamConnectorID, $pName, $pEnvironment = FALSE, $pDescription = "" )
	{
		$messageboard =  steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_MESSAGEBOARD,
		$pEnvironment,
		array(
                "attributes" => array(OBJ_DESC => $pDescription),
				"entries"  => array()
		)
		);
		return $messageboard;
	}

	/**
	 * function create_wiki
	 *
	 * @param $pSteamConnector
	 * @param $pName
	 * @param $pEnviroment
	 * @param $pDescription
	 *
	 * @return
	 */
	public static function create_wiki( $pSteamConnectorID, $pName, $pEnvironment = FALSE, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return  steam_factory::create_document(
		$pSteamConnectorID,
		$pName,
            "",
            "text/wiki",
		$pEnvironment,
		$pDescription
		);
	}

	/**
	 *function create_textdoc:
	 *
	 * @param $pSteamConnector
	 * @param $pName
	 * @param $pContent
	 * @param $pEnviroment
	 * @param $pDescription
	 *
	 * @return
	 */
	public static function create_textdoc( $pSteamConnectorID, $pName, $pContent, $pEnvironment = FALSE, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return  steam_factory::create_document(
		$pSteamConnectorID,
		$pName,
		$pContent,
			"text/plain",
		$pEnvironment,
		$pDescription
		);
	}

	/**
	 * function create_document:
	 *
	 * Creates a new document
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pName object's name
	 * @param string $pMimeType document's mime type
	 * @param steam_container $pEnvironment room or container where the document should be created
	 * @return steam_document
	 */
	public static function create_document( $pSteamConnectorID, $pName, $pContent, $pMimeType, $pEnvironment = FALSE, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$textdoc = steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_DOCUMENT,
		$pEnvironment,
		array( "mimetype" => $pMimeType,
				     "attributes" => array(OBJ_DESC => $pDescription) )
		);
		$textdoc->set_content( $pContent );
		return $textdoc;
	}
	/**
	 * function create_docextern:
	 *
	 * Creates a new url-document
	 *
	 * @param steam_connector $pSteamConnector connection to sTeam-server
	 * @param string $pName title of the URL
	 * @param string $pUrl the internet-address
	 * @param steam_container $pEnvironment room or container where the document should be created
	 * @return steam_docextern
	 */
	public static function create_docextern( $pSteamConnectorID, $pName, $pUrl, $pEnvironment = FALSE, $pDescription = "" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		return steam_factory::create_object(
		$pSteamConnectorID,
		$pName,
		CLASS_DOCEXTERN,
		$pEnvironment,
		array( "url" => $pUrl,
				     "attributes" => array(OBJ_DESC => $pDescription) )
		);
	}

	/**
	 * function create_user:
	 *
	 * Creates a new user and returns its activation code
	 *
	 * Please keep in mind, that you will need extended rights
	 * to execute this function, that means a steam_connector
	 * with an administrator login.
	 *
	 * Suggestion: Divide the registration and activation
	 * process, if you want to be sure about the existence
	 * of the user's e-mail-address; send the activation-code
	 * via e-mail...
	 *
	 * Example for registration and activation:
	 * <code>
	 * $activation_code = steam_factory::create_user(
	 *		$steam_con,
	 *		"nbates",
	 *		"mother",
	 *		"norman@bates-motel.com",
	 *		"Norman",
	 *		"Bates",
	 *		"english"
	 * 		);
	 * if ( $activation_code )
	 * {
	 *	$new_user = steam_factory::username_to_object( "nbates" );
	 *	$new_user->set_attributes(
	 *		array(
	 *			"country" => "United States",
	 *			"occupation" => "motel keeper"
	 *		)
	 *	);
	 *	if ( $new_user->activate( $activation_code ) )
	 *	{
	 *		print( "Bates, you can login now!" );
	 *	}
	 * }
	 * else
	 * {
	 *	print( "Login name exists. Choose another one." );
	 * }
	 * </code>
	 *
	 * @see steam_user->activate()
	 * @param steam_connector $pSteamConnector connection to sTeam-server, for creating new users you will need extended rights
	 * @param string $pLogin user's login name
	 * @param string $pPassword user's password
	 * @param string $pEMail user's email
	 * @param string $pFullname user's surname
	 * @param string $pFirstname user's firstname
	 * @param string $pLanguage user's prefered language (optional)
	 * @return string activation code; needed to activate this login
	 */
	public static function create_user( $pSteamConnectorID, $pLogin, $pPassword, $pEMail, $pFullname, $pFirstname, $pLanguage = "english" )
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		$new_user = steam_factory::create_object(
			$pSteamConnectorID,
			$pLogin,
			CLASS_USER,
			FALSE,
			array(
				"name"		=> (string) $pLogin,
				"pw"		=> (string) $pPassword,
				"email"		=> (string) $pEMail,
				"fullname"	=> (string) $pFullname,
				"firstname"	=> (string) $pFirstname,
				"language"	=> (string) $pLanguage
			)
		);
		if ( $new_user )
		{
      $factories = steam_connector::get_instance($pSteamConnectorID)->get_login_data()->get_arguments();
			$user_factory = $factories[ 9 ][ CLASS_USER ];
			$activation_code = steam_connector::get_instance($pSteamConnectorID)->predefined_command(
				$user_factory,
				"get_activation",
				array(),
				0
			);
			return $activation_code;
		}
		else
		{
			return FALSE;
		}
	}
	
	public static function setUserCache($userName, $steam_user) {
		self::$userLookupCache[$userName] = $steam_user;
	}
	
	public static function setGroupCache($groupName, $steam_group) {
		self::$groupLookupCache[$groupName] = $steam_group;
	}
}

?>