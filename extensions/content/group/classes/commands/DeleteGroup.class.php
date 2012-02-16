<?php
namespace Group\Commands;

class DeleteGroup extends \AbstractCommand implements \IFrameCommand {
	
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
		$user = \lms_steam::get_current_user();
		
		
		
		try {
  			$steam_group = ( ! empty( $group_id ) ) ? \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id) : FALSE;
		} catch (Exception $ex) {
		  include( "bad_link.php" );
		  exit;
		}
		
		$group_is_private = FALSE;
		if ( $steam_group && is_object($steam_group) ) {
			switch( (string) $steam_group->get_attribute( "OBJ_TYPE" ) ) {
				case( "course" ):
					$group = new koala_group_course( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . SEMESTER_URL . "/" . $group->get_semester()->get_name() . "/" . h($group->get_name()) . "/";
				break;
				default:
					$group = new \koala_group_default( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . "groups/" . $group->get_id() . "/";
		      // Determine if group is public or private
		      $parent = $group->get_parent_group();
		      if ($parent->get_id() == STEAM_PRIVATE_GROUP ) $group_is_private = TRUE;
				break;
			}
		}
		
		if ($group_is_private) {
		  if ( !$steam_group->is_member( $user ) && !\lms_steam::is_koala_admin($user) )
		    throw new Exception( gettext( "You have no rights to access this group" ), E_USER_RIGHTS );
		}
		
		
		
		if ( ! $steam_group->check_access_write( $user ) )
		throw new Exception( str_replace("%USER", $user->get_login(), sr_replace("%GROUP", $group->get_id(), gettext( "Access denied: User %USER has no right to delete the group %GROUP" ))) , E_USER_RIGHTS );

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"  ) {
		  $values     = $_POST[ "values" ];
		  $env = $steam_group->get_environment();
		  // TODO: Passt der link?
		  $upper_link = PATH_URL . "groups/" . (is_object($env)?"?cat=" . $env->get_id():"");
		  $group_name = $group->get_display_name();
		  $members = $group->get_members();
		  $inventory = $group->get_workroom()->get_inventory_raw();
		  $deleted = TRUE;
			
		  foreach ($inventory as $object) {
		    if ( !($object instanceof \steam_user)) {
		      try {
		        \lms_steam::delete( $object );
		      }catch(Exception $ex3){
		        \logging::write_log( LOG_DEBUGLOG, "groups_delete:error deleting object from group workroom\t" . $login . " \t" . $group->get_display_name() . " \t". $steam_group->get_id() . " \t" . $object->get_id() );
		      }
		    }
		  }
		  if ( $steam_group->delete( ) ) {
		    $user->get_attributes(array(OBJ_NAME, USER_FIRSTNAME, USER_FULLNAME));
		    foreach ($members as $member) {
		      $cache = get_cache_function( $member->get_name() );
		      $cache->drop( "lms_steam::user_get_groups", $member->get_name(), TRUE );
		      $cache->drop( "lms_steam::user_get_groups", $member->get_name(), FALSE );
		      $cache->drop( "lms_steam::user_get_profile", $member->get_name() );
		      $cache->drop( "lms_portal::get_menu_html", $member->get_name(), TRUE );
		    }
		    $cache = get_cache_function( $steam_group->get_id() );
		    $cache->drop( "lms_steam::group_get_members", $steam_group->get_id() );
		    foreach ($members as $member) {
		      \lms_steam::mail( $member, $user, PLATFORM_NAME . ": " . str_replace( "%NAME", h($group_name), gettext("Group %NAME has been deleted.")) , str_replace("%USER", $user->get_name() . " (" . $user->get_attribute(USER_FIRSTNAME) . " " . $user->get_attribute(USER_FULLNAME) . ")", str_replace( "%NAME", h($group_name), gettext( "The group '%NAME' has been deleted from he koaLA System by %USER." )) ) . "\n\n-- \n" .  str_replace( "%NAME", h($group_name), gettext("This system generated notification message was sent to you as a former member of the deleted group \"%NAME\"") ) );
		    }
		    
				$_SESSION[ "confirmation" ] = str_replace( "%NAME", h($group_name), gettext( "The group '%NAME' has been deleted. A notification has been sent to former members." ) );
				header( "Location: " . $upper_link );
				exit;
			} else {
		    throw new Exception("Deletion of group failed");
		  }
		}
		
		$content = \Group::getInstance()->loadTemplate("group_delete.template.html");	
		$content->setVariable( "FORM_ACTION", "" );
		$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure?" ) );
		$content->setVariable( "INFO_DELETE_GROUP", str_replace( "%GROUP_NAME", h($group->get_name()), gettext( "You are going to delete '%GROUP_NAME'." ) ) . "<br />" . gettext("All data of this group will be removed from the system including weblogs, wikis, forums and documents. All members of this group will be notified about the deletion automatically.") . "<br /><br /><strong>" . gettext("The deletion process may take several minutes.")) . "</strong>";
		
		$content->setVariable( "LABEL_DELETE_IT", gettext( "Yes, delete this group" ) );
		$content->setVariable( "DELETE_BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
		
			
		$frameResponseObject->setTitle("Group");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>