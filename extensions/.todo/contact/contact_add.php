<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Add contact" ) );

$user = lms_steam::get_current_user();

$id = ( ! empty( $_GET[ "id" ] ) ) ? $_GET[ "id" ] : $_POST[ "id" ];
$contact = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id, CLASS_USER );
$contact_attributes = $contact->get_attributes( 
		array( "USER_FIRSTNAME", "USER_FULLNAME" ) 
		);
if ( ! $contact instanceof steam_user )
{
	include( "bad_link.php" );
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( TRUE || strlen( trim( $_POST[ "message" ] ) ) > 10 )
	{
		$user_attributes = $user->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "USER_FAVOURITES", "USER_EMAIL" ) );
		$buddies = $user_attributes[ "USER_FAVOURITES" ];
		if ( ! is_array( $buddies ) )
			$buddies = array();
    $is_buddy = FALSE;
		foreach( $buddies as $buddy )
		{
			if ( ( is_object( $buddy ) ) && ( $buddy->get_id() == $id ) )
			{
				//throw new Exception( "User is in buddy list yet." );
        $is_buddy = TRUE;
			}
		}
    if (!$is_buddy) {
      $buddies[] = $contact;
      $user->set_attribute( "USER_FAVOURITES", $buddies );
    }
    // Clear buddies from cache
    $cache = get_cache_function( $user->get_name(), 86400 );
		$cache->drop( "lms_steam::user_get_buddies", $user->get_name());
		//$contact->contact_confirm();
    // Is new contact in the list of contacts to confirm ?
    $toconfirm = $user->get_attribute("USER_CONTACTS_TOCONFIRM");
    if (!is_array($toconfirm)) $toconfirm = array();
    $admin_steam = new steam_connector(STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW);
    $acontact = steam_factory::get_object($admin_steam->get_id(), $contact->get_id(), CLASS_USER);
    // set confirmed contacts of contact
    $cc = $acontact->get_attribute("USER_CONTACTS_CONFIRMED");
    if (!is_array($cc)) $cc = array();
    $cc[$user->get_id()] = 1;
    $cc["_OBJECT_KEYS"] = "TRUE";
    $acontact->set_attribute("USER_CONTACTS_CONFIRMED", $cc);
    // set contacts to confirm of contact
    $ctc = $acontact->get_attribute("USER_CONTACTS_TOCONFIRM");
    if (!is_array($ctc)) $ctc = array();
    $found = FALSE;
    foreach($ctc as $tc) {
      if (!is_object($tc) || $tc->get_id() == $user->get_id()) $found = TRUE;
    }
    if (!$found) {
      $ctc[] = $user;
      $acontact->set_attribute("USER_CONTACTS_TOCONFIRM", $ctc);
    }
    $admin_steam->disconnect();
    // contacts to confirm of user
    $new_tc = array();
    foreach($toconfirm as $tc) {
      if (!is_object($tc) || $tc->get_id() == $contact->get_id())           $continue;
      $new_tc[]=$tc;
    }
    $user->set_attribute("USER_CONTACTS_TOCONFIRM", $new_tc);
		// WRITE MAIL TO CONTACT
		$message = str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "Dear %NAME" ) ) . ",\n\n"
		. str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ], gettext( "%NAME would like to add you as contact." ) )
		. "\n\n" . gettext( "Here is the memo:" ) . "\n\n" . $_POST[ "message" ] . "\n\n" . "<a href=\"" . PATH_URL . "contact_confirm.php?id=" . $user->get_id() . "\">" . gettext( "Confirm this contact." ) . "</a>";	
		lms_steam::mail($contact, $user, PLATFORM_NAME . ": " . str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ], gettext( "Confirmation Request from %NAME" ) ), $message);

		require_once( 'Cache/Lite.php' );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache = get_cache_function( $user->get_name() );
		$cache->clean( $user->get_name() );
		$cache->clean( $user->get_id());
		$cache = get_cache_function( $contact->get_name() );
		$cache->clean( $contact->get_name() );
		$cache->clean( $contact->get_id());

		header( "Location: " . $_POST[ "backlink" ]);
		exit;
	}
	else
	{
		$portal->set_problem_description( gettext( "Your message is too short." ), gettext( "Please write one sentence why you would like to add them as contact." ) );
	}
}



$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "contact_add.template.html" );
$content->setVariable( "CONTACT_ID", $contact->get_id());
$content->setVariable( "INFO_TEXT", gettext( "Confirmed contacts will show up in your contact list. They have read access on your contact details in your profile."  ) );
$content->setVariable( "MESSAGE_FOR_ADDING_CONTACT", gettext( "Message" ) . ":" );
$content->setVariable( "MESSAGE_INFORMATION", str_replace( "%NAME", h($contact_attributes[ "USER_FIRSTNAME" ]) . " " . h($contact_attributes[ "USER_FULLNAME" ]) , gettext( "Please let %NAME know in a few words why you would like to add them as a contact. %NAME will read this when confirming your request." ) ) );

$content->setVariable( "LABEL_OK", gettext( "Request confirmation" ) );
$content->setVariable( "BACKLINK_", "<a href=\"" . PATH_URL . "user/" . $contact->get_name() . "/\">" . str_replace( "%NAME", h($contact_attributes[ "USER_FIRSTNAME" ]) . " " . h($contact_attributes[ "USER_FULLNAME" ]), gettext( "cancel and go back to %NAME's profile." ) ). "</a>" );
$content->setVariable( "BACK_LINK", PATH_URL . "user/" . $contact->get_name() . "/" );

$portal->set_page_main(
		array( array( "link" => "", "name" => str_replace( "%NAME", h($contact_attributes[ "USER_FIRSTNAME" ]) . " " . h($contact_attributes[ "USER_FULLNAME" ]), gettext( "Add %NAME as a contact?" ) ) ) ),
		$content->get(),
		"ThinCase"
		);
$portal->show_html();
?>
