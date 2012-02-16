<?php
/**
 * Root class for all objects in the metaphor of virtual knowledge rooms
 *
 * The metaphor of virtual knowledge rooms describes an object model and
 * methods, how those objects - as parts of the metaphor - can interact.
 * The root class steam_object keeps general functions for all subclasses
 * ready. That is, why all other classes derived from steam_object.
 *
 * PHP versions 5
 *
 * @package	PHPsTeam
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author	Alexander Roth <aroth@it-roth.de>, Daniel Buese <dbuese@upb.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 *
 */
require_once( "steam_types.conf.php" );

/**
 * Root class for all objects in the metaphor of virtual knowledge rooms
 *
 * The metaphor of virtual knowledge rooms describes an object model and
 * methods, how those objects - as parts of the metaphor - can interact.
 * The root class steam_object keeps general functions for all subclasses
 * ready. That is, why all other classes derived from steam_object.
 *
 * @package     PHPsTeam
 */
class steam_object implements Serializable {
	/**
	 * Unique id for this object inside the virtual space, which is
	 * assigned by a sTeam-server.
	 */
	protected $id;

	/**
	 * Binary string which defines the type of object.
	 * @see steam_types.conf.php for more details about the types.
	 */
	protected $type = CLASS_OBJECT;

	/**
	 * Array of attributes, whereas the key equals the attribute name
	 * in sTeam. This array is filled, each time the get_attributes() method
	 * delivers new values for this object from sTeam.
	 */
	protected $attributes = array();

	/**
	 * Array of additional values, where some additional information may be
	 * stored. This feature will be used by some special methods only
	 * to cut down server requests
	 * (@see steam_container::get_inventory_data)
	 * delivers additional values for this object.
	 */
	protected $additional = array();

	
	private $prefetched = false;
	
	/**
	 * ID of steam_connector. Connection to sTeam-server
	 */
	public $steam_connectorID;

	/**
	 * Initialization of steam_object. The arguments are stored
	 * as class variables.
	 *
	 * @param steam_connector $pSteamConnector The connection to a sTeam-server
	 * @param integer $pID unique object ID inside the virtual space (optional)
	 */
	public function __construct( $pSteamConnectorID, $pID = "0")
	{
		$s = debug_backtrace();
		if ($s[1]['class'] !== "steam_factory") {
			error_log("phpsteam error: direct construtor-call not allowed ({$s[1]['class']})");
			throw new Exception("direct construtor-call not allowed ({$s[1]['class']})");
		}
		if (!is_string($pSteamConnectorID)) throw new ParameterException( "pSteamConnectorID", "string" );
		$this->id 	= (int) $pID;
		$this->steam_connectorID = $pSteamConnectorID;
	}
	
	public function get_type() {
		return CLASS_OBJECT;
	}

	public function __toString() {
		return "#" . $this->get_id();
	}
	
	/**
	 * function get_id:
	 *
	 * returns the unique object id
	 * @return integer unique object id
	 */
	public function get_id()
	{
		return $this->id;
	}

	public function serialize() {
		return serialize(array($this->id, $this->steam_connectorID));
	}
		
	public function unserialize($data) {
		$values = unserialize($data);
		$this->id = $values[0];
		$this->steam_connectorID = $values[1];
	}

	/**
	 * function get_object_class:
	 *
	 * Queries and returns the object's type according to a bitmask
	 * of CLASS_* constants. In contrast to get_type(), this can be
	 * a mixture of several CLASS_* types, whereas get_type() only
	 * returns the major type.
	 *
	 * @see steam_types.conf.php
	 *
	 * Example:
	 *   $user->get_type() == CLASS_USER
	 *   $user->get_object_class() == CLASS_USER|CLASS_CONTAINER|CLASS_OBJECT
	 *
	 * @return int CLASS_* constant bitmask for this object
	 */
	public function get_object_class($pBuffer = FALSE)
	{
		return $this->steam_command($this, "get_object_class", array(),	$pBuffer);
	}

	/**
	 *  Returns all attributes of this object
	 *  Note: This method doesnt fill the object cache on PHP side
	 *
	 */
	public function get_all_attributes( $pBuffer = FALSE ) {
		return $this->steam_command($this, "query_attributes", 0, $pBuffer);
	}

	/**
	 * function get_attributes:
	 *
	 * returns the object's attributes you have asked for
	 *
	 * First, the class variable $attributes is analized,
	 * if some of the requested attributes are gotten from
	 * sTeam in advance.
	 * The others are got from the sTeam-server and are added
	 * to the class variable $attributes.
	 * The values of attributes, defined in the argument
	 * $pAttributes, are returned.
	 *
	 * @param mixed $pAttributes List of attributes' names
	 * @return mixed Array of attributes'names and their values
	 */
	public function get_attributes( $pAttributes = array(), $pBuffer = FALSE, $pFollowLinks = FALSE )
	{
		if (!$pBuffer) {
			if ( count( $pAttributes ) == 0 )  {
				return $this->strip_tags_array($this->attributes);
			}
			$known_attributes = array();
			$unknown_attributes = array();
			$result = array();
			foreach( $pAttributes as $key ) {
				if ( ! array_key_exists( $key, $this->attributes ) ) {
					$unknown_attributes[ $key ] = "";
				}
				else {
					$known_attributes[ $key ] = $this->attributes[ $key ];
				}
			}
			if ( count( $unknown_attributes ) > 0 ) {
				if ( $pFollowLinks && ( ( CLASS_LINK & $this->get_type() ) == CLASS_LINK ) )
				{
					$object = $this->get_source_object();
					$source_id = $object->get_id();				                        
				}
					else {
						$object = $this;
						$source_id = 0;
					}
					$result = $this->steam_command(
					$object,
                              "query_attributes",
					array( $unknown_attributes ),
					0
					);
					$this->attributes = array_merge( $this->attributes, $result );
					return $this->strip_tags_array(array_merge( $known_attributes, $result ));
			}
			else return $this->strip_tags_array($known_attributes);
		}
		else {  // Use the buffer
			// we need a mapping instead of an array here,
			// so convert it
			$pAttr = array();
			foreach( $pAttributes as $key ) {
				$pAttr[ $key ] = "";
			}
			return $this->strip_tags_array($this->get_steam_connector()->buffer_attributes_request(
			$this,
			$pAttr,
			0
			));
		}
	}

	private function strip_tags_array($array) {
		if (!is_array($array)) {
			return $array;
		}
		$keys = array_keys($array);
		foreach ($keys as $key) {
			$value = $array[$key];
			if (is_string($value)) {
				$array[$key] = strip_tags($value);
			}
		}
		return $array;
	}

	/**
	 * function get_cached_attributes
	 *
	 * returns the local attribute cache for of this object
	 *
	 * @return mixed the local attribute cache for this object
	 */
	public function get_cached_attributes() {
		return $this->attributes;
	}

	/**
	 * function get_attribute_cached
	 *
	 * if the value for the given key is locally cached, the value is
	 * returned. If the value of the given key wasnt locally cached, FALSE
	 * will be returned.
	 * This method is useful if you want to ensure that no server call
	 * was made after pre-loading attributes. (very valuable in combination
	 * with steam_container->get_inventory_data())
	 * @return mixed the local attribute cache for this object
	 */
	public function get_attribute_cached($key) {
		if (!array_key_exists($key, $this->attributes)) 
			return FALSE;
		return $this->attributes[$key];
	}

	/**
	 * function get_attribute
	 *
	 * This funktion returns the attribute you asked for
	 *
	 *Example:
	 *<code>
	 *$myObject->get_attribute(NAME_OF_THE_ATTRIBUTE);
	 *</code>
	 *
	 * @param $pAttribute constant of attribute name
	 *
	 * @return mixed the attribute you asked for
	 */
	public function get_attribute( $pAttribute, $pBuffer = FALSE)
	{
		if ($pBuffer) {
			(!API_DEBUG) or error_log("query_attribute with buffer");
			$value = $this->steam_command($this, "query_attribute", array( $pAttribute ), $pBuffer);
			if (is_string($value)) {
				$value = strip_tags($value);
			}
			return $value;
		}
		else {
			if (!$this->is_prefetched() && !isset($this->attributes[$pAttribute])){
				(!API_DEBUG) or error_log("query_attribute without buffer");
				$this->attributes[ $pAttribute ] = $this->steam_command($this, "query_attribute", array( $pAttribute ), 0);
			}
			$value = isset($this->attributes[$pAttribute]) ? $this->attributes[$pAttribute] : 0;
			if (is_string($value)) {
				$value = strip_tags($value);
			}
			return $value;
		}
	}

	/**
	 *function get_attribute_names:
	 *
	 * this function returns the names of the attributes from
	 * the object on wich is was invoked
	 *
	 *Example:
	 *<code>
	 *$attributeNames = $myObject->get_attribute_names();
	 *</code>
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return  array with the attribute names
	 */
	public function get_attribute_names($pBuffer = 0){
		if ($this->is_prefetched()) {
			$result = array_keys($this->attributes);
		} else {
			$result = $this->steam_command($this, "get_attribute_names", array(), $pBuffer);
		}
		return $result;
	}

	/**
	 *function get_path:
	 *
	 *This function returns the path of the object you
	 *asked for
	 *
	 *Example:
	 *<code>
	 *$myObject->get_path();
	 *</code>
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return string path of the object
	 */
	public function get_path($pBuffer = FALSE){
		$modules = $this->get_steam_connector()->get_login_data()->get_arguments();
		$result = $this->steam_command($modules[8][ "filepath:tree" ], "object_to_filename", array( $this ), $pBuffer);
		return $result;
	}

	/**
	 * function get_name:
	 *
	 * Returns the name of this object
	 *
	 *Example:
	 *<code>
	 *$name_of_the_object = $myObject->get_name();
	 *</code>
	 * @return string Name of the object
	 */
	public function get_name( $pBuffer = FALSE ){
		return $this->get_attribute("OBJ_NAME", $pBuffer);
	}

	/**
	 * function set_name:
	 *
	 * This function sets the name of the object on which is was invoked
	 *
	 *Example:
	 *<code>
	 *$myObject->set_name(name_of_the_object);
	 *</code>
	 *
	 * @param string $pName the name you want to give to the object
	 * @param $pValue
	 *
	 * @return
	 */
	public function set_name($pName, $pValue = 0){
		return $this->set_attribute("OBJ_NAME", $pName, $pValue);
	}

	/**
	 *function get_references
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_references($pBuffer = 0){
		return $this->steam_command($this, "get_referencing", array(), $pBuffer);
	}

	/**
	 *function get_steam_connector:
	 *
	 */
	public function get_steam_connector(){
		return steam_connector::get_instance($this->steam_connectorID);
	}

	/**
	 *function unlock_attribute:
	 *
	 * @param $pKey
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function unlock_attribute($pKey, $pBuffer = 0){
		return $this->steam_command($this,"unlock_attribute",array((string) $pKey), $pBuffer);
	}
	
	/**
	 *function lock_attribute:
	 *
	 * @param $pKey
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function lock_attribute($pKey, $pBuffer = 0){
		return $this->steam_command($this,"lock_attribute",array((string) $pKey), $pBuffer);
	}
	
	/**
	 *function is_locked:
	 *
	 * @param $pKey
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function is_locked($pKey, $pBuffer = 0){
		return $this->steam_command($this,"is_locked",array((string) $pKey), $pBuffer);
	}

	/**
	 *function get_identifier:
	 *
	 * @param $pBuffer
	 *
	 * @return The objects identifier
	 */
	public function get_identifier($pBuffer = FALSE){
		return $this->steam_command($this, "get_identifier", array(), $pBuffer);
	}

	/**
	 *function unlock_attribures:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function unlock_attributes($pBuffer = 0){
		return $this->steam_command($this, "unlock_attributes", array(), $pBuffer);
	}

	/**
	 * function set_attribute:
	 *
	 * Sets new attribute value
	 *
	 * @param string $pAttribute attribute key.
	 * @param mixed $pValue attribute value
	 * @param $pBuffer 0= send now, 1 = buffer command and send later
	 */
	public function set_attribute( $pAttribute, $pValue, $pBuffer= 0 )
	{
		$pValue = ( is_string( $pValue ) ) ? strip_tags(stripslashes( $pValue )) : $pValue;
		return $this->set_attributes(array($pAttribute => $pValue ), $pBuffer);
	}

	/**
	 * function delete_attribute:
	 *
	 * Deletes an object attribute
	 *
	 * @param string $pAttribute attribute key
	 * @return void
	 */
	public function delete_attribute( $pAttribute, $pBuffer = 0 )
	{
		unset($this->attributes[$pAttribute]);
		return $this->steam_command(
		$this,
																"set_attribute",
		array( $pAttribute ),
		$pBuffer
		);
	}

	/**
	 * function set_attributes:
	 *
	 * Sets new attributes
	 *
	 * @param mixed $pAttributes New attributes and their values.
	 */
	public function set_attributes( $pAttributes, $pBuffer= 0 )
	{
		$pAttributes = $this->strip_tags_array($pAttributes);
		$this->attributes = array_merge( $this->attributes, $pAttributes );
		return $this->steam_command(
		$this,
																"set_attributes",
		array($pAttributes),
		$pBuffer
		);
	}

	/**
	 * function set_additional_values:
	 *
	 * @param $pValues
	 *
	 */
	public function set_additional_values( $pValues ) {
		$this->additional = array_merge( $this->additional, $pValues );
	}
		
	/**
	 * function get_additional_values:
	 *
	 * @param $pValues
	 *
	 */
	public function get_additional_values() {
		return $this->additional;
	}

	/**
	 * function get_values
	 *
	 * @return $pValues
	 *
	 */
	public function get_values()
	{
		return $this->attributes;
	}

	/**
	 * function set_values
	 *
	 * @param $pValues
	 *
	 */
	public function set_values( $pValues )
	{
		$this->attributes = array_merge( $this->attributes, $pValues );
	}

	/**
	 * function set_value:
	 * set single attribute cache value for this object
	 * @param $pKey
	 * @param $pValue
	 *
	 */
	public function set_value( $pKey, $pValue )
	{
		$this->attributes [$pKey] = $pValue;
	}

	/**
	 * function set_value:
	 * set single attribute cache value for this object
	 * @param $pKey
	 * @param $pValue
	 *
	 */
	public function delete_value( $pKey )
	{
		unset($this->attributes [$pKey]);
	}

	/**
	 * function steam_command:
	 *
	 * Generates COAL-command from scratch
	 *
	 * For programmer's convenience; the actual function
	 * is implemented in the steam_connector class.
	 * Look there for more details.
	 *
	 * <b>ATTENTION: Function is needed when you want to implement
	 * new COAL-commands!</b>
	 * @see steam_connector:predefined_command()
	 * @param steam_object $pObject
	 * @param string $pMethod COAL-command, see sTeam-documentation for further informations
	 * @param mixed $pArgs Array of arguments, see sTeam-documentation for further informations
	 * @param boolean $pBuffer 0 = send now, 1 = buffer command and send later
	 * @return steam_request | integer Depending on the buffer argument either a steam_request instance or a unique transaction id is given back
	 */
	protected function steam_command( $pObject, $pMethod, $pArgs, $pBuffer = 0 )
	{

		return $this->get_steam_connector()->predefined_command(
		$pObject,
		$pMethod,
		$pArgs,
		$pBuffer
		);
	}

	/**
	 * function steam_buffer_flush:
	 *
	 * See steam_connector::buffer_flush for more information.
	 * @see steam_connector::buffer_flush
	 */
	protected function steam_buffer_flush()
	{
		return $this->get_steam_connector()->buffer_flush();
	}

	/**
	 * function get_enviroment:
	 *
	 * Returns the steam_object which acts as an environment for this object
	 *
	 * Due to the metaphor of virtual knowledge rooms, most objects
	 * are located in a room (or a container in general). This function
	 * determines the environment for this object and returns an instance
	 * of the corresponding subclass of steam_object.
	 *
	 * @return steam_object the environment of this object
	 */
	public function get_environment ()
	{
		return $this->steam_command(
		$this,
			"get_environment",
		array(),
		0
		);
	}

	/**
	 * function get_root_environment:
	 *
	 * Returns the steam_object which is the (recursive) root environment of
	 * this object.
	 *
	 * Note: Available on server with version >= 2.7.18, throws exception if
	 * server version is less than 2.7.18.
	 *
	 * Due to the metaphor of virtual knowledge rooms, most objects
	 * are located in a room (or a container in general). This function
	 * determines the root environment for this object and returns an instance
	 * of the corresponding subclass of steam_object.
	 *
	 * @return steam_object the root environment of this object
	 */
	public function get_root_environment ()
	{
		// check for server version 2.7.18
		$version = $this->get_steam_connector()->get_server_version();
		list($major, $minor, $micro) = explode(".", $version);
		if (((int)$major < 2) || ((int)$major == 2 && (int)$minor < 7) || ((int)$major == 2 && (int)$minor == 7 && (!isset($micro) ||  (int)$micro < 18) )) {
			throw new steam_exception( $this->get_steam_connector()->get_login_user_name(), "Error: get_root_environment is not available on servers with version < 2.7.18 (actual server version is " . $version . ").", 404 );
		}
		return $this->steam_command(
		$this,
			"get_root_environment",
		array(),
		0
		);
	}

	/**
	 * function get_creator:
	 *
	 * Returns the user which creates this object
	 * @returns steam_user the creator of this object
	 */
	public function get_creator($pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
																"get_creator",
		array(),
		$pBuffer
		);
	}

	/**
	 * function get_annotations:
	 *
	 * Returns the object's annotations
	 *
	 * In sTeam, objects can be annotated with other
	 * objetcs. This function returns the annotations
	 * of this object.
	 *
	 * @param int $pClass Only return annotations of this class /
	 *   these classes
	 * @return mixed Array of steam_objects
	 */
	public function get_annotations( $pClass = FALSE, $pBuffer = FALSE )
	{
		if ( ! $pClass )
		return $this->steam_command(
		$this,
				"get_annotations",
		array(),
		$pBuffer
		);
		else
		return $this->steam_command(
		$this,
				"get_annotations_by_class",
		array( (int) $pClass ),
		$pBuffer
		);
	}

	/**
	 * Returns the annotations of this object, optionally filtered by object
	 * class, attribute values or pagination.
	 * The description of the filters and sort options can be found in the
	 * filter_objects_array() function of the "searching" module of open-sTeam.
	 *
	 * Example:
	 * Return all the annotations that have been created or last modified by user
	 * "root" in the last 24 hours, recursively and sorted by modification date
	 * (newest first) and return only the first 10 results:
	 * get_inventory_filtered(
	 *   array(  // filters:
	 *     array( '-', '!class', CLASS_DOCUMENT ),
	 *     array( '-', 'attribute', 'DOC_LAST_MODIFIED', '<', time()-86400 ),
	 *     array( '+', 'function', 'get_creator', '==', steam_factory::get_user( $GLOBALS['STEAM'], 'root' ) ),
	 *     array( '+', 'attribute', 'DOC_USER_MODIFIED', '==', steam_factory::get_user( $GLOBALS['STEAM'], 'root' ) ),
	 *   ),
	 *   array(  // sort:
	 *     array( '>', 'attribute', 'DOC_LAST_MODIFIED' )
	 *   ), 0, 10 );
	 *
	 * @param pFilters (optional) an array of filters (each an array as described
	 *   in the "searching" module) that specify which objects to return
	 * @param pSort (optional) an array of sort entries (each an array as described
	 *   in the "searching" module) that specify the order of the items
	 * @param pOffset (optional) only return the objects starting at (and including)
	 *   this index
	 * @param pLength (optional) only return a maximum of this many objects
	 * @param pMaxDepth (optional) max recursion depth (0 = only return
	 *   annotations of this object)
	 * @return an array of objects that match the specified filters, sort order and
	 *   pagination.
	 */
	public function get_annotations_filtered ( $pFilters = array(), $pSort = array(), $pOffset = 0, $pLength = 0, $pMaxDepth = 0, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
			"get_annotations_filtered",
		array( $pFilters, $pSort, $pOffset, $pLength, $pMaxDepth ),
		$pBuffer
		);
	}

	public function get_annotating($pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
				"get_annotating",
		array(),
		$pBuffer
		);
	}

	public function remove_annotation($pAnnotation, $pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
				"remove_annotation",
		array( $pAnnotation ),
		$pBuffer
		);
	}

	/**
	 * function add_annotation:
	 *
	 * Adds an annotation to an steam_object
	 *
	 * Keep in mind that you can use every subclass of steam_object
	 * as an annotation, e.g. steam_document, steam_messageboard or
	 * steam_docextern
	 *
	 * Examples:
	 *
	 * <code>
	 * $new_annotation = steam_factory::create_textdoc(
	 *		$my_object->steam_connector,
	 *		"My annotation headline",
	 *		"This is my annotation body. " .
	 *		"This can be a whole text."
	 *	);
	 * $my_object->add_annotation( $newAnnotation );
	 * </code>
	 * <code>
	 * $new_url = steam_factory::create_docextern(
	 *	$my_object->steam_connector,
	 *	"Link to the PHPsTeam-Homepage",
	 *	"http://www.phpsteam.org"
	 * );
	 * $my_object->add_annotation( $new_url );
	 * </code>
	 *
	 * @param steam_object $pAnnotation Object, which is the annotation
	 * @param steam_object object, which should act as the root (optional)
	 * @param boolean $pBuffer send command now = 0, send later = 1
	 * @return boolean|integer depends on the buffer argument: boolean if buffer = 0, unique transaction id otherwise
	 */
	public function add_annotation( $pAnnotation, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
																"add_annotation",
		$pAnnotation,
		$pBuffer
		);
	}


	/**
	 * function add_annotations:
	 *
	 * Adds a whole bundle of objects as annotations
	 *
	 * Example:
	 * <code>
	 * $my_object->add_annotations(
	 *	$my_steam_user->rucksack_get_inventory( CLASS_DOCUMENT )
	 * );
	 * $my_steam_user->rucksack_drop_objects( FALSE, CLASS_DOCUMENT );
	 *</code>
	 *
	 * @param mixed $pAnnotations Array of steam_object
	 * @return boolean TRUE|FALSE
	 */
	public function add_annotations( $pAnnotations )
	{
		foreach( $pAnnotations as $annotation )
		{
			$this->steam_command(
			$this,
																				"add_annotation",
			$annotation,
			1
			);
		}
		return $this->steam_buffer_flush();
	}


	/**
	 * function move:
	 *
	 * Moves the object into another environment.
	 * @param steam_object $pNewEnvironment the destination, in general a container
	 * @param steam_object $pObject the object on the move
	 * @param boolean $pBuffer 0 = send command now, 1 = buffer command
	 * @return boolean TRUE | FALSE
	 */
	public function move( $pNewEnvironment, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
																"move",
		array( $pNewEnvironment ),
		$pBuffer
		);
	}

	/**
	 * function resolve_access:
	 *
	 * returns a mapping with all objects having access rights on this
	 * object as keys and their effective rights as value resolving all
	 * access rights from all objects including all acquired access rights
	 *
	 * @return a mapping with all objects having access rights on this
	 *   object as keys and their effective rights as value
	 */
	public function resolve_access($pBuffer = 0 )
	{
		$access = array();
		$acquire = $this;
		while (is_object($acquire)) {
			$localaccess = $acquire->get_sanction();
			foreach( array_keys($localaccess) as $id) {
				if (isset( $access[$id] )) {
					$access[ $id ] |= $localaccess[ $id ];
				}
				else {
					$access[ $id ] = $localaccess[ $id ];
				}
			}
			$acquire = $acquire->get_acquire();
		}
		return $access;
	}

	/**
	 * function get_user_access:
	 *
	 * @param $pBit
	 * @param $pUser
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_user_access( $pUser, $pBuffer = 0 )
	{
		$modules = $this->get_steam_connector()->get_login_data()->get_arguments();
		$steam_security = $modules[ 8 ][ "security" ];
		return $this->steam_command(
		$steam_security,
			"get_user_permissions",
		array( $this, $pUser, 32767 ),
		$pBuffer
		);
	}

	/**
	 * function get_group_access:
	 *
	 * @param $pBit
	 * @param $pGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_group_access( $pGroup, $pBuffer = 0 )
	{
		return $this->get_user_access(
		$pGroup,
		$pBuffer
		);
	}

	/**
	 * function check_access:
	 *
	 * @param $pBit
	 * @param $pUser
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function check_access( $pBit, $pUser = "", $pBuffer = 0 )
	{
		$pUser   = empty( $pUser) ? $this->get_steam_connector()->get_current_steam_user() : $pUser;
		$steam_security = $this->get_steam_connector()->get_login_data()->get_arguments();
		$steam_security = $steam_security[ 8 ][ "security" ];
		return $this->steam_command(
		$steam_security,
			"check_user_access",
		array( $this, $pUser, $pBit, 0, 0 ),
		$pBuffer
		);
	}

	/**
	 * function set_sanction:
	 *
	 * @param $pSanction access rights (as int value, e.g. SANCTION_READ,
	 *   SANCTION_EXECUTE ...)
	 * @param $pPersonOrGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function set_sanction( $pPersonOrGroup, $pSanction, $pBuffer=0 )
	{
		return $this->sanction( $pSanction, $pPersonOrGroup, $pBuffer );
	}

	/**
	 * function sanction:
	 *
	 * @param $pSanction access rights (as Decimal!)
	 * @param $pPersonOrGroup the group to grant the specified rights to
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function sanction( $pSanction, $pPersonOrGroup, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"sanction_object",
		array( $pPersonOrGroup, $pSanction ),
		$pBuffer
		);
	}

	/**
	 * function sanction_meta:
	 *
	 * @param $pSanction access rights (as Decimal!)
	 * @param $pPersonOrGroup the group to grant the specified rights to
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function sanction_meta( $pSanction, $pPersonOrGroup, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"sanction_object_meta",
		array( $pPersonOrGroup, $pSanction ),
		$pBuffer
		);
	}

	/**
	 * function get_sanction:
	 *
	 * @param $pPeronOrGroup
	 * @param $pBuffer
	 *
	 * @return mapping with object ids as keys and access rights bitmask
	 * as values
	 */
	public function get_sanction( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"get_sanction",
		array( ),
		$pBuffer
		);
	}

	/**
	 * function query_sanction:
	 *
	 * @param $pPeronOrGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function query_sanction( $pPersonOrGroup, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"query_sanction",
		array( $pPersonOrGroup ),
		$pBuffer
		);
	}

	/**
	 * function set_sanction_all:
	 *
	 * @param $pPersonOrGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function set_sanction_all( $pPersonOrGroup, $pBuffer = FALSE )
	{
		// dont need to respect old rights in here, just set SANCTION_ALL
		// otherwise this may lead into problems with negative rights
		$sanction = SANCTION_ALL;
		return $this->sanction( $sanction, $pPersonOrGroup, $pBuffer );
	}

	/**
	 * function check_access_read:
	 *
	 * @param $pPersonOrGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function check_access_read($pPersonOrGroup = "", $pBuffer = FALSE )
	{
		return $this->check_access( SANCTION_READ, $pPersonOrGroup, $pBuffer );
	}

	/**
	 * function check_access_write:
	 *
	 * @param $pPersonOrGroup
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function check_access_write($pPersonOrGroup = "", $pBuffer = FALSE )
	{
		return $this->check_access( SANCTION_WRITE, $pPersonOrGroup, $pBuffer );
	}

	public function check_access_annotate( $pPersonOrGroup = "", $pBuffer = FALSE )
	{
		return $this->check_access( SANCTION_ANNOTATE, $pPersonOrGroup, $pBuffer );
	}

	public function check_access_insert( $pPersonOrGroup = "", $pBuffer = FALSE )
	{
		return $this->check_access( SANCTION_INSERT, $pPersonOrGroup, $pBuffer );
	}

	public function check_access_move( $pPersonOrGroup = "", $pBuffer = FALSE )
	{
		return $this->check_access( SANCTION_MOVE, $pPersonOrGroup, $pBuffer );
	}

	/**
	 * function set_read_access:
	 *
	 * @param $pPersonOrGroup
	 * @param $pSetOrUnset
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function set_read_access( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		$sanction = $this->query_sanction( $pPersonOrGroup, 0 );
		if ( $pSetOrUnset && ( ! ($sanction & SANCTION_READ ) ) )
		{
			$sanction |= SANCTION_READ;
		}
		if ( ! $pSetOrUnset && ( $sanction & SANCTION_READ ) )
		{
			$sanction &= ~SANCTION_READ;
		}
		return $this->sanction( $sanction, $pPersonOrGroup, $pBuffer );
	}

	public function set_annotate_access( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		$sanction = $this->query_sanction( $pPersonOrGroup, 0 );
		if ( $pSetOrUnset && ( ! ( $sanction & SANCTION_ANNOTATE ) ) )
		{

			$sanction |= SANCTION_ANNOTATE;
		}
		if ( ! $pSetOrUnset && ( $sanction & SANCTION_ANNOTATE ) )
		{
			$sanction &= ~SANCTION_ANNOTATE;
		}
		return $this->sanction( $sanction, $pPersonOrGroup, $pBuffer );
	}

	public function set_rights_annotate( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		return $this->set_annotate_access( $pPersonOrGroup, $pSetOrUnset, $pBuffer );
	}

	public function set_insert_access( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		$sanction = $this->query_sanction( $pPersonOrGroup, 0 );
		if ( $pSetOrUnset && ( ! ( $sanction & SANCTION_INSERT ) ) )
		{
			$sanction |= SANCTION_INSERT;
		}
		if ( ! $pSetOrUnset && ( $sanction & SANCTION_INSERT ) )
		{
			$sanction &= ~SANCTION_INSERT;
		}
		return $this->sanction( $sanction, $pPersonOrGroup, $pBuffer );
	}

	public function set_rights_insert( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		return $this->set_insert_access( $pPersonOrGroup, $pSetOrUnset, $pBuffer );
	}

	/**
	 * function set_write_access:
	 *
	 * @param $pPersonOrGroup
	 * @param $pSetOrUnset
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function set_write_access( $pPersonOrGroup, $pSetOrUnset = 1, $pBuffer = 0 )
	{
		$sanction = $this->query_sanction( $pPersonOrGroup, 0 );
		if ( $pSetOrUnset && ( ! ($sanction & SANCTION_WRITE ) ) )
		{
			$sanction |= SANCTION_WRITE;
		}
		if ( ! $pSetOrUnset && ( $sanction & SANCTION_WRITE ) )
		{
			$sanction &= ~SANCTION_WRITE;
		}
		return $this->sanction( $sanction, $pPersonOrGroup, $pBuffer );
	}

	/**
	 * function delete:
	 *
	 * This function deletes the object on wich it was invoked.
	 *
	 * Example:
	 * <code>
	 * $objectToDelete->delete()
	 * </code>
	 *
	 * @param $pBuffer 0 = send command now, 1 = buffer command
	 *
	 * @return int returns 1 if successful
	 */
	public function delete($pBuffer = 0 )
	{
		// TODO: CHECK!!!
		// TODO: If this was needed it must be moved to a separate function to avoid problems with the buffer !
		// replace links with source object if source is deleted
		// using server side support (set_euid....) for this
		/*
		$references = $this->get_references();
		foreach( $references as $reference )
		{
		if ( $reference instanceof steam_link ) {
		try
		{
		$reference->delete();
		}
		catch( Exception $e ){}
		}
		}
		*/
		return $this->steam_command(
		$this,
			"delete",
		array(),
		$pBuffer
		);
	}

	/**
	 * function set_acquire:
	 *
	 * Sets the objet to acquire permissions from another object, or disable
	 * acquiring of permissions.
	 *
	 * @see get_acquire
	 * @see set_acquire_from_environment
	 *
	 * @param $pObject the object to acquire permissions from, or 0 or FALSE to
	 *   disable permission acquiring
	 * @param $pBuffer 0 = send command now, 1 = buffer command
	 * @return Int 1 on success, 0 on failure
	 */
	public function set_acquire( $pObject = FALSE, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"set_acquire",
		array($pObject),
		$pBuffer
		);
	}

	/**
	 * function set_acquire_from_environment:
	 *
	 * Sets the object to acquire permissions from its environment. You can
	 * disable acquiring of permissions by calling set_acquire(0).
	 *
	 * @see set_acquire
	 * @see get_acquire
	 *
	 * @param $pBuffer 0 = send command now, 1 = buffer command
	 * @return Int 1 on success, 0 on failure
	 */
	public function set_acquire_from_environment ( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"set_acquire_from_environment",
		array(),
		$pBuffer
		);
	}

	public function set_acquire_attribute( $pAttribute, $pObject = FALSE, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"set_acquire_attribute",
		array( $pAttribute, $pObject ),
		$pBuffer
		);
	}

	public function get_acquire_attribute( $pAttribute, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"get_acquire_attribute",
		array( $pAttribute),
		$pBuffer
		);
	}

	/**
	 * function get_acquire:
	 *
	 * Returns the object from which this object acquires access rights.
	 * If this object doesnt acquire rights the return value will be an
	 * Int value (usually 0).
	 *
	 * @see get_acquire
	 * @see set_acquire_from_environment
	 *
	 * @param $pBuffer 0 = send command now, 1 = buffer command
	 * @return 0 or the object where this object acquires rights from
	 */
	public function get_acquire( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"resolve_acquire",
		array(),
		$pBuffer
		);
	}

	/**
	 * function describe_attributes:
	 *
	 * Describes all attributes of this object
	 *
	 * @param boolean $pBuffer 0 = send command now, 1 = buffer command
	 * @return array associative array describing attribute types
	 */

	public function describe_attributes( $pBuffer = 0)
	{
		return $this->steam_command(
		$this,
																"describe_attributes",
		array( ),
		$pBuffer
		);
	}
	
	public function is_prefetched() {
		return $this->prefetched;
	}
	
	public function set_prefetched() {
		$this->prefetched = true;
	}
}
?>