<?php

class koala_container extends koala_object
{
	private $link_base;
	private $types_visible;
	private $types_invisible;
	private $obj_types_visible;
	private $obj_types_invisible;

	/**
	 * Set up a koala_container for a steam_container.
	 *
	 * @param Object $steam_container the steam_container that shall be
	 *   represented by the koala_container
	 * @param String $link_base the link base for this container (as a string),
	 *   or FALSE if you don't need or don't know the link base. If the link
	 *   base will be needed (e.g. when getting the context menu or link path,
	 *   then the link base will be calculated if not set).
	 */
	public function __construct( $steam_container, $link_base = FALSE )
	{
		if ( !is_object( $steam_container ) || !($steam_container instanceof steam_container) )
			throw new Exception( "No valid steam_container provided", E_PARAMETER );
		parent::__construct( $steam_container );
		if ( is_string( $link_base ) ) $this->link_base = $link_base;
		$this->set_types_visible();
		$this->set_types_invisible();
		$this->set_obj_types_visible();
		$this->set_obj_types_invisible();
	}

	/**
	 * Creates and returns a new steam_container object.
	 * 
	 * @param String $name name of the new container
	 * @param Object $environment steam_container or koala_container in which
	 *   to create the new container
	 * @param String $description (optional) description of the new container
	 * @return Object the resulting new steam_container
	 */
	static public function create_container( $name, $environment, $description = "" ) {
		if ( $environment instanceof koala_container )
			$environment = $environment->get_steam_object();
		if ( $environment instanceof koala_container )
			$environment = $environment->get_steam_object();
		if ( ! $environment instanceof steam_container )
			throw new Exception( "No valid environment provided.", E_PARAMETER );
		// just create and return a new steam_container (containers need no
		// special treatment in koaLA):
		return steam_factory::create_container( $GLOBALS["STEAM"]->get_id(), $name, $environment, $description );
	}

	/**
	 * Returns the link base that has been set for this container or calculates
	 * a link base if none has been set.
	 *
	 * @return String link base of this container
	 */
	public function get_link_base() {
		if ( !is_string( $this->link_base ) ) {
			$tmp_link_path = $this->get_link_path();
			$this->link_base = $tmp_link_path[ 0 ][ "link" ];
		}
		return $this->link_base;
	}

	protected function get_link_path_internal( $top_object )
	{
		$parent = $this->steam_object->get_environment();
		if ( !is_object( $parent ) ) {
			$parent = $this->steam_object->get_creator();
			$koala_parent = koala_object::get_koala_object( $parent );
			$link_path[-1] = $koala_parent->get_link_path( $top_object );
			$link_path[] = array( "name" => $this->get_display_name(), "koala_obj" => $this, "obj" => $this->steam_object );
			return $link_path;
		}
		if ( $parent instanceof steam_user )
			$koala_parent = new koala_container_clipboard( $parent );
		else
			$koala_parent = koala_object::get_koala_object( $parent );
		$link_path = $koala_parent->get_link_path( $top_object );
		$link = array( "name" => $this->get_display_name(), "koala_obj" => $this, "obj" => $this->steam_object );
		if ( isset( $link_path[0]["link"] ) )
			$link["link"] = $link_path[0]["link"] . $this->get_id() . "/";
		$link_path[] = $link;
		return $link_path;
	}

	public function get_link() {
		return array(
			"name" => $this->get_display_name(),
			"link" => $this->get_url(),
		);
	}

	public function get_url() {
		return $this->get_link_base() . $this->get_id() . "/";
	}

	/**
	 * Returns the root koaLA owner of this container. This is determined as the
	 * owner whose workroom is the root environment of this container. For
	 * courses learner's workrooms, the course object is returned instead.
	 *
	 * @return Object the koaLA owner of this container, or FALSE if none could
	 *   be determined
	 */
	public function get_koala_owner() {
		$root = $this->steam_object->get_root_environment();
		if ( !is_object( $root ) ) $root = $this->steam_object;
		if ( is_object( $creator = $root->get_creator() ) ) {
			if ( $creator->get_attribute( OBJ_TYPE ) === "course_learners" )
				return $creator->get_parent_group();
			return $creator;
		}
		return FALSE;
	}

	public function set_types_visible( $types = FALSE )
	{
		if ( $types === FALSE ) $this->types_visible = CLASS_DOCUMENT | CLASS_CONTAINER | CLASS_ROOM | CLASS_DOCEXTERN; 
		else $this->types_visible = $types;
	}

	public function get_types_visible()
	{
		return $this->types_visible;
	}

	public function set_types_invisible( $types = FALSE )
	{
		if ( $types === FALSE ) $this->types_invisible = CLASS_USER;
		else $this->types_invisible = $types;
	}

	public function get_types_invisible()
	{
		return $this->types_invisible;
	}

	public function set_obj_types_visible( $types = array() )
	{
		$this->obj_types_visible = is_array( $types ) ? $types : array();
	}

	public function get_obj_types_visible()
	{
		return $this->obj_types_visible;
	}

	public function set_obj_types_invisible( $types = array() )
	{
		$this->obj_types_invisible = is_array( $types ) ? $types : array();
	}

	public function get_obj_types_invisible()
	{
		return $this->obj_types_invisible;
	}

	public function get_inventory( $pOffset = 0, $pLength = 0, $pSort = FALSE, $pBuffer = FALSE )
	{
		$filters = array();
		if ( $this->types_invisible )
			$filters[] = array( "-", "class", $this->types_invisible );
		if ( is_array( $this->obj_types_invisible ) && count( $this->obj_types_invisible ) > 0 )
			$filters[] = array( "-", "attribute", OBJ_TYPE, "==", $this->obj_types_invisible );
		if ( is_array( $this->obj_types_visible ) && count( $this->obj_types_visible ) > 0 )
			$filters[] = array( "-", "attribute", OBJ_TYPE, "!=", $this->obj_types_visible );
		// if you want to add more filters, insert them here (the CLASS_ALL rule below should be the last rule)
		if ( $this->types_visible )
			$filters[] = array( "+", "class", $this->types_visible );
		else
			$filters[] = array( "+", "class", CLASS_ALL );
		if ( is_string( $pSort ) ) {
			if ( strlen( $pSort ) > 0 && substr( $pSort, 0, 1 ) === '-' ) {
				$direction = '<';
				$pSort = substr( $pSort, 1 );
			}
			else $direction = '>';
			switch ( $pSort ) {
				case 'name':
					$sort = array( array( $direction, 'attribute', OBJ_NAME ) );
					break;
				case 'size':
					$sort = array( array( $direction, 'function', 'get_content_size', array() ), array( $direction, 'class' ) );
					break;
				case 'date':
					$sort = array( array( $direction, 'attribute', array( DOC_LAST_MODIFIED, OBJ_CREATION_TIME ) ) );
					break;
				default:
					$sort = array();
					break;
			}
		}
		else $sort = array();
		// you can insert sort options here
		return $this->steam_object->get_inventory_filtered( $filters, $sort, $pOffset, $pLength, $pBuffer );
	}

	public function get_inventory_paginated( $pOffset = 0, $pLength = 0, $pSort = FALSE, $pBuffer = FALSE )
	{
		$filters = array();
		if ( $this->types_invisible )
			$filters[] = array( "-", "class", $this->types_invisible );
		if ( is_array( $this->obj_types_invisible ) && count( $this->obj_types_invisible ) > 0 )
			$filters[] = array( "-", "attribute", OBJ_TYPE, "==", $this->obj_types_invisible );
		if ( is_array( $this->obj_types_visible ) && count( $this->obj_types_visible ) > 0 )
			$filters[] = array( "-", "attribute", OBJ_TYPE, "!=", $this->obj_types_visible );
		// if you want to add more filters, insert them here (the CLASS_ALL rule below should be the last rule)
		if ( $this->types_visible )
			$filters[] = array( "+", "class", $this->types_visible );
		else
			$filters[] = array( "+", "class", CLASS_ALL );
		if ( is_string( $pSort ) ) {
			if ( strlen( $pSort ) > 0 && substr( $pSort, 0, 1 ) === '-' ) {
				$direction = '<';
				$pSort = substr( $pSort, 1 );
			}
			else $direction = '>';
			switch ( $pSort ) {
				case 'name':
					$sort = array( array( $direction, 'attribute', OBJ_NAME ) );
					break;
				case 'size':
					$sort = array( array( $direction, 'function', 'get_content_size', array() ), array( $direction, 'class' ) );
					break;
				case 'date':
					$sort = array( array( $direction, 'attribute', array( DOC_LAST_MODIFIED, OBJ_CREATION_TIME ) ) );
					break;
				default:
					$sort = array();
					break;
			}
		}
		else $sort = array();
		// you can insert sort options here
		return $this->steam_object->get_inventory_paginated( $filters, $sort, $pOffset, $pLength, $pBuffer );
	}

	public function accepts_object( $koala_object )
	{
		return TRUE;
	}

	public function get_webdav_url()
	{
		$container_path = $this->steam_object->get_attribute( "OBJ_PATH" );
		if ( !is_string( $container_path ) || empty( $container_path ) ) return "";
		$https_port = (int)(is_object( $GLOBALS[ "STEAM" ] ) ? $GLOBALS[ "STEAM" ]->get_config_value( "https_port" ) : 443);
		if ( $https_port == 443 || $https_port == 0 ) $https_port = "";
		else $https_port = ":" . (string)$https_port;
		return "https://" . STEAM_SERVER . $https_port . str_replace( "%2F", "/", rawurlencode( $container_path ) . "/" );
	}

	public function get_context_menu( $context )
	{
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_write( $user ) ) {
			$menu[] = array( "name" => gettext( "Preferences" ), "link" => $this->get_url() . "edit" );
			// TODO: write access may not suffice for deleting:
			$menu[] = array( "name" => gettext( "Delete this folder" ), "link" => $this->get_url() . "delete" );
		}
		if ( $this->steam_object->check_access_insert( $user ) ) {
			$menu[] = array( "name" => gettext( "Create folder" ), "link" => $this->get_url() . "new-folder" );
			$menu[] = array( "name" => gettext( "Upload document" ), "link" => PATH_URL . "upload.php?env=" . $this->get_id() );
      		$menu[] = array( "name" => gettext( "Create Weblink" ), "link" => PATH_URL . "docextern_create.php?env=" . $this->get_id() );
		}
		return $menu;
	}

  public static function get_items( $id ) {
    $container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id, CLASS_CONTAINER );
    $items = $container->get_inventory();
    $res = array();
    $i = 0;
    $data_tnr = array();
    foreach( $items as $item )
    {
      $tnr = array();
      $tnr["ATTRIBUTES"] = $item->get_attributes( 
        array( 
            OBJ_NAME, 
            OBJ_DESC, 
            OBJ_KEYWORDS,
            DOC_MIME_TYPE,
            OBJ_CREATION_TIME,
            DOC_EXTERN_URL
          ), TRUE
        );
      $tnr["CREATOR"] = $item->get_creator(TRUE);
      $tnr["CLASSTYPE"] = $item->get_object_class(TRUE);
      if ($item instanceof steam_document) {
        $tnr["CONTENTSIZE"] = $item->get_content_size(TRUE);
      }
      $data_tnr[$i] = $tnr;
      $i++;
    }
    $data_result = $GLOBALS["STEAM"]->buffer_flush();
    $i = 0;
    $author_tnr = array();
    foreach( $items as $item )
    {
      $author_tnr[$i] = $data_result[$data_tnr[$i]["CREATOR"]]->get_attributes( array( USER_FIRSTNAME, USER_FULLNAME ), TRUE );
      $i++;
    }
    $author_result = $GLOBALS["STEAM"]->buffer_flush();
    $result = array();
    $i = 0;
    foreach( $items as $item )
    {
      $result = $data_result[$data_tnr[$i]["ATTRIBUTES"]];
      if ($item instanceof steam_document) {
        $result["CONTENTSIZE"] = $data_result[$tnr["CONTENTSIZE"]];
      }
      $result["CLASSTYPE"] = $item->get_type();
      $result[ "OBJ_ID" ] = $item->get_id();
      $result[ "OBJ_NAME" ] = $data_result[$data_tnr[$i]["ATTRIBUTES"]][ OBJ_NAME ];
      $result[ "AUTHOR" ] = $author_result[$author_tnr[$i]][ USER_FIRSTNAME ] . " " . $author_result[$author_tnr[$i]][ USER_FULLNAME ];
      $res[] = $result;
      $i++;
    }
    return array_reverse($res);
  }
  
	static public function get_access_descriptions( $group )
	{
		if ( $group instanceof steam_user ) {
			return array(
				PERMISSION_UNDEFINED => array(
					"label" => gettext("This folder inherits its permissions from its environment." ),
					"summary_short" => gettext( "Environment" ),
				),
				PERMISSION_PUBLIC => array(
					"label" =>  gettext( "All users can read, comment, insert and remove documents." ),
					"summary_short" => gettext( "Public" ),
					"members" => 0,
					"steam" => SANCTION_READ | SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
				),
				PERMISSION_PRIVATE => array(
					"label" => str_replace( "%USER", h($group->get_full_name()), gettext( "Only %USER can read, comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Private" ),
					"members" => 0,
					"steam" => 0,
				),
			);
		}
		// Course:
		if ( (string) $group->get_attribute( "OBJ_TYPE" ) == "course" ) {
			return array(
				PERMISSION_UNDEFINED => array(
					"label" => gettext("This folder inherits its permissions from its environment." ),
					"summary_short" => gettext( "Environment" ),
				),
				PERMISSION_PUBLIC => array(
					"label" =>  gettext( "All users can read, comment, insert and remove documents." ),
					"summary_short" => gettext( "Public" ),
					"members" => 0,
					"steam" => SANCTION_READ | SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
				),
				PERMISSION_PUBLIC_READONLY => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "All users can read. Only members of %GROUP can comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Public" ),
					"members" => SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
					"steam" => SANCTION_READ,
				),
				PERMISSION_PRIVATE => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "Only members of %GROUP can read, comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Private" ),
					"members" =>  SANCTION_READ | SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
					"steam" => 0,
				),
				PERMISSION_PRIVATE_READONLY => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "Only members of %GROUP can read and comment. Only staff members of %GROUP can insert and remove documents." ) ),
					"summary_short" => gettext( "Private" ),
					"members" =>  SANCTION_READ | SANCTION_ANNOTATE,
					"steam" => 0,
				)
			);
		// Group:
		} else {
			return array(
				PERMISSION_UNDEFINED => array(
					"label" => gettext("This folder inherits its permissions from its environment." ),
					"summary_short" => gettext( "Environment" ),
				),
				PERMISSION_PUBLIC => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "All users can read, comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Public" ),
					"members" => 0,
					"steam" => SANCTION_READ | SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
				),
				PERMISSION_PUBLIC_READONLY => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "All users can read. Only members of %GROUP can comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Public" ),
					"members" => SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
					"steam" => SANCTION_READ,
				),
				PERMISSION_PRIVATE => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "Only members of %GROUP can read, comment, insert and remove documents." ) ),
					"summary_short" => gettext( "Private" ),
					"members" =>  SANCTION_READ | SANCTION_WRITE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE,
					"steam" => 0,
				),
				PERMISSION_PRIVATE_READONLY => array(
					"label" => str_replace( "%GROUP", h($group->get_name()), gettext( "Only members of %GROUP can read and comment. Only staff members of %GROUP can insert and remove documents." )),
					"summary_short" => gettext( "Private" ),
					"members" =>  SANCTION_READ | SANCTION_ANNOTATE,
					"steam" => 0,
				)
			);
		}
	}

	/**
	 * Set access to inherit permissions from the container's environment.
	 * For containers, this corresponds to PERMISSION_UNDEFINED in the koaLA
	 * access schemes.
	 * You can pass the same groups as for the set_access() function, which
	 * will clear these groups' permissions on this object. Since the
	 * container will inherit its permissions from its environment, you will
	 * usually not keep explicit permissions on the container, because the
	 * container would keep them even when moved into a different environment.
	 *
	 * @param Object $group_members steam_group for the "members" in the access
	 *   scheme (the permissions for this group will be cleared on the container)
	 * @param Object $group_staff steam_group for the "staff" in the access
	 *   scheme (the permissions for this group will be cleared on the container)
	 * @param Object $group_admins steam_group for the "admins" in the access
	 *   scheme (the permissions for this group will be cleared on the container)
	 */
	public function set_access_inherit( $group_members = 0, $group_staff = 0, $group_admins = 0 )
	{
		$group_steam = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$buffer = TRUE;
		if ( is_object($group_staff) ) {
			$this->steam_object->set_sanction( $group_staff, 0, $buffer );
			$this->steam_object->sanction_meta( 0, $group_staff, $buffer );
		}
		if ( is_object($group_admins) ) {
			$this->steam_object->set_sanction( $group_admins, 0, $buffer );
			$this->steam_object->sanction_meta( 0, $group_admins, $buffer );
		}
		if ( is_object($group_members) ) {
			$this->steam_object->set_sanction($group_members, 0, $buffer );
			$this->steam_object->sanction_meta( 0, $group_members, $buffer );
		}
		$this->steam_object->set_sanction($group_steam, 0, $buffer );
		
		// enable acquiring
		$this->steam_object->set_acquire_from_environment( $buffer );
		
		// Store access setting in the steam_object
		$this->steam_object->set_attribute("KOALA_ACCESS", PERMISSION_UNDEFINED, $buffer);
		
		// flush the buffer to set rights in open-sTeam
		$GLOBALS[ "STEAM" ]->buffer_flush();
	}
}
?>
