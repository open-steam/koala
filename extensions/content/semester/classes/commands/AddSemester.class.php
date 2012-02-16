<?php
namespace Semester\Commands;

class AddSemester extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$portal = \lms_portal::get_instance();
		
		$user = \lms_steam::get_current_user();

		
		if(!\lms_steam::is_steam_admin($user)) {
			//TODO: REDIRECT!!
		}

		
		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$values = $_POST["values"];
			$start_date = iso_to_unix($values["start"]);
			$end_date   = iso_to_unix($values["end"]);
			
			// TODO PROBLEM CHECKING MISSING 
			$courses = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP);
			$all_user = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), STEAM_ALL_USER);
			$new_semester = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), $values["name"], $courses, FALSE, $values["desc"]);
			$new_semester_admins = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "admins", $new_semester, FALSE, "admin group for ".$values["desc"]);
			$new_semester_admins->set_attribute("OBJ_TYPE", "semester_admins");
			$new_semester_admins->add_member($user);
			$new_semester->set_insert_access($new_semester_admins, TRUE);
			$new_semester->set_read_access($all_user, TRUE);
			$new_semester->set_attributes(array(
				"SEMESTER_START_DATE" => $start_date,
				"SEMESTER_END_DATE"   => $end_date
			));	
			// CACHE ZURÜCKSETZEN
			$cache = get_cache_function("ORGANIZATION");
			$cache->drop("lms_steam::get_semesters");
		
			header("Location: ".PATH_URL."semester/index/".$values["name"]."/all");
		
		}
		
		$content = \Semester::getInstance()->loadTemplate("semester_create.template.html");
		$content->setVariable("INFO_TEXT", gettext("So, you want to start a new semester?")." ".gettext("Please fill out the requested values on the right.")."<br/><br/>".str_replace("%SEMESTER", STEAM_CURRENT_SEMESTER, gettext("And don't forget to reset the current semester in the etc/koala.def.php, which is <b>%SEMESTER</b> at the moment."))  );
		
		$content->setVariable("LABEL_NAME", gettext("Shortname"));
		$content->setVariable("INFO_NAME", gettext("IMPORTANT: Don't use any slashes, commas or dots in this name. Keep it short, like 'WS0607' or 'SS06'."));
		
		$content->setVariable("LABEL_DESC", gettext("Name"));
		$content->setVariable("INFO_DESC", gettext("Examples: 'Wintersemester 06/07', or 'Sommersemester 2006'"));
		
		$content->setVariable("LABEL_START_DATE", gettext("Starting date of semester"));
		$content->setVariable("LABEL_END_DATE", gettext("Ending date of semester"));
		$content->setVariable("INFO_DATE_FORMAT", gettext("Please type in the date in the following format: YYYY-MM-DD"));
		$content->setVariable("LABEL_CREATE", gettext("Create Semester"));
		
		/* TODO: Portal anpassen
		$portal->set_page_main(
			array(array("link" => PATH_URL.SEMESTER_URL."/", "name" => gettext("Semester")), array("link" => "", "name" => gettext("Create new"))),
			$content->get(),
			""
		);
		*/
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>