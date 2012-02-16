<?php

require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php");
require_once( PATH_ETC . "permissions.def.php" );


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$em = lms_steam::get_extensionmanager();

if ( ! $steam_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "group" ] ) )
throw new Exception( "Group not found: " . $_GET[ "id" ] );
if ( ! $steam_group instanceof steam_group )
				throw new Exception( "Is not a group: " . $_GET[ "id" ]  );

$group = koala_object::get_koala_object( $steam_group );
$backlink = $group->get_url();
//if the group is a tutorial and the course has exclusive subgroups for tutorials set, we have to
//see if we are already member in one of the other tutorials.
$already_member_and_exclusive = false;
if( $group instanceof koala_group_tutorial )
{
	if( $group->get_course_group()->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") === "TRUE" )
	{
		$course_learners_group = $steam_group->get_parent_group();
		$subgroups = $course_learners_group->get_subgroups();
		foreach( $subgroups as $sg )
		{
			if( ($sg->get_attribute("OBJ_TYPE") === "course_tutorial" || $sg->get_attribute("OBJ_TYPE") === "group_tutorial_koala") && $sg->is_member($user) && $sg != $steam_group )
			{
				$already_member_and_exclusive = true;
				$in_group = $sg;
			}
		}
	}
}

// POST
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" ) {
  if ( $group->is_member( $user ))
    throw new Exception( $user->get_name() . " is already a member of " . $group->get_groupname() );

				$values = $_POST[ "values" ];
				$problems = "";
				$hints   = "";

				if ( isset( $values[ "password" ] ) )
					$password = $values[ "password" ];
				else
					$password = "";
				if ( isset( $values[ "message" ] ) )
					$message = $values[ "message" ];
				else
					$message = "";

				if( !$already_member_and_exclusive )
				{
          if (defined("LOG_DEBUGLOG")) {
            logging::write_log( LOG_DEBUGLOG, "group_subscribe\t" . $user->get_name() . " joins " . $steam_group->get_identifier() );
          }
          logging::start_timer("join_group");
					$result = $group->subscribe( $password, $message );
          if (defined("LOG_DEBUGLOG")) {
            logging::append_log( LOG_DEBUGLOG, " runtime=" . logging::print_timer("join_group") );
          }
					if ( $result[ "succeeds" ] )
					{
									$_SESSION[ 'confirmation' ] = $result[ 'confirmation' ];
									// uncache menu so that course/group appears:
									$cache = get_cache_function( $user->get_name() );
									$cache->drop( "lms_steam::user_get_profile", $user->get_name() );
									$cache->drop( "lms_portal::get_menu_html", $user->get_name(), TRUE );
									if ( ! $group->is_member( $user ) )
										$backlink = PATH_URL . 'desktop/';
									header( 'Location: ' . $backlink );
					}
					else
					{
									$portal->set_problem_description( $result[ "problem" ], $result[ "hint" ] );
					}
				}
				else
				{
					$portal->set_problem_description(gettext("You are already member of tutorial") . " " . $in_group->get_name() . ".", gettext("Resign from the tutorial to become member here.") );
				}
}

// GET
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_join.template.html" );
$content->setVariable( "BACKLINK", $backlink );

if ( $group->requested_membership( $user ) )
{
	if ( empty( $_SESSION['confirmation'] ) ) {  // don't warn if we came here on successful membership request...
	    if ($group instanceof koala_group_course) $portal->set_problem_description( gettext( "You have already requested a membership for this course." ));
		else $portal->set_problem_description( gettext( "You have already requested a membership for this group." ));
	}
} else if ( $group->is_member( $user ) ) {
  if ( empty( $_SESSION['confirmation'] ) ) {  // don't warn if we came here on successful membership request...
    if ($group instanceof koala_group_course) $portal->set_problem_description( gettext( "You are already member of this course." ));
		else $portal->set_problem_description( gettext( "You are already member of this group." ));
	}
} else if( $already_member_and_exclusive )
{
	if( empty( $_SESSION['confirmation'] ) )
		$portal->set_problem_description(gettext("You are already member of tutorial") . " " . $in_group->get_name() . ".", gettext("Resign from the tutorial to become member here.") );
}
else if ($group->get_attribute(KOALA_GROUP_ACCESS) == PERMISSION_GROUP_UNDEFINED) {
  $content->setCurrentBlock( "BLOCK_FORM" );
        if ( $group->get_maxsize() > 0 && $group->get_maxsize() <= $group->count_members() ) {
                $content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Group is full" ) );
                $content->setVariable( "INFO_KIND_OF_GROUP", str_replace("%NUMBER", $group->get_maxsize(),  gettext("The maximum number of %NUMBER participants has been reached." )) . " " . gettext( "You are not be able to join this group at this time." ) );
                $content->setVariable( "LABEL_SUBMIT", gettext( "Unable to join group" ) );
                $content->setVariable( "SUBMIT_EXTRA", "style=\"display:none\"" );
        }
				elseif ( $group->is_password_protected() )
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Password protected group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "The moderators of this group had set a password to avoid unauthorized access." ) . " " . gettext( "If you would like to join this group and don't know it, please ask the moderators." ) );
								$content->setCurrentBlock( "BLOCK_GROUP_PASSWORD" );
								$content->setVariable( "LABEL_PASSWORD", gettext( "Password" ) );
								$content->parse( "BLOCK_GROUP_PASSWORD" );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Join this group" ) );
				}
				elseif( $group->is_moderated() )
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Moderated group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "To avoid unauthorized access, the moderators of this group have to approve your membership request first, before you can join the group." ) . " " . gettext( "Here, you can fill out a membership request and send it." ) . " ". gettext( "You will get automatically informed by mail if your request succeeds." ) );
								$content->setCurrentBlock( "BLOCK_GROUP_MODERATED" );
								$content->setVariable( "LABEL_REASON_TO_JOIN", gettext( "Please provide some information about the reason why you should join this group." ) );
								$content->parse( "BLOCK_GROUP_MODERATED" );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Send membership request" ) );
				}
				else
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Public group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "This is a public group everyone can join." ) . " " . gettext( "Please confirm your intention." ) );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Join this group" ) );
				}
				$content->setVariable( "BACKLINK_FORM", $backlink );
				$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
				$content->parse( "BLOCK_FORM" );
} else {
        $access = $group->get_attribute(KOALA_GROUP_ACCESS);
				$content->setCurrentBlock( "BLOCK_FORM" );
        if ( $group->get_maxsize() > 0 && $group->get_maxsize() <= $group->count_members() ) {
                $content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Group is full" ) );
                $content->setVariable( "INFO_KIND_OF_GROUP", str_replace("%NUMBER", $group->get_maxsize(),  gettext("The maximum number of %NUMBER participants has been reached." )) . " " . gettext( "You are not be able to join this group at this time." ) );
                $content->setVariable( "LABEL_SUBMIT", gettext( "Unable to join group" ) );
                $content->setVariable( "SUBMIT_EXTRA", "style=\"display:none\"" );
        }
				elseif ( ($group instanceof koala_group_default && !($group instanceof koala_group_course) && $access == PERMISSION_GROUP_PUBLIC_PASSWORD) || ($group instanceof koala_group_course || $group instanceof koala_group_tutorial) && $access == PERMISSION_COURSE_PASSWORD )
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Password protected group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "The moderators of this group had set a password to avoid unauthorized access." ) . " " . gettext( "If you would like to join this group and don't know it, please ask the moderators." ) );
								$content->setCurrentBlock( "BLOCK_GROUP_PASSWORD" );
								$content->setVariable( "LABEL_PASSWORD", gettext( "Password" ) );
								$content->parse( "BLOCK_GROUP_PASSWORD" );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Join this group" ) );
				}
				elseif( ($group instanceof koala_group_default && !($group instanceof koala_group_course) && $access == PERMISSION_GROUP_PUBLIC_CONFIRMATION)  || ($group instanceof koala_group_course|| $group instanceof koala_group_tutorial) && $access == PERMISSION_COURSE_CONFIRMATION )
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Moderated group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "To avoid unauthorized access, the moderators of this group have to approve your membership request first, before you can join the group." ) . " " . gettext( "Here, you can fill out a membership request and send it." ) . " ". gettext( "You will get automatically informed by mail if your request succeeds." ) );
								$content->setCurrentBlock( "BLOCK_GROUP_MODERATED" );
								$content->setVariable( "LABEL_REASON_TO_JOIN", gettext( "Please provide some information about the reason why you should join this group." ) );
								$content->parse( "BLOCK_GROUP_MODERATED" );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Send membership request" ) );
				}
        elseif( $group instanceof koala_group_course && $access == PERMISSION_COURSE_PAUL_SYNC )
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "PAUL Import" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "The participants of this course are imported from the PAUL system. To join this course make sure to join the course in PAUL. After your Registration for this course in PAUL, the automatic synchronization will add you to this course in koaLA automatically." ) . "<br />". gettext("Please note that the synchronization may take up to one hour.") . "<br /><b>" . gettext("The Synchronisation will start if the registration period in PAUL ends at 30.04.2009.") . "</b>" );
                $content->setVariable("SUBMIT_EXTRA", "style='display: none;'");
                $printor = FALSE;
				}
				else
				{
								$content->setVariable( "LABEL_KIND_OF_GROUP", gettext( "Public group" ) );
								$content->setVariable( "INFO_KIND_OF_GROUP", gettext( "This is a public group everyone can join." ) . " " . gettext( "Please confirm your intention." ) );
								$content->setVariable( "LABEL_SUBMIT", gettext( "Join this group" ) );
				}
				$content->setVariable( "BACKLINK_FORM", $backlink );
				if (!isset($printor) || $printor) $content->setVariable( "LABEL_OR", gettext( "Or," ) );
				$content->setVariable( "LABEL_RETURN", gettext( "Return to the group's page" ) );
				$content->setVariable( "BACK_LINK", "javascript:history.back();" );
				$content->parse( "BLOCK_FORM" );
}

$koala_group = koala_object::get_koala_object( $group );
$portal->set_page_main(
	array( array( "link" => $backlink, "name" => $koala_group->get_display_name() ), array( "link" => "", "name" => gettext( "Subscribe" ) ) ),
	$content->get()
);
$portal->show_html();
?>
