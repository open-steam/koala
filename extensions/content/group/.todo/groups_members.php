<?php

if ( ! $group instanceof koala_group )
	throw new Exception( "Variable group not set." );
	
if (COURSE_PARTICIPANTS_STAFF_ONLY && !$group->is_admin( $user )) {
	header("location:../");
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( is_array( $_POST[ "remove" ] ) )
	{
		$id = key( $_POST[ "remove" ] );
		$member_to_kick = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $id );
		$group->remove_member( $member_to_kick );
		$portal->set_confirmation( str_replace( "%NAME", h($member_to_kick->get_attribute( "USER_FIRSTNAME" ))." " . h($member_to_kick->get_attribute( "USER_FULLNAME" )), gettext( "User %NAME successfully removed from group members." ) ) );
		// clear caches:
		$cache = get_cache_function( $member_to_kick->get_name() );
		$cache->drop( "lms_steam::user_get_groups", $member_to_kick->get_name(), TRUE );
		$cache->drop( "lms_steam::user_get_groups", $member_to_kick->get_name(), FALSE );
		$cache->drop( "lms_steam::user_get_profile", $member_to_kick->get_name() );
		$cache->drop( "lms_portal::get_menu_html", $member_to_kick->get_name(), TRUE );
		$cache = get_cache_function( $group->get_id() );
		$cache->drop( "lms_steam::group_get_members", $group->get_id() );
	}
}

$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
switch( get_class( $group ) )
{
	case( "koala_group_course" ):
		$html_handler_group = new koala_html_course( $group );
		$html_handler_group->set_context( "members" );
		$members = $cache->call( "lms_steam::group_get_members", $group->steam_group_learners->get_id() );
	break;

	default:
		$html_handler_group = new koala_html_group( $group );
		$html_handler_group->set_context( "members" );
		$members = $cache->call( "lms_steam::group_get_members", $group->get_id() );
	break;
}
$is_admin = $group->is_admin( $user );


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );


$is_member = $group->is_member( $user );
//echo "is_member? " . $is_member;
$privacy_deny_participants = $group->get_attribute("GROUP_PRIVACY");
//echo "attribute: ''" . $privacy_deny_participants . "''";
if ($privacy_deny_participants == PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS && !$is_member) {
	//echo "*** deny ***";
	//TODO
	$content->setVariable( "LABEL_PRIVACY_DENY_PARTICIPANTS", gettext( "Participants are hidden." ) );
}
else {
	//echo "*** permit ***";


$no_members = count( $members );
if ( $no_members > 0 )
{

	switch( get_class( $group ) )
	{
		case( "koala_group_course" ):
	    $groupname = $group->get_course_id();
		break;
		default:
	    $groupname = $group->get_name();
		break;
	}

	if (!USER_LIST_NO_PAGEING) {
		$start = $portal->set_paginator( $content, 10, $no_members, "(" . str_replace( "%NAME", h($groupname), gettext( "%TOTAL members in %NAME" ) ) . ")" );
		$end = ( $start + 10 > $no_members ) ? $no_members : $start + 10;
		$content->setVariable( "LABEL_CONTACTS", gettext( "Members" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_members ), gettext( "%a-%z out of %s" ) ) . ")"  );
	} else {
		//if (count($members) > 10) {
			$content->setVariable( "LABEL_CONTACTS", gettext( "Members" ) . " (Anzahl " . count($members) . ")"  );
			$content->setCurrentBlock("BLOCK_FILTER");
			$content->setVariable( "HELP_TEXT", "Benutzer lassen sich einfacher finden, indem Sie den Filter verwenden. Tippen Sie einfach einen Teil des Benutzernamen oder der Benutzerkennung in das Textfeld." );
			$content->setVariable('LABEL_FILTER',"<b>".gettext('Filter')."</b>");
			$start = 0;
			$end = count( $members );
			if (COURSE_PARTICIPANTS_EXTENSIONS) {
				$extensions = $group->get_extensions();
				$html = "";
				foreach($extensions as $extension) {
					$html .= $extension->get_filter_html($portal, "filter_user", "extension_data");
				}
				$content->setCurrentBlock("BLOCK_EXTENSION_FILTER");
				$content->setVariable("EXTENSION_FILTER", $html);
				$content->parse("BLOCK_EXTENSION_FILTER");
			}
			$content->parse( "BLOCK_FILTER" );
		//}
	}


	$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
	$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
	(!COURSE_PARTICIPANTS_FACULTY_AND_FOCUS) or $content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
	(!COURSE_PARTICIPANTS_COMMUNICATION) or $content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
	if ( lms_steam::is_koala_admin($user) || (!COURSE_KOALAADMIN_ONLY && $is_admin)  )
	{
		(!COURSE_PARTICIPANTSLIST_MANAGE) or $content->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
	}
	(!COURSE_PARTICIPANTS_EXTENSIONS) or $content->setVariable( "TH_MANAGE_EXTENSIONS", "Status" );
	$content->setVariable( "BEGIN_HTML_FORM", "<form method=\"POST\" action=\"\">" );
	$content->setVariable( "END_HTML_FORM", "</form>" );

	for( $i = $start; $i < $end; $i++ )
	{
		$member = $members[ $i ];
		if ($member["USER_TRASHED"] === 1) {
			continue;
		}
		$content->setCurrentBlock( "BLOCK_CONTACT" );
		$content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($member[ "OBJ_NAME" ]) . "/" );
		$icon_link = ( $member[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . h($member[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
		$content->setVariable( "CONTACT_IMAGE", $icon_link );
		$title = ( ! empty( $member[ "USER_ACADEMIC_TITLE" ] ) ) ? h($member[ "USER_ACADEMIC_TITLE" ]) . " " : "";
		$content->setVariable( "CONTACT_NAME", $title . h($member[ "USER_FIRSTNAME" ]) . " " . h($member[ "USER_FULLNAME" ]) );
		(!COURSE_PARTICIPANTS_COMMUNICATION) or $content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($member[ "OBJ_NAME" ]) );
		(!COURSE_PARTICIPANTS_COMMUNICATION) or $content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
		(!COURSE_PARTICIPANTS_COMMUNICATION) or $content->setVariable( "LABEL_SEND", gettext( "Send" ) );
		(!COURSE_PARTICIPANTS_FACULTY_AND_FOCUS) or $content->setVariable( "FACULTY_AND_FOCUS", h($member[ "USER_PROFILE_FACULTY" ]) );
		if ( lms_steam::is_koala_admin($user) || (!COURSE_KOALAADMIN_ONLY && $is_admin)  )
			(!COURSE_PARTICIPANTSLIST_MANAGE) or $content->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"remove[" . h($member[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Remove" ) . "\"/></td>" );
		if (COURSE_PARTICIPANTS_EXTENSIONS) {
			$extensions = $group->get_extensions();
			$result = "";
			foreach($extensions as $extension) {
				$result .= $extension->get_member_info(steam_factory::get_user($GLOBALS[ "STEAM" ]->get_id(), $member[ "OBJ_NAME" ]), $group);
			}
			$content->setVariable("EXTENSIONS_DATA", $result);
		}
		$member_desc = ( empty( $member[ "OBJ_DESC" ] ) ) ? "student" : $member[ "OBJ_DESC" ];
		$status = secure_gettext( $member_desc );
		$content->setVariable( "OBJ_DESC", h($status) . " " . ($i+1));
		$content->parse( "BLOCK_CONTACT" );
	}

	$content->parse( "BLOCK_CONTACT_LIST" );
}
else
{
	$content->setVariable( "LABEL_NO_MEMBERS", gettext( "No members found." ) );
}

}

$html_handler_group->set_html_left( $content->get() );

$portal->set_page_main( $html_handler_group->get_headline(), $html_handler_group->get_html() , "" );
$portal->show_html();
?>
