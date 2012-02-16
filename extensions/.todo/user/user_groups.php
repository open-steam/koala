<?php

function viewer_authorized( $login, $user )
{
	$cache = get_cache_function( $user->get_name(), 3600 );
	$user_privacy = $cache->call( "lms_steam::user_get_profile_privacy", $user->get_name() );
	
	(isset($user_privacy[ "PRIVACY_GROUPS" ])) ? $group_authorization = $user_privacy[ "PRIVACY_GROUPS" ] : $group_authorization = "" ;
	
	$confirmed = ( $user->get_id() != $login->get_id() ) ? TRUE : FALSE;
	$contacts = $cache->call( "lms_steam::user_get_buddies", $user->get_name(), $confirmed );

	$contact_ids = array();
	foreach ($contacts as $contact)
	{
		$contact_ids[] = $contact["OBJ_ID"];
	}

	$is_contact = in_array( $login->get_id(), $contact_ids );

	if ( !( $group_authorization & PROFILE_DENY_ALLUSERS ) ) return true;
	if ( $is_contact && !( $group_authorization & PROFILE_DENY_CONTACTS ) ) return true;

	return false;
}

$current_user = lms_steam::get_current_user();

$cache = get_cache_function( $login, 86400 );
$portal->set_page_title( $login );

$html_handler_profile = new koala_html_profile( $user );
$html_handler_profile->set_context( "groups" );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "list_groups.template.html" );

if ( viewer_authorized( $current_user, $user ) )
{
	$public = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
	$groups = $cache->call( "lms_steam::user_get_groups", $login, $public );
	usort( $groups, "sort_objects" );

	$no_groups = count( $groups );
	if ( $no_groups > 0 )
	{
		$content->setCurrentBlock( "BLOCK_GROUP_LIST" );
		$start = $portal->set_paginator( $content, 10, $no_groups, "(" . gettext("%TOTAL groups in list") . ")" );
		$end   = ( $start + 10 > $no_groups ) ? $no_groups : $start + 10;

		if ( $current_user->get_id() == $user->get_id() )
		{
			$content->setVariable( "LABEL_GROUPS", gettext( "Your groups" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_groups ), gettext( "%a-%z out of %s" ) ) . ")");
		}
		else
		{
			$content->setVariable( "LABEL_GROUPS", str_replace( "%NAME", $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" ),  gettext( "%NAME's groups" ) ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_groups ), gettext( "%a-%z out of %s" ) ) . ")" );
		}

		// GROUPS
		$content->setVariable( "LABEL_NAME_DESCRIPTION", gettext( "Name, description" ) );
		$content->setVariable( "LABEL_MEMBERS", gettext( "Members list" ) );
		$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );


		if ( $user->get_id() == $current_user->get_id() )
		{
			$content->setVariable( "TH_MANAGE_GROUP", gettext( "Manage membership" ) );
		}

		for( $i = $start; $i < $end; $i++ )
		{
			$group = $groups[ $i ];
			$content->setCurrentBlock( "BLOCK_GROUP" );
			$content->setVariable( "GROUP_LINK", PATH_URL . "groups/" . $group[ "OBJ_ID" ] . "/" );
			$content->setVariable( "GROUP_NAME", h($group[ "OBJ_NAME" ]) );
			$content->setVariable( "MEMBER_LINK", PATH_URL . "groups/" . $group[ "OBJ_ID" ] . "/members/" );
			$content->setVariable( "GROUP_MEMBERS", h($group[ "GROUP_NO_MEMBERS" ]) );
			$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?group=" . $group[ "OBJ_ID" ] );
			$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
			$content->setVariable( "LABEL_SEND", gettext( "Send" ) );

			if ( $user->get_id() == $current_user->get_id() )
			{
				$content->setVariable( "TD_MANAGE_GROUP", "<a href=\"" . PATH_URL . "group_cancel.php?group=" . $group[ "OBJ_ID" ] . "\">" . gettext( "Resign" ) . "</a>" );
			}

			$content->setVariable( "OBJ_DESC", h($group[ "OBJ_DESC" ]) );
			$content->parse( "BLOCK_GROUP" );
		}
		$content->parse( "BLOCK_GROUP_LIST" );
	}
	else
	{
		$content->setVariable( "LABEL_GROUPS", gettext( "No memberships found." ) );
	}
}
else
{
	$messagebox = "<div class=\"infoBar\"><h2>" . gettext("The user has restricted the display of this information.") . "</h2></div>";
	$content->setVariable( "LABEL_PRIVACY_DENY_PARTICIPANTS", $messagebox );
}

$html_handler_profile->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_profile->get_headline(), $html_handler_profile->get_html(), "vcard" );
$portal->show_html();
?>