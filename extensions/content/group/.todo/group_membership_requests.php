<?php

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "membership_requests.template.html" );
$content->setVariable( "INFO_TEXT", gettext( "The people listed are interested in becoming a member of your group." ) . " " . gettext( "Here, you can choose wether to affirm their membership or cancel the request." ) . " " . gettext( "In both cases, the user concerned will automatically get informed by mail about your decision." ) );

// always try to use the correct specialized group:
if ( $group instanceof koala_object )
	$group = koala_object::get_koala_object( $group->get_steam_object() );
else if ( $group instanceof steam_object )
	$group = koala_object::get_koala_object( $group );
else
	throw new Exception( "No 'group' param provided" );

$type = $group->get_attribute("OBJ_TYPE");

switch ($type)
{
	case ("group_tutorial_koala"):
		$backlink = $backlink . "tutorials/" . $group->get_id() . "/";
		break;
	case ("course_tutorial"):
		$backlink = $backlink . "tutorials/" . $group->get_id() . "/";
		break;
	default:
		$backlink = $backlink . "members/";
}

if ( ! $group->is_admin( $user ) ) {
  //				throw new Exception( $user->get_name() . " is no admin of " . $group->get_name() );
  header( "Location: " . PATH_URL  . "error.php?error=" . E_USER_RIGHTS );
}

if( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
				if ( isset( $_POST[ 'affirm' ]) && is_array( $_POST[ 'affirm' ] ) )
				{
								$candidate = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), key( $_POST[ "affirm" ] ) );

								if( $group instanceof koala_group_tutorial )
								{
									if( $group->get_course_group()->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") === "TRUE" )
									{
										$course_learners_group = $group->steam_group_course_learners;
										$subgroups = $course_learners_group->get_subgroups();
										foreach( $subgroups as $sg )
										{
											if( ($sg->get_attribute("OBJ_TYPE") === "course_tutorial" || $sg->get_attribute("OBJ_TYPE") === "group_tutorial_koala") && $sg->is_member($candidate) && $sg != $steam_group )
											{
												$already_member_and_exclusive = true;
												$in_group = $sg;
											}
										}
									}
								}
								if( $already_member_and_exclusive )
								{
									if( !isset($_POST[ 'confirmed' ]) || $_POST[ 'confirmed' ] != "true" )
									{
										$content->setCurrentBlock("BLOCK_WARNING");
										$content->setVariable("WARNING_TEXT", gettext("Attention! The user whose membership you wish to affirm already became member in another tutorial in the meantime."));
										$content->parse("BLOCK_WARNING");
									}

									if (isset($_POST[ 'confirmed' ]) && $_POST[ 'confirmed' ] === "true")
									{
										$group->add_member( $candidate );
										$group->remove_membership_request( $candidate );
										$subject = str_replace( "%GROUP", $group->get_name(), gettext( "Welcome to '%GROUP'" ) );
										$message = gettext( "Your membership was affirmed." );
										$portal->set_confirmation( str_replace( "%NAME", $candidate->get_attribute( "USER_FIRSTNAME" ) . " " . $candidate->get_attribute( "USER_FULLNAME" ), gettext( "Membership of %NAME affirmed." ) ) );
										// uncache group members page:
										$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
										$cache->drop( "lms_steam::group_get_members", $group->get_id() );
										// uncache menu so that course/group appears:
										$cache = get_cache_function( $candidate->get_name() );
										$cache->drop( "lms_steam::user_get_profile", $candidate->get_name() );
										$cache->drop( "lms_portal::get_menu_html", $candidate->get_name(), TRUE );
									}
								}
								else
								{
									$group->add_member( $candidate );
									$group->remove_membership_request( $candidate );
									$subject = str_replace( "%GROUP", $group->get_name(), gettext( "Welcome to '%GROUP'" ) );
									$message = gettext( "Your membership was affirmed." );
									$portal->set_confirmation( str_replace( "%NAME", $candidate->get_attribute( "USER_FIRSTNAME" ) . " " . $candidate->get_attribute( "USER_FULLNAME" ), gettext( "Membership of %NAME affirmed." ) ) );
									// uncache group members page:
									$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
									$cache->drop( "lms_steam::group_get_members", $group->get_id() );
									// uncache menu so that course/group appears:
									$cache = get_cache_function( $candidate->get_name() );
									$cache->drop( "lms_steam::user_get_profile", $candidate->get_name() );
									$cache->drop( "lms_portal::get_menu_html", $candidate->get_name(), TRUE );
								}
				}
				elseif( isset( $_POST[ 'cancel' ] ) && is_array( $_POST[ 'cancel' ] ) )
				{
								$candidate = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), key( $_POST[ "cancel" ] ) );
								$group->remove_membership_request( $candidate );
								$subject = str_replace( "%GROUP", $group->get_name(), gettext( "Your membership for '%GROUP' was rejected" ));
								$message = gettext( "Your membership was rejected." );
								$portal->set_confirmation( str_replace( "%NAME", $candidate->get_attribute( "USER_FIRSTNAME" ) . " " . $candidate->get_attribute( "USER_FULLNAME" ), gettext( "Membership of %NAME rejected." ) ) );
				}
				//$candidate->mail( $subject, $message, $user->get_attribute( "USER_EMAIL" ));
        lms_steam::mail($candidate, $user, $subject, $message);
}

$result = $group->get_membership_requests();

$html_people = new HTML_TEMPLATE_IT();
$html_people->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
$no_people = count( $result );
if ( $no_people > 0 )
{
				$start = $portal->set_paginator( 10, $no_people, "(" . gettext( "%TOTAL membership requests" ). ")" );
				$end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
				$html_people->setVariable( "LABEL_CONTACTS", gettext( "Membership requests" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
				$html_people->setCurrentBlock( "BLOCK_CONTACT_LIST" );
				$html_people->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
				$html_people->setVariable( "LABEL_SUBJECT_AREA", gettext( "Subject area" ) );
				$html_people->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
				$html_people->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
				foreach( $result as $candidate )
				{
								$person = $candidate->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON", "OBJ_NAME", "OBJ_DESC" ) );
								$html_people->setCurrentBlock( "BLOCK_CONTACT" );
								$html_people->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($person[ "OBJ_NAME" ]). "/" );
								if ( is_object( $person[ "OBJ_ICON" ] ) )
									$icon_link = PATH_URL . "cached/get_document.php?id=" . $person[ "OBJ_ICON" ]->get_id() . "&type=usericon";
								else
									$icon_link = PATH_STYLE . "images/anonymous.jpg";
								$html_people->setVariable( "CONTACT_IMAGE", $icon_link );
								$html_people->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
								$html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($person[ "OBJ_NAME" ]) );
								$html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
								$html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );

								//if the group is a tutorial and the course has exclusive subgroups for tutorials set, we have to
								//see if our candidate is already member in one of the other tutorials.
								$already_member_and_exclusive = false;
								if( $group instanceof koala_group_tutorial )
								{
									if( $group->get_course_group()->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") === "TRUE" )
									{
										$course_learners_group = $group->steam_group_course_learners;
										$subgroups = $course_learners_group->get_subgroups();
										foreach( $subgroups as $sg )
										{
											if( ($sg->get_attribute("OBJ_TYPE") === "course_tutorial" || $sg->get_attribute("OBJ_TYPE") === "group_tutorial_koala") && $sg->is_member($candidate) && $sg != $steam_group )
											{
												$already_member_and_exclusive = true;
												$in_group = $sg;
											}
										}
									}
								}
								if( $already_member_and_exclusive )
									$html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><small><b>" . gettext("Attention: User already in tutorial") . " " . $in_group->get_name() . "<p /></b></small><input type=\"hidden\" name=\"confirmed\" value=\"true\"/><input type=\"submit\"  name=\"affirm[" . $candidate->get_id() . "]\" value=\"" . gettext( "Affirm anyhow" ). "\"> " . gettext( "or" ) . " <input type=\"submit\"  name=\"cancel[" . $candidate->get_id() . "]\" value=\"" . gettext( "Decline" ) . "\"/></td>" );
								else
									$html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"affirm[" . $candidate->get_id() . "]\" value=\"" . gettext( "Affirm" ). "\"> " . gettext( "or" ) . " <input type=\"submit\"  name=\"cancel[" . $candidate->get_id() . "]\" value=\"" . gettext( "Decline" ) . "\"/></td>" );
								$html_people->setVariable( "OBJ_DESC", h($person[ "OBJ_DESC" ]) );
								$html_people->parse( "BLOCK_CONTACT" );
				}
				$html_people->parse( "BLOCK_CONTACT_LIST" );
				$content->setVariable( "HTML_USER_LIST", $html_people->get() );
}
else
{
				$content->setVariable( "LABEL_NO_REQUESTS", "<h3>" . gettext( "No membership request found." ) . "</h3>" );
}

$portal->set_page_title( gettext( "Membership Requests" ) );
//$portal->set_page_main( array( array( "link" => $backlink . "members/", "name" => $group->get_display_name() ), array( "link" => "", "name" => gettext( "Membership Requests" )) ), $content->get() , "" );
$portal->set_page_main( array( $group->get_link(), array( "name" => gettext( "Membership Requests" )) ), $content->get() , "" );
$portal->show_html();
?>
