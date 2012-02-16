<?php
namespace Questionary\Commands;

class NewQuestionary implements \ICommand {
	
	public function execute ($request, $response) {

		$myExtension = \Questionary::getInstance();
		$name = trim((isset($_POST["name"]))?$_POST["name"]:"test");
		 
		//current room steam object
	  	//if( (int) $object != 0 ) 
	  		//$current_room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object);
	  	//else 
	  		$current_room = $GLOBALS["STEAM"]->get_current_steam_user()->get_workroom(); 
		
	  	$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
		
	  	//get write permission
		$access_write = $current_room->check_access_write($steamUser);
	  	$access_read = $current_room->check_access_read($steamUser);
	  	$access_insert = $current_room->check_access_insert($steamUser);
	  	$allowed =  $access_write && $access_read && $access_insert ? true : false;
	  	
	  	if(!$allowed)
	  	{
	    	// ACCESS DENIED?
	  	}

	  	
	  	//create questionary if adviced too
  		if(isset($_POST["mission"]) && $_POST["mission"] == "create" && $name != "" && $allowed)
  		{
		  	//create new questionary
			$questionary = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $current_room);
			$questions_folder = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), 'questions', $questionary);
			$answers_folder = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), 'answers', $questionary);
		
			if(isset($_POST["description"]))
				$description=$_POST["description"];
			else 
				$description="";
			
			if($questionary && $questions_folder && $answers_folder)
			{
				//set standard layout
				$layout="template";//$templates[1];
				
				//define rights for the attributes
				$loginuser_id = $steamUser->get_id(); 
				$rootid = \steam_factory::username_to_object($GLOBALS[ "STEAM" ]->get_id(), "root")->get_id();
				$author_rights[$loginuser_id] = $loginuser_id;
				$editor_rights[$loginuser_id] = $loginuser_id;
				$analyst_rights[$loginuser_id] = $loginuser_id;
				$author_rights[$rootid] = $rootid;
				$editor_rights[$rootid] = $rootid;
				$analyst_rights[$rootid] = $rootid;
			  
				//define container as questionary and set all attributes
				$attributes = array(
			    	"bid:doctype" => "questionary",
					"bid:questionary:fillout" => "1",
			    	"bid:questionary:editanswer" => "1",
					"bid:questionary:editownanswer" => "0",
			    	"bid:questionary:number" => "1",
			    	"bid:questionary:resultcreator" => "1",
			    	"bid:questionary:resultcreationtime" => "1",
					"bid:questionary:description" => $description,
					"bid:questionary:edittime" => array(0,0,0),
					"bid:questionary:enabled" => false,
					"bid:questionary:layout" => $layout,
					"bid:questionary:author_rights" => $author_rights,
					"bid:questionary:editor_rights" => $editor_rights,
					"bid:questionary:analyst_rights" => $analyst_rights
			  	);
			  
				$result = $questionary->set_attributes($attributes); 
				
				header("Location: ".PATH_URL."questionary/editQuestionary/".$questionary->get_id());
			}
  		}	
  		else {
			$myExtension->addCSS();
			
			$content = $myExtension->loadTemplate("questionaryNewQuestionary.template.html");
			
			$content->setCurrentBlock('BLOCK_FORM_NEW_QUESTIONARY');
			$content->setVariable("QUESTIONARY_ID", "");
			$content->setVariable("BUTTON_MISSION", "create");
			$content->setVariable("OBJECT_ID", $current_room->get_id());
	  		$content->parse('BLOCK_FORM_NEW_QUESTIONARY');
			
			return $content->get();
  		}
	}
}
?>