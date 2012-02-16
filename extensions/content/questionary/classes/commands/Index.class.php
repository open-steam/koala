<?php
namespace Questionary\Commands;

class Index implements \ICommand {
	
	public function execute ($request, $response) {

		$path = $request->getPath();
		$objectId = $path[2];
		
		$questionary = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $objectId );
		$question_folder = $questionary->get_object_by_name('questions');
  		$answer_folder = $questionary->get_object_by_name('answers');
		$answers = $answer_folder->get_inventory();
		
		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
		
		$questionaryAttributes = $questionary->get_attributes(array(
			OBJ_NAME, OBJ_DESC,
			"bid:questionary:fillout",
			"bid:questionary:description",
			"bid:questionary:editownanswer",
			"bid:questionary:edittime",
			"bid:questionary:enabled"
		)); 

		$questionary_display_name = $questionaryAttributes[OBJ_NAME];					
		$att_fillout = $questionaryAttributes["bid:questionary:fillout"];
		$att_description = $questionaryAttributes["bid:questionary:description"];
		$att_edit_own_answer = $questionaryAttributes["bid:questionary:editownanswer"];
		$att_edit_time = $questionaryAttributes["bid:questionary:edittime"];
		$att_enabled = $questionaryAttributes["bid:questionary:enabled"];

		$questionary_display_name = $questionaryAttributes[OBJ_NAME];
		if(isset($questionaryAttributes[OBJ_DESC]) && $questionaryAttributes[OBJ_DESC] != "")
		{
			$questionary_display_name = $questionaryAttributes[OBJ_DESC];
		}
		
		$att_fillout = $questionaryAttributes["bid:questionary:fillout"];
		$att_description = $questionaryAttributes["bid:questionary:description"];
		$att_edit_own_answer = $questionaryAttributes["bid:questionary:editownanswer"];
		$att_edit_time = $questionaryAttributes["bid:questionary:edittime"];
		$att_enabled = $questionaryAttributes["bid:questionary:enabled"];

		
		$myExtension = \Questionary::getInstance();
		$myExtension->addCSS();
		$content = $myExtension->loadTemplate("questionaryIndex.template.html");
		
		$content->setCurrentBlock('BLOCK_HEAD');
		$content->setVariable("QUESTIONARY_NAME", $questionary_display_name);
		$content->setVariable("DESCRIPTION", $att_description);
		$content->parse('BLOCK_HEAD');
		
		/*
		$content->setCurrentBlock('BLOCK_EDIT_TIME_ROW');
		$content->setVariable("START_DATE", date("d.m.Y", $att_edit_time[1]));
		$content->setVariable("END_DATE", date("d.m.Y", $att_edit_time[2]));
		$content->parse('BLOCK_EDIT_TIME_ROW');
		*/
		return $content->get();
	}
}
?>