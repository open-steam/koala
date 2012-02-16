<?php

class koala_container_workroom extends koala_container
{
	function get_display_name()
	{
		$creator = $this->steam_object->get_creator();
		if ( $creator instanceof steam_user && $creator->get_id() == lms_steam::get_current_user()->get_id() )
			return gettext( "Your workroom" );
		$koala_creator = koala_object::get_koala_object( $creator );
		return str_replace( "%NAME", $koala_creator->get_display_name(), gettext( "%NAME's workroom" ) );
	}

	protected function get_link_path_internal( $top_object )
	{
		$koala_creator = koala_object::get_koala_object( $this->steam_object->get_creator() );
		$link_path = $koala_creator->get_link_path( $top_object );
		
		if ( !($top_object instanceof koala_object) )
			$top_object = koala_object::get_koala_object( $top_object );
		
		// communication:
		if ( $top_object instanceof lms_weblog || $top_object instanceof lms_wiki || $top_object instanceof lms_forum )
			$link_path[] = array( "name" => gettext( "Communication" ), "link" => $koala_creator->get_url() . "communication/", "koala_obj" => $this, "obj" => $this->steam_object );
		
		$link_path[] = array( "name" => $this->get_display_name(), "link" => $koala_creator->get_url() . "documents/", "koala_obj" => $this, "obj" => $this->steam_object );
		return $link_path;
	}

	public function get_context_menu( $context, $params = array() )
	{
		return array();
	}

}

?>
