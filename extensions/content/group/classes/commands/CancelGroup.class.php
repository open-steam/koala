<?php
namespace Group\Commands;

class CancelGroup extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$user  = \lms_steam::get_current_user();
		
		//TODO: Was soll dieses $em hier??
		//$em = \lms_steam::get_extensionmanager();
		
		if ( ! $steam_group = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id ) )
		{
			include( "bad_link.php" );
			exit;
		}
		
		if ( ! $steam_group instanceof \steam_group )
						throw new \Exception( "Is not a group: " . $_GET[ "id" ]  );
		
		$group = \koala_object::get_koala_object( $steam_group );
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"  )
		{
		  if ( ( $group instanceof \koala_group_course ) && ! ( $group->is_staff( $user ) || $group->is_learner( $user ) ) ) {
		   		//throw new \Exception( $user->get_name() . " is not a member of " . $group->get_groupname() );
		   		
		   		header( "Location: " . $values[ "return_to" ] );
				exit;
		  }
						
		
		  if ( ( $group instanceof \koala_group_default ) && ! ( $group->is_member( $user ) ) ) {
		  		//throw new \Exception( $user->get_name() . " is not a member of " . $group->get_groupname() );
		  		
		  		header( "Location: " . $values[ "return_to" ] );
				exit;
		  }
						
						
				$values = $_POST[ "values" ];
		        if (defined("LOG_DEBUGLOG")) {
		          \logging::write_log( LOG_DEBUGLOG, "group_cancel\t" . $user->get_name() . " leaves " . $steam_group->get_identifier() );
		        }
		        \logging::start_timer("leave_group");
				if ( $group->remove_member( $user ) )
				{
					$group_name = $group->get_display_name();
					$short_confirmation = str_replace( "%GROUP", $group_name, gettext( "Your membership in the group '%GROUP' has been terminated." ) );
					$confirmation = str_replace( "%NAME", $user->get_full_name(), gettext( "Dear %NAME," ) ) . "\n\n"
						. $short_confirmation . "\n\n" 
						. gettext( "Your koaLA Team" );
					\lms_steam::mail( $user, "\"" . PLATFORM_NAME . " System\"<no_reply@" . STEAM_SERVER . ">" , PLATFORM_NAME . ": " . str_replace( "%GROUP", $group_name, gettext( "Your membership in the group '%GROUP' has been terminated" ) ), $confirmation);
                
					$cache = get_cache_function( $user->get_name() );
					$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
					$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
					$cache->drop( "lms_steam::user_get_profile", $user->get_name() );
					$cache->drop( "lms_portal::get_menu_html", $user->get_name(), TRUE );
					$cache = get_cache_function( $group->get_id() );
					$cache->drop( "lms_steam::group_get_members", $group->get_id() );
								
					$_SESSION[ "confirmation" ] = $short_confirmation;
	                if (defined("LOG_DEBUGLOG")) {
	                  \logging::append_log( LOG_DEBUGLOG, " runtime=" . \logging::print_timer("leave_group") );
	                }
	                
					header( "Location: " . $values[ "return_to" ] );
					exit;
				}
				else
				{
					throw new \Exception( "Cannot delete membership." );
				}
		        if (defined("LOG_DEBUGLOG")) {
		          \logging::append_log( LOG_DEBUGLOG, "failed. runtime=" . \logging::print_timer("leave_group") );
		        }
		}
		
		
		$content = \Group::getInstance()->loadTemplate("groups_cancel_membership.template.html");
		$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
		
		
		if ( ( $group instanceof \koala_group_course ) && ! ( $group->is_staff( $user ) || $group->is_learner( $user ) ) ) {
		  if ( empty( $_SESSION['confirmation'] ) ) {  // don't warn if we came here on successful membership cancel...
		    if ($group instanceof \koala_group_course) $portal->set_problem_description( gettext( "You are not member of this course." ));
				else $portal->set_problem_description( gettext( "You are not member of this group." ));
			}
		} else if ( ( $group instanceof \koala_group_default ) && ! ( $group->is_member( $user ) ) ) {
		  if ( empty( $_SESSION['confirmation'] ) ) {  // don't warn if we came here on successful membership cancel...
		    if ($group instanceof \koala_group_course) $portal->set_problem_description( gettext( "You are not member of this course." ));
				else $portal->set_problem_description( gettext( "You are not member of this group." ));
			}
		} else {
		  $redirect = $_SERVER[ "HTTP_REFERER" ];
		  if ($group instanceof \koala_group_default && !$group->is_public()) {
		    $redirect = PATH_URL . "user/" . \lms_steam::get_current_user()->get_name() . "/groups/";
		  }
		  $content->setVariable( "DELETE_BACK_LINK", $redirect );
		  $content->setVariable( "FORM_ACTION", PATH_URL . "group/cancelGroup/	" . $group_id );
		  $content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure?" ) );
		  $content->setVariable( "INFO_CANCEL_MEMBERSHIP", str_replace( "%NAME", $group->get_display_name(), gettext( "You are going to cancel your membership in <b>'%NAME'</b>." ) ) );
		  $content->setVariable( "BUTTON_SUBMIT", "<input type=\"submit\" name=\"values[delete]\"  value=\"" . gettext( "Yes, cancel my membership" ) . "\"/>");
		}
		
		/*
		$portal->set_page_main(
										"",
										$content->get(),
										""
										);
		$portal->show_html();
		*/		
				
		$frameResponseObject->setTitle("Group");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>