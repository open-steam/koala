<?php

// Seems to be never used

require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( ! $steam_group = steam_factory::get_object( $GLOBALS[ 'STEAM' ]->get_id(), $_REQUEST[ 'group' ] ) )
	throw new Exception( 'Group not found: ' . $_REQUEST[ 'group' ] );
if ( ! $steam_group instanceof steam_group )
	throw new Exception( 'Is not a group: ' . $_REQUEST[ 'group' ]  );

// always try to use the correct specialized group:
if ( !isset( $group ) )
	$group = koala_object::get_koala_object( $steam_group );
else if ( $group instanceof koala_object )
	$group = koala_object::get_koala_object( $group->get_steam_object() );
else
	throw new Exception( "No 'group' param provided" );

$backlink = $group->get_url();

if ( ! $group->is_admin( $user ) )
	throw new Exception( "No admin of " . $group->get_groupname() . ": " . $user->get_name() );

if ( !empty( $_REQUEST[ "selected_user" ] ) )
	$selected_users = array_keys( $_REQUEST[ "selected_user" ] );

if ( !empty( $_REQUEST[ "add" ] ) && count( $_REQUEST[ "add" ] ) > 0 )
	$selected_users = array( key( $_REQUEST[ "add" ] ) );

if ( isset( $selected_users ) && count( $selected_users ) > 0 )
{
	$confirmations = array();
	$problems = array();
	foreach ( $selected_users as $login ) {
		$new_member = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
		if ( $group->add_member( $new_member ) )
		{
			$confirmations[] = h( $new_member->get_full_name() );
			// PROCEDURE FOR COURSE ADMINS 
			if ( $group->get_attribute( "OBJ_TYPE" ) == "course_staff" )
			{
				// TODO: fct to add an admin? set_sanction_all does not seem to work... :(
				$steam_group->set_sanction_all( $new_member );
			}
			if ( $group->get_attribute( "OBJ_TYPE" ) == "course_tutorial" || $group->get_attribute( "OBJ_TYPE" ) == "group_tutorial_koala")
			{
				$no_of_learners = (int) $group->get_attribute( "GROUP_NO_MEMBERS" );
				$group->set_attribute("GROUP_NO_MEMBERS", $no_of_learners + count( $_REQUEST[ "add" ] ));
			}
			
			$group_name = koala_object::get_koala_object( $group )->get_display_name();
			$message = str_replace( "%NAME", $new_member->get_full_name(), gettext( "Dear %NAME," ) ). "\n\n";
			$message .= str_replace( "%GROUP", $group_name, gettext( "You were added to '%GROUP' as a new member." ) ) . "\n\n";
			$message .= gettext( "This is an automatically generated message." ) . " " . gettext( "If you haven't been informed about this membership in advance, please contact the sender of this message." ) . "\n\n" . str_replace( "%GROUP", "<a href=\"" . $backlink . "\">" . $group_name. "</a>", gettext( "See '%GROUP' for further information." ));
			lms_steam::mail($new_member, $user, PLATFORM_NAME . ": " . str_replace( "%GROUP", h($group_name), gettext( "You were added to '%GROUP' as a new member" ) ) , $message);
			
			$cache = get_cache_function( $new_member->get_name(), 60 );
			$cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), TRUE );
			$cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), FALSE );
			$cache->drop( "lms_steam::user_get_groups", $new_member->get_name() );
		}
		else
			$problems[] = h( $new_member->get_full_name() );
	}
	$portal->set_confirmation( str_replace( '%NAME', implode( ', ', $confirmations ), gettext( '%NAME successfully added to group.' )) );
	if ( count( $problems ) > 0 )
		$portal->set_problem_description( '%NAME', implode( ', ', $problems ), gettext( '%NAME could not be added to group.' ) );
}

$content = new HTML_TEMPLATE_IT();
if ( $group->get_maxsize() > 0 && $group->get_maxsize() <= $group->count_members() ) {
	$html_content = ("<div class=\"infoBar\">" . gettext("The group is full! You can not add any further members, because the maximum size of the group has been reached. Please increase this value to add more members.") . "</div>");
}
else
{
	$content->loadTemplateFile( PATH_TEMPLATES . "search_persons.template.html" );
	$content->setVariable( "HEAD_SEARCH", gettext( "Search" ) );
	$content->setVariable( "INFO_TEXT", gettext( "Here you can lookup some people." ) . " " . str_replace( "%GROUP", $group->get_display_name(),  gettext( "You can add a search result as member to '<b>%GROUP</b>'." ) ) );
	if(!empty($_REQUEST[ "pattern" ]))  $content->setVariable( "VALUE_PATTERN", $_REQUEST[ "pattern" ] );
	$content->setVariable( "LABEL_CHECK_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_CHECK_LOGIN", gettext( "Email address or login" ) );
	$content->setVariable( "LABEL_SEARCH", gettext( "Search" ) );
	$content->setVariable( "GROUP_ID", $group->get_id() );
	$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
	// SEARCH RESULTS
	if ( ! empty( $_REQUEST[ "pattern" ] ) )
	{
		$cache = get_cache_function( $user->get_name(), 60 );
		$result = $cache->call( "lms_steam::search_user", $_REQUEST[ "pattern" ], $_REQUEST[ "lookin" ] );
		if( $_REQUEST[ "lookin" ] == "name" )
		{
			$content->setVariable( "CHECKED_NAME", 'checked="checked"' );
		}
		else
		{
			$content->setVariable( "CHECKED_LOGIN", 'checked="checked"' );
		}
		// PROCEED RESULT SET
		$html_people = new HTML_TEMPLATE_IT();
		$html_people->loadTemplateFile( PATH_TEMPLATES . "list_users_selection.template.html" );
		$no_people = count( $result );
		if ( $no_people > 0 )
		{
			if ( isset( $_REQUEST[ "backlink" ] ) ) $tmp_backlink = $_REQUEST[ "backlink" ];
			else if ( isset( $backlink ) ) $tmp_backlink = $backlink;
			$start = $portal->set_paginator( $html_people, 10, $no_people, "(" . gettext( "%TOTAL people in result set" ) . ")", "?pattern=" . $_REQUEST[ "pattern" ] . "&lookin=" . $_REQUEST[ "lookin" ] . "&group=" . $_REQUEST["group"] . "&backlink=" . $tmp_backlink );
			$end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
			$html_people->setVariable( "LABEL_CONTACTS", gettext( "Results" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
			$html_people->setCurrentBlock( "BLOCK_CONTACT_LIST" );
			$html_people->setVariable( 'SELECT_ALL', gettext( 'Select all' ) );
			$html_people->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
			$html_people->setVariable( "LABEL_SUBJECT_AREA", gettext( "Subject area" ) );
			$html_people->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
			$html_people->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
			for ( $i = $start; $i < $end; $i++ )
			{
				$person = $result[ $i ];
				$p = new steam_object( $GLOBALS[ "STEAM" ], $person[ "OBJ_ID" ] );
				$is_member = $group->is_member( $p );
				$html_people->setCurrentBlock( "BLOCK_CONTACT" );
				if ( ! $is_member )
					$html_people->setVariable( 'TD_SELECT_CONTACT', '<input type="checkbox" name="selected_user[' . $person[OBJ_NAME] . ']" />' );
				$html_people->setVariable( "CONTACT_LINK", PATH_URL . "user/" . $person[ "OBJ_NAME" ]. "/" );
				$icon_link = ( $person[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $person[ "OBJ_ICON" ];
				$html_people->setVariable( "CONTACT_IMAGE", $icon_link );
				$html_people->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
				$html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . $person[ "OBJ_NAME" ] );
				$html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
				$html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );
				$html_people->setVariable( "OBJ_DESC", h($person[ "OBJ_DESC" ]) );
				if ( ! $is_member )
				   $html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"add[" . h($person[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Add" ). "\"/></td>" );
				else
				   $html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\">".gettext( "Already a member." )."</td>" );
				$html_people->parse( "BLOCK_CONTACT" );
			}
			$html_people->setVariable( 'LABEL_ADD_SELECTED', gettext( 'Add selected' ) );
			$html_people->parse( "BLOCK_CONTACT_LIST" );
		}
		else
		{
			$html_people->setVariable( "LABEL_CONTACTS", gettext( "No results." ) );
		}
		$content->setVariable( "HTML_USER_LIST", $html_people->get() );

	}
	else
	{
		$content->setVariable( "CHECKED_NAME", 'checked="checked"' );
	}
}
$portal->set_page_title( gettext( "Add member" ) );

// give input focus to search field:
$portal->add_javascript_onload("group_add_members", "document.getElementById('pattern').focus();");

if(!isset($html_content))
{
	$portal->set_page_main(
		array( $group->get_link(), array( "name" => gettext( "Add member" ) ) ),
		$content->get(),
		""
	);
} else
{
	$portal->set_page_main(
		array( $group->get_link(), array( "name" => gettext( "Add member" ) ) ),
		$html_content,
		""
	);
}
$portal->show_html();
?>
