<?php
namespace Course\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		//$portal = \lms_portal::get_instance();
		//$portal->initialize( GUEST_NOT_ALLOWED );
		//$portal->set_guest_allowed( GUEST_NOT_ALLOWED );

		$user = \lms_steam::get_current_user();
		//$portal_user = $portal->get_user();
		//$path = $request->getPath();
		$scg = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
		$current_semester = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER );
		
		//$current_semester = $path[3];
		
		//Vorher
		
		$group_course = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->params[0] . "." . $this->params[1]);
		$course = new \koala_group_course( $group_course );
		$html_handler_course = new \koala_html_course($course);
		$html_handler_course->set_context( "start" );
		$content = \Course::getInstance()->loadTemplate("courses_start.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "courses_start.template.html" );
		$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );

		// Pre-load Course Attributes
		$course->get_steam_object()->get_attributes(array("COURSE_SHORT_DSC", OBJ_DESC, "COURSE_LONG_DSC"));
		$desc = $course->get_attribute( "COURSE_SHORT_DSC" );

		if ( empty( $desc ) )
		{
			$content->setVariable( "OBJ_DESC", gettext( "No description available." ) );
		}
		else
		{
			$content->setVariable( "OBJ_DESC", get_formatted_output( $desc ) );
		}
		$about = $course->get_attribute( "COURSE_LONG_DSC" );
		if ( ! empty( $about ) )
		{
			$content->setCurrentBlock( "BLOCK_ABOUT" );
			$content->setVariable( "LABEL_ABOUT", gettext( "About" ) );
			$content->setVariable( "VALUE_ABOUT", get_formatted_output( $about ) );
			$content->parse( "BLOCK_ABOUT" );
		}

		if (COURSE_SHOW_MAXSIZE) {
			if ($course->get_maxsize() > 0) {
				$content->setCurrentBlock("BLOCK_GROUPSIZE");
				$content->setVariable("LABEL_MAXSIZE_HEADER", gettext("The number of participants of this course is limited."));
				$content->setVariable("LABEL_MAXSIZE_DESCRIPTION", str_replace("%MAX", $course->get_maxsize(), str_replace("%ACTUAL", $course->count_members() ,  gettext("The actual participant count is %ACTUAL of %MAX."))));
				$content->parse("BLOCK_GROUPSIZE");
			}
		}
		$admins = $course->get_staff();

		$hidden_members = $course->get_steam_group()->get_attribute("COURSE_HIDDEN_STAFF");
		if (!is_array($hidden_members)) $hidden_members = array();

		$visible_staff = 0;
		foreach( $admins as $admin ) {
			if( ! in_array( $admin->get_id(), $hidden_members ))
			{
				$content->setCurrentBlock( "BLOCK_ADMIN" );
				if (COURSE_START_ADMIN_PROFILE_ANKER) {
					$content->setCurrentBlock( "PROFILE_ANKER" );
				} else {
					$content->setCurrentBlock( "PROFILE_NO_ANKER" );
				}
				$admin_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON", "OBJ_DESC", "OBJ_NAME" ) );
				if ( $admin instanceof \steam_user )
				{
					$content->setVariable( "ADMIN_NAME", $admin_attributes[ "USER_FIRSTNAME" ] . " " . $admin_attributes[ "USER_FULLNAME" ] );
					(!COURSE_START_ADMIN_PROFILE_ANKER) or $content->setVariable( "ADMIN_LINK", PATH_URL . "user/" . $admin->get_name() . "/" );
				}
				else
				{
					$content->setVariable( "ADMIN_NAME", $admin_attributes[ "OBJ_NAME" ] );
					(!COURSE_START_ADMIN_PROFILE_ANKER) or $content->setVariable( "ADMIN_LINK", PATH_URL . "groups/" . $admin->get_id() . "/" );
				}
				$icon_link = ( is_object( $admin_attributes[ "OBJ_ICON" ] ) ) ? PATH_URL . "download/image/" . $admin_attributes[ "OBJ_ICON" ]->get_id() . "/40/47" : PATH_STYLE . "images/anonymous.jpg";
				$content->setVariable( "ADMIN_ICON", $icon_link );

				$adminDescription = h($admin_attributes["OBJ_DESC"]);
				switch ($adminDescription){
					case "student":$adminDescription = gettext("student");break;
					case "staff member":$adminDescription = gettext("staff member");break;
					case "alumni":$adminDescription = gettext("alumni");break;
					case "guest":$adminDescription = gettext("guest");break;
					case "" : $adminDescription = gettext("student");break;
					default: break;
				}

				if (COURSE_START_SEND_MESSAGE && (!COURSE_SHOW_ONLY_EXTERN_MAIL || (COURSE_SHOW_ONLY_EXTERN_MAIL && is_string(steam_factory::get_user($GLOBALS['STEAM']->get_id(), $admin_attributes[ "OBJ_NAME" ])->get_attribute("USER_EMAIL")) && (steam_factory::get_user($GLOBALS['STEAM']->get_id(), $admin_attributes[ "OBJ_NAME" ])->get_attribute("USER_EMAIL")) != "") && (steam_factory::get_user($GLOBALS['STEAM']->get_id(), $admin_attributes[ "OBJ_NAME" ])->get_attribute("USER_FORWARD_MSG") === 1)) ) {
					$adminDescription = $adminDescription . " - <a href=\"/messages_write.php?to=".$admin->get_name()."\">".gettext("Nachricht senden")."</a>";
				}

				$content->setVariable( "ADMIN_DESC", $adminDescription );
				if (COURSE_START_ADMIN_PROFILE_ANKER) {
					$content->parse( "PROFILE_ANKER" );
				} else {
					$content->parse( "PROFILE_NO_ANKER" );
				}
				$content->parse( "BLOCK_ADMIN" );
				$visible_staff++;
			}
		}
		if ($visible_staff > 0) {
			$content->setCurrentBlock("BLOCK_ADMIN_HEADER");
			$content->setVariable( "LABEL_ADMINS", gettext( "Staff members" ) );
			$content->parse("BLOCK_ADMIN_HEADER");
		}

		$html_handler_course->set_html_left( $content->get() );
		$frameResponseObject->setHeadline($html_handler_course->get_headline());
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($html_handler_course->get_html());
		$frameResponseObject->addWidget($widget);
		//$portal->set_page_main( $html_handler_course->get_headline(), $html_handler_course->get_html() , "" );
		return $frameResponseObject;
	}
	
}
?>