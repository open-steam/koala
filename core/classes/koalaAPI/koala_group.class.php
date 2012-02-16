<?php

abstract class koala_group extends koala_object
{
	private $group_type = "group_default";

	public function __construct( $group )
	{
		if ( ! $group instanceof steam_group )
			throw new Exception( "Param is not a steam_group" );
		parent::__construct( $group );
	}

	protected function get_link_path_internal ( $top_object )
	{
		return array( -1 => $this->get_link() );
	}

	public function get_groupname()
	{
		return $this->steam_object->get_groupname();
	}

	public function get_steam_group()
	{
		return $this->steam_object;
	}

	public function get_parent_group()
	{
		return $this->steam_object->get_parent_group();
	}

	public function is_parent_group( $group )
	{
		return $this->steam_object->is_parent_group( $group );
	}

	public function is_parent( $group )
	{
		return $this->steam_object->is_parent( $group );
	}

	public function get_calendar()
	{
		return $this->steam_object->get_calendar();
	}

	public function subscribe( $password = "", $message = "" )
	{
		$user = lms_steam::get_current_user();
		$group_name = $this->get_display_name();
		if ( $this->is_password_protected() )
		{
			if ( $this->check_group_pw( $password ) )
			{
				$this->add_member( $user, $password );
				$result = array( "succeeds" => TRUE, "confirmation" => str_replace( "%GROUP", $group_name, str_replace( "%GROUP", $this->get_name(), gettext( "You have been added to '%GROUP'." ) ) ) );
			}
			else
			{
				$result = array( "succeeds" => FALSE, "problem" => gettext( "Wrong password." ), "hint" => gettext( "If you want to join and do not know the password, please contact a group moderator." ), "confirmation" => "" );
			}
		}
		else if ( $this->is_moderated() )
		{
			$user = lms_steam::get_current_user();
			$username = $user->get_full_name();
			
			$this->add_membership_request( $user );
			$admins = $this->get_admins();
			if ( ! is_array( $admins ) ) 
			{
				$admins = array( $admins );
			}
			$link = ( $this instanceof koala_group_course ) ? PATH_URL . SEMESTER_URL . "/" . $this->get_semester()->get_name(). "/" . $this->get_name() . "/requests/" : PATH_URL . "groups/" . $this->get_id() . "/requests/";
			foreach( $admins as $admin )
			{
				$adminname = $admin->get_full_name();
				$mailbody = str_replace( "%NAME", $adminname, gettext( "Dear %NAME," ) ) . "\n\n" . str_replace( array( "%NAME", "%GROUP" ), array( $username, $group_name ), gettext( "The user %NAME has requested membership for '%GROUP':" ) ) . "\n\n<b>$message</b>\n\n" . gettext( "Since you are a moderator for this group, you can affirm the membership or decline." ) . "\n\n" . str_replace( "%PAGE", "<a href=\"$link\">" . gettext( "open membership requests" ) . "</a>", gettext( "Please see %PAGE for further instructions." ) );
				//$admin->mail( "LLMS: Membership Request" , $message,  $user->get_attribute( "USER_EMAIL" ) );
				lms_steam::mail($admin, $user, gettext( "koaLA: Membership Request" ), $mailbody );
			}
			$result = array( "succeeds" => TRUE, "confirmation" => str_replace( "%GROUP", $group_name, str_replace( "%GROUP", $group_name, gettext( "Membership request for '%GROUP' has been sent." ) ) ) );
		}
		else
		{
			$this->add_member( $user );
			$result = array( "succeeds" => TRUE, "confirmation" => str_replace( "%GROUP", $group_name, gettext( "You have been added to '%GROUP'." ) ) );
		}
		return $result;
	}

	public function get_maxsize() {
		return $this->steam_object->get_attribute("GROUP_MAXSIZE");
	}

	public function get_members_group() { return $this->steam_object; }
	public function get_staff_group() { return FALSE; }
	public function get_admins_group() { return FALSE; }

	abstract function add_member( $user, $password = "" );
	abstract function add_admin( $user );
	abstract function add_membership_request( $user );
	abstract function get_membership_requests();
	abstract function requested_membership( $user );
	abstract function remove_membership_request( $user );
	abstract function remove_member( $user );
	abstract function count_members();
	abstract function is_member( $user );
	abstract function is_admin( $user );
	abstract function check_group_pw( $password );
	abstract function is_password_protected();
	abstract function is_moderated();
	abstract function get_workroom();
}
?>
