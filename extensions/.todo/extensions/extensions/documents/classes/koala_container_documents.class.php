<?php

class koala_container_documents extends koala_container
{
	public function __construct ( $steam_object, $link_base = FALSE )
	{
		parent::__construct( $steam_object, $link_base );
		$this->set_types_invisible( CLASS_USER|CLASS_CALENDAR );
		$this->set_obj_types_invisible( array( "container_wiki_koala", "KOALA_WIKI" ) );
	}

	public function get_display_name()
	{
		$creator = lms_steam::get_root_creator( $this->steam_object );
		if ( $creator instanceof steam_user && $creator->get_id() == lms_steam::get_current_user()->get_id() )
			return gettext( "Your documents" );
		$koala_creator = koala_object::get_koala_object( $creator );
		return str_replace( "%NAME", $koala_creator->get_display_name(), gettext( "%NAME's documents" ) );
	}

	public function get_url()
	{
		return koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) )->get_url() . "documents/";
	}

	protected function get_link_path_internal( $top_object )
	{
		$koala_creator = koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) );
		$link_path = $koala_creator->get_link_path( $top_object );
		$link_path[] = array( "name" => $this->get_display_name(), "link" => $koala_creator->get_url() . "documents/", "koala_obj" => $this, "obj" => $this->steam_object );
		return $link_path;
	}

	public function accepts_object( $koala_object )
	{
		if ( $koala_object instanceof lms_wiki || $koala_object instanceof lms_forum || $koala_object instanceof lms_weblog )
			return FALSE;
		$steam_object = $koala_object->get_steam_object();
		if ( $steam_object instanceof steam_user )
			return FALSE;
		if ( !($steam_object instanceof steam_container) && !($steam_object instanceof steam_document) &&  !($steam_object instanceof steam_docextern))
			return FALSE;
		$obj_type = $koala_object->get_attribute( OBJ_TYPE );
		if ( strrpos( $obj_type, 'unit_koala' ) == strlen( $obj_type) - 10 )
			return FALSE;
		return TRUE;
	}

	public function get_context_menu( $context, $params = array() )
	{
		if ( $context !== "documents" ) return array();
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_insert( $user ) ) {
			      		(CREATE_FOLDER) ? $menu[] = array( "name" => gettext( "Create folder" ), "link" => $this->get_link_base() . "new-folder" ) : "";
			(UPLOAD_DOCUMENT) ? $menu[] = array( "name" => gettext( "Upload document" ), "link" => PATH_URL . "upload.php?env=" . $this->get_id() ) : "";
      		(ADD_WEBLINK) ? $menu[] = array( "name" => gettext( "Create Weblink" ), "link" => PATH_URL . "docextern_create.php?env=" . $this->get_id() ) : "";
		}
		return $menu;
	}

}

?>
