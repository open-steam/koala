<?php
namespace Rapidfeedback\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$RapidfeedbackExtension->addCSS();
		$RapidfeedbackExtension->addJS();
		$create_label = "Neue Umfrage erstellen";
		
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_edit.template.html");
		$content->setCurrentBlock("BLOCK_CREATE_SURVEY");
		$content->setVariable("CREATE_LABEL", "Umfrage erstellen");
		$content->setVariable("TITLE_LABEL", "Titel:*");
		$content->setVariable("BEGINTEXT_LABEL", "Willkommenstext:");
		$content->setVariable("ENDTEXT_LABEL", "Abschlusstext:");
		$content->setVariable("STARTTYPE_LABEL", "Starttyp:*");
		$content->setVariable("STARTTYPE0_LABEL", "Manuell");
		$content->setVariable("STARTTYPE1_LABEL", "Automatisch");
		$content->setVariable("START_LABEL", "von:");
		$content->setVariable("END_LABEL", "bis:");
		$content->setVariable("ELEMENT_COUNTER", 0);
		$content->setVariable("STARTTYPE_FIRST", "checked");
		$content->setVariable("DISPLAY_DATEPICKER", "none");
		$content->setVariable("QUESTION_LABEL", "Frage");
		$content->setVariable("HELPTEXT_LABEL", "Hilfetext");
		$content->setVariable("QUESTIONTYPE_LABEL", "Fragetyp");
		$content->setVariable("TEXTQUESTION_LABEL", "Text");
		$content->setVariable("TEXTAREAQUESTION_LABEL", "Textarea");
		$content->setVariable("SINGLECHOICE_LABEL", "Single Choice");
		$content->setVariable("MULTIPLECHOICE_LABEL", "Multiple Choice");
		$content->setVariable("MATRIX_LABEL", "Matrix");
		$content->setVariable("GRADING_LABEL", "Benotung");
		$content->setVariable("TENDENCY_LABEL", "Tendenz");
		$content->setVariable("ARRANGEMENT_LABEL", "Anordnung in");
		$content->setVariable("SCALE_LABEL", "Skala");
		$content->setVariable("STEPS_LABEL", "Schritte");
		$content->setVariable("POSSIBLEANSWERS_LABEL", "Antwortmöglichkeiten");
		$content->setVariable("ADDOPTION_LABEL", "Weitere Option hinzufügen");
		$content->setVariable("COLUMNS_LABEL", "Spalten");
		$content->setVariable("COLUMNSLABEL_LABEL", "Spalten Label");
		$content->setVariable("ROWSLABEL_LABEL", "Zeilen Label");
		$content->setvariable("ELEMENTS_LABEL", "Elemente");
		$content->setVariable("ADDROWS_LABEL", "Weitere Zeilen hinzufügen");
		$content->setVariable("MANDATORY_LABEL", "Als Pflichtfrage definieren");
		$content->setVariable("SAVE_LABEL", "Speichern");
		$content->setVariable("CANCEL_LABEL", "Abbrechen");
		$content->setVariable("ADDQUESTION_LABEL", "Neue Frage hinzufügen");
		$content->setVariable("ADDLAYOUT_LABEL", "Layout-Element hinzufügen");
		$content->setVariable("CREATE_SURVEY", "Umfrage erstellen");
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id());
		
		// if command is called with an object id load the corresponding survey data
		if (isset($this->params[1])) {
			$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
			$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
			$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
			$survey_object->parseXML($xml);
			$content->setVariable("TITLE_VALUE", $survey_object->getName());
			$content->setVariable("BEGINTEXT_VALUE", $survey_object->getBeginText());
			$content->setVariable("ENDTEXT_VALUE", $survey_object->getEndText());
			$starttype= $survey->get_attribute("RAPIDFEEDBACK_STARTTYPE");
			if (is_array($starttype)) {
				$content->setVariable("STARTTYPE_FIRST", "");
				$content->setVariable("STARTTYPE_SECOND", "checked");
				$content->setVariable("DISPLAY_DATEPICKER", "");
				$content->setVariable("BEGIN_VALUE", date('d.m.Y', $starttype[1]));
				$content->setVariable("END_VALUE", date('d.m.Y', $starttype[0]));
			}
			$questions = $survey_object->getQuestions();
			$question_html = "";
			$id_counter = 0;
			$asseturl = $RapidfeedbackExtension->getAssetUrl() . "icons/";
			for ($count = 0; $count < count($questions); $count++) {
				$question_html = $question_html . $questions[$count]->getEditHTML($id_counter);
				$id_counter++;
			}
			$content->setVariable("ELEMENT_COUNTER", $id_counter);
			$content->setVariable("QUESTIONS_HTML", $question_html);
			$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/" . $survey->get_id());
			$content->setVariable("CREATE_LABEL", "Umfrage bearbeiten");
			$content->setVariable("CREATE_SURVEY", "Änderungen speichern");
			$create_label = "Umfrage bearbeiten";
		}
		
		$content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
		$content->parse("BLOCK_CREATE_SURVEY");
		
		$group = $rapidfeedback->get_attribute("RAPIDFEEDBACK_GROUP");
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id()),
			array("name" => $create_label)
		));
		return $frameResponseObject;
	}
}
?>