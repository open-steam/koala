<?php
namespace Exercise\Commands;
class ListReviews extends \AbstractCommand implements \IFrameCommand {

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

		/*
		 * for testing purpose preselect course EXT-01: 
		 */
		$prm = array("WS1011", "Ext-01");
		$basepath = "/home/Courses." . $prm[0] . "." . $prm[1] . ".learners/";
		$ex_path = $basepath . "exercises/";
		$sl_path = $basepath . "solutions/";
		$rv_path = $basepath . "reviews/";
		
		/*
		 * Get Data
		 */
		$rv_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path);
		$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $prm[0]); 
		/*
		if ( isset ( $this->params[0] ) ) {
			
			$ex_container_name = $this->params[0];
			
			if ( self::existsContainer($sl_path . $ex_container_name) ) {
				
				$sl_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
				//$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name);
				//$ex_container_id = $ex_container->get_id();
				
			}
			else {
				echo "error: Exercise does not exist"; 
				die;
			}
		}
		else {
			echo "error: no Exercise selected!";
			die;  
		}
		*/
		
		/*
		 * Fetch all of the current users solutions in this course
		 */
		$fltr = array(  array( '-', 'class', CLASS_USER	     ),
						array( '+', 'class', CLASS_CONTAINER )
					 );
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$exercise = $rv_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
		$my_reviews = array();
		
		foreach ($exercise as $folder) {
			
			$solution = $folder->get_inventory_filtered( $fltr, $sort, 0, 0 );
			foreach ($solution as $doc) {

				$me = \lms_steam::get_current_user();
				$author = $doc->get_attribute("RV_REVIEWER");
				if ( $author ==  $me->get_name() ) {
					$my_reviews[] = $doc;
				}
			}
		}

		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "Übungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Meine Korrekturen")));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("ListReviews.template.html");
		
		foreach ($my_reviews as $entry) {
			
			$tmplt->setCurrentBlock("BLOCK_ENTRY");
			
			$parent = $entry->get_environment();
			$parent_name = $parent->get_name();
			$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $parent_name );
			
			$link = '<a href="' . PATH_URL . 'exercise/DisplayReview/' . $parent_name . '/'  . $entry->get_name() . '/">Korrektur zur L&ouml;sung ' . $entry->get_name() . '</a>';
			$tmplt->setVariable( "ICON_PATH", PATH_URL . "exercise/asset/review_doc.png" );
			$tmplt->setVariable( "EX_NAME", $link );
			
			$ex = $ex_container->get_name();
			$tmplt->setVariable( "EX_EXERCISE", "zu &Uuml;bung: " . $ex );
			
			$authorobj = $entry->get_attribute("CONT_USER_MODIFIED");
			if (!is_object($authorobj)) 
				$authorobj = $entry->get_creator();
			$author = $authorobj->get_name();
			$authorlnk = '<a href="' . PATH_URL . 'user/index/' . $authorobj->get_name() . '/">' . $authorobj->get_name() . '</a>';
			$tmplt->setVariable( "RV_AUTHOR", $authorlnk );
			
			$changed = $entry->get_attribute("OBJ_LAST_CHANGED");
			if ($changed == 0) 
				$changed = $entry->get_attribute("OBJ_CREATION_TIME");
			$tmplt->setVariable( "RV_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
			
			$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path.$parent_name.'/'.$entry->get_name());
			$authorobj = $sl_container->get_creator();
			$author = $authorobj->get_name();
			$authorlnk = '<a href="' . PATH_URL . 'user/index/' . $authorobj->get_name() . '/">' . $authorobj->get_name() . '</a>';
			$tmplt->setVariable( "SL_AUTHOR", $authorlnk );
			
			$changed = $sl_container->get_attribute("SL_LEARNER_LAST_MODIFIED");
			if ($changed == 0) 
				$changed = $sl_container->get_attribute("OBJ_CREATION_TIME");
			$tmplt->setVariable( "SL_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
			
			$rv_points = $entry->get_attribute("RV_RESULT");
			$tmplt->setVariable( "RV_POINTS", $rv_points."/".$ex_container->get_attribute("EX_POINTS")." Punkten" );
			
			#Actions
			$actionCP  = '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">';
			$actionCP .= '<img src="' . PATH_URL . 'exercise/asset/link.png"></a><br />';
			$actionCP .= '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">Link</a>';
			$tmplt->setVariable( "ACTION_SHOWSL", $actionCP );
			
			$actionED  = '<a href="' . PATH_URL . 'exercise/CreateReview/' . $parent_name . '/' . $entry->get_name() . '/">';
			$actionED .= '<img src="' . PATH_URL . 'exercise/asset/edit.png"></a><br />';
			$actionED .= '<a href="' . PATH_URL . 'exercise/CreateReview/' . $parent_name . '/' . $entry->get_name() . '/">Ändern</a>';
			$tmplt->setVariable( "ACTION_SHOWRV", $actionED );
			
			$actionVS  = '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $parent_name . '/' . $entry->get_name() . '/">';
			$actionVS .= '<img src="' . PATH_URL . 'exercise/asset/solution_small.png"></a><br />';
			$actionVS .= '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $parent_name . '/' . $entry->get_name() . '/">Lösung</a>';
			$tmplt->setVariable( "ACTION_REVIEW", $actionVS );
			
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