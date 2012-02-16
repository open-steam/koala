<?php

if ( ! $group instanceof koala_group )
	throw new Exception( "Variable group not set." );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( isset($_POST[ "remove" ]) && is_array( $_POST[ "remove" ] ) )
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
	} else {
    if ( isset( $_POST[ "hide" ] ) && is_array( $_POST[ "hide" ] ) )
    {
    	$hidden_members = $group->get_steam_group()->get_attribute("COURSE_HIDDEN_STAFF");
    	if (!is_array($hidden_members)) {
    		$hidden_members = array();
    	}
		$users_to_hide = array_keys( $_POST[ "hide" ] );
		$displayed_staff_members = array();
		$displayed_staff_members = array_keys( $_POST[ "displayed_staff_member" ] );
		$tmp1_users_to_hide = array_unique(array_merge($hidden_members, $users_to_hide));
		$tmp2_users_to_hide = array_diff($tmp1_users_to_hide, $displayed_staff_members);
		$final_users_to_hide = array_unique(array_merge($tmp2_users_to_hide, $users_to_hide));
		$group->get_steam_group()->set_attribute("COURSE_HIDDEN_STAFF", $final_users_to_hide);
    }
    else {
		$hidden_members = $group->get_steam_group()->get_attribute("COURSE_HIDDEN_STAFF");
		if (!is_array($hidden_members)) {
			$hidden_members = array();
		}
		$displayed_staff_members = array();
		$displayed_staff_members = array_keys( $_POST[ "displayed_staff_member" ] );
    	$users_to_hide = array();
    	$users_to_hide = array_diff($hidden_members, $displayed_staff_members);
		$group->get_steam_group()->set_attribute("COURSE_HIDDEN_STAFF", $users_to_hide);
    }
    $portal->set_confirmation( "Sucessfully updated the visibility of course staff" );
  }
}

$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
switch( get_class( $group ) )
{
	case( "koala_group_course" ):
		$html_handler_group = new koala_html_course( $group );
		$html_handler_group->set_context( "staff" );
		//$members = $cache->call( "lms_steam::group_get_members", $group->steam_group_staff->get_id() );
    $members = lms_steam::group_get_members( $group->steam_group_staff->get_id() );
	break;

	default:
		$html_handler_group = new koala_html_group( $group );
		$html_handler_group->set_context( "staff" );
		//$members = $cache->call( "lms_steam::group_get_members", $group->get_id() );
    $members = lms_steam::group_get_members( $group->get_id() );
	break;
}
$is_admin = $group->is_admin( $user );


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "list_staff.template.html" );

$no_members = count( $members ); //DONE
if ( $no_members > 0 )
{
$start = $portal->set_paginator( $content, 10, $no_members, "(" . str_replace( "%NAME", h($group->get_name()), gettext( "%TOTAL members in %NAME" ) ) . ")" );
$end = ( $start + 10 > $no_members ) ? $no_members : $start + 10;

$content->setVariable( "LABEL_CONTACTS", gettext( "staff member" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_members ), gettext( "%a-%z out of %s" ) ) . ")"  );

$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
(!COURSE_STAFF_FACULTY_AND_FOCUS) or $content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
if ( lms_steam::is_koala_admin($user) || (!COURSE_KOALAADMIN_ONLY && $is_admin)  )
{
	(!COURSE_STAFFLIST_MANAGE) or $content->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
	(!COURSE_STAFFLIST_HIDE) or $content->setVariable( "TH_STAFF_MEMBER_VISIBILITY", gettext( "hidden" ) );
	(!COURSE_STAFFLIST_HIDE) or $content->setVariable( "STAFF_MEMBER_VISIBILITY_TITLE", gettext( "Selected staff members will not be visible on the course start page." ) );
}
(!COURSE_STAFF_EXTENSIONS) or $content->setVariable( "TH_MANAGE_EXTENSIONS", "Status" );
$content->setVariable( "BEGIN_HTML_FORM", "<form method=\"POST\" action=\"\">" );
$content->setVariable( "END_HTML_FORM", "</form>" );

$hidden_members = $group->get_steam_group()->get_attribute("COURSE_HIDDEN_STAFF");
if (!is_array($hidden_members)) $hidden_members = array();


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
	if (!COURSE_SHOW_ONLY_EXTERN_MAIL || (COURSE_SHOW_ONLY_EXTERN_MAIL && is_string(steam_factory::get_user($GLOBALS['STEAM']->get_id(), $member[ "OBJ_NAME" ])->get_attribute("USER_EMAIL")) && (steam_factory::get_user($GLOBALS['STEAM']->get_id(), $member[ "OBJ_NAME" ])->get_attribute("USER_EMAIL")) != "") && (steam_factory::get_user($GLOBALS['STEAM']->get_id(), $member[ "OBJ_NAME" ])->get_attribute("USER_FORWARD_MSG") === 1) ) {
		$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($member[ "OBJ_NAME" ]) );
		$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
		$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
	}
	(!COURSE_STAFF_FACULTY_AND_FOCUS) or $content->setVariable( "FACULTY_AND_FOCUS", h($member[ "USER_PROFILE_FACULTY" ]) );
	if ( lms_steam::is_koala_admin($user) || (!COURSE_KOALAADMIN_ONLY && $is_admin)  )
	{
		(!COURSE_STAFFLIST_MANAGE) or $content->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"remove[" . h($member[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Remove" ) . "\"/></td>" );
		if(in_array( $member["OBJ_ID"], $hidden_members ))
			(!COURSE_STAFFLIST_HIDE) or $content->setVariable( "TD_STAFF_MEMBER_VISIBILITY", "<td align=\"center\"><input type=\"checkbox\" name=\"hide[" . $member[ "OBJ_ID" ] . "]\" checked=\"checked\"/>" . "\n\t\t\t<input type=\"hidden\" name=\"displayed_staff_member[" . $member[ "OBJ_ID" ] . "]\" />" . "</td>" );
		else
			(!COURSE_STAFFLIST_HIDE) or $content->setVariable( "TD_STAFF_MEMBER_VISIBILITY", "<td align=\"center\"><input type=\"checkbox\" name=\"hide[" . $member[ "OBJ_ID" ] . "]\" />" . "\n\t\t\t<input type=\"hidden\" name=\"displayed_staff_member[" . $member[ "OBJ_ID" ] . "]\" />". "</td>" );
	}
	$member_desc = ( empty( $member[ "OBJ_DESC" ] ) ) ? "student" : $member[ "OBJ_DESC" ];
	$status = secure_gettext( $member_desc );
	$content->setVariable( "OBJ_DESC", h($status) );
	if (COURSE_STAFF_EXTENSIONS) {
		$extensions = $group->get_extensions();
		$result = "";
		foreach($extensions as $extension) {
			$result .= $extension->get_member_info(steam_factory::get_user($GLOBALS[ "STEAM" ]->get_id(), $member[ "OBJ_NAME" ]), $group);
		}
		$content->setVariable("EXTENSIONS_DATA", $result);
	}
	
	$content->parse( "BLOCK_CONTACT" );
}
if (lms_steam::is_koala_admin($user) || (!COURSE_KOALAADMIN_ONLY && $is_admin) ) {
	(!COURSE_STAFFLIST_HIDE) or $content->setVariable("LABEL_SUBMIT_BUTTON", gettext("Save"));
}
$content->parse( "BLOCK_CONTACT_LIST" );
}
else
{
	$content->setVariable( "LABEL_NO_MEMBERS", gettext( "No staff found." ) );
}

$html_handler_group->set_html_left( $content->get() );

$portal->set_page_main( $html_handler_group->get_headline(), $html_handler_group->get_html() , "" );
$portal->show_html();

?>