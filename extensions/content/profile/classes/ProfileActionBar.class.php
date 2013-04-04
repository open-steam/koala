<?php

class ProfileActionBar {
    
    private $user;
    private $current_user;
    private $context = "profile";
    
    function __construct($user, $current_user) {    
        $this->user = $user;
        $this->current_user = $current_user;
    }

    public function setContext($context) {
        $this->context = $context;
    }
    
    public function getActions() {
        $actions = array();
        if ($this->current_user->get_id() == $this->user->get_id()) {
            array_push($actions, array( "link" => PATH_URL . "profile/index/", "name" => "Profil"));
            if (CHANGE_PROFILE_PICTURE && PROFILE_PICTURE) array_push($actions, array( "link" => PATH_URL . "profile/image/", "name" => "Benutzerbild"));
            if (CHANGE_PROFILE_PRIVACY && PROFILE_PRIVACY) array_push($actions, array( "link" => PATH_URL . "profile/privacy/", "name" => gettext( "Profile Privacy" )));
            if (USERMANAGEMENT && CHANGE_PASSWORD) array_push($actions, array( "link" => PATH_URL . "usermanagement", "name" => "Passwort ändern"));
            if (YOUR_GROUPS) array_push($actions, array("name" => gettext( "Groups" ), "link" => PATH_URL . "profile/" . "groups/". $this->user->get_name()."/"));
            if (YOUR_CONTACTS) array_push($actions, array("name" => gettext( "Contacts" ), "link" => PATH_URL . "profile/" . "contacts/". $this->user->get_name() ."/" ));
            if (YOUR_FAVORITES) {
                if ($this->context === "profile") {
                    array_push($actions, array("name" => "Favoriten", "link" => PATH_URL . "favorite/index/"));
                } else if ($this->context === "favorite") {
                    array_push($actions, array("name" => "Favoriten suchen und hinzufügen", "link" => PATH_URL . "favorite/search/"));
                }
            }
            if (defined("PLATFORM_USERMANAGEMENT_URL")) array_push($actions, array("name" => "Benutzerverwaltung", "link" => PLATFORM_USERMANAGEMENT_URL));
            //if (PROFILE_VISITORS) array_push($actions, array( "link" => PATH_URL . "profile_visitors.php", "name" => gettext( "Visitors of your profile" )));
        } else {
            array_push($actions, array( "link" => PATH_URL . "profile/index/" . $this->user->get_name(), "name" => "Profil"));
            
            $current_users_cache = get_cache_function($this->current_user->get_id(), 8640);
            $current_users_buddies = $current_users_cache->call("lms_steam::user_get_buddies", $this->current_user->get_name(), FALSE);
            
            $is_buddy = FALSE;
            foreach($current_users_buddies as $buddy) {
                if ($buddy["OBJ_ID"] == $this->user->get_id() ) {
                    $is_buddy = TRUE;
                    break;
                }
            }
            
            if (!$is_buddy) {
                $toconfirm = $this->current_user->get_attribute("USER_CONTACTS_TOCONFIRM");
		if (!is_array($toconfirm)) $toconfirm = array();
                    $confirm_necessary = FALSE;
                    foreach($toconfirm as $tc) {
                        if (is_object($tc) && $tc->get_id() == $this->current_user->get_id()) {
                            $confirm_necessary = TRUE;
                            break;
			}
                    }
                    if (!$confirm_necessary) {
                        if (PROFILE_MANAGE_CONTACT) 
                            array_push($actions, array( "link" => PATH_URL . "favorite/add/" . $this->user->get_id() . "/user/", "name" => gettext( "Add this person as a contact" )));
                    } else {
                        if (PROFILE_MANAGE_CONTACT) 
                            array_push($actions, array( "link" => PATH_URL . "contact_confirm.php?id=" . $this->user->get_id(), "name" => gettext( "Confirm this contact" )));
                    }

                    if (PROFILE_SEND_MAIL) 
                        array_push($actions, array( "link" => PATH_URL . "messages_write.php?to=" . $this->user->get_name(), "name" => gettext( "Send this person a message" )));
            } else {
		if (PROFILE_SEND_MAIL) 
                    array_push($actions, array( "link" => PATH_URL . "messages_write.php?to=" . $this->user->get_name(), "name" => gettext( "Send this person a message" )));
		if (PROFILE_INTRODUCE_PERSON) 
                    array_push($actions, array( "link" => PATH_URL . "contact_introduction.php?type=introduction&id=" . $this->user->get_id(), "name" => gettext( "Introduce this person" )));
		if (PROFILE_MANAGE_CONTACT) 
                    array_push($actions, array( "link" => PATH_URL . "favorite/delete/" . $this->user->get_id() . "/", "name" => gettext( "Delete contact to this person" )));
            }
            
            $privacy = $this->menu_privacy();
            if ($privacy["contacts"] && $privacy["groups"]) {
                if (YOUR_GROUPS) array_push($actions, array("name" => gettext( "Groups" ), "link" => PATH_URL . "profile/" . "groups/". $this->user->get_name() ."/"));
		if (YOUR_CONTACTS) array_push($actions, array("name" => gettext( "Contacts" ), "link" => PATH_URL . "profile/" . "contacts/". $this->user->get_name() ."/"));
		//if (YOUR_FAVORITES) array_push($actions, array("name" => "Favoriten", "link" => PATH_URL . "favorite/index/" . $this->user->get_name() . "/"));
            } else if ($privacy["contacts"] && !$privacy["groups"]) { 
		if (YOUR_CONTACTS) array_push($actions, array("name" => gettext( "Contacts" ), "link" => PATH_URL . "profile/contacts/" . $this->user->get_name() . "/"));
            } else if (!$privacy["contacts"] && $privacy["groups"] ) {
		if (YOUR_GROUPS) array_push($actions, array("name" => gettext( "Groups" ), "link" => PATH_URL . "profile/groups/" . $this->user->get_name() . "/"));
            }
        }
        return $actions;
    }
    
    public function menu_privacy() {
		$cache = get_cache_function( $this->user->get_name(), 3600 );
		$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $this->user->get_name() );
		(isset($user_privacy[ "PRIVACY_CONTACTS" ])) ? $contact_authorization = $user_privacy[ "PRIVACY_CONTACTS" ] : $contact_authorization = "";
		(isset($user_privacy[ "PRIVACY_GROUPS" ])) ? $group_authorization = $user_privacy[ "PRIVACY_GROUPS" ] : $group_authorization = "";

		$contacts = $cache->call( "lms_steam::user_get_buddies", $this->user->get_name(), FALSE);

		$contact_ids = array();
		foreach ($contacts as $contact)
		{
			$contact_ids[] = $contact["OBJ_ID"];
		}
		$is_contact = in_array( $this->current_user->get_id(), $contact_ids );

		$result = array( "contacts" => false, "groups" => false );

		if ( !( $contact_authorization & PROFILE_DENY_ALLUSERS ) ) $result["contacts"] = true;
		if ( $is_contact && !( $contact_authorization & PROFILE_DENY_CONTACTS ) ) $result["contacts"] = true;

		if ( !( $group_authorization & PROFILE_DENY_ALLUSERS ) ) $result["groups"] = true;
		if ( $is_contact && !( $group_authorization & PROFILE_DENY_CONTACTS ) ) $result["groups"] = true;

		return $result;
	}
}
?>