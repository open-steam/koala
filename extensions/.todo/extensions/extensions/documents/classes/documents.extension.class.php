<?php

require_once( PATH_EXTENSIONS . "documents/classes/koala_container_documents.class.php");

class documents extends koala_extension
{
	static $PATH;
	static $version = "1.0.0";
	
	function __construct()
	{
		self::$PATH = PATH_EXTENSIONS . "documents/";
		parent::__construct(PATH_EXTENSIONS . "documents.xml");
	}

	static public function get_koala_object_for( $steam_object, $type, $obj_type )
	{
		if ( $obj_type === "room_documents_koala" )
			return new koala_container_documents( $steam_object );
		return FALSE;
	}

	function get_display_name()
	{
		return h( gettext( "documents" ) );
	}

	function get_display_description()
	{
		return h( gettext( "an area for arbitrary documents and folders" ) );
	}

	function get_headline( $headline = array(), $context = "", $params = array() )
	{
		if ( $context !== "documents" || !isset( $params["owner"] ) ) return FALSE;
		$owner = $params["owner"];
		if ( $owner instanceof steam_user && $owner->get_id() == lms_steam::get_current_user()->get_id() )
			return array( array( "name" => gettext( "Your documents" ), "link" => $owner->get_url() . "documents/" ) );
		return array(
			$owner->get_link(),
			array( "name" => gettext( "Documents" ), "link" => $owner->get_url() . "documents/" ),
		);
	}

	function can_extend( $koala_class )
	{
		if ( $koala_class == 'koala_group_default' || $koala_class == 'koala_user' ||
				is_subclass_of( $koala_class, 'koala_group_default' ) || is_subclass_of( $koala_class, 'koala_user' ) )
			return TRUE;
		return FALSE;
	}

	public function enable_for( $koala_object )
	{
		$koala_object->set_attribute( 'KOALA_EXTENSION_DOCUMENTS_ENABLED', 'TRUE' );
	}

	public function disable_for( $koala_object )
	{
		$koala_object->set_attribute( 'KOALA_EXTENSION_DOCUMENTS_ENABLED', 'FALSE' );
	}

	protected function is_enabled_for( $koala_object )
	{
		$enabled = $koala_object->get_attribute("KOALA_EXTENSION_DOCUMENTS_ENABLED");
		if ( $enabled === "TRUE" )
			return TRUE;
    else {
      if ( get_class( $koala_object ) == 'koala_user' || is_subclass_of( $koala_class_name, 'koala_user' ) ) {
        return TRUE;
      } else {
		    return FALSE;
      }
    }
	}

	function get_menu( $params = array() )
	{
		if ( !is_array($params) || !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		return array(
			"name" => gettext( "Documents" ),
			"link" => $params[ "owner" ]->get_url() . "documents/"
		);
	}
	
	function get_context_menu( $context, $params = array() )
	{
		if ( $context !== "documents" ) return array();
		if ( !is_array($params) || !isset( $params[ "owner" ] ) )
			throw new Exception( "No 'owner' param provided.", E_PARAMETER );
		if ( ! isset( $params[ "koala_container" ] ) || !( ($koala_container = $params[ "koala_container" ] ) instanceof koala_container) )
			throw new Exception( "koala_container required as param for context '$context'", E_PARAMETER );
		return $koala_container->get_context_menu( $context );
	}
	
	function handle_path( $path, $owner = FALSE, $portal = FALSE)
	{
		if ( is_string( $path ) ) $path = url_parse_rewrite_path( $path );
		if ( !is_object( $owner ) )
			throw new Exception( "No 'owner' provided.", E_PARAMETER );
		
		if(!isset($portal) || !is_object($portal))
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}
		$portal_user = $portal->get_user();
		$user   = lms_steam::get_current_user();
		
		$backlink = $owner->get_url() . $this->get_path_name() . "/";
		
		$action = "";
		if ( isset( $path[ 0 ] ) && is_numeric( $path[ 0 ] ) ) {
			$backlink .= $path[ 0 ] . "/";
			$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), (int)$path[ 0 ], CLASS_CONTAINER );
			$koala_container = new koala_container( $container );
			if ( isset( $path[ 1 ] ) ) $action = $path[ 1 ];
		}
		else {
			$container = $this->get_documents_folder( $owner, TRUE );
			if ( ! is_object( $container ) )
				return;
			$koala_container = new koala_container_documents( $container, $backlink );
			if ( isset( $path[ 0 ] ) ) $action = $path[ 0 ];
		}
		
		$koala_container->set_types_invisible( CLASS_USER|CLASS_CALENDAR );
		$koala_container->set_obj_types_invisible( array( "container_wiki_koala", "KOALA_WIKI" ) );
		$html_handler = $owner->get_html_handler();
		$html_handler->set_context( "documents", array( "koala_container" => $koala_container ) );
		switch ( $action ) {
			case "new-folder":
				$environment = $container;
				unset( $container );
				unset( $koala_container );
				include( "container_new.php" );
				exit;
			break;
			
			case "edit":
				include( "container_edit.php" );
				exit;
			break;
			
			case "delete":
				include( "container_delete.php" );
				exit;
			break;
		}
		
		include( "container_inventory.php" );
		exit;
	}

	protected function get_documents_folder( $owner, $create = FALSE )
	{
		$workroom = $owner->get_workroom();
		$docs = $workroom->get_object_by_name( "documents" );
		if ( is_object( $docs ) && $docs->get_attribute( OBJ_TYPE ) === "room_documents_koala" )
			return $docs;
		else if ( ! $create || ! $workroom->check_access_insert( lms_steam::get_current_user() ) )
			return FALSE;
		$docs = steam_factory::create_room( $GLOBALS["STEAM"]->get_id(), "documents", $workroom);
		if ( !is_object( $docs ) )
			throw new Exception( "Could not create documents folder for " . get_class( $owner ) . " : id " . $owner->get_id() );
		$docs->set_attribute( OBJ_TYPE, "room_documents_koala" );
		return $docs;
	}

	function get_wrapper_class($obj)
	{	
		return FALSE;
	}

	function get_path_name()
	{
		return $this->get_name();
	}
	
	static function get_version() {
		return self::$version;
	}
}
?>
