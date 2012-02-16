<?php
require_once( PATH_LIB . "cache_handling.inc.php" );

class koala_group_default extends koala_group
{

				public function get_url ()
				{
					return PATH_URL . "groups/" . $this->get_id() . "/";
				}

				public function get_html_handler ()
				{
					return new koala_html_group( $this );
				}

				public function count_members()
				{
								return $this->steam_object->count_members();
				}

				public function get_members()
				{
								return $this->steam_object->get_members();
				}

				public function get_admins()
				{
								return $this->steam_object->get_admins();
				}

				public function is_public() {
								$cache = get_cache_function( "ORGANIZATION", CACHE_LIFETIME_STATIC );
								return $cache->call( "lms_steam::group_is_public", $this->get_id() );		
				}

				public function is_password_protected()
				{
								return $this->steam_object->has_password();
				}

				public function check_group_pw( $password )
				{
								return $this->steam_object->check_group_pw( $password );
				}

				public function is_admin( $user )
				{
					$ret = $this->steam_object->is_admin( $user );
          if (!$ret) {
            $ret = lms_steam::is_koala_admin( $user );
          }
					return $ret; 
				}

				public function is_member( $user )
				{
								return $this->steam_object->is_member( $user );
				}

				public function is_moderated()
				{
								if ( $this->is_password_protected() )
												return FALSE;
								$user = lms_steam::get_current_user();
								return !$this->steam_object->check_access( SANCTION_INSERT, $user );
				}

				public function add_admin( $user )
				{
								if ( ! $this->steam_object->is_member($user ) )
									$this->add_member( $user );
            $this->steam_object->set_sanction_all( $user );
            $this->steam_object->sanction_meta( SANCTION_ALL, $user );
            $workroom = $this->get_workroom();
            if (is_object($workroom)) {
            $workroom->set_sanction_all( $user );
            $workroom->sanction_meta( SANCTION_ALL, $user );
            }
            $cache = get_cache_function( $user->get_name() );
            $cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
            $cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
            $cache->drop( "lms_steam::user_get_profile", $user->get_name() );
            $cache->drop( "lms_portal::get_menu_html", $user->get_name(), TRUE );
            $cache = get_cache_function( $this->get_id() );
            $cache->drop( "lms_steam::group_get_members", $this->get_id() );
            return TRUE;
				}

				public function add_member( $user, $password = "" )
				{
            $result = $this->steam_object->add_member( $user, $password );
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
            $cache->drop( "lms_steam::user_get_profile", $user->get_name() );
            $cache->drop( "lms_portal::get_menu_html", $user->get_name(), TRUE );
								$cache = get_cache_function( $this->get_id() );
								$cache->drop( "lms_steam::group_get_members", $this->get_id() );
								return $result;
				}

				public function add_membership_request( $user )
				{
								return $this->steam_object->add_membership_request( $user );
				}

				public function requested_membership( $user )
				{
								return $this->steam_object->requested_membership( $user );
				}

				public function remove_membership_request( $user )
				{
								return $this->steam_object->remove_membership_request( $user );
				}

				public function remove_member( $user )
				{
          $ret = $this->steam_object->remove_member( $user );
          if ($ret) {
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
								$cache = get_cache_function( $this->get_id() );
								$cache->drop( "lms_steam::group_get_members", $this->get_id() );
            try {
              $this->steam_object->sanction( ACCESS_DENIED, $user );
              //$this->steam_object->sanction_meta( ACCESS_DENIED, $user );
              $workroom = $this->get_workroom();
              if (is_object($workroom)) {
                $workroom->sanction( ACCESS_DENIED, $user );
                //$workroom->sanction_meta( ACCESS_DENIED, $user );
              }
              $cache = get_cache_function( $user->get_name() );
            } catch(Exception $ex) {
              throw new Exception( "cannot reject access rights removing member", E_PARAMETER );
            }
          }
          return $ret;
				}

				public function get_membership_requests()
				{
								return $this->steam_object->get_membership_requests();
				}

	/**
	 * Returns the group's workroom. The group's documents folder is contained in
	 * the workroom and can be retrieved with get_documents_folder() instead.
	 * 
	 * @see get_documents_folder
	 *
	 * @return Object the group's workroom
	 */
	public function get_workroom()
	{
		return $this->steam_object->get_workroom();
	}

  static public function get_group_access_descriptions( ) {
    $ret = array(
      PERMISSION_GROUP_UNDEFINED => array(
      "label" =>  gettext( "Not defined." ),
      "summary_short" => gettext("-")),
      PERMISSION_GROUP_PRIVATE => array(
        "label" => gettext( "The group cannot be joined by users. Only group moderators can add users to this group." ),
        "summary_short" => gettext("Private")
      ),
      PERMISSION_GROUP_PUBLIC_FREEENTRY => array(
        "label" => gettext( "All users can join this group freely." ),
        "summary_short" => gettext("Public")
      ),
      PERMISSION_GROUP_PUBLIC_PASSWORD => array(
        "label" => gettext( "Users must enter the password joining the group." ),
        "summary_short" => gettext("Password")
      ),
      PERMISSION_GROUP_PUBLIC_CONFIRMATION => array(
        "label" => gettext( "Users application to join must be confirmed by a moderator of the group." ),
        "summary_short" => gettext("Confirmation")
      )
    );
    return $ret;
  }

  public function set_group_access($access = -1, $admins = 0, $access_attribute_key = KOALA_GROUP_ACCESS) {
    if ($access == PERMISSION_GROUP_UNDEFINED) return "";
    $all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
    $world_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Everyone" );
    $group = $this->get_steam_group();
    $workroom = $group->get_workroom();
    // Generally reset access rights here to be able to repair access rights on older groups

    // Re-Set access rights for non members
    $group->set_sanction($all_users, 0);
    $group->set_sanction($world_users, 0);
    $workroom->set_sanction($all_users, 0);
    $workroom->set_sanction($world_users, 0);
    // if admin group set, give access to admins
    if (is_object( $admins)) {
      $admins->set_sanction_all( $admins );
      $admins->sanction_meta( SANCTION_ALL, $admins);
      $group->set_sanction_all( $admins );
      $group->sanction_meta( SANCTION_ALL, $admins );
      $workroom->set_sanction_all( $admins );
      $workroom->sanction_meta( SANCTION_ALL, $admins );
    }
    // Disable acquiring
    $group->set_acquire(FALSE);
    switch( $access ) {
      case( PERMISSION_GROUP_PRIVATE ):
        // Nothing to do
      break;
      case( PERMISSION_GROUP_PUBLIC_FREEENTRY ):
        $group->set_insert_access( $world_users, TRUE );
        $group->set_read_access( $world_users, TRUE );
        $group->set_insert_access( $all_users, TRUE );
        $workroom->set_read_access( $all_users, TRUE );
      break;
      case( PERMISSION_GROUP_PUBLIC_PASSWORD ):
        $group->set_insert_access( $world_users, FALSE );
        $group->set_read_access( $world_users, FALSE );
        $group->set_insert_access( $all_users, FALSE );
        $workroom->set_read_access( $all_users, TRUE );
      break;
      case( PERMISSION_GROUP_PUBLIC_CONFIRMATION ):
        $group->set_insert_access( $world_users, FALSE );
        $group->set_read_access( $world_users, FALSE );
        $group->set_insert_access( $all_users, FALSE );
        $workroom->set_read_access( $all_users, TRUE );
      break;
      default:
        throw new Exception( "try to set invalid access on group access=" . $access, E_PARAMETER );
      break;
    }
    $group->set_attribute($access_attribute_key, $access);
  }
}
?>
