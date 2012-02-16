<?php

require_once( "HTML/Template/IT.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

class koala_html_group extends koala_html
{
	private $koala_group;

	public function __construct ( $group )
	{
		if ( $group instanceof koala_group )
			$this->koala_group = $group;
		else if ( $group instanceof steam_group )
			$this->koala_group = new koala_group_default( $group );
		else
			throw new Exception( "'group' param is not koala_group or steam_group", E_PARAMETER );
		parent::__construct( PATH_EXTENSIONS . "content/group/ui/html/" . "group_profile.template.html" );
	}

	public function set_context ( $context, $params = array() )
	{
		parent::set_context( $context, array_merge( $params, array( "owner" => $this->koala_group ) ) );
	}

	public function get_headline ()
	{
		$cache = get_cache_function( $this->koala_group->get_id() );
		$headline = array();
		$group_url = PATH_URL . "groups/" . $this->koala_group->get_id() . "/";
		if ( ( $category = $this->koala_group->get_steam_object()->get_environment() ) && $cache->call( "lms_steam::group_is_public", $this->koala_group->get_id() ) )
		{
			$headline[] = array( "name" => h($category->get_name()), "link" => PATH_URL . "groups/?cat=" . $category->get_id() );
			$headline[] = array( "name" => $this->koala_group->get_display_name(), "link" => $group_url );
		}
		else
		{
			$headline[] = array ( "name" => h($this->koala_group->get_name()) );
		}
		if ( is_string( $context = $this->get_context() ) ) {
			switch ( $context) {
				case "documents":  //TODO: move this into documents extension somehow?
					$headline[] = array( "name" => gettext( "Documents" ), "link" => "" );
					break;
				case "communication":
					$headline[] = array( "name" => gettext( "Communication" ), "link" => "" );
					break;
				case "members":
					$headline[] = array( "name" => gettext( "Members" ), "link" => "" );
					break;
			}
			// try extensions:
			foreach ($this->koala_group->get_extensions() as $extension)
			{
				$tmp_headline = $extension->get_headline( $headline, $this->get_context(), $this->get_context_params() );
				if ( is_array( $tmp_headline ) )
					return $tmp_headline;
			}
		}
		return $headline;
	}
	
	protected function get_menu ( $params = array() )
	{
		$menu = array(
			"start" => array(
				"name" => gettext( "Start page" ),
				"link" => PATH_URL . "group/view/" . $this->koala_group->get_id() . "/"
			),
			"communication" => array(
				"name" => gettext( "Communication" ),
				"link" => PATH_URL . "group/communication/" . $this->koala_group->get_id()
			)
		);
		$menu[ "members" ] = array(
				"name" => gettext( "Members" ),
				"link" => PATH_URL . "group/members/" . $this->koala_group->get_id()
		);
		
		// extensions menu entries:
/*		foreach ($this->koala_group->get_extensions() as $extension)
		{
			$extension_menu = $extension->get_menu( $params );
			if ( is_array( $extension_menu ) && !empty( $extension_menu ) )
				$menu[ "{$extension->get_path_name()}" ] = $extension_menu;
		}*/
		
		return $menu;
	}

	protected function get_context_menu ( $context, $params = array() )
	{ 
		if ( !isset($_SESSION[ "LMS_USER" ]) || !($_SESSION[ "LMS_USER" ] instanceof lms_user) || !$_SESSION[ "LMS_USER" ]->is_logged_in() )
			return array();
		$current_user = lms_steam::get_current_user();
		$current_group = $this->koala_group;
		$group_workroom = $current_group->get_workroom();

		$context_menu = array();
		/*
		if ( $context == "documents" ) {
			if ( ! isset( $params[ "koala_container" ] ) || !( ($koala_container = $params[ "koala_container" ] ) instanceof koala_container) )
				throw new Exception( "koala_container required as param for context '$context'", E_PARAMETER );
			$context_menu = array_merge( $context_menu, $koala_container->get_context_menu( $context ) );
		}
		*/
    $is_admin = $current_group->is_admin( $current_user );
		if ( $is_admin || $current_group->is_member( $current_user ) ) {
			if ( $is_admin ) {
				switch ( $context ) {
					case "start":
						$context_menu[] = array( "link" => PATH_URL . "group/manageGroup/editGroup/" . $current_group->get_id(), "name" => gettext("Preferences" ) );
						$context_menu[] = array( "link" => PATH_URL . "group/addAdmin/" . $current_group->get_id(), "name" => gettext( "Add new moderator" ) );
						// $context_menu[] = array( "link" => "", "name" => gettext( "Change group name" ) );
						// $context_menu[] = array( "link" => "", "name" => gettext( "Alter group description" ) );
					break;
					case "members":
						$context_menu[] = array( "link" => PATH_URL . "messages_write.php?group=" . $current_group->get_id(), "name" => gettext( "Write circular" ) );
						$context_menu[] = array( "link" => PATH_URL . "group/request/" . $current_group->get_id(), "name" => gettext( "Manage membership requests" ) );
						$context_menu[] = array( "link" => PATH_URL . "group/addMember/" . $current_group->get_id(), "name" => gettext( "Add member" ) );
					break;
					case "communication":
						$context_menu[] = array( "link" => PATH_URL . "weblog_new.php?env=" . $current_group->get_workroom()->get_id() . "&group=" . $current_group->get_id() , "name" => gettext( "Create new weblog" ) );
						$context_menu[] = array( "link"  => PATH_URL . "messageboard/newMessageboard/" . $current_group->get_workroom()->get_id() . "/" . $current_group->get_id() , "name" => gettext( "Create new forum" ) );
						$context_menu[] = array( "link" => PATH_URL . "wiki_new.php?env=" . $current_group->get_workroom()->get_id() . "&group=" . $current_group->get_id() , "name" => gettext( "Create new wiki" ) );
					break;
				}
			}
			if ( $context == "start" )
				$context_menu[] = array( "name" => gettext( "Calendar" ), "link" => PATH_URL . "calendar/index/" . $this->koala_group->get_id()  );
			if ( $context == "start" || $context == "members" )
				$context_menu[] = array( "link" => PATH_URL . "group/cancelGroup/" . $current_group->get_id(), "name" => gettext( "Cancel membership" ) );
		}
		else {
			$context_menu[] = array( "link" => PATH_URL . "group_subscribe.php?group=" . $current_group->get_id(), "name" => gettext( "Join this group" ) );
			$context_menu[] = array( "link" => PATH_URL . "user/" . $current_user->get_name(). "/groups/", "name" => gettext( "Manage subscriptions" ) );
		}
		if ( $context == "start" && $is_admin ) {
      $context_menu[] = array( "link" => PATH_URL . "group/deleteGroup/" . $current_group->get_id(), "name" => gettext("Delete group" ) );
    }
		
		// extensions context menu entries:
	/*	foreach ($this->koala_group->get_extensions() as $extension)
		{
			$extension_context_menu = $extension->get_context_menu( $context, $params );
			if ( is_array( $extension_context_menu ) && !empty( $extension_context_menu ) ) {
				$context_menu = array_merge( $context_menu, $extension_context_menu );
			}
		}*/
		
		return $context_menu;
	}


	public function set_html_left ( $html_code )
	{
		$this->template->setVariable( "HTML_CODE_LEFT", $html_code );
	}

	public function set_html_right ( $html_code )
	{
		$this->template->setVariable( "HTML_CODE_RIGHT", $html_code );
	}

	public function get_discussion ()
	{
		$workroom = $this->koala_group->get_workroom();
		if ( ! $discussion = $workroom->get_object_by_name( "public_discussion" ) )
		{
			return FALSE;
		}
		else
		{
			return $discussion->get_id();
		}
	}
}

?>
