<?php
if (!defined("PATH_TUTORIALS")) define("PATH_TUTORIALS", PATH_EXTENSIONS . "tutorials/");
require_once( PATH_TUTORIALS . "classes/koala_group_tutorial.class.php");

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( isset( $_POST[ "subscribe" ] ) && is_array( $_POST[ "subscribe" ] ) )
	{
		header( "Location: " . PATH_URL . "group_subscribe.php?group=" . key( $_POST[ "subscribe" ] ) );
		exit;
	}
	if ( isset( $_POST[ "resign" ] ) && is_array( $_POST[ "resign" ] ) )
	{
		header( "Location: " . PATH_URL . "group_cancel.php?group=" . key( $_POST[ "resign" ] ) );
		exit;
	}
}
$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "tutorials" );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TUTORIALS . "templates/tutorials.template.html" );
$current_user = lms_steam::get_current_user();
$cu_has_membership = FALSE;

$csg = $course->get_steam_group();
$excl_memb_used = $csg->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") == "FALSE" || $csg->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") == "0" ? FALSE : TRUE;
$extra_info = "";

$tutorials = $course->steam_group_learners->get_subgroups();
$a_Keys = array_keys($tutorials);

$attributes = array(OBJ_DESC, OBJ_TYPE, OBJ_NAME, "TUTORIAL_LONG_DESC", "TUTORIAL_PRIVATE", "TUTORIAL_MAX_LEARNERS" );

$tnr = array();
foreach($a_Keys as $key) {
  $tnr[$tutorials[$key]->get_id()] = array();
  $tnr[$tutorials[$key]->get_id()]["is_member"] = $tutorials[$key]->is_member($current_user, TRUE);
  $tnr[$tutorials[$key]->get_id()]["attributes"] = $tutorials[$key]->get_attributes($attributes, TRUE);
  $tnr[$tutorials[$key]->get_id()]["has_password"] = $tutorials[$key]->has_password(TRUE);
  $tnr[$tutorials[$key]->get_id()]["membercount"] =  $tutorials[$key]->count_members(TRUE);
}
$result = $GLOBALS["STEAM"]->buffer_flush();

foreach($a_Keys as $key)
{
  $tutorials[$key]->set_value(OBJ_NAME, $result[$tnr[$tutorials[$key]->get_id()]["attributes"]][OBJ_NAME]);
  $tutorials[$key]->set_value("TUTORIAL_PRIVATE", $result[$tnr[$tutorials[$key]->get_id()]["attributes"]]["TUTORIAL_PRIVATE"]);
	if ($result[$tnr[$tutorials[$key]->get_id()]["is_member"]]) $cu_has_membership = TRUE;
	if ($result[$tnr[$tutorials[$key]->get_id()]["attributes"]][OBJ_TYPE] == "0") {unset($tutorials[$key]);continue;}
	if ($result[$tnr[$tutorials[$key]->get_id()]["attributes"]][OBJ_TYPE] != "course_tutorial" && $result[$tnr[$tutorials[$key]->get_id()]["attributes"]][OBJ_TYPE] != "group_tutorial_koala") {unset($tutorials[$key]);continue;}
}
$tutorials = array_values($tutorials);

usort( $tutorials, "sort_objects_new" );
$no_tutorials = count( $tutorials );
// $GLOBALS["STEAM"]->disconnect();


if ( $no_tutorials > 0 )
{
				$content->setCurrentBlock( "BLOCK_GROUP_LIST" );

        if ( isset( $_GET['nrshow'] ) ) $nr_show = (int)$_GET['nrshow'];
        else $nr_show = 10;
        if ( isset( $_REQUEST['sort'] ) )
          $sort = $_REQUEST['sort'];
        else $sort = FALSE;

        $paginator_text = gettext('%START - %END of %TOTAL');
        if ( $nr_show > 0 )
          $paginator_text .= ', <a href="?nrshow=0' . (is_string($sort) ? '&sort=' . $sort : '') . '">' . gettext( 'show all' ) . '</a>';
        else $nr_show = $no_tutorials;
        $start = $portal->set_paginator( $content, $nr_show, $no_tutorials, '(' . $paginator_text . ')', is_string($sort) ? '?sort=' . $sort : '' );

        if ( ($nr_show == $no_tutorials) || ($start + 10 > $no_tutorials )) {
          $end = $no_tutorials;
        } else {
          $end = $start + 10;
        }

				$content->setVariable( "LABEL_GROUPS", gettext( "Tutorials for the course" ) . " " . h($course->get_attribute("OBJ_DESC")) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_tutorials ), gettext( "%a-%z out of %s" ) ) . ")");

				$content->setVariable( "LABEL_NAME_DESCRIPTION", gettext( "Tutorial No, description, long description" ) );
				$content->setVariable( "LABEL_MEMBERS", gettext( "# lns" ) );
				$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );


				for( $i = $start; $i < $end; $i++ )
				{
								$tutorial = $tutorials[ $i ];

								$lms_tutorial = new koala_group_tutorial($tutorial);
                				$password_protected = $result[$tnr[$tutorial->get_id()]["has_password"]];

								if($password_protected) $participant_mgmnt = gettext("password protected tutorial");
								else if ($lms_tutorial->is_moderated() && ! $password_protected) $participant_mgmnt = gettext("moderated tutorial");
								else if ($lms_tutorial->is_private() && ! $password_protected) $participant_mgmnt = gettext("private tutorial");
								else $participant_mgmnt = gettext("public tutorial");

								$content->setCurrentBlock( "BLOCK_GROUP" );

								if ( $tutorial->get_workroom()->check_access_read($current_user) )
									$content->setVariable( "GROUPLINK", "<td><a href=\"" . $backlink  . $tutorial->get_id() . "\"><b>" . $lms_tutorial->get_display_name() . "</b></a>" . " (" . $participant_mgmnt . ")" . "<br>" . h($result[$tnr[$tutorial->get_id()]["attributes"]]["OBJ_DESC"]) . "<br><small>" . h($result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_LONG_DESC"] ) . "</small></td>");
								else
									$content->setVariable( "GROUPLINK", "<td><b>" . $lms_tutorial->get_display_name() . "</b>" . " (" . $participant_mgmnt . ")" . "<br>" . h($result[$tnr[$tutorial->get_id()]["attributes"]]["OBJ_DESC"] ) . "<br><small>" . h($result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_LONG_DESC"] ) . "</small></td>");
								$content->setVariable( "MEMBER_LINK", $backlink . "tutorials/" . $tutorial->get_id() . "/members/" );
								$content->setVariable( "GROUP_MEMBERS", $result[$tnr[$tutorial->get_id()]["membercount"]] );
								($result[$tnr[$tutorial->get_id()]["is_member"]] || $lms_tutorial->is_admin($current_user)) ? $content->setVariable( "COMMUNICATION", "<td align=\"center\"><a href=\"" . PATH_URL . "messages_write.php?group=" . $tutorial->get_id() . "\">" . gettext("Send Message") . "</a></td>" ) : $content->setVariable( "COMMUNICATION", "<td align=\"center\">" . gettext("not allowed") . "</td>");
								$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
								$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
								$content->setVariable("LABEL_TUTOR", gettext("Tutor"));
								$content->setVariable("LABEL_ACTION", gettext("Sign in/Resign"));
								$content->setVariable("LABEL_MAX_LEARNERS", gettext("# max. lns"));
								$content->setVariable("GROUP_MEM_TITLE", gettext("Number of participants"));
								$content->setVariable("VALUE_MAX_TITLE", gettext("Maximum number of participants"));
								$content->setVariable("VALUE_TUTOR", $tutorial->get_attribute("TUTORIAL_TUTOR"));
								if ($result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_MAX_LEARNERS"] != "" && $result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_MAX_LEARNERS"] != 0)
									$content->setVariable("VALUE_MAX_LEARNERS", $result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_MAX_LEARNERS"]);
								else
									$content->setVariable("VALUE_MAX_LEARNERS", gettext("unlimited"));


								if ($lms_tutorial->is_staff($current_user) ) $content->setVariable( "TUTORIAL_ACTION", gettext("You are staff member"));
								else
								{
									if ( $result[$tnr[$tutorial->get_id()]["is_member"]] )
									{
										$content->setVariable( "TUTORIAL_ACTION", "<input type=\"submit\" value=\"" . gettext( "Resign" ) . "\" name=\"resign[" . $tutorial->get_id() . "]" . "\" >");
									}
									else
									{
                    $max = $result[$tnr[$tutorial->get_id()]["attributes"]]["TUTORIAL_MAX_LEARNERS"];
										if ( ($max != "") && (int)$max > 0 && (int)$max <= (int)$result[$tnr[$tutorial->get_id()]["membercount"]] )
										{
										  $content->setVariable( "TUTORIAL_ACTION", gettext("Tutorial is full"));
										}
										else if ($tutorial->requested_membership($current_user))
										{
											$content->setVariable( "TUTORIAL_ACTION", gettext("Membership requested"));
										}
										else if ($lms_tutorial->is_private())
										{
											$content->setVariable( "TUTORIAL_ACTION", gettext("Private tutorial!"));
										}
										else
										{
											if($cu_has_membership && $excl_memb_used)
												$content->setVariable( "TUTORIAL_ACTION", "<input type=\"submit\" value=\"" . gettext( "Sign on" ) . "\" name=\"subscribe[" . $tutorial->get_id() . "]\" disabled=\"disabled\" / >");
											else
												$content->setVariable( "TUTORIAL_ACTION", "<input type=\"submit\" value=\"" . gettext( "Sign on" ) . "\" name=\"subscribe[" . $tutorial->get_id() . "]\" / >");
										}
									}
								}
								$content->parse( "BLOCK_GROUP" );
				}
				$content->setVariable("GROUP_MEM_TITLE", gettext("Number of participants"));
				$content->setVariable("VALUE_MAX_TITLE", gettext("Maximum number of participants"));
				$content->parse( "BLOCK_GROUP_LIST" );

				if($no_tutorials > 1 && $excl_memb_used)
				{
					$extra_info .= "<p><div class=\"infoBar\">";
					$extra_info .= gettext("Advice: Exclusive tutorial membership is enabled. Students can only subsribe to one tutorial at a time.");
					$extra_info .= "</div></p>";
				}
}
else
{
				$content->setVariable( "LABEL_GROUPS", gettext( "No tutorials for the course yet." ) );
}
$content->setVariable("EXTRA_INFO", $extra_info );

$html_handler_course->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_course->get_headline(), $html_handler_course->get_html() , "" );
$portal->show_html();
?>
