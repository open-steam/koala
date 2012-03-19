<?php
namespace Exercise\Commands;
class DisplaySolution extends \AbstractCommand implements \IFrameCommand {

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

		if ( isset ( $this->params[0] ) ) {
			if ( isset ( $this->params[1] ) ) {
				
				$ex_container_name = $this->params[0];
				$sl_container_name = $this->params[1];
				
				if ( Index::existsContainer($sl_path .$ex_container_name) ) {
					
					//$ex_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path . $ex_container_name);
					$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name); //id of container is name of the container in sl path.
					$ex_container_id = $ex_container->get_id();
					
					if ( Index::existsContainer($sl_path .$ex_container_name. "/" .$sl_container_name) ) {
						
						$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path .$ex_container_name. "/" .$sl_container_name);
						$sl_container_id = $sl_container->get_id();
					}
					else {
						echo "error: Solution does not exist";
						die;
					}
				}
				else {
					echo "error: Exercise does not exist"; 
					die;
				}
			}
			else {
				echo "error: no Solution selected!";
				die;  
			}
		}
		else {
			
			echo "error: no Exercise selected!";
			die; 
		}
		
		
		/*
		 * Get data
		 */
		$sl_desc = $sl_container->get_attribute("SL_DESCRIPTION");
		$sl_reviewer = $sl_container->get_attribute("SL_REVIEWER");
		# get participants
		$sl_participants = array();
		$j = (integer)($sl_container->get_attribute("SL_PARTICIPANTS_AMOUNT"));
		while ( $j > 0 ) {
			
			$file = array();
			$file['ID'] = $sl_container->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
			$file['FULLNAME'] = $sl_container->get_attribute("SL_PARTICIPANTS_" . ($j) . "_FN");
			$sl_participants[] = $file;
			
			$j--;
		}
		$sl_participants = array_reverse($sl_participants);
		# get documents in exercise container
		$sl_documents = array();
		$fltr = array(array( '+', 'class', CLASS_DOCUMENT ));
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$document = $sl_container->get_inventory_filtered( $fltr , $sort, 0, 0 );
		foreach ( $document as $file ) {
			
			if (!($file instanceof \steam_document))
				continue;
			
			$farr = array();
			$farr['NAME'] = $file->get_name();
			$farr['LINK'] = PATH_URL . "Download/Document/" . $file->get_id();
			$sl_documents[] = $farr;
		}
		
		
		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "Übungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Meine L&ouml;sungen", "link" => PATH_URL . "exercise/ListSolutions/"), array("name" => "L&ouml;sung zu " . $ex_container->get_name())));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "L&ouml;sung", "link" => PATH_URL . "exercise/CreateSolution/" . $ex_container_name . "/" . $sl_container_name . "/"), ));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("DisplaySolution.template.html");
		
		if ( isset($_SESSION['SUCCESS']) && isset($_SESSION['SUCMSG']) && $_SESSION['SUCCESS'] === TRUE ) {
			
			$sucmsg = '<div id=notice><p id="ex_success" style="display:none;" >' . $_SESSION['SUCMSG'] . '</p></div>';
			$sucjs  = "$('#ex_success').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $sucmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $sucjs );
			unset($_SESSION['SUCCESS']);
			unset($_SESSION['SUCMSG']);
		}
		
		
		
		foreach ($sl_participants as $author) {
			
			$tmplt->setCurrentBlock( "BLOCK_PARTICIPANTS" );
			$tmplt->setVariable( "ATTR_AUTHOR_ID", '<a href="' . PATH_URL . 'user/index/' . $author['ID'] . '/">' . $author['ID'] . '</a>' );
			$tmplt->setVariable( "ATTR_AUTHOR_FULLNAME", $author['FULLNAME'] );
			$tmplt->parse( "BLOCK_PARTICIPANTS" );
		}
		
		$tmplt->setVariable( "ATTR_SUBMITTED", date("d.m.Y H:i", $sl_container->get_attribute("OBJ_CREATION_TIME")) );
		$tmplt->setVariable( "ATTR_CHANGED", date("d.m.Y H:i", $sl_container->get_attribute("SL_LEARNER_LAST_MODIFIED")) );
		$tmplt->setVariable( "ATTR_AUTHORS_AMT", $sl_container->get_attribute("SL_PARTICIPANTS_AMOUNT") );
		$tmplt->setVariable( "ATTR_TUTOR", '<a href="' . PATH_URL . 'user/index/' . $sl_reviewer . '/">' . $sl_reviewer . '</a>' );
		$tmplt->setVariable( "ATTR_DESCRIPTION", nl2br($sl_desc) );
		
		foreach ($sl_documents as $doc) {
			
			$tmplt->setCurrentBlock( "BLOCK_DOCUMENTS" );
			$tmplt->setVariable( "DOC_LINK", $doc['LINK'] );
			$tmplt->setVariable( "DOC_NAME", $doc['NAME'] );
			$tmplt->parse( "BLOCK_DOCUMENTS" );
		}
		
		/*
		 * assemble frameResponse
		 */
		$displayCss = Index::readFile( PATH_URL . "exercise/css/display_obj.css" );
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setCss($displayCss);
		$rawHtml->setHtml($tmplt->get());
		
		$frameResponseObject->setTitle("Exercise");
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>