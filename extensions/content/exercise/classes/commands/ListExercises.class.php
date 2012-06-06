<?php
namespace Exercise\Commands;
class ListExercises extends \AbstractCommand implements \IFrameCommand {

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

	public function execute( \FrameResponseObject $frameResponseObject ){
		
		if (!isset($this->id)) {
                    header("location: " . PATH_URL . "404/");
                    exit;
                }

                $exerciseObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                if (!$exerciseObject instanceof \steam_object) {
                    header("location: " . PATH_URL . "404/");
                    exit;
                }

                $basepath = $exerciseObject->get_path() . "/";
                $ex_path = $basepath . "exercises/";
                $sl_path = $basepath . "solutions/";
                $rv_path = $basepath . "reviews/";

		/*
		 * Get Data
		 */
		$ex_room = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path);
		$fltr = array(  array( '-', 'class', CLASS_USER	     ),
						array( '+', 'class', CLASS_CONTAINER )
					 );
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$exercises = $ex_room->get_inventory_filtered( $fltr , $sort, 0, 0 ); 

		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "Übungsaufgaben", "link" => PATH_URL . "exercise/index/" . $this->id), array("name" => "Liste der Aufgaben")));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("ListExercises.template.html");
		
		
		
		
		
		foreach ($exercises as $entry) {
			
			$tmplt->setCurrentBlock("BLOCK_ENTRY");
			
			$link = '<a href="' . PATH_URL . 'exercise/DisplayExercise/' . $this->id . "/" . $entry->get_name() . '/">' . $entry->get_name() . '</a>';
			$tmplt->setVariable( "ICON_PATH", PATH_URL . "exercise/asset/exercise_doc.png" );
			$tmplt->setVariable( "EX_NAME", $link );
			
			$end = $entry->get_attribute("EX_DEADLINE");
			$ex_dead = date("d.m.Y H:i", $end);
			$tmplt->setVariable( "EX_DEADLINE", "Bearbeitung bis " . $ex_dead );
			
			$authorobj = $entry->get_attribute("CONT_USER_MODIFIED");
			if (!is_object($authorobj)) 
				$authorobj = $entry->get_creator();
			$author = $authorobj->get_attribute("USER_FIRSTNAME") . " " . $authorobj->get_attribute("USER_FULLNAME");
			$authorlnk = '<a href="' . USER_EXTENSION_URL . $authorobj->get_name() . '/">' . $author . '</a>';
			$tmplt->setVariable( "EX_AUTHOR", $authorlnk );
			
			$changed = $entry->get_attribute("OBJ_LAST_CHANGED");
			if ($changed == 0) 
				$changed = $entry->get_attribute("OBJ_CREATION_TIME");
			$tmplt->setVariable( "EX_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
			$tmplt->setVariable( "EX_COMMENTS", "0 Kommentare" );
			
			/*$actionCP  = '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">';
			$actionCP .= '<img src="' . PATH_URL . 'exercise/asset/link.png"></a><br />';
			$actionCP .= '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">Link</a>';
			$tmplt->setVariable( "ACTION_COPY", $actionCP );
                        */
                   
			$actionED  = '<a href="' . PATH_URL . 'exercise/CreateExercise/' . $this->id . "/" . $entry->get_name() . '/">';
			$actionED .= '<img src="' . PATH_URL . 'exercise/asset/edit.png"></a><br />';
			$actionED .= '<a href="' . PATH_URL . 'exercise/CreateExercise/' . $this->id . "/" . $entry->get_name() . '/">Ändern</a>';
			$tmplt->setVariable( "ACTION_EDIT", $actionED );
			
			$actionSV  = '<a href="' . PATH_URL . 'exercise/CreateSolution/' . $this->id . "/" . $entry->get_id() . '/">';
			$actionSV .= '<img src="' . PATH_URL . 'exercise/asset/solve_small.png"></a><br />';
			$actionSV .= '<a href="' . PATH_URL . 'exercise/CreateSolution/' . $this->id . "/" . $entry->get_id() . '/">Abgabe</a>';
			$tmplt->setVariable( "ACTION_SOLVE", $actionSV );
			
			$actionVS  = '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $this->id . '/">';
			$actionVS .= '<img src="' . PATH_URL . 'exercise/asset/solution_small.png"></a><br />';
			$actionVS .= '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $this->id . '/">Lösung</a>';
			$tmplt->setVariable( "ACTION_VIEWSOLUTION", $actionVS );
			
			$tmplt->parse("BLOCK_ENTRY");
		}
		
		
		/*
		 * assemble frameResponse
		 */
		$listCss = Index::readFile( PATH_URL . "exercise/css/list_obj.css");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setCss($listCss);
		$rawHtml->setHtml($tmplt->get());
		
		$frameResponseObject->setTitle("Exercise");
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>