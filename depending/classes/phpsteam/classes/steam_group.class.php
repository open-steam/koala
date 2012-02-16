<?php

/**
 * steam_group
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 *
 */

/**
 *
 * @package     PHPsTeam
 */
class steam_group extends steam_object
{
	
	private $subGroupsLookupCache;
	
	public function get_type() {
		return CLASS_GROUP | CLASS_OBJECT;
	}
	
	/**
	 * function get_members:
	 * This function returns the members of the group
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return array with steam_user objects
	 */
	public function get_members( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"get_members",
		array( ),
		$pBuffer
		);
	}

	/**
	 * function add_membership_request:
	 *
	 * @param $pUser
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function add_membership_request( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"add_membership_request",
		array( $pUser ),
		$pBuffer
		);
	}

	/**
	 * function get_membership_request:
	 *
	 * Just for compatibility issues (Deprecated !)
	 * @param $pBuffer
	 *
	 * @deprecated  use get_membership_requests instead
	 */
/*	public function get_membership_request($pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
				"get_requests",
		array(),
		$pBuffer
		);
	}*/

	/**
	 * function get_membership_requests:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_membership_requests($pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
				"get_requests",
		array(),
		$pBuffer
		);
	}

	public function requested_membership( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"requested_membership",
		array( $pUser ),
		$pBuffer
		);
	}
	public function remove_membership_request( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"remove_membership_request",
		array( $pUser ),
		$pBuffer
		);
	}
	public function invite_user( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"invite_user",
		array( $pUser ),
		$pBuffer
		);
	}
	public function remove_invitation( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"remove_invite",
		array( $pUser ),
		$pBuffer
		);
	}
	public function is_invited( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"is_invited",
		array( $pUser ),
		$pBuffer
		);
	}
	public function get_invited($pBuffer = 0)
	{
		return $this->steam_command(
		$this,
				"get_invited",
		array(),
		$pBuffer
		);
	}
	public function get_parent_and_group_name( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"parent_and_group_name",
		array(),
		$pBuffer
		);
	}
	public function check_group_pw( $pString, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"check_group_pw",
		array( $pString ),
		$pBuffer
		);
	}
	/**
	 * function add_member:
	 *
	 * @param $pNewMember
	 * @param $pPassword
	 *
	 * @return
	 */
	public function add_member( $pNewMember, $pPassword = "" )
	{
		return $this->steam_command(
		$this,
				"add_member",
		array( $pNewMember, $pPassword ),
		0
		);
	}

	/**
	 * function add_members:
	 *
	 * @param $pNewMembers
	 * @param $pPassword
	 *
	 * @return
	 */
	public function add_members( $pNewMembers, $pPassword = "" )
	{
		foreach( $pNewMembers as $new_member )
		{
			$this->steam_command(
			$this,
					"add_member",
			array( $new_member, $pPassword ),
			0
			);
		}
		return $this->steam_buffer_flush();
	}

	/**
	 * function remove_member
	 *
	 * @param $pMemeber
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function remove_member( $pMember, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"remove_member",
		array( $pMember ),
		$pBuffer
		);
	}

	/**
	 * function is_member:
	 * This function returns if a user is member of the group or not
	 *
	 * * <code>
	 * $myGroup->is_member($the_user_to_check);
	 * </code>
	 *
	 * @param Boolean $pBuffer send now or buffer request?
	 * @param steam_user $pUser the user you want to check
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return Boolean returns 1 if true, 0 if not
	 */
	public function is_member( $pUser, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"is_member",
		array( $pUser ),
		$pBuffer
		);
	}

	/**
	 * function count_members:
	 * This function counts the number of members
	 * Example:
	 * <code>
	 * $number_of_members = $myGroup->count_members();
	 * </code>
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return int the number of members
	 */
	public function count_members( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"count_members",
		array(),
		$pBuffer
		);
	}

	/**
	 *function is_admin:
	 * This function checks if a member is a admin or not
	 *
	 * <code>
	 * $myGroup->is_admin(the_user_to_check);
	 * </code>
	 *
	 * @param steam_user $pMember the member to check if it is admin
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return Boolean returns 1 if true, 0 if false
	 */
	public function is_admin( $pMember, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"is_admin",
		array( $pMember ),
		$pBuffer
		);
	}

	/**
	 *function set_admin:
	 * This function defines an admin for this group
	 *
	 * <code>
	 * $myGroup->set_admin(user);
	 * </code>
	 *
	 * @param steam_user $pMember the member define as group admin
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return Boolean returns 1 if true, 0 if false
	 */
	public function set_admin( $pMember, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"set_admin",
		array( $pMember ),
		$pBuffer
		);
	}

	/**
	 * function get_admins:
	 * This function returns the admins of the group
	 *
	 * Example:
	 * <code>
	 * $admins = $myGroup->get_admins();
	 * </code>
	 * @param Boolean $pBuffer send now or buffer request?
	 *
	 * @return array with steam_user objects
	 */
	public function get_admins($pBuffer = 0)
	{
		return $this->steam_command(
		$this,
				"get_admins",
		array(),
		$pBuffer
		);
	}


	/**
	 * function set_password:
	 *
	 * @param $pPassword
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function set_password( $pPassword, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"set_group_password",
		array( (string) $pPassword ),
		$pBuffer
		);
	}

	public function has_password( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"has_password",
		array(),
		$pBuffer
		);
	}
	/**
	 * function get_parent_group:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_parent_group($pBuffer = 0)
	{
		return $this->steam_command(
		$this,
				"get_parent",
		array(),
		$pBuffer
		);
	}

	public function is_parent( $pGroup )
	{
		$parent = $pGroup->get_parent_group();
		while ( $parent instanceof steam_group )
		{
			if ( $parent->get_id() == $this->get_id() )
			{
				return TRUE;
			}
			$parent = $parent->get_parent_group();
		}
		return FALSE;
	}
	/**
	 * function get_subgroups:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_subgroups($pBuffer = 0)
	{
		if (!isset($this->subGroupsLookupCache)) {
			$result = $this->steam_command($this,
												"get_sub_groups",
										array(),
										$pBuffer
					);
			if (is_array($result)) {
				$this->subGroupsLookupCache = $result;
			}
		} else {
			$result = $this->subGroupsLookupCache;
		}
		return $result;
	}

	/**
	 *function create_subgroup:
	 *
	 *@param $pName
	 *@param $pEnviroment
	 *@param $pDescription
	 *
	 *@return
	 */
	public function create_subgroup( $pName, $pEnvironment = FALSE, $pDescription = "" )
	{
		return steam_factory::create_group(
		$this->steam_connectorID,
		$pName,
		$this,
		$pEnvironment,
		$pDescription
		);
	}

	/**
	 *function set_name:
	 *
	 *@param $pName
	 *@param $pBuffer
	 *
	 *@return
	 */
	public function set_name( $pName, $pBuffer = FALSE )
	{
		$myConnector = steam_connector::get_instance($this->steam_connectorID);
		return $this->steam_command(
		$myConnector->get_factory(CLASS_GROUP),
      	"rename_group",
		array(
		$this,
		$pName
		),
		$pBuffer
		);
	}

	/**
	 * function get_calendar:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_calendar( $pBuffer = 0 )
	{
		return $this->get_attribute(
				"GROUP_CALENDAR",
		$pBuffer
		);
	}

	/**
	 * function get_workroom:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_workroom( $pBuffer = 0 )
	{
		return $this->get_attribute(
				"GROUP_WORKROOM",
		$pBuffer
		);
	}

	/**
	 * Returns the group's emails, optionally filtered by object class,
	 * attribute values or pagination.
	 * The description of the filters and sort options can be found in the
	 * filter_objects_array() function of the open-sTeam "searching" module.
	 *
	 * Example:
	 * Return the 10 newest mails whose subjects do not start with "{SPAM}",
	 * sorted by date.
	 * get_mails_filtered(
	 *   array( // filters:
	 *     array( "-", "attribute", "OBJ_DESC", "prefix", "{SPAM}" ),
	 *     array( "+", "class", CLASS_DOCUMENT ),
	 *   ),
	 *   array( // sort:
	 *     array( ">", "attribute", "OBJ_CREATION_TIME" )
	 *   ), 0, 10 );
	 *
	 * @param $pFolder (optional) mail folder from which to return the mails
	 *   (if not specified, then the inbox of the group is used)
	 * @param $pFilters (optional) an array of filters (each an array as described
	 * in the "searching" module) that specify which objects to return
	 * @param $pSort (optional) an array of sort entries (each an array as described
	 *   in the "searching" module) that specify the order of the items
	 * @param $pOffset (optional) only return the objects starting at (and including)
	 *   this index
	 * @param $pLength (optional) only return a maximum of this many objects
	 * @param $pBuffer Send now or buffer request?
	 * @return an array of objects that match the specified filters, sort order and
	 *   pagination
	 */
	public function get_mails_filtered ( $pFolder = FALSE, $pFilters = array(), $pSort = array(), $pOffset = 0, $pLength = 0, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"get_mails_filtered",
		array( $pFolder, $pFilters, $pSort, $pOffset, $pLength ),
		$pBuffer
		);
	}

	/**
	 * function mail:
	 *
	 * Sends a message to the group through the internal mail system
	 * which is automatically forwarded to its members.
	 * If the individual receiver has set its attribute USER_FORWARD_MSG as
	 * true, this message will be delivered also as e-mail to its account.
	 * @param String  $pSubject message's subject
	 * @param String  $pHtmlMessageBody message's message body in html Format.
	 * @param String  $pSender If $pSender is not specified ( == 0) the server will use the Firstname, Lastname  of the current user and constructs the users email address using the users name and the server address. If $Sender is specified please note that you must specify the $pSender in the following format as string: "\"" . $name . "\"<login@server.com>" with $name as printed quotable encoded string. You may use steam_connector::quoted_printable_encode() to encode the name correctly. An Example: $userobject->mail( "a subject", " a message", "\"" . steam_connector::quoted_printable_encode("Firstname Lastname") . "\"<login@server.com>");
	 * @param Boolean $pBuffer Send now or buffer request
	 */
	public function mail( $pSubject, $pHtmlMessageBody, $pSender = 0, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
				"mail",
		array(
		$pHtmlMessageBody,
					$pSubject,
					$pSender),
				$pBuffer
				);
	}

	public function get_groupname( $pBuffer = 0 )
	{
		return $this->steam_command(
			$this,
			"get_group_name",
			array(),
			$pBuffer
		);
	}
	
	public function drop_subGroupsLookupCache() {
		$this->subGroupsLookupCache = null;
	}
	
	public function get_subgroup_by_name($subgroup_name) {
		return steam_factory::get_group($this->steam_connectorID, $this->get_groupname() . '.' . $subgroup_name);
	}
}

?>