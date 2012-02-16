<?php
namespace Semester\Commands;

class ManageSemester extends \AbstractCommand implements \IFrameCommand {
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
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
		
		$portal = \lms_portal::get_instance();
		
		$user = \lms_steam::get_current_user();

		if(isset($path[0])) {
	  		if((\steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "courses.".$path[0])) instanceof \steam_group)
	  			$current_semester = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "courses.".$path[0]);
	  		else {
	  			header ("Location: ".PATH_URL."404/");
				die;
	  		}	
	  	}
	  	else 
	  		$current_semester = \lms_steam::get_current_semester();
	  		
		$current_semester_name = $current_semester->get_name();
		
		if(\lms_steam::is_steam_admin($user)) {
            if(!$portal->get_user()->is_logged_in())
              throw new Exception("Access denied. Please login.", E_USER_AUTHORIZATION);
            
			$semester_admins = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $current_semester->get_groupname().".admins");
			$admin_group = new \koala_group_default($semester_admins);
			
			
			if($_SERVER["REQUEST_METHOD"] == "POST") {
				$delete = $_POST["delete"];
				if (count($delete) == 1 ) {
					$login = key($delete);
					$admin = \steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $login);
					$admin_group->remove_member($admin);
				}
			}
			
			$content = \Semester::getInstance()->loadTemplate("semester_admins.template.html");
			
			$content->setVariable( "INFORMATION_ADMINS", str_replace( "%SEMESTER", h($current_semester->get_attribute( "OBJ_DESC" )), gettext( "These people are allowed to create courses for %SEMESTER." ) ) . " " . gettext( "They can appoint other users as staff members/moderators for their own courses." ) );
			$content->setVariable( "LINK_ADD_ADMIN", PATH_URL."semester/addAdmin/".$current_semester_name."/".$admin_group->get_id());
			$content->setVariable( "LABEL_ADD_ADMIN", gettext( "Add another admin" ) );
			//TODO: Messages extension schreiben
			// TODO: Passt der Link?
			$content->setVariable( "LINK_MESSAGE", PATH_URL."mail/write/".$admin_group->get_id());
			$content->setVariable( "LABEL_MESSAGE_ADMINS", gettext( "Mail to admins" ) );
			
			$admins = $admin_group->get_members(); 
			$no_admins = count( $admins );
			
			if ( $no_admins > 0 )
			{
				$content->setVariable( "LABEL_ADMINS", gettext( "Course admins" ) );
				$content->setCurrentBlock( "BLOCK_CONTACT_LIST" );
				$content->setVariable( "LABEL_NAME_POSITION", gettext( "Name" ) . "/" . gettext( "Position" ) );
				$content->setVariable( "LABEL_SUBJECT_AREA", gettext( "Subject area" ) );
				$content->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
				$content->setVariable( "LABEL_REMOVE_ADMIN", gettext( "Action" ) );
				
				foreach( $admins as $admin )
				{
					$adm_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_DESC", "OBJ_ICON" ) );
					$content->setCurrentBlock( "BLOCK_CONTACT" );
					$content->setVariable( "CONTACT_NAME", h($adm_attributes[ "USER_FIRSTNAME" ])  . " " . h($adm_attributes[ "USER_FULLNAME" ]) );
					// TODO: Profile Image einfügen
					// TODO: Passt der Link?
					$icon_link = \lms_user::get_user_image_url(30,40);
					$content->setVariable( "CONTACT_IMAGE", $icon_link );
					// TODO: Passt der Link?
					$content->setVariable( "CONTACT_LINK", PATH_URL . "user/" . $admin->get_name() . "/" );
					$content->setVariable( "OBJ_DESC", h($adm_attributes[ "OBJ_DESC"]) );
					$content->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
					// TODO: Passt der Link?
					$content->setVariable( "LINK_SEND_MESSAGE", PATH_URL."mail/write/".$admin->get_name() );
					$content->setVariable( "LABEL_SEND", gettext( "Send" ) );
					$content->setVariable( "LABEL_REMOVE", gettext( "Remove" ) );
					$content->setVariable( "CONTACT_ID", $admin->get_name() );
					$content->parse( "BLOCK_CONTACT" );
				}
				
				$content->parse( "BLOCK_CONTACT_LIST" );
			}
			else
			{
				$content->setVariable( "LABEL_ADMINS", gettext( "No admins found." ) );
			}
			
			
			/* TODO: Portal anpassen
			$portal->set_page_title( h($current_semester->get_name()) . " Admins" );
			$portal->set_page_main( 
				array(
					array( "link" => PATH_URL . SEMESTER_URL . "/" . h($current_semester->get_name()) . "/", "name" => h($current_semester->get_attribute( "OBJ_DESC" ))), array( "link" => "", "name" => gettext( "Admins" ) )
				),
				$content->get(),
				""
			);
			$portal->show_html( );
			*/
		}
		else {
			header ("Location: ".PATH_URL."404/");
			die;
		}
		
		$frameResponseObject->setTitle("Semester " . $current_semester_name);
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}

?>