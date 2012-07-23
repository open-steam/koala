<?php

require_once( PATH_LIB . "format_handling.inc.php" );

class koala_html_profile extends koala_html
{
	private $steam_user;
	public  $networking_profile;

	public function __construct( $steam_user )
	{
		if ( ! $steam_user instanceof steam_user )
		throw new Exception( "not a user", E_PARAMETER );

		parent::__construct( PATH_EXTENSIONS . "content/profile/ui/html/profile.template.html" );

		$this->steam_user = $steam_user;
		$this->networking_profile = new lms_networking_profile( $steam_user );
		$this->networking_profile->count_profile_visit( lms_steam::get_current_user() );
		$cache = get_cache_function( $steam_user->get_name(), 86400 );
		$user_profile = $cache->call( "lms_steam::user_get_profile", $steam_user->get_name() );
		$itemId = $user_profile[ "OBJ_ICON" ];
		$icon_link = ( $user_profile[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "download/image/".$itemId."/140/185";
		//$icon_link = ( $user_profile[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $user_profile[ "OBJ_ICON" ] . "&type=usericon&width=140&height=185";
		$this->template->setVariable( "USER_IMAGE", $icon_link);
		$this->template->setVariable( "GIVEN_NAME", $user_profile[ "USER_FIRSTNAME" ] );
		$this->template->setVariable( "FAMILY_NAME", $user_profile[ "USER_FULLNAME" ] );
		if ( ! empty( $user_profile[ "USER_ACADEMIC_TITLE" ] ) )
		{
			$this->template->setVariable( "ACADEMIC_TITLE", $user_profile[ "USER_ACADEMIC_TITLE" ] );
		}
		if ( ! empty( $user_profile[ "USER_ACADEMIC_DEGREE" ] ) )
		{
			$this->template->setVariable( "ACADEMIC_DEGREE", "(" . $user_profile[ "USER_ACADEMIC_DEGREE" ] . ")" );
		}
		$user = lms_steam::get_current_user();
		if( lms_steam::is_koala_admin($user) )
		{
			$this->template->setVariable( "LABEL_LAST_LOGIN", gettext("last login") . ":" );
			$this->template->setVariable( "LABEL_PAGE_HITS", gettext("page hits") . ":" );
			$this->template->setVariable( "LAST_LOGIN", how_long_ago( $user_profile[ "USER_LAST_LOGIN" ] ) );
			$this->template->setVariable( "PAGE_HITS", $this->networking_profile->get_profile_visits() );
		}

	}

	public function get_headline()
	{
		//$cache = get_cache_function( $this->steam_user->get_id() );
		$headline = array();
		if(PLATFORM_ID == "bid"){
			$user_url = PATH_URL . "home/";
		}else{
			$user_url = PATH_URL . "profile/";
		}		
		$user_name = h($this->steam_user->get_name()) ;
		$headline[] = array( "name" => h($this->steam_user->get_full_name()), "link" => $user_url );
		if ( is_string( $context = $this->get_context() ) ) {
			switch ( $context) {
				case "profile":
					$headline[] = array( "name" => gettext( "Profile" ), "link" => "" );
					break;
				case "documents":
					$headline[] = array( "name" => gettext( "Documents" ), "link" => "" );
					break;
				case "communication":
					$headline[] = array( "name" => gettext( "Communication" ), "link" => "" );
					break;
				case "groups":
					$headline[] = array( "name" => gettext( "Groups" ), "link" => "" );
					break;
				case "contacts":
					$headline[] = array( "name" => gettext( "Contacts" ), "link" => "" );
					break;
			}
		}
		return $headline;
	}

	public function get_menu( $params = array() )
	{
		$privacy = $this->menu_privacy();

		if ( $privacy["contacts"] && $privacy["groups"] )
		{
			$menu = array();
			$userName = $this->steam_user->get_name();
			//if (YOUR_PROFILE) $menu["profile"] = array("name" => gettext( "Profile" ), "link" => PATH_URL . "profile/" . $this->steam_user->get_name() . "/");
			//if (YOUR_GROUPS) $menu["groups"] = array("name" => gettext( "Groups" ), "link" => PATH_URL . "profile/" . $this->steam_user->get_name() . "/groups/");
			//if (YOUR_CONTACTS) $menu["contacts"] = array("name" => gettext( "Contacts" ), "link" => PATH_URL . "profile/" . $this->steam_user->get_name() . "/contacts/");
			if (YOUR_PROFILE) $menu["profile"] = array("name" => gettext( "Profile" ), "link" => PATH_URL . "profile/index/".$userName."/" );
			if (YOUR_GROUPS) $menu["groups"] = array("name" => gettext( "Groups" ), "link" => PATH_URL . "profile/" . "groups/". $userName."/");
			if (YOUR_CONTACTS) $menu["contacts"] = array("name" => gettext( "Contacts" ), "link" => PATH_URL . "profile/" . "contacts/".$userName."/" );
			//if (($this->steam_user->get_id() == lms_steam::get_current_user()->get_id())  && YOUR_FAVORITES) $menu["favorites"] = array("name" => gettext( "Favorites" ), "link" => PATH_URL . "favorite/" . "index/");
			if (($this->steam_user->get_id() == lms_steam::get_current_user()->get_id())  && YOUR_FAVORITES) $menu["favorites"] = array("name" => "Favoriten", "link" => PATH_URL . "favorite/" . "index/");
				
			return $menu;
		}
		else if ( $privacy["contacts"] && !$privacy["groups"] )
		{
				
			return array(
				"profile" => array(
					"name" => gettext( "Profile" ),
					"link" => PATH_URL . "profile/index/" . $this->steam_user->get_name() . "/"
					),
				"contacts" => array(
					"name" => gettext( "Contacts" ),
					"link" => PATH_URL . "profile/contacts/" . $this->steam_user->get_name() . "/contacts/"
					)
					);
		}
		else if ( !$privacy["contacts"] && $privacy["groups"] )
		{
			return array(
				"profile" => array(
					"name" => gettext( "Profile" ),
					"link" => PATH_URL . "profile/index" . $this->steam_user->get_name() . "/"
					),
				"groups" => array(
					"name" => gettext( "Groups" ),
					"link" => PATH_URL . "profile/groups" . $this->steam_user->get_name() . "/groups/"
					)
					);
		}
		else
		{
			return array(
				"profile" => array(
					"name" => gettext( "Profile" ),
					"link" => PATH_URL . "profile/index/" . $this->steam_user->get_name() . "/"
					)
					);
		}
	}

	public function menu_privacy()
	{
		$current_user = lms_steam::get_current_user();
		$user = $this->steam_user;
		$cache = get_cache_function( $user->get_name(), 3600 );
		$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $user->get_name() );

		(isset($user_privacy[ "PRIVACY_CONTACTS" ])) ? $contact_authorization = $user_privacy[ "PRIVACY_CONTACTS" ] : $contact_authorization = "";
		(isset($user_privacy[ "PRIVACY_GROUPS" ])) ? $group_authorization = $user_privacy[ "PRIVACY_GROUPS" ] : $group_authorization = "";

		$confirmed = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
		$contacts = $cache->call( "lms_steam::user_get_buddies", $user->get_name(), $confirmed );

		$contact_ids = array();
		foreach ($contacts as $contact)
		{
			$contact_ids[] = $contact["OBJ_ID"];
		}

		$is_contact = in_array( $current_user->get_id(), $contact_ids );

		$result = array( "contacts" => false, "groups" => false );

		if ( !( $contact_authorization & PROFILE_DENY_ALLUSERS ) ) $result["contacts"] = true;
		if ( $is_contact && !( $contact_authorization & PROFILE_DENY_CONTACTS ) ) $result["contacts"] = true;

		if ( !( $group_authorization & PROFILE_DENY_ALLUSERS ) ) $result["groups"] = true;
		if ( $is_contact && !( $group_authorization & PROFILE_DENY_CONTACTS ) ) $result["groups"] = true;

		return $result;
	}

	public function get_context_menu( $context, $params = array() )
	{
		$current_user = lms_steam::get_current_user();
		// own profile:
		if ( $current_user->get_id() == $this->steam_user->get_id() )
		{
			switch ( $context ) {
				case "profile":
					if(CHANGE_PROFILE_DATA | CHANGE_PROFILE_PICTURE | CHANGE_PROFILE_PRIVACY | PROFILE_VISITORS){
						return array(
						(CHANGE_PROFILE_DATA && PROFILE_EDIT) ? array( "link" => PATH_URL . "profile/edit/", "name" => gettext( "Edit profile" ) ) : "",
						(CHANGE_PROFILE_PICTURE && PROFILE_PICTURE) ? array( "link" => PATH_URL . "profile/image/", "name" => gettext( "Change buddy icon" ) ) : "",
						(CHANGE_PROFILE_PRIVACY && PROFILE_PRIVACY) ? array( "link" => PATH_URL . "profile/privacy/", "name" => gettext( "Profile Privacy" ) ) : "",
						(USERMANAGEMENT && CHANGE_PASSWORD) ? array( "link" => PATH_URL . "usermanagement", "name" => "Passwort ändern" ) : "",
						(PROFILE_VISITORS) ? array( "link" => PATH_URL . "profile_visitors.php", "name" => gettext( "Visitors of your profile" ) ) : ""
							
						);
					}
					break;
				case "groups":
					if(SHOW_ALL_PUBLIC_GROUPS | CREATE_GROUPS){
						return array(
						(SHOW_ALL_PUBLIC_GROUPS) ? array( "link" => PATH_URL . "groups/", "name" => gettext("Show public groups" ) ) : "",
						(CREATE_GROUPS) ? array( "link" => PATH_URL . "groups_create.php?parent=" . STEAM_PUBLIC_GROUP, "name" => gettext( "Create group" ) ) : ""
						);
					}
					break;
				case "contacts":
					if(USER_SEARCH){
						return array(
						array( "link" => PATH_URL . "search/people/",
							"name" => gettext( "Search a person")
						)
						);}
						break;
				default:
					return array();
			}
		}
		// another user's profile:
		else
		{
			$context_menu = array();
			$current_users_cache = get_cache_function( $current_user->get_id(), 8640 );
			$current_users_buddies = $current_users_cache->call( "lms_steam::user_get_buddies", $current_user->get_name(), FALSE );

			$is_buddy = FALSE;
			foreach( $current_users_buddies as $buddy )
			{
				if ( $buddy[ "OBJ_ID" ] == $this->steam_user->get_id() )
				{
					$is_buddy = TRUE;
					break;
				}
			}
			$contact_confirmed = $this->steam_user->contact_is_confirmed( $current_user );

			if ( ! $is_buddy ){
				$toconfirm = $current_user->get_attribute("USER_CONTACTS_TOCONFIRM");
				if (!is_array($toconfirm)) $toconfirm = array();
				$confirm_necessary = FALSE;
				foreach($toconfirm as $tc) {
					if (is_object($tc) && $tc->get_id() == $current_user->get_id()) {
						$confirm_necessary = TRUE;
						break;
					}
				}
				if ( !$confirm_necessary ) {
					(PROFILE_MANAGE_CONTACT) ? $context_menu[] = array( "link" => PATH_URL . "contact_add.php?id=" . $this->steam_user->get_id(),
						"name" => gettext( "Add this person as a contact" ) ) : "";
				} else {
					(PROFILE_MANAGE_CONTACT) ? $context_menu[] = array( "link" => PATH_URL . "contact_confirm.php?id=" . $this->steam_user->get_id(),
						"name" => gettext( "Confirm this contact" ) ) : "";
				}

				(PROFILE_SEND_MAIL) ? $context_menu[] = array( "link" => PATH_URL . "messages_write.php?to=" . $this->steam_user->get_name(),
					"name" => gettext( "Send this person a message" ) ) : "";
			} else {
				(PROFILE_SEND_MAIL) ?  $context_menu[] = array( "link" => PATH_URL . "messages_write.php?to=" . $this->steam_user->get_name(),
					"name" => gettext( "Send this person a message" ) ) : "";
				(PROFILE_INTRODUCE_PERSON) ? $context_menu[] = array( "link" => PATH_URL . "contact_introduction.php?type=introduction&id=" . $this->steam_user->get_id(),
					"name" => gettext( "Introduce this person" ) ) : "";
				(PROFILE_MANAGE_CONTACT) ? $context_menu[] = array( "link" => PATH_URL . "contact_delete.php?id=" . $this->steam_user->get_id(),
					"name" => gettext( "Delete contact to this person" ) ) : "";
			}
			return $context_menu;
		}
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
