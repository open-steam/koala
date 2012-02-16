<?php
/**
 * Implements the steam_container class
 *
 * Container play an essential part in the metaphor of virtual knowledge rooms;
 * they can act as an environment for every type of object. Therefore, container
 * help to structure information, and, in particularly, to implement
 * operational and organizational structure of all kind.
 *
 * PHP versions 5
 *
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Alexander Roth <aroth@it-roth.de>, Daniel Buese <dbuese@upb.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 *
 */
define( "SORT_NONE", "0" );
// SORT_DESC is PHP global for SORT DESCENDING and produces output in error_log
// if defined here again
define( "SORT_DESCENDING", "2" );
define( "SORT_NAME", "4" );
define( "SORT_SIZE", "8" );
define( "SORT_DATE", "16" );
define( "SORT_TYPE", "32" );

$CONTAINER_SORT_ORDER = SORT_DATE;

define( "AS_ORIG", "0" );
define( "AS_LINK", "1" );
define( "AS_COPY", "2" );

/**
 * function sort_documents:
 *
 * @param $pDocA
 * @param $pDocB
 *
 * @return
 */
function sort_documents( $pDocA, $pDocB )
{
	global $CONTAINER_SORT_ORDER;
	$result = ( $CONTAINER_SORT_ORDER & SORT_DESCENDING ) ? -1 : 1;

	if ( $CONTAINER_SORT_ORDER & SORT_NAME )
	{
		return( strtoupper( $pDocA->get_attribute( OBJ_NAME ) ) < strtoupper( $pDocB->get_attribute( OBJ_NAME ) ) ) ? - $result : $result ;
	}
	if ( $CONTAINER_SORT_ORDER & SORT_SIZE )
	{
		return( $pDocA->get_attribute( DOC_SIZE ) < $pDocB->get_attribute( DOC_SIZE ) ) ? - $result : $result ;
	}
	if ( $CONTAINER_SORT_ORDER & SORT_TYPE )
	{
		return ( $pDocA->get_attribute( DOC_TYPE ) < $pDocB->get_attribute( DOC_TYPE) ) ? - $result : $result;
	}
	return ( $pDocA->get_attribute( "OBJ_CREATION_TIME" ) < $pDocB->get_attribute( "OBJ_CREATION_TIME" ) ) ? $result : - $result;
}


/**
 * steam_container
 *
 * This class acts mainly as an environment for other objects, and can be
 * part of a complex of several containers which - as a whole - implements
 * some kind of operational and/or organizational structure.
 *
 * @package     PHPsTeam
 */

class steam_container extends steam_object
{
	
	public function get_type() {
		return CLASS_CONTAINER | CLASS_OBJECT;
	}
	
	/**
	 * function insert:
	 *
	 * @param mixed $pSteamObjects Array of steam_objects
	 * @param integer $pType 0 = take originals, 1 = create links, 2 = take copies
	 **/
	public function insert( $pSteamObjects, $pType = 0 )
	{
		$objects_to_insert = array();
		if ( ! is_array( $pSteamObjects ) )
		{
			$pSteamObjects = array( 0 => $pSteamObjects );
		}

		if ( $pType == 1 )
		{
			foreach( $pSteamObjects as $steam_object )
			{
				steam_factory::create_link($this->steam_connectorID, $steam_object, 1 );
			}
			$objects_to_insert = $this->steam_buffer_flush();
		}
		elseif( $pType == 2 )
		{
			foreach( $pSteamObjects as $steam_object )
			{
				steam_factory::create_copy($this->steam_connectorID, $steam_object, 1 );
			}
			$objects_to_insert = $this->steam_buffer_flush();
		}
		else
		{
			$objects_to_insert = $pSteamObjects;
		}

		foreach( $objects_to_insert as $object )
		{
			$steam_object = ( get_class( $object ) == "steam_request" ) ? $object->arguments : $object;
			$steam_object->move( $this, 1 );
		}
		return $this->steam_buffer_flush();
	}

	/**
	 * function upload
	 *
	 * @param $pIdentifier
	 *
	 * @return
	 */
	public function upload( $pIdentifier )
	{
		$temp_name = $_FILES[ $pIdentifier ][ "tmp_name" ];
		$file_name = $_FILES[ $pIdentifier ][ "name" ];
		$file_name = str_replace( "\\", "", $file_name );
		$file_name = str_replace( "'", "", $file_name );
		$file_type = $_FILES[ $pIdentifier ][ "type" ];
		$file_size = $_FILES[ $pIdentifier ][ "size" ];
		$result    = $_FILES[ $pIdentifier ][ "error" ];

		// check filename
		if ( empty( $file_name ) )
		{
			return FALSE; // TODO Exception
		}
		// TODO check filesize
		// TODO file type check to prevent possible attacks

		ob_start();
		readfile( $temp_name );
		$content = ob_get_contents();
		ob_end_clean();

		$path = $this->get_path() . "/" . $file_name;
		$new_doc = $this->get_steam_connector()->upload( $path, $content );
		return $new_doc;
	}

	/**
	 * function get_inventory_data:
	 *
	 * Returns all objects for which the container acts as an environment
	 *
	 * Requires the module "package:whiteboardsupport" to be installed on s
	 * server side, if this module is not available, calling this method
	 * results in an exception
	 *
	 * Due to a container acting as environment for some objects,
	 * this function returns all objects located inside.
	 *
	 * @param int $pClass if you ask for objects of a specific type, you can optionally use this int for the typedefinition (see steam_types.conf.php)
	 * @return mixed Array of steam_objects
	 */
	public function get_inventory_data( $pClass = FALSE )
	{
		$whiteboardsupport = $this->get_steam_connector()->get_module("package:whiteboardsupport");
		if (!is_object($whiteboardsupport))
		throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "package:whiteboardmodule was not installed on the server.", 404);
		$params = array($this);
		if ( $pClass ) $params[] = $pClass;
		$invdata =  $this->steam_command(
		$whiteboardsupport,
        "query_inventory_data",
		$params,
		0
		);
		// "version check" for whiteboardsupport module
		if (sizeof($invdata["self_defined"] > 0)) {
			$mykey = array_keys($invdata["self_defined"]);
			$mykey = $mykey[0];
			if (!isset($invdata["self_defined"][$mykey]["object_class"])) {
				throw new steam_exception($this->get_steam_connector()->get_login_user_name(), "incompatible version: you must install the latest version of package:whiteboardmodule on the server.", 500);
			}
		}
		$inventory = array();
		foreach( $invdata["attributes"] as $id => $data ) {
			$type = $invdata["self_defined"][$id]["object_class"];
			$obj = steam_factory::get_object($this->steam_connectorID, $id, $type );
			$obj->set_values($invdata["attributes"][$id]);
			$inventory[] = $obj;

		}
		foreach( $inventory as $object) {
			$object->set_additional_values( $invdata["self_defined"][$object->get_id()] );
		}
		return $inventory;
	}

	/**
	 * function get_inventory_paged:
	 *
	 * Returns the objects from object $pFrom to $pTo for which the container
	 * acts as an environment
	 *
	 * @param int $pFrom start object ocunt count
	 * @param int $pTo
	 * @param Boolean $pBuffer use command buffer or not
	 * @return mixed Array of steam_objects
	 */
	public function get_inventory_paged( $pFrom = 0, $pTo = 0 , $pBuffer = FALSE )
	{
		$inventory = $this->steam_command(
		$this,
        "get_inventory",
		array( $pFrom, $pTo ),
		$pBuffer
		);
		return $inventory;
	}

	/**
	 * function get_inventory_raw:
	 *
	 * Returns the objects for which the container acts as an environment
	 * Due to a container acting as environment for some objects,
	 * this function returns all objects located inside.
	 *
	 * @param int $pClass if you ask for objects of a specific type, you can optionally use this int for the typedefinition (see steam_types.conf.php)
	 * @param Boolean $pBuffer use command buffer or not
	 * @return mixed Array of steam_objects
	 */
	public function get_inventory_raw( $pClass = 0, $pBuffer = FALSE )
	{
		if ( ! $pClass )
		{
			return $this->steam_command(
			$this,
					"get_inventory",
			array(),
			$pBuffer
			);
		}
		else
		{
			return $this->steam_command(
			$this,
					"get_inventory_by_class",
			array( (int) $pClass ),
			$pBuffer
			);
		}
	}

	/**
	 * Returns the inventory of this container, optionally filtered by object
	 * class, attribute values or pagination.
	 * The description of the filters and sort options can be found in the
	 * filter_objects_array() function of the "searching" module of open-sTeam.
	 *
	 * Example:
	 * Return all documents with keywords "urgent" or "important" that are no
	 * wikis and that have been changed in the last 24 hours, sort them by
	 * modification date (newest first) and return only the first 10 results:
	 * get_inventory_filtered(
	 *   array( // filters:
	 *     array( '-', '!access', SANCTION_READ ),
	 *     array( '-', 'attribute', 'OBJ_TYPE', 'prefix', 'container_wiki' ),
	 *     array( '-', 'attribute', 'DOC_LAST_MODIFIED', '<', time()-86400 ),
	 *     array( '-', 'attribute', 'OBJ_KEYWORDS', '!=', array( 'urgent', 'important' ) ),
	 *     array( '+', 'class', CLASS_DOCUMENT )
	 *   ),
	 *   array( // sort:
	 *     array( '>', 'attribute', 'DOC_LAST_MODIFIED' )
	 *   ), 0, 10 );
	 *
	 * @param $pFilters (optional) an array of filters (each an array as described
	 *   in the "searching" module) that specify which objects to return
	 * @param $pSort (optional) an array of sort entries (each an array as described
	 *   in the "searching" module) that specify the order of the items
	 * @param $pOffset (optional) only return the objects starting at (and including)
	 *   this index
	 * @param $pLength (optional) only return a maximum of this many objects
	 * @param $pBuffer Send now or buffer request?
	 * @return an array of objects that match the specified filters, sort order and
	 *   pagination.
	 */
	public function get_inventory_filtered( $pFilters = array(), $pSort = array(), $pOffset = 0, $pLength = 0, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
			"get_inventory_filtered",
		array( $pFilters, $pSort, $pOffset, $pLength ),
		$pBuffer
		);
	}

	/**
	 * Same as get_inventory_filtered, but returns an array( 'objects'=>array(...), 'total'=>nr, 'start'=>nr, 'length'=>nr, 'page'=>nr ) instead of an object array.
	 *
	 * @param unknown_type $pFilters
	 * @param unknown_type $pSort
	 * @param unknown_type $pOffset
	 * @param unknown_type $pLength
	 * @param unknown_type $pBuffer
	 * @return unknown
	 */
	public function get_inventory_paginated( $pFilters = array(), $pSort = array(), $pOffset = 0, $pLength = 0, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
			"get_inventory_paginated",
		array( $pFilters, $pSort, $pOffset, $pLength ),
		$pBuffer
		);
	}

	/**
	 * function get_inventory:
	 *
	 * Returns the objects for which the container acts as an environment
	 *
	 * Due to a container acting as environment for some objects,
	 * this function returns all objects located inside.
	 *
	 * @param int $pClass if you ask for objects of a specific type, you can optionally use this int for the typedefinition (see steam_types.conf.php)
	 * @param mixed $pAttributes define additional attributes for the object instances. Following are default: OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED, OBJ_KEYWORDS, DOC_TYPE, DOC_LAST_ACCESSED
	 * @param int $pOrder sort order, combination of SORT_DATE, SORT_DESC, SORT_NAME, SORT_SIZE, SORT_TYPE (constants definend in steam_container.class.php )
	 * @param boolean $pFollowLinks Optional, get attributes from source in case of steam_link-instances. TRUE as default.
	 * @return mixed Array of steam_objects
	 */
	public function get_inventory( $pClass = 0, $pAttributes = array(), $pSort = SORT_NONE, $pFollowLinks = TRUE )
	{
		if ( ! $pClass )
		{
			$inventory = $this->steam_command(
			$this,
					"get_inventory",
			array(),
			0
			);
		}
		else
		{
			$inventory =  $this->steam_command(
			$this,
					"get_inventory_by_class",
			array( (int) $pClass ),
			0
			);
		}

		$trnsctnid = array();
		$attributes = array(
		OBJ_NAME,
		OBJ_DESC,
		OBJ_CREATION_TIME,
		OBJ_LAST_CHANGED,
		OBJ_KEYWORDS,
		DOC_TYPE,
		DOC_LAST_ACCESSED,
		DOC_ENCODING,
		);
		$attributes = array_merge( $attributes, $pAttributes );
		if ( count( $pAttributes) > 0 ) $attributes = array_unique($attributes);

		foreach( $inventory as $item )
		{
			if ( $pFollowLinks && ( ( CLASS_LINK & $item->get_type() ) == CLASS_LINK ) )
			{
				$object = $item->get_source_object();
			}
			else
			{
				$object = $item;
			}
			$item->get_attributes( $attributes, TRUE, FALSE );
			if ( ( CLASS_DOCUMENT & $object->get_type() ) == CLASS_DOCUMENT )
			{
				$trnsctnid[ $item->get_id() ] = $object->get_content_size( 1 );
			}
		}
		$result = $this->steam_buffer_flush();
		$all_items = array();
		foreach ( $inventory as $item )
		{
			if ( isset( $trnsctnid[ $item->get_id() ] ) )
			{
				$result_size = $result[ $trnsctnid[ $item->get_id() ] ];
				if ( $result_size > 0 )
				{
					$item->set_values(
					array( "DOC_SIZE" => $result_size )
					);
				}
			}
			$all_items[ ] = $item;
		}
		$GLOBALS[ "CONTAINER_SORT_ORDER" ] = $pSort;

		if ($pSort != SORT_NONE) {
			usort( $all_items, "sort_documents" );
		}
		return $all_items;
	}

	/**
	 * function count_inventory:
	 * This function returns the number of objects in this container
	 *
	 *Example:
	 *<code>
	 *$nr_of_objects = $my_container_Object->count_inventory();
	 *</code>
	 *
	 * @param Boolean $pBuffer use command buffer or not
	 * @return int number of objects
	 */
	public function count_inventory( $pBuffer = FALSE )
	{
		$invsize = $this->steam_command(
		$this,
				"get_size",
		array(),
		$pBuffer
		);
		return $invsize;
	}

	/**
	 * function get_object_by_name:
	 *
	 * Returns an instance of the object through its given name
	 * from the inventory of this container
	 *
	 * @param String $pObjectName Name of the object
	 * @param Boolean $pBuffer use command buffer or not
	 * @return steam_object Object
	 */
	public function get_object_by_name( $pObjectName, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
				"get_object_byname",
		array( $pObjectName ),
		$pBuffer
		);
	}

	/**
	 * function swap_inventory:
	 *
	 * swap to objects in the inventory order of the container
	 *
	 * @param string $pObjectFrom
	 * @param string $pObjectTo
	 * @param Boolean $pBuffer use command buffer or not
	 * @return Boolean TRUE if succesfully swapped the two objects in the
	 *                 inventory order
	 */
	public function swap_inventory( $pObjectFrom, $pObjectTo, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
            'swap_inventory',
		array($pObjectFrom, $pObjectTo),
		$pBuffer
		);
	}

	/**
	 * function order_inventory:
	 *
	 * Set new order of the container's inventory.
	 * The parameter $pOrder must contain an array with indexes representing
	 * the indexes of the Objects in the actual inventory order.
	 * Example: (for 5 objects in the container)
	 * $pOrder = array( 4,3,2,1,0 );   // reverse inventory order
	 * $pOrder = array( 0,3,2,1,4 );   // Just swap object 2 and 4 in the
	 * order of the inventory
	 * It is important to include indexes for all objects in $pOrder, otherwise
	 * you will get an steam_exception indication that the size of the new order
	 * has to match the size of the inventory
	 *
	 * @param array $pOrder an array of the new indexes
	 * @param Boolean $pBuffer use command buffer or not
	 * @return Boolean TRUE if the new inventory order was set sucessfully
	 */
	public function order_inventory( $pOrder, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
            'order_inventory',
		array($pOrder),
		$pBuffer
		);
	}

	/**
	 * function order_inventory_objects:
	 *
	 * Change the order of some objects in the container's inventory.
	 * The parameter $pObjects must contain an array with objects or object ids.
	 * These objects will be reordered in the container's inventory so that they
	 * match the order that is given in the $pObjects array. The order of other
	 * objects in the inventory will not be affected.
	 *
	 * @param array $pObjects an array of objects or object ids
	 * @param Boolean $pBuffer use command buffer or not
	 * @return Boolean TRUE if the new inventory order was set sucessfully
	 */
	public function order_inventory_objects( $pObjects, $pBuffer = FALSE )
	{
		return $this->steam_command(
		$this,
            'order_inventory_objects',
		array($pObjects),
		$pBuffer
		);
	}
}

?>