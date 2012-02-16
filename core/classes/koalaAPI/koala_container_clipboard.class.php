<?php

class koala_container_clipboard extends koala_container
{
	function get_display_name()
	{
		if ( $this->steam_object->get_id() == lms_steam::get_current_user()->get_id() )
			return gettext( "Your clipboard" );
		return gettext( "Clipboard" );
	}

	function get_url()
	{
		if ( $this->steam_object->get_id() == lms_steam::get_current_user()->get_id() )
			return PATH_URL . "desktop/clipboard/";
		else
			return PATH_URL . "user/" . $this->get_name() . "/clipboard/";
	}

	protected function get_link_path_internal( $top_object )
	{
		if ( $this->steam_object->get_id() == lms_steam::get_current_user()->get_id() )
			return array( array( "name" => $this->get_display_name(), "link" => $this->get_url(), "koala_obj" => $this, "obj" => $this->steam_object ) );
		$koala_user = new koala_user( $this->steam_object );
		$link_path = $koala_user->get_link_path( $top_object );
		$link_path[] = array( "name" => $this->get_display_name(), "link" => $this->get_url(), "koala_obj" => $this, "obj" => $this->steam_object );
		return $link_path;
	}

	public function get_context_menu( $context, $params = array() )
	{
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_insert( $user ) ) {
			$menu[] = array( "name" => gettext( "Create folder" ), "link" => $this->get_link_base() . "new-folder" );
			$menu[] = array( "name" => gettext( "Upload document" ), "link" => PATH_URL . "upload.php?env=" . $this->get_id() );
      		$menu[] = array( "name" => gettext( "Create Weblink" ), "link" => PATH_URL . "docextern_create.php?env=" . $this->get_id() );
		}
		return $menu;
	}

}

?>
