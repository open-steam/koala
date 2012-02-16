<?php

function viewer_authorized( $login, $user )
{
	$cache = get_cache_function( $user->get_name(), 3600 );
	$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $user->get_name() );
	(isset($user_privacy[ "PRIVACY_CONTACTS" ])) ? $contact_authorization = $user_privacy[ "PRIVACY_CONTACTS" ] : $contact_authorization = "";
	$confirmed = ( $user->get_id() != $login->get_id() ) ? TRUE : FALSE;
	$contacts = $cache->call( "lms_steam::user_get_buddies", $user->get_name(), $confirmed );

	$contact_ids = array();
	foreach ($contacts as $contact)
	{
		$contact_ids[] = $contact["OBJ_ID"];
	}

	$is_contact = in_array( $login->get_id(), $contact_ids );

	if ( !( $contact_authorization & PROFILE_DENY_ALLUSERS ) ) return true;
	if ( $is_contact && !( $contact_authorization & PROFILE_DENY_CONTACTS ) ) return true;

	return false;
}

$current_user = lms_steam::get_current_user();

$cache = get_cache_function( $login, 86400 );
$portal->set_page_title( $login );

$html_handler_profile = new koala_html_profile( $user );
$html_handler_profile->set_context( "contacts" );

if ( viewer_authorized( $current_user, $user ) )
{
	// Display Contacts
	$unconfirmed_html = "";
	// Contacts to confirm (visible only for the user himself)
	if ($current_user->get_id() == $user->get_id() ) {
	  $content = new HTML_TEMPLATE_IT();
	  $content->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
	  $contacts = $cache->call( "lms_steam::user_get_contacts_to_confirm", $login);
	  $no_contacts = count( $contacts );
	  if ( $no_contacts > 0 )
	  {
	    $content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
	    $start = $portal->set_paginator( $content, 10, $no_contacts, "(" . gettext("%TOTAL contact requests in list") . ")" );
	    $end   = ( $start + 10 > $no_contacts ) ? $no_contacts : $start + 10;

	    if ( $current_user->get_id() == $user->get_id() )
	    {
	      $content->setVariable( "LABEL_CONTACTS", gettext( "Contact requests" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_contacts ), gettext( "%a-%z out of %s" ) ) . ")");
	    }
	    else
	    {
	      $content->setVariable( "LABEL_CONTACTS", str_replace( "%NAME", h($user->get_attribute( "USER_FIRSTNAME" )) . " " . h($user->get_attribute( "USER_FULLNAME" )),  gettext( "%NAME's contacts" ) ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_contacts ), gettext( "%a-%z out of %s" ) ) . ")" );
	    }

	    // CONTACTS
	    $content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
	    $content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
	    $content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );


	    if ( $user->get_id() == $current_user->get_id() ) {
	      $content->setVariable( "TH_MANAGE_CONTACT", gettext( "Manage request" ) );
	    }

	    for( $i = $start; $i < $end; $i++ )
	    {
	      $contact = $contacts[ $i ];
	      $content->setCurrentBlock( "BLOCK_CONTACT" );
	      $content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($contact[ "OBJ_NAME" ]) . "/" );
	      $icon_link = ( $contact[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . h($contact[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
	      $content->setVariable( "CONTACT_IMAGE", $icon_link );
	      $title = ( ! empty ( $contact[ "USER_ACADEMIC_TITLE" ] ) ) ? h($contact[ "USER_ACADEMIC_TITLE" ]) . " " : "";
	      $content->setVariable( "CONTACT_NAME", $title . h($contact[ "USER_FIRSTNAME" ]) . " " . h($contact[ "USER_FULLNAME" ]) );
	      $faf = lms_steam::get_faculty_name( $contact[ "USER_PROFILE_FACULTY" ] );
	      $faf .= ( empty( $contact[ "USER_PROFILE_FOCUS" ] ) ) ? "" : ": " . h($contact[ "USER_PROFILE_FOCUS" ]);
	      $content->setVariable( "FACULTY_AND_FOCUS", $faf);
	      $content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($contact[ "OBJ_NAME" ]) );
	      $content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
	      $content->setVariable( "LABEL_SEND", gettext( "Send" ) );
	      if ( $user->get_id() == $current_user->get_id() )
	      {
	        $content->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><a href=\"" . PATH_URL . "contact_confirm.php?id=" . h($contact[ "OBJ_ID" ]) . "\">" . gettext( "Confirm" ) . " / " . gettext("Deny") . "</a></td>" );
	      }
	      $contact_desc = ( empty( $contact[ "OBJ_DESC" ] ) ) ? "student" : $contact[ "OBJ_DESC" ];
	      $status = secure_gettext( $contact_desc );
	      $content->setVariable( "OBJ_DESC", h($status) );
	      $content->parse( "BLOCK_CONTACT" );
	    }
	    $content->parse( "BLOCK_CONTACT_LIST" );
	  }
	  $unconfirmed_html = $content->get();
	}

	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
	// Contact list

	$confirmed = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;

	$contacts = $cache->call( "lms_steam::user_get_buddies", $login, $confirmed );
	// If user views his own contact list, get information about the confirmed contacts too
	if (!$confirmed) {
	  $confirmed_contacts = $user->get_attribute("USER_CONTACTS_CONFIRMED");
	}
	if (!is_array($confirmed_contacts)) $confirmed_contacts = array();
	$no_contacts = count( $contacts );

	if ( $no_contacts > 0 )
	{
		$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
		$start = $portal->set_paginator( $content, 10, $no_contacts, "(" . gettext("%TOTAL contacts in list") . ")" );
		$end   = ( $start + 10 > $no_contacts ) ? $no_contacts : $start + 10;

		if ( $current_user->get_id() == $user->get_id() )
		{
			$content->setVariable( "LABEL_CONTACTS", gettext( "Your contacts" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_contacts ), gettext( "%a-%z out of %s" ) ) . ")");
		}
		else
		{
			$content->setVariable( "LABEL_CONTACTS", str_replace( "%NAME", h($user->get_attribute( "USER_FIRSTNAME" )) . " " . h($user->get_attribute( "USER_FULLNAME" )),  gettext( "%NAME's contacts" ) ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_contacts ), gettext( "%a-%z out of %s" ) ) . ")" );
		}

		// CONTACTS
		$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
		$content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
		$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );


		if ( $user->get_id() == $current_user->get_id() )
		{
			$content->setVariable( "TH_MANAGE_CONTACT", gettext( "Manage contact" ) );
		}

		for( $i = $start; $i < $end; $i++ )
		{
			$contact = $contacts[ $i ];
			$content->setCurrentBlock( "BLOCK_CONTACT" );
			$content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($contact[ "OBJ_NAME" ]) . "/" );
			$icon_link = ( $contact[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . h($contact[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
			$content->setVariable( "CONTACT_IMAGE", $icon_link );
			$title = ( ! empty ( $contact[ "USER_ACADEMIC_TITLE" ] ) ) ? h($contact[ "USER_ACADEMIC_TITLE" ]) . " " : "";
			$content->setVariable( "CONTACT_NAME", $title . h($contact[ "USER_FIRSTNAME" ]) . " " . h($contact[ "USER_FULLNAME" ]) );
			$faf = lms_steam::get_faculty_name( $contact[ "USER_PROFILE_FACULTY" ] );
			$faf .= ( empty( $contact[ "USER_PROFILE_FOCUS" ] ) ) ? "" : ": " . h($contact[ "USER_PROFILE_FOCUS" ]);
			$content->setVariable( "FACULTY_AND_FOCUS", $faf);
			$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($contact[ "OBJ_NAME" ]) );
			$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
			$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
	    $cmessage = "";
			if ( $user->get_id() == $current_user->get_id() )
			{
				if ( isset($confirmed_contacts[$contact["OBJ_ID"]]) && $confirmed_contacts[$contact["OBJ_ID"]] ) {
					$cmessage .= "(" . gettext("Confirmed") . ")";
				} else {
	        $cmessage .= "(" . gettext("Unconfirmed") . ")";
	      }
				$content->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><a href=\"" . PATH_URL . "contact_delete.php?id=" . h($contact[ "OBJ_ID" ]) . "\">" . gettext( "Delete" ) . "</a></td>" );
			}
			$contact_desc = ( empty( $contact[ "OBJ_DESC" ] ) ) ? "student" : $contact[ "OBJ_DESC" ];
			$status = secure_gettext( $contact_desc );
			$content->setVariable( "OBJ_DESC", h($status) . (strlen($cmessage)>0?"<br />" . $cmessage:"") );
			$content->parse( "BLOCK_CONTACT" );
		}
		$content->parse( "BLOCK_CONTACT_LIST" );
	}
	else
	{
		$content->setVariable( "LABEL_CONTACTS", gettext( "No contacts yet." ) );
	}
}
else
{
	$messagebox = "<div class=\"infoBar\"><h2>" . gettext("The user has restricted the display of this information.") . "</h2></div>";
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
	$content->setVariable( "LABEL_PRIVACY_DENY_PARTICIPANTS", $messagebox );
}


$html_handler_profile->set_html_left( $unconfirmed_html . $content->get() );
$portal->set_page_main( $html_handler_profile->get_headline(), $html_handler_profile->get_html(), "vcard" );
$portal->show_html();
?>
