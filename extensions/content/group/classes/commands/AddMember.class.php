<?php
namespace Group\Commands;

class AddMember extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		return true;
		if (isset($this->params[0])) {
			return true;
		} 
		else {
			return false;
		}
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$group_id = $this->params[0];
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );
		$user = \lms_steam::get_current_user();
		
		$content = \Group::getInstance()->loadTemplate("search_persons.template.html");
		
		if ( ! $steam_group = \steam_factory::get_object( $GLOBALS[ 'STEAM' ]->get_id(), $group_id ) )
			throw new \Exception( 'Group not found: ' . $_REQUEST[ 'group' ] );
		if ( ! $steam_group instanceof \steam_group )
						throw new \Exception( 'Is not a group: ' . $group_id  );
		
		// always try to use the correct specialized group:
		if ( !isset( $group ) )
			$group = \koala_object::get_koala_object( $steam_group );
		else if ( $group instanceof \koala_object )
			$group = \koala_object::get_koala_object( $group->get_steam_object() );
		else
			throw new \Exception( "No 'group' param provided" );
		
		// TODO: Passt der backlink?
		$backlink = $group->get_url();
		
		if ( ! $group->is_admin( $user ) )
		{
						throw new \Exception( "No admin of " . $group->get_groupname() . ": " . $user->get_name() );
		}
		
		if ( !empty( $_REQUEST[ "add" ] ) && count( $_REQUEST[ "add" ] ) > 0 )
		{
						$login = key( $_REQUEST[ "add" ] );
						$new_member = \steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
		
						if( $group instanceof \koala_group_tutorial )
						{
							if( $group->get_course_group()->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") === "TRUE" )
							{
								$course_learners_group = $group->steam_group_course_learners;
								$subgroups = $course_learners_group->get_subgroups();
								foreach( $subgroups as $sg )
								{
									if( ($sg->get_attribute("OBJ_TYPE") === "course_tutorial" || $sg->get_attribute("OBJ_TYPE") === "group_tutorial_koala") && $sg->is_member($new_member) && $sg != $steam_group )
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
								$content->setVariable("WARNING_TEXT", "<p/>" . gettext("Attention! The user you want to add already became member in another tutorial in the meantime."));
							}
		
							if (isset($_POST[ 'confirmed' ]) && $_POST[ 'confirmed' ] === "true")
							{
								if ( $group->add_member( $new_member ) )
								{
												// PROCEDURE FOR COURSE ADMINS
												if ( $group->get_attribute( "OBJ_TYPE" ) === "course_staff" )
												{
																// TODO: fct to add an admin? set_sanction_all does not seem to work... :(
																$steam_group->set_sanction_all( $new_member );
												}
												if ( $group->get_attribute( "OBJ_TYPE" ) == "course_tutorial" || $group->get_attribute( "OBJ_TYPE" ) == "group_tutorial_koala")
												{
													$no_of_learners = (int) $group->get_attribute( "GROUP_NO_MEMBERS" );
													$group->set_attribute("GROUP_NO_MEMBERS", $no_of_learners + count( $_REQUEST[ "add" ] ));
												}
		
												$group_name = \koala_object::get_koala_object( $group )->get_display_name();
												$message = str_replace( "%NAME", $new_member->get_full_name(), gettext( "Dear %NAME," ) ). "\n\n";
												$message .= str_replace( "%GROUP", $group_name, gettext( "You were added to '%GROUP' as a new member." ) ) . "\n\n";
												$message .= gettext( "This is an automatically generated message." ) . " " . gettext( "If you haven't been informed about this membership in advance, please contact the sender of this message." ) . "\n\n" . str_replace( "%GROUP", "<a href=\"" . $backlink . "\">" . $group_name. "</a>", gettext( "See '%GROUP' for further information." ));
												\lms_steam::mail($new_member, $user, PLATFORM_NAME . ": " . str_replace( "%GROUP", h($group_name), gettext( "You were added to '%GROUP' as a new member" ) ) , $message);
		
												$cache = get_cache_function( $new_member->get_name());
												$cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), TRUE );
												$cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), FALSE );
												$cache->drop( "lms_steam::user_get_groups", $new_member->get_name() );
		                    $cache->drop( "lms_steam::user_get_profile", $new_member->get_name() );
		                    $cache->drop( "lms_portal::get_menu_html", $new_member->get_name(), TRUE );
		                    $cache = get_cache_function( $group->get_id() );
		                    $cache->drop( "lms_steam::group_get_members", $group->get_id() );
												$portal->set_confirmation( str_replace( "%NAME", $new_member->get_full_name(), gettext( "%NAME successfully added to group." )) );
								}
							}
						}
						else
						{
							if ( $group->add_member( $new_member ) )
							{
											// PROCEDURE FOR COURSE ADMINS
											if ( $group->get_attribute( "OBJ_TYPE" ) === "course_staff" )
											{
															// TODO: fct to add an admin? set_sanction_all does not seem to work... :(
															$steam_group->set_sanction_all( $new_member );
											}
											if ( $group->get_attribute( "OBJ_TYPE" ) == "course_tutorial" || $group->get_attribute( "OBJ_TYPE" ) == "group_tutorial_koala")
											{
												$no_of_learners = (int) $group->get_attribute( "GROUP_NO_MEMBERS" );
												$group->set_attribute("GROUP_NO_MEMBERS", $no_of_learners + count( $_REQUEST[ "add" ] ));
											}
		
											$group_name = \koala_object::get_koala_object( $group )->get_display_name();
											$message = str_replace( "%NAME", $new_member->get_full_name(), gettext( "Dear %NAME," ) ). "\n\n";
											$message .= str_replace( "%GROUP", $group_name, gettext( "You were added to '%GROUP' as a new member." ) ) . "\n\n";
											$message .= gettext( "This is an automatically generated message." ) . " " . gettext( "If you haven't been informed about this membership in advance, please contact the sender of this message." ) . "\n\n" . str_replace( "%GROUP", "<a href=\"" . $backlink . "\">" . $group_name. "</a>", gettext( "See '%GROUP' for further information." ));
											\lms_steam::mail($new_member, $user, PLATFORM_NAME . ": " . str_replace( "%GROUP", h($group_name), gettext( "You were added to '%GROUP' as a new member" ) ) , $message);
		
		                  $cache = get_cache_function( $new_member->get_name());
		                  $cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), TRUE );
		                  $cache->drop( "lms_steam::user_get_groups", $new_member->get_name(), FALSE );
		                  $cache->drop( "lms_steam::user_get_groups", $new_member->get_name() );
		                  $cache->drop( "lms_steam::user_get_profile", $new_member->get_name() );
		                  $cache->drop( "lms_portal::get_menu_html", $new_member->get_name(), TRUE );
		                  $cache = get_cache_function( $group->get_id() );
		                  $cache->drop( "lms_steam::group_get_members", $group->get_id() );
											$portal->set_confirmation( str_replace( "%NAME", $new_member->get_full_name(), gettext( "%NAME successfully added to group." )) );
							}
						}
		}
		
		if ( $group->get_maxsize() > 0 && $group->get_maxsize() <= $group->count_members() ) {
			$html_content = ("<div class=\"infoBar\">" . gettext("The group is full! You can not add any further members, because the maximum size of the group has been reached. Please increase this value to add more members.") . "</div>");
		}else
		{
		
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
						$html_people = \Group::getInstance()->loadTemplate("list_users.template.html");
						$no_people = count( $result );
						if ( $no_people > 0 )
						{
							// TODO: Passt der backlink?
							if ( isset( $_REQUEST[ "backlink" ] ) ) $tmp_backlink = $_REQUEST[ "backlink" ];
							else if ( isset( $backlink ) ) $tmp_backlink = $backlink;
										$paginator = \lms_portal::get_paginator(10, $no_people, "(" . gettext( "%TOTAL people in result set" ) . ")", "?pattern=" . $_REQUEST[ "pattern" ] . "&lookin=" . $_REQUEST[ "lookin" ] . "&group=" . $_REQUEST["group"] . "&backlink=" . $tmp_backlink );
										$start = $paginator["startIndex"];
										//$start = $portal->set_paginator( $html_people, 10, $no_people, "(" . gettext( "%TOTAL people in result set" ) . ")", "?pattern=" . $_REQUEST[ "pattern" ] . "&lookin=" . $_REQUEST[ "lookin" ] . "&group=" . $_REQUEST["group"] . "&backlink=" . $tmp_backlink );
										$end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
										$html_people->setVariable("PAGINATOR", $paginator["html"]);
										$html_people->setVariable( "LABEL_CONTACTS", gettext( "Results" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
										$html_people->setCurrentBlock( "BLOCK_CONTACT_LIST" );
										$html_people->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
										$html_people->setVariable( "LABEL_SUBJECT_AREA", gettext( "Subject area" ) );
										$html_people->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
										$html_people->setVariable( "TH_MANAGE_CONTACT", gettext( "Action" ) );
										for ( $i = $start; $i < $end; $i++ )
										{
														$person = $result[ $i ];
		
														$html_people->setCurrentBlock( "BLOCK_CONTACT" );
														$html_people->setVariable( "CONTACT_LINK", PATH_URL . "user/" . $person[ "OBJ_NAME" ]. "/" );
														// TODO: Passt der link?
														$icon_link = ( $person[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $person[ "OBJ_ICON" ];
														$html_people->setVariable( "CONTACT_IMAGE", $icon_link );
														$html_people->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
														$html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . $person[ "OBJ_NAME" ] );
														$html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
														$html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );
														$html_people->setVariable( "OBJ_DESC", h($person[ "OBJ_DESC" ]) );
		
														$p = new \steam_object( $GLOBALS[ "STEAM" ]->get_id(), $person[ "OBJ_ID" ] );
														//if the group is a tutorial and the course has exclusive subgroups for tutorials set, we have to
														//see if our person is already member in one of the other tutorials.
														$already_member_and_exclusive = false;
														if( $group instanceof \koala_group_tutorial )
														{
															if( $group->get_course_group()->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") === "TRUE" )
															{
																$course_learners_group = $steam_group->get_parent_group();
																$subgroups = $course_learners_group->get_subgroups();
																foreach( $subgroups as $sg )
																{
																	if( ($sg->get_attribute("OBJ_TYPE") === "course_tutorial" || $sg->get_attribute("OBJ_TYPE") === "group_tutorial_koala") && $sg->is_member($p) && $sg != $steam_group )
																	{
																		$already_member_and_exclusive = true;
																		$in_group = $sg;
																	}
																}
															}
														}
														if ( ! $group->is_member( $p ) )
														{
															if( $already_member_and_exclusive )
																$html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><small><b>" . gettext("Attention: User already in tutorial") . " " . $in_group->get_name() . "<p /></b></small><input type=\"hidden\" name=\"confirmed\" value=\"true\"/><input type=\"submit\"  name=\"add[" . h($person[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Add anyhow" ). "\"/></td>" );
														    else
														    	$html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\"><input type=\"submit\"  name=\"add[" . h($person[ "OBJ_NAME" ]). "]\" value=\"" . gettext( "Add" ). "\"/></td>" );
														}
														else
														{
														   $html_people->setVariable( "TD_MANAGE_CONTACT", "<td align=\"center\">".gettext( "Already a member." )."</td>" );
														}
														$html_people->parse( "BLOCK_CONTACT" );
										}
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
		$portal->add_javascript_onload("group_add_member", "document.getElementById('pattern').focus();");
		
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
		//$portal->show_html();
		
		$frameResponseObject->setTitle("Group");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}

?>