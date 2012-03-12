<?php
namespace Exercise\Commands;
class ListWorkload extends \AbstractCommand implements \IFrameCommand {

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
		 * Get Container Reference
		 */
		if ( isset ( $this->params[0] ) ) {
			
			$ex_container_name = $this->params[0];
			
			if ( Index::existsContainer($sl_path . $ex_container_name) ) {
				
				$sl_ex_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path . $ex_container_name );
				$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name );
				
				$submission_closed = (($ex_container->get_attribute("EX_DEADLINE") < time())) ? true : false ;
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
		
		
		/*
		 * Fetch all solutions in this exercise which are assigned to the current user (=reviewer)
		 */
		if ($submission_closed) {
			$fltr = array(  array( '-', 'class', CLASS_USER	     ),
							array( '+', 'class', CLASS_CONTAINER )
						 );
			$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
			$solution = $sl_ex_container->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
			$my_workload = array();
			
			foreach ($solution as $doc) {
			
				$me = \lms_steam::get_current_user();
				$assigned = $doc->get_attribute("SL_REVIEWER");
				if ( $assigned ==  $me->get_name() ) {
					$my_workload[] = $doc;
				}
			}
		}
		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "Übungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Meine zugeteilten Abgaben f&uuml;r \"".$ex_container->get_name()."\"")));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("ListWorkload.template.html");
		
		if ( !$submission_closed ) { 
			
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >Der Abgabezeitraum der &Uuml;bung "'.$ex_container->get_name().'" ist noch nicht abgelaufen.</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
		}
		else {
		
			foreach ($my_workload as $entry) {
				
				$tmplt->setCurrentBlock("BLOCK_ENTRY");
				
				$sl_participants = array();
				$j = (integer)($entry->get_attribute("SL_PARTICIPANTS_AMOUNT"));
				while ( $j > 0 ) {
					
					$sl_participants[] = $entry->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
					$j--;
				}
				$sl_participants = array_reverse($sl_participants);
				
				$link = '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $ex_container_name . '/'  . $entry->get_name() . '/">L&ouml;sung Nr: ' . $entry->get_name() . '</a>';
				$tmplt->setVariable( "ICON_PATH", PATH_URL . "exercise/asset/solution_doc.png" );
				$tmplt->setVariable( "EX_NAME", $link );
				$tmplt->setVariable( "EX_ID", 'Von: ' . implode(', ', $sl_participants) );
				
				$authorobj = $entry->get_attribute("CONT_USER_MODIFIED");
				if (!is_object($authorobj)) 
					$authorobj = $entry->get_creator();
				$author = $authorobj->get_name();
				$authorlnk = '<a href="' . PATH_URL . 'user/index/' . $authorobj->get_name() . '/">' . $authorobj->get_name() . '</a>';
				$tmplt->setVariable( "SL_AUTHOR", $authorlnk );
				
				$changed = $entry->get_attribute("SL_LEARNER_LAST_MODIFIED");
				if ($changed == 0) 
					$changed = $entry->get_attribute("OBJ_CREATION_TIME");
				$tmplt->setVariable( "SL_CHANGED", strftime("%d.%m.%Y, ", $changed) . strftime("%R", $changed) );
				$tmplt->setVariable( "SL_PARTICIPANTS" , $entry->get_attribute("SL_PARTICIPANTS_AMOUNT") );
				
				#Actions
				$actionCP  = '<a onClick="' .
							"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
							"null, null, 'explorer'); return false;" . '" href="#">';
				$actionCP .= '<img src="' . PATH_URL . 'exercise/asset/link.png"></a><br />';
				$actionCP .= '<a onClick="' .
							"sendRequest('Copy', {'id':'" . $entry->get_id() . "'}, '', 'updater', " .
							"null, null, 'explorer'); return false;" . '" href="#">Link</a>';
				$tmplt->setVariable( "ACTION_SHOWSL", $actionCP );
				
				
				$actionRV  = '<a href="' . PATH_URL . 'exercise/CreateReview/' . $ex_container_name . '/' . $entry->get_name() . '/">';
				$actionRV .= '<img src="' . PATH_URL . 'exercise/asset/makereview_small.png"></a><br />';
				$actionRV .= '<a href="' . PATH_URL . 'exercise/CreateReview/' . $ex_container_name . '/' . $entry->get_name() . '/">Korrigieren</a>';
				$tmplt->setVariable( "ACTION_REVIEW", $actionRV );
				
				$tmplt->parse("BLOCK_ENTRY");
			}
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
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>