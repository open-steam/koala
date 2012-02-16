<?php

require_once( PATH_LIB . "format_handling.inc.php" );

class koala_html_user extends koala_html
{
	private $koala_user;

	public function __construct( $user )
	{
		if ( $user instanceof steam_user )
			$this->koala_user = new koala_user( $user );
		else if ( $user instanceof koala_user )
			$this->koala_user = $user;
		else
			throw new Exception( "'user' param is not a koala_user or steam_user", E_PARAMETER );
		parent::__construct( PATH_TEMPLATES . "user.template.html" );
	}

	public function set_context ( $context, $params = array() )
	{
		parent::set_context( $context, array_merge( $params, array( "owner" => $this->koala_user ) ) );
	}

	public function get_headline()
	{
		//$cache = get_cache_function( $this->koala_user->get_id() );
		$headline = array();
		$user_url = PATH_URL . "user/" . $this->koala_user->get_name() . "/";
		$headline[] = array( "name" => $this->koala_user->get_display_name(), "link" => $user_url );
		if ( is_string( $context = $this->get_context() ) ) {
			switch ( $context) {
				case "profile":
					$headline[] = array( "name" => gettext( "Profile" ), "link" => $user_url );
					return $headline;
				case "communication":
					$headline[] = array( "name" => gettext( "Communication" ), "link" => $user_url . "communication/" );
					return $headline;
				case "groups":
					$headline[] = array( "name" => gettext( "Groups" ), "link" => $user_url . "groups/" );
					return $headline;
				case "contacts":
					$headline[] = array( "name" => gettext( "Contacts" ), "link" => $user_url . "contacts/" );
					return $headline;
				case "clipboard":
					$clipboard = new koala_container_clipboard( $this->koala_user->get_steam_object() );
					return $clipboard->get_link_path();
			}
			// try extensions:
			foreach ($this->koala_user->get_extensions() as $extension)
			{
				$tmp_headline = $extension->get_headline( $headline, $this->get_context(), $this->get_context_params() );
				if ( is_array( $tmp_headline ) )
					return $tmp_headline;
			}
		}
		return $headline;
	}
	
	protected function get_context_menu( $context, $params = array() )
	{
		$context_menu = array();
		switch ( $context ) {

			case "clipboard":
				if ( isset( $params["koala_container"] ) )
					$koala_container = $params["koala_container"];
				else
					$koala_container = new koala_container_clipboard( $this->koala_user->get_steam_object() );
				$context_menu = array_merge( $context_menu, $koala_container->get_context_menu( $context ) );
			break;

		}
		
		// extensions context menu entries:
		foreach ($this->koala_user->get_extensions() as $extension)
		{
			$extension_context_menu = $extension->get_context_menu( $context, $params );
			if ( is_array( $extension_context_menu ) && !empty( $extension_context_menu ) ) {
				$context_menu = array_merge( $context_menu, $extension_context_menu );
			}
		}
		return $context_menu;
	}

	public function get_clipboard_menu ( $koala_container = FALSE )
	{
		// clipboard:
		$clipboard_menu = array();
		$koala_clipboard = new koala_container_clipboard( $this->koala_user->get_steam_object() );
		if ( is_object( $koala_container ) )
			$may_insert = $koala_container->get_steam_object()->check_access_insert( $this->koala_user->get_steam_object() );
		else $may_insert = FALSE;
		foreach ( $koala_clipboard->get_inventory() as $item ) {
			$koala_item = koala_object::get_koala_object( $item );
			$menu_item = array();
			if ( $item instanceof steam_container )
				$menu_item[ "link" ] = PATH_URL . "desktop/clipboard/" . $item->get_id() . "/";
			else
				$menu_item[ "link" ] = PATH_URL . "doc/" . $item->get_id() . "/";
			$menu_item[ "name" ] = $koala_item->get_display_name();
			if ( is_object( $icon = $item->get_attribute( "OBJ_ICON" ) ) )
				$menu_item[ "icon" ] = "<img src='" . PATH_URL . "cached/get_document.php?id=" . $icon->get_id() . "&type=objecticon&width=16&height=16' />";
			
			if ( $may_insert && $koala_container->accepts_object( $koala_item ) ) {
				$menu_item[ "menu" ] = array(
					array( "name" => gettext( "drop object" ), "link" => PATH_URL . "clipboard/drop/" . $item->get_id() . "/into/" . $koala_container->get_id(), "icon" => "<img title='Einfügen' src='" . PATH_STYLE . "images/paste.png' />" ),
					//array( "name" => gettext( "drop copy" ), "link" => PATH_URL . "clipboard/drop-copy/" . $item->get_id() . "/into/" . $container->get_id() ),
					//array( "name" => gettext( "drop link" ), "link" => PATH_URL . "clipboard/drop-link/" . $item->get_id() . "/into/" . $container->get_id() ),
				);
			}
			$clipboard_menu[] = $menu_item;
		}
		return new koala_html_menu( array( array( "name" => gettext( "Clipboard" ) . " (" . sizeof( $clipboard_menu ) . ")", "link" => $koala_clipboard->get_url(), "menu" => $clipboard_menu ) ) );
	}

	public function set_html_left( $html_code )
	{
		$this->template->setVariable( "HTML_CODE_LEFT", $html_code );
	}

	public function set_html_right( $html_code )
	{
		$this->template->setVariable( "HTML_CODE_RIGHT", $html_code );
	}

}

?>
