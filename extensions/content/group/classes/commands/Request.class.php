<?php
namespace Group\Commands;

class Request extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$path = $this->params;
		$user = \lms_steam::get_current_user();
		$public = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP);
		$id = $path[0];
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );
		$portal_user = $portal->get_user();


		try {
		  $steam_group = ( ! empty( $id ) ) ? \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id) : FALSE;
		} catch (\Exception $ex) {
		  include( "bad_link.php" );
		  exit;
		}
		

		$group_is_private = FALSE;
		if ( $steam_group && is_object($steam_group) ) {
			switch( (string) $steam_group->get_attribute( "OBJ_TYPE" ) ) {
				case( "course" ):
					$group = new \koala_group_course( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . SEMESTER_URL . "/" . $group->get_semester()->get_name() . "/" . h($group->get_name()) . "/";
				break;
				default:
					$group = new \koala_group_default( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . "groups/" . $group->get_id() . "/";
				    // Determine if group is public or private
				    $parent = $group->get_parent_group();
				    if ($parent->get_id() == STEAM_PRIVATE_GROUP ) 
				    	$group_is_private = TRUE;
					break;
			}
		}

		if ($group_is_private) {
		  if ( !$steam_group->is_member( $user ) && !\lms_steam::is_koala_admin($user) )
		    throw new \Exception( gettext( "You have no rights to access this group" ), E_USER_RIGHTS );
		}
		
		
		
		
		$content = \Group::getInstance()->loadTemplate("membership_requests.template.html");	
		$content->setVariable( "INFO_TEXT", gettext( "The people listed are interested in becoming a member of your group." ) . " " . gettext( "Here, you can choose wether to affirm their membership or cancel the request." ) . " " . gettext( "In both cases, the user concerned will automatically get informed by mail about your decision." ) );
		
		// always try to use the correct specialized group:
		if ( $group instanceof \koala_object )
			$group = \koala_object::get_koala_object( $group->get_steam_object() );
		else if ( $group instanceof \steam_object )
			$group = \koala_object::get_koala_object( $group );
		else
			throw new \Exception( "No 'group' param provided" );
		
		$type = $group->get_attribute("OBJ_TYPE");
		
		switch ($type)
		{
			case ("group_tutorial_koala"):
					// TODO: Passt der backlink?
				$backlink = $backlink . "tutorials/" . $group->get_id() . "/";
				break;
			case ("course_tutorial"):
					// TODO: Passt der backlink?
				$backlink = $backlink . "tutorials/" . $group->get_id() . "/";
				break;
			default:
					// TODO: Passt der backlink?
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
				$candidate = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), key( $_POST[ "affirm" ] ) );

				if( $group instanceof \koala_group_tutorial )
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
				$candidate = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), key( $_POST[ "cancel" ] ) );
				$group->remove_membership_request( $candidate );
				$subject = str_replace( "%GROUP", $group->get_name(), gettext( "Your membership for '%GROUP' was rejected" ));
				$message = gettext( "Your membership was rejected." );
				$portal->set_confirmation( str_replace( "%NAME", $candidate->get_attribute( "USER_FIRSTNAME" ) . " " . $candidate->get_attribute( "USER_FULLNAME" ), gettext( "Membership of %NAME rejected." ) ) );
			}
			//$candidate->mail( $subject, $message, $user->get_attribute( "USER_EMAIL" ));
		    \lms_steam::mail($candidate, $user, $subject, $message);
		}
		
		$result = $group->get_membership_requests();
		
		$html_people = \Group::getInstance()->loadTemplate("list_users.template.html");
		
		$no_people = count( $result );
		if ( $no_people > 0 )
		{
			$paginator = \lms_portal::get_paginator(10, $no_people, "(" . gettext( "%TOTAL membership requests" ). ")" );
			$start = $paginator["startIndex"];
			//$start = $portal->set_paginator( 10, $no_people, "(" . gettext( "%TOTAL membership requests" ). ")" );
			$end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
			$content->setVariable("PAGINATOR", $paginator["html"]);
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
					// TODO: Passt der link?
					$icon_link = PATH_URL . "cached/get_document.php?id=" . $person[ "OBJ_ICON" ]->get_id() . "&type=usericon";
				else
					// TODO: Passt der link?
					$icon_link = PATH_STYLE . "images/anonymous.jpg";
				$html_people->setVariable( "CONTACT_IMAGE", $icon_link );
				$html_people->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
				$html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($person[ "OBJ_NAME" ]) );
				$html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
				$html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );

				//if the group is a tutorial and the course has exclusive subgroups for tutorials set, we have to
				//see if our candidate is already member in one of the other tutorials.
				$already_member_and_exclusive = false;
				if( $group instanceof \koala_group_tutorial )
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
		
		
		
		$frameResponseObject->setTitle("Group");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>