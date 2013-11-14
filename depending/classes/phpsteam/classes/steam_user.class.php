<?php
/**
 * steam_user
 *
 * Class definition for the user of sTeam
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 *
 */
require_once( "steam_container.class.php" );

/**
 * The class steam_user
 *
 * How to start working with it? First, you need an instance of steam_connector
 * with an established connection. Let's say, this instance is $steam_con.
 * Then you can try this:
 * <code>
 * $it_is_me  = $steam_con->get_current_steam_user();
 * $here_i_am = $it_is_me->get_environment();
 * $it_is_me->move( $new_place );
 * $it_is_me->rucksack_set_current_folder( $document_folder );
 * $it_is_me->rucksack_drop_objects( $new_place. CLASS_DOCUMENTS );
 * </code>
 *
 * @package     PHPsTeam
 */
class steam_user extends steam_container
{

	private $rucksack_current_folder;
	
	public function get_type() {
		return CLASS_USER | CLASS_CONTAINER | CLASS_OBJECT;
	}

	public function decline_contact_request($contact_id){
		//Remove from USER_CONTACTS_TOCONFIRM
		$unconfirmed = $this->get_attribute("USER_CONTACTS_TOCONFIRM");
		if(!is_array($unconfirmed)) $unconfirmed = array();
		$found = FALSE;
		$s = count($unconfirmed);
		for($i =0;$i< $s; $i++){
			$tc = $unconfirmed[$i];
			if($tc->get_id() == $contact_id){
				$found = TRUE;
				unset($unconfirmed[$i]);
			}
		}
		if($found){
			$unconfirmed_old = array_values($unconfirmed);
			$this->set_attribute("USER_CONTACTS_TOCONFIRM", $unconfirmed);
		}
	}

	public function remove_contact($contact_id){
		$confirmed_contacts = $this->get_attribute( "USER_CONTACTS_CONFIRMED" );
		$user_favourites = $this->get_attribute( "USER_FAVOURITES" );
		if(!is_array($confirmed_contacts)) $confirmed_contacts = array();
		if(!is_array($user_favourites))$user_favourites = array();

		//REMOVE from USER_CONTACTS_CONFIREMD
		unset($confirmed_contacts[$contact_id]);
		$confirmed_contacts["_OBJECT_KEYS"] = "TRUE";
		$this->set_attribute("USER_CONTACTS_CONFIRMED", $confirmed_contacts);

		//REMOVE from USER_FAVOURITES
		$found = FALSE;
		$s = count($user_favourites);
		$my_id = $this->get_id();
		for($i =0;$i< $s; $i++){
			$tc = $user_favourites[$i];
			if($tc->get_id() == $contact_id){
				$found = TRUE;
				unset($user_favourites[$i]);
			}
		}
		if($found){
			$user_favourites = array_values($user_favourites);
			$this->set_attribute("USER_FAVOURITES", $user_favourites);
		}
	}

	public function add_contact($contact_id){
		$admin_steam = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
		$contact = steam_factory::get_object( $admin_steam->get_id(), $contact_id, CLASS_USER );

		//The user you are sending a request to is immediatly your contact
		$this->add_confirmed_contact($contact_id, $admin_steam);

		//This user have to be confirmed befor he is a also a conntact
		//of the target user

		$contact->add_unconfirmed_contact($this->get_id(), $admin_steam);
		$admin_steam->disconnect();
	}

	/**
	 * Add an unconfirmed contact.
	 *
	 * @param integer $contact_id the ID of the contact that should be added
	 */
	public function add_unconfirmed_contact($contact_id, $steam_connector = null){
		if(!isset($steam_connector)) $steam_connector = $GLOBALS["STEAM"];
		$unconfirmed_old = $this->get_attribute("USER_CONTACTS_TOCONFIRM");
		if(!is_array($unconfirmed_old)) $unconfirmed_old = array();
		$contact = steam_factory::get_object($steam_connector->get_id(), $contact_id, CLASS_USER );
		//Registry::get('logger')->info($this->get_name()." add " . $contact->get_name(). " to unconfirmed contacts.");


		$unconfirmed_new = $unconfirmed_old;
		$unconfirmed_new[] = $contact;


		$this->set_attribute("USER_CONTACTS_TOCONFIRM", $unconfirmed_new);
	}

	/**
	 * Add an confirmed contact. If contact was unconfirmed, removes contact from the "unconfirmed"-list.
	 *
	 * @param integer $contact_id the ID of the contact that should be added
	 */
	public function add_confirmed_contact($contact_id, $steam_connector = null){

		if(!isset($steam_connector)) $steam_connector = $GLOBALS["STEAM"];
		$confirmed_old = $this->get_attribute("USER_CONTACTS_CONFIRMED");
		$favourites_old = $this->get_attribute("USER_FAVOURITES");
		$to_confirm = $this->get_attribute("USER_CONTACTS_TOCONFIRM");
		$contact = steam_factory::get_object( $steam_connector->get_id(), $contact_id, CLASS_USER );
		//Registry::get('logger')->info($this->get_name()." add " . $contact->get_name(). " to confirmed contacts.");

		if(!is_array($confirmed_old)) $confirmed_old = array();
		if(!is_array($favourites_old)) $favourites_old = array();
		if(!is_array($to_confirm)) $to_confirm = array();

		$contact = steam_factory::get_object( $steam_connector->get_id(), $contact_id, CLASS_USER );

		//check if attribute 'user_contacts_toconfirm' contains the contact
		//if contact is in the "user_contacts_toconfirm"-list
		//	remove the contacts from the list and add the contact to
		//	USER_CONTACTS_CONFIRMED
		$found = FALSE;//array_search($contact, $to_confirm);
		$s = count($to_confirm);
		for($i =0;$i< $s; $i++){
			$tc = $to_confirm[$i];
			if($tc->get_id() == $contact->get_id()){
				$found = TRUE;
				unset($to_confirm[$i]);
			}
		}
		if($found){
			$to_confirm = array_values($to_confirm);
			$this->set_attribute("USER_CONTACTS_TOCONFIRM", $to_confirm);
		}


		$confirmed_new = $confirmed_old;

		//Ugly! Used because of backwards compatibility
		$confirmed_new[$contact->get_id()] = 1;
		$confirmed_new["_OBJECT_KEYS"] = "TRUE";

		$this->set_attribute("USER_CONTACTS_CONFIRMED", $confirmed_new);

		//Backward compatibility: Also add contact to favourites
		$favourites_new = $favourites_old;
		$favourites_new[] = $contact;
		$this->set_attribute("USER_FAVOURITES", $favourites_new);
	}


	public function get_full_name( ) {
		$fullname = $this->get_attribute( "USER_FULLNAME");
		$firstname = $this->get_attribute( "USER_FIRSTNAME");
		if ( !is_string( $firstname ) ) return $fullname;
		else return $firstname . " " . $fullname;
	}

	/**
	 * function get_workroom:
	 *
	 * Returns the user's workroom
	 *
	 * Every steam_user has its own workroom; an area, where the user
	 * can structure information and objects on her/his own accord.
	 * This function returns the steam_room object for that workroom.
	 *
	 * @return steam_room user's workroom
	 */
	public function get_workroom($pBuffer = FALSE)
	{
		return $this->get_attribute( "USER_WORKROOM", $pBuffer );
	}

	/**
	 * function get_trashbin:
	 *
	 * Returns the user's trashbin
	 *
	 * Every steam_user has its own trashhbin. the trashbin is located in the
	 * users workroom by default
	 *
	 * @return steam_room user's workroom
	 */
	public function get_trashbin($pBuffer = FALSE)
	{
		return $this->get_attribute( "USER_TRASHBIN", $pBuffer );
	}

	/**
	 * function get_calendar:
	 *
	 * Returns user calendar
	 *
	 * @return steam_calendar User calendar
	 */
	public function get_calendar($pBuffer = FALSE)
	{
		return $this->get_attribute( "USER_CALENDAR", $pBuffer );
	}

	/**
	 * function rucksack_get_inventory:
	 *
	 * Returns the content of the user's rucksack
	 *
	 * The metaphor of virtual knowledge rooms consider
	 * a user as a container; therefore, users are able
	 * to move between rooms and use themselves as a
	 * rucksack to carry objects from one room to another.
	 * The function returns the content of the container
	 * personate as the user.
	 *
	 * @see steam_container::get_inventory
	 * @param string $pClass if you ask for objects of a specific type, you can optionally use this byte-string for the typedefinition (see steam_types.conf.php)
	 * @return mixed Array of steam_objects
	 */
	public function rucksack_get_inventory( $pClass = "", $pAttributes = array(), $pSort = SORT_DATE, $pFollowLinks = TRUE )
	{
		return $this->rucksack_get_current_folder()->get_inventory( $pClass, $pAttributes, $pSort, $pFollowLinks  );
	}

	/**
	 * function rucksack_drop_objects:
	 *
	 * Drops objects from rucksack's current folder to specified environment
	 * Example:
	 * <code>
	 * $me->move( $somewhere );
	 * $me->rucksack_drop_objects( $somewhere, CLASS_DOCUMENTS )
	 * </code>
	 *
	 * @param steam_container $pNewEnvironment New place where the objects should move to
	 * @param string $pClass if you ask for objects of a specific type, you can optionally use this byte-string for the typedefinition (see steam_types.conf.php)
	 */
	public function rucksack_drop_objects( $pNewEnvironment = "", $pClass = "" )
	{
		$pNewEnvironment = empty( $pNewEnvironment ) ? $this->get_environment() : $pNewEnvironment ;
		$all_items = $this->rucksack_get_current_folder()->get_inventory( $pClass, array(), SORT_DATE, FALSE );
		foreach( $all_items as $single_item )
		{
			$single_item->move( $pNewEnvironment, 1 );
		}
		return $this->steam_buffer_flush();
	}

	/**
	 * function rucksack_set_current_folder:
	 *
	 * Sets the rucksack's current folder
	 * @param steam_container $pCurrentFolder
	 */
	public function rucksack_set_current_folder( $pCurrentFolder = "" )
	{
		$pCurrentFolder = empty( $pCurrentFolder ) ? $this : $pCurrentFolder;
		// check, if currentfolder is located in rucksack!
		$this->rucksack_current_folder = $pCurrentFolder;
	}

	/**
	 * function rucksack_get_current_folder:
	 *
	 * Returns the current folder of the rucksack
	 * @return steam_container The rucksack's current folder
	 */
	public function rucksack_get_current_folder()
	{
		return $this->rucksack_current_folder;
	}

	/**
	 * function rucksack_get_subfolders:
	 *
	 * Returns the subfolders of the rucksack's current folder
	 * @return mixed Array of steam_container objects
	 */
	public function rucksack_get_subfolders()
	{
		return $this->rucksack_get_current_folder()->get_inventory( CLASS_CONTAINER );
	}

	/**
	 * function rucksack_get_prior_folder:
	 *
	 * Returns the folder where the current rucksack folder is located in
	 * @return steam_container Environment of the current rucksack folder
	 */
	public function rucksack_get_prior_folder()
	{
		return $this->rucksack_get_current_folder()->get_environment();
	}

	/**
	 * function rucksack_insert:
	 *
	 * puts an object into the users rucksack
	 *
	 * Example:
	 * <code>
	 * $me->rucksack_insert(objectToTake);
	 * </code>
	 * or with parameter $pType,  <I>(optional)</I>
	 * <code>
	 * $me->rucksack_insert(objectToTake, CLASS_DOCUMENT);
	 * </code>
	 *
	 * @param $pSteamObjects
	 * @param $pType
	 *
	 * @return
	 */
	public function rucksack_insert( $pSteamObjects, $pType = 0 )
	{
		return $this->rucksack_get_current_folder()->insert( $pSteamObjects, $pType );
	}

	/**
	 * function get_groups:
	 *
	 * Returns a list of this user's memberships
	 *
	 * @return mixed Array of steam_group objects
	 */
	public function get_groups()
	{
		return $this->steam_command(
		$this,
			"get_groups",
		array(),
		0
		);
	}

	/**
	 * function set_password:
	 *
	 * Sets a new password for this user
	 *
	 * @param string $pPassword New password
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return boolean TRUE | FALSE
	 */
	public function set_password( $pPassword )
	{
		if ( ! is_string ( $pPassword ) )
		{
			return FALSE;
		}
		else
		{
			return $this->steam_command(
			$this,
				"set_user_password",
			array( $pPassword ),
			0
			);
		}
	}

	/**
	 * Check if a given password is correct. Users can authenticate with their
	 * password or with temporary tickets.
	 * Please note that Authentication will always fail if the user is not
	 * activated.
	 *
	 * @param  string $password password to check
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return boolean TRUE|FALSE
	 */
	public function check_password( $password, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"check_user_password",
		array( $password ),
		$pBuffer
		);
	}

	/**
	 * function activate:
	 *
	 * Activates a registered user with means of its activation code
	 *
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @param  string $pActivationCode String which got back from steam_factory::create_user()
	 * @return boolean TRUE|FALSE
	 */
	public function activate( $pActivationCode, $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"activate_user",
		array( (int) $pActivationCode ),
		$pBuffer
		);
	}

	/**
	 * function is_activated:
	 *
	 * checks if user is activated
	 *
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return boolean TRUE|FALSE
	 */
	public function is_activated( $pBuffer = 0 )
	{
		$activated = $this->steam_command(
		$this,
			"get_activation",
		array( ),
		$pBuffer
		);
		return $activated == 0;
	}

	/**
	 * function enter:
	 *
	 * Moves the user into a room
	 *
	 * @param steam_room $pRoom room, where the user steps into
	 * @return boolean TRUE|FALSE
	 */
	public function enter( $pRoom )
	{
		return $this->move( $pRoom );
	}

	/**
	 * function set_email_forwarding:
	 *
	 * Activates the user's e-mail-forwardning functionality,
	 * means that every message it get's via the internal
	 * mail system will be delivered
	 *
	 * @param Boolean $pSetUnset Set or unset the forwarding
	 * @param Boolean $pBuffer Send now or buffer request?
	 */
	public function set_email_forwarding( $pSetOrUnset = TRUE, $pBuffer = 0 )
	{
		$forward_module = $this->get_steam_connector()->get_module( "forward" );
		if ( $pSetOrUnset )
		{
			return $this->steam_command(
			$forward_module,
				"add_forward",
			array(
			$this,
			$this->get_attribute( "USER_EMAIL" )
			),
			$pBuffer
			);
		}
		else
		{
			return $this->steam_command(
			$forward_module,
				"delete_forward",
			array( $this ),
			$pBuffer
			);
		}
	}

	public function add_forward( $pForward, $pBuffer = FALSE )
	{
		$forward_module = $this->get_steam_connector()->get_module( "forward" );
		return $this->steam_command(
		$forward_module,
			"add_forward",
		array( $this, $pForward ),
		$pBuffer
		);
	}
	public function delete_forward( $pForward, $pBuffer = FALSE )
	{
		$forward_module = $this->get_steam_connector()->get_module( "forward" );
		return $this->steam_command(
		$forward_module,
			"delete_forward",
		array( $this, $pForward ),
		$pBuffer
		);
	}
	/**
	 * function get_email_forwarding:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	function get_email_forwarding( $pBuffer = 0 )
	{
		$forward_module = $this->get_steam_connector()->get_module( "forward" );
		$output=$this->steam_command(
		$forward_module,
			"get_forward",
		array( $this ),
		$pBuffer
		);
		return (is_array($output))?$output:array();
	}

	/**
	 * function mail:
	 *
	 * Sends a message to the user through the internal mail system.
	 * If the receiver has set its attribute USER_FORWARD_MSG as
	 * true, this message will be delivered also as e-mail to its account.
	 *
	 * @param String  $pSubject message's subject
	 * @param String  $pHtmlMessageBody message's message body in html Format.
	 * @param String  $pSender If $pSender is not specified ( == 0) the server will use the Firstname, Lastname of the current user and constructs the users email address using the users name and the server address. If $Sender is specified please note that you must specify the $pSender in the following format as string: "\"" . $name . "\"<login@server.com>" with $name as printed quotable encoded string. You may use steam_connector::quoted_printable_encode() to encode the name correctly. An Example: $userobject->mail( "a subject", " a message", "\"" . steam_connector::quoted_printable_encode("Firstname Lastname") . "\"<login@server.com>");
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
		$pSender
		),
		$pBuffer
		);
	}


	/**
	 * function send_mail:
	 *
	 * An E-Mail is sent to the target by smtp-server.
	 *
	 * @param String $pTarget target e-mail address
	 * @param String $pSubject E-Mail's subject
	 * @param String $pMessageBody E-Mail's Message body
	 * @param Boolean $pBuffer Send now or buffer request?
	 * @return void
	 */
	public function send_email( $pTarget, $pSubject, $pMessageBody, $pBuffer = 0 )
	{
		$smtp_module = $this->get_steam_connector()->get_module( "smtp" );
		return $this->steam_command(
		$smtp_module,
			"send_mail",
		array( $pTarget, $pSubject, $pMessageBody ),
		$pBuffer
		);
	}

	/**
	 * function get_mails:
	 *
	 * @param Boolean pIncludeFolders Include folders in the result (TRUE),
	 *   or only mails (FALSE, default)
	 * @param Object pFolder Optional folder object from which to return the
	 *   mails (default: return user's mailbox)
	 * @return mixed Array of mails (or mails and folders)
	 */
	public function get_mails( $pIncludeFolders = FALSE, $pFolder = 0 )
	{
		if ( is_object( $pFolder ) ) $mailbox = $pFolder;
		else $mailbox = $this;
		if ( $pIncludeFolders ) $mails = $mailbox->get_annotations();
		else $mails = $mailbox->get_annotations( CLASS_DOCUMENT );
		if ( !is_array( $mails ) ) return array();
		return $mails;
	}

	/**
	 * Returns the user's emails, optionally filtered by object class,
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
	 *   (if not specified, then the inbox of the user is used)
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
	 * Returns the user's sent-mail folder, or 0 if the user has none.
	 *
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return Object user's sent-mail folder object or 0 if the user has none
	 */
	public function get_sent_mail_folder ( $pBuffer = 0 ) {
		return $this->steam_command( $this, "get_sent_mail_folder", array(), $pBuffer );
	}

	/**
	 * Create a sent-mail folder for the user if he has none.
	 * If the user already has a sent-mail folder, then it is kept unchanged.
	 * If the user doesn't have a sent-mail folder, then a new one will be created.
	 *
	 * @param String $folder_name optional name for the sent-mail folder if one is created (default: "sent")
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return Object the user's (existing or new, if created) sent-mail folder
	 */
	function create_sent_mail_folder ( $folder_name = NULL, $pBuffer = 0 ) {
		return $this->steam_command( $this, "create_sent_mail_folder", array( $folder_name ), $pBuffer );
	}

	/**
	 * Set a sent-mail folder for the user.
	 * If the user already has a sent-mail folder, then it will be turned into
	 * a regular mail folder for the user and the new folder will be made the
	 * user's sent-mail folder.
	 *
	 * @param Object $mail_folder the mail folder to use as a sent-mail folder
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return Object the user's new sent-mail folder
	 */
	function set_sent_mail_folder ( $mail_folder, $pBuffer = 0 ) {
		return $this->steam_command( $this, "set_sent_mail_folder", array( $mail_folder ), $pBuffer );
	}

	/**
	 * Query whether sent mails are stored for the user.
	 * This setting is independant of whether the user actually has a sent-mail
	 * folder. If he has no sent-mail folder, then no sent mails will be stored
	 * until one is created or set for him.
	 *
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return Boolean TRUE if sent mails are stored for the user, FALSE if not
	 */
	function is_storing_sent_mail ( $pBuffer = 0 ) {
		return $this->steam_command( $this, "is_storing_sent_mail", array(), $pBuffer ) ? TRUE : FALSE;
	}

	/**
	 * Set whether sent mails shall be stored for the user.
	 * This setting is independant of whether the user actually has a sent-mail
	 * folder. If he has no sent-mail folder, then no sent mails will be stored
	 * until one is created or set for him.
	 *
	 * @param Boolean $store TRUE if sent mails shall be stored, FALSE if not
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return Boolean TRUE if sent mails are stored for the user, FALSE if not
	 */
	function set_is_storing_sent_mail ( $store, $pBuffer = 0 ) {
		return $this->steam_command( $this, "set_is_storing_sent_mail", array( $store ), $pBuffer ) ? TRUE : FALSE;
	}

	/**
	 * function get_ticket:
	 *
	 * Get a ticket whis is valid to login one time
	 *
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return a ticket which is valid to login one time
	 */
	public function get_ticket( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
			"get_ticket",
		array( ),
		$pBuffer
		);
	}

	public function set_buddies( $pBuddies, $pBuffer = 0 )
	{
		return $this->set_attribute( "USER_FAVOURITES", $pBuddies, $pBuffer );
	}

	public function get_buddies( $pBuffer = 0 )
	{
		return $this->get_confirmed_contacts();
	}

	public function is_buddy( $pUser )
	{
		//Registry::get('logger')->info("-----is-buddy----");
		$buddies = $this->get_buddies();
		$is_buddy = FALSE;
		if ( ! is_array( $buddies ) )
		{
			$buddies = array();
		}
		foreach( $buddies as $buddy )
		{
			if ( ! $buddy instanceof steam_user )
			{
				continue;
			}
			if ( $buddy->get_id() == $pUser->get_id() ){
				$is_buddy = TRUE;
				break;
			}
		}
		/*
		if($is_buddy){
			Registry::get('logger')->info("User: ". $pUser->get_name(). " is buddy of: ".$this->get_name());
		}
		else{
			Registry::get('logger')->info("User: ". $pUser->get_name(). " is NOT buddy of: ".$this->get_name());
		}
		Registry::get('logger')->info("END--is-buddy----");*/
		return $is_buddy;
	}

	/**
	 * @deprecated
	 */
	public function contact_confirm()
	{
		trigger_error("Deprecated function called.", E_USER_NOTICE);
		return $this->steam_command(
		$this,
			"confirm_contact",
		array(),
		0
		);
	}

	public function contact_is_confirmed( $pUser )
	{
		//Registry::get('logger')->info("-----contact-is-confirmed-----");
		$conf_contacts = $this->get_confirmed_contacts();
		$is_confirmed = false;

		foreach($conf_contacts as $cc){
			if($cc->get_id()== $pUser->get_id()) {
				$is_confirmed = true;
				break;
			}
		}
		//Registry::get('logger')->info("User: ". $this->get_name(). " has confirmed: ".$pUser->get_name());
		//Registry::get('logger')->info("END--contact-is-confirmed-----");
		return $is_confirmed;
	}

	public function get_confirmed_contacts()
	{
		//Workaround because of inconsistent data - some users are in confirmed and also in unconfirmed
		$confirmed_contacts = $this->get_attribute( "USER_CONTACTS_CONFIRMED" );
		$user_favourites_obj = $this->get_attribute( "USER_FAVOURITES" );
		$user_toconfirm_obj = $this->get_attribute( "USER_CONTACTS_TOCONFIRM" );

		$user_favourites_ids = array();
		$user_toconfirm_ids = array();
		$buddies = array();

		//Get ids
		if(is_array($user_favourites_obj))
		foreach($user_favourites_obj as $ufo){
                     if($ufo instanceof steam_object){
                         $user_favourites_ids[] =  $ufo->get_id();
                     }
			
		}

		//Get ids
		if(is_array($user_toconfirm_obj))
		foreach($user_toconfirm_obj as $utco){
			$user_toconfirm_ids[] =  $utco->get_id();
		}

		$buddie_ids = array();
		if(is_array($confirmed_contacts)){
			$buddie_ids += array_keys($confirmed_contacts);
		}
		$buddie_ids += $user_favourites_ids;

		$c = array_diff($buddie_ids, $user_toconfirm_ids);
		$buddie_ids = array_intersect($c, $buddie_ids);


		foreach($buddie_ids as $buddy_id){
			$buddies[] = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $buddy_id, CLASS_USER | CLASS_GROUP);
		}

		//Registry::get('logger')->info("Picked buddies: ".print_r($buddie_ids, true));

		return $buddies;
	}

	public function get_unconfirmed_contacts()
	{
		$user_toconfirm_obj = $this->get_attribute( "USER_CONTACTS_TOCONFIRM" );
		if(!is_array($user_toconfirm_obj)) $user_toconfirm_obj = array();
		return $user_toconfirm_obj;
	}

	/**
	 * function get_status:
	 *
	 * get users status
	 *
	 * @param Boolean $pBuffer Send now or buffer request
	 * @return users status
	 */
	public function get_status( $pBuffer = 0 )
	{
		return $this->steam_command(
		$this,
            "get_status",
		array( ),
		$pBuffer
		);
	}
}
?>