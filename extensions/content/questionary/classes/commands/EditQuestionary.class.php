<?php
namespace Questionary\Commands;

class EditQuestionary implements \ICommand {
	
	public function execute ($request, $response) {
		
		$myExtension = \Questionary::getInstance();
		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$path = $request->getPath();
		
		//******************************************************
		//** Presumption
  		//******************************************************

		$questionary_id = (isset($path[2]))?$path[2]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
		$action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");
		$question_id = (isset($_GET["question_id"]))?$_GET["question_id"]:((isset($_POST["question_id"]))?$_POST["question_id"]:"");
		$combo_id = (isset($_GET["option"]))?$_GET["option"]:((isset($_POST["option"]))?$_POST["option"]:"");
				
	  
		//current room steam object
		if( (int) $questionary_id != 0 ) 
		{
 			$questionary = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $questionary_id);
 			$question_folder = $questionary->get_object_by_name('questions');
 			$answer_folder = $questionary->get_object_by_name('answers');

 			if((int) $question_id != 0 ) $question = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $question_id);
  		}
  		else  
  		{
  			//TODO: HÄÄÄÄ?????????
  			//header("Location: $config_webserver_ip/index.php");
			//exit();
  		}
	  	
  		
  		/*  
  		 * TODO: RIGHTS MANAGEMENT

  		// create new RIGHTS object
		$rights = new rights($steam, $questionary, $question_folder, $answer_folder);

		//check author permission  
		$login_user = $steam->get_login_user();
		$login_user_id = $login_user->get_id();
		$login_user_groups = $login_user->get_groups();
		
		foreach($login_user_groups as $login_user_group)
			$login_user_group_ids[]=$login_user_group->get_id();
		
		$is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
		
		if(!$is_author || count($answer_folder->get_inventory())>0)
		{
			//Disconnect & close
			$steam->disconnect();
			die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
		}
		*/

  		
  		
  		//disable enable questionary to fill out
		if($action == "enable")
		{
			$questionary->set_attribute("bid:questionary:enabled", true);
		}
		if($action == "disable")
		{
			$questionary->set_attribute("bid:questionary:enabled", false);
		}
  		
		
		
		//load Attributes
		$attributes = $questionary->get_attributes(array(	OBJ_NAME, 
  													"bid:questionary:number", 
													"bid:questionary:enabled", 
													"bid:questionary:edittime"));
		$questionary_name = $attributes[OBJ_NAME];
		$number = $attributes["bid:questionary:number"];
		$enabled = $attributes["bid:questionary:enabled"];
		$edittime = $attributes["bid:questionary:edittime"][0];
  
  
  
  		
	  	/*
	  	$myExtension->addCSS();
			
		$content = $myExtension->loadTemplate("questionaryNewQuestionary.template.html");
			
		$content->setCurrentBlock('BLOCK_FORM_NEW_QUESTIONARY');
		$content->setVariable("QUESTIONARY_ID", "");
		$content->setVariable("BUTTON_MISSION", "create");
		$content->setVariable("OBJECT_ID", $current_room->get_id());
  		$content->parse('BLOCK_FORM_NEW_QUESTIONARY');
			
		return $content->get();
  		*/
	}
}
?>