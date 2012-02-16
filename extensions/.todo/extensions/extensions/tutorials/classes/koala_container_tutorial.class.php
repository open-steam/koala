<?php

class koala_container_tutorial extends koala_container
{
	public function __construct ( $steam_object, $link_base = FALSE )
	{
		parent::__construct( $steam_object, $link_base );
		$this->set_types_invisible( CLASS_USER|CLASS_CALENDAR );
		$this->set_obj_types_invisible( array( "container_wiki_koala", "KOALA_WIKI" ) );
	}

	public function get_display_name ()
	{
		return gettext( 'materials' );
	}

	public function get_url ()
	{
		return koala_object::get_koala_object( $this->steam_object->get_creator() )->get_url();
	}

	protected function get_link_path_internal ( $top_object )
	{
		return koala_object::get_koala_object( $this->steam_object->get_creator() )->get_link_path();
	}

	public function accepts_object ( $koala_object )
	{
		if ( $koala_object instanceof lms_wiki || $koala_object instanceof lms_forum || $koala_object instanceof lms_weblog )
			return FALSE;
		$steam_object = $koala_object->get_steam_object();
		if ( $steam_object instanceof steam_user )
			return FALSE;
		if ( !($steam_object instanceof steam_container) && !($steam_object instanceof steam_document) )
			return FALSE;
		$obj_type = $koala_object->get_attribute( OBJ_TYPE );
		if ( strrpos( $obj_type, 'unit_koala' ) == strlen( $obj_type) - 10 )
			return FALSE;
		return TRUE;
	}
}

?>
