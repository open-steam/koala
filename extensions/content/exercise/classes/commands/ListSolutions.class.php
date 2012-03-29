<?php
namespace Exercise\Commands;
class ListSolutions extends \AbstractCommand implements \IFrameCommand {

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
		$sl_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
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
		$exercise = $sl_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
		$my_solutions = array();
		
		foreach ($exercise as $folder) {
			
			$solution = $folder->get_inventory_filtered( $fltr, $sort, 0, 0 );
			foreach ($solution as $doc) {

				$n = (integer)($doc->get_attribute("SL_PARTICIPANTS_AMOUNT"));
				for ( $j = 1 ; $j <= $n ; $j++ ) {
					
					$me = \lms_steam::get_current_user();
					$author = $doc->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
					if ( $author ==  $me->get_name() ) {
						$my_solutions[] = $doc;
					}
				}
			}
		}

		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "Übungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Meine L&ouml;sungen")));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("ListSolutions.template.html");
		
		
		
		foreach ($my_solutions as $entry) {
			
			$tmplt->setCurrentBlock("BLOCK_ENTRY");
			
			$parent = $entry->get_environment();
			$parent_name = $parent->get_name();
			$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $parent_name );
			
			$link = '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $parent_name . '/'  . $entry->get_name() . '/">L&ouml;sung zu ' . $ex_container->get_name() . '</a>';
			$tmplt->setVariable( "ICON_PATH", PATH_URL . "exercise/asset/solution_doc.png" );
			$tmplt->setVariable( "EX_NAME", $link );
			
			$end = $ex_container->get_attribute("EX_DEADLINE");
			$ex_dead = date("d.m.Y H:i", $end);
			$tmplt->setVariable( "EX_DEADLINE", "Bearbeitung bis " . $ex_dead );
		
			$authorobj = $entry->get_creator();
			$author = $authorobj->get_name();
			$authorlnk = '<a href="' . PATH_URL . 'user/index/' . $authorobj->get_name() . '/">' . $authorobj->get_name() . '</a>';
			$tmplt->setVariable( "SL_AUTHOR", $authorlnk );
			
			$changed = $entry->get_attribute("SL_LEARNER_LAST_MODIFIED");
			if ($changed == 0) 
				$changed = $entry->get_attribute("OBJ_CREATION_TIME");
			$tmplt->setVariable( "SL_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
			
			$rvid = $entry->get_attribute("SL_REVIEW_ID");
			$rv_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path.$parent_name.'/'.$entry->get_name());
			if (($rvid != "NONE") && !($rv_container === 0)) {
				
				$authorobj = $rv_container->get_attribute("CONT_USER_MODIFIED");
				if (!is_object($authorobj)) 
					$authorobj = $rv_container->get_creator();
				$author = $authorobj->get_name();
				$authorlnk = '<a href="' . PATH_URL . 'user/index/' . $authorobj->get_name() . '/">' . $authorobj->get_name() . '</a>';
				$tmplt->setVariable( "RV_AUTHOR", $authorlnk );
				
				$changed = $rv_container->get_attribute("OBJ_LAST_CHANGED");
				if ($changed == 0) 
					$changed = $rv_container->get_attribute("OBJ_CREATION_TIME");
				$tmplt->setVariable( "RV_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
				
				$rv_points = $rv_container->get_attribute("RV_RESULT");
				$tmplt->setVariable( "RV_POINTS", $rv_points."/".$ex_container->get_attribute("EX_POINTS")."<br />Punkten" );
			}
			else {
				$tmplt->setVariable( "RV_AUTHOR", '<span style="color:black;font-style:none;">noch keine Korrektur</span>' );
				$tmplt->setVariable( "RV_CHANGED", '/' );
				$tmplt->setVariable( "RV_POINTS", "-/".$ex_container->get_attribute("EX_POINTS")."<br />Punkten" );
			}
			
			#Actions
			$actionCP  = '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">';
			$actionCP .= '<img src="' . PATH_URL . 'exercise/asset/link.png"></a><br />';
			$actionCP .= '<a onClick="' .
						"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
						"null, null, 'explorer'); return false;" . '" href="#">Link</a>';
			$tmplt->setVariable( "ACTION_SHOWSL", $actionCP );
			
			if (($rvid != "NONE") && !($rv_container === 0)) {
				
				$actionRV  = '<a href="' . PATH_URL . 'exercise/DisplayReview/' . $parent_name . '/' . $entry->get_name() . '/">';
				$actionRV .= '<img src="' . PATH_URL . 'exercise/asset/review_small.png"></a><br />';
				$actionRV .= '<a href="' . PATH_URL . 'exercise/DisplayReview/' . $parent_name . '/' . $entry->get_name() . '/">Korrektur</a>';
				$tmplt->setVariable( "ACTION_SHOWRV", $actionRV );
			}
			
			$time = time();
			if (! ($end < $time)) {
				
				$actionED  = '<a href="' . PATH_URL . 'exercise/CreateSolution/' . $parent_name . '/">';
				$actionED .= '<img src="' . PATH_URL . 'exercise/asset/edit.png"></a><br />';
				$actionED .= '<a href="' . PATH_URL . 'exercise/CreateSolution/' . $parent_name . '/">Ändern</a>';
				$tmplt->setVariable( "ACTION_SOLVE", $actionED );
			}
			
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