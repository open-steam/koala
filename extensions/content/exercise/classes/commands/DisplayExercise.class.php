<?php
namespace Exercise\Commands;
class DisplayExercise extends \AbstractCommand implements \IFrameCommand {

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
			
			$container_name = $this->params[0];
			
			if ( Index::existsContainer($ex_path .$container_name) ) {
				
				$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path . $container_name);
				$container_id = $container->get_id();
				$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path.$container_id.'/');
			}
			else {
				echo "error: container does not exist";  //FIX THIS?!!
				die;
			}
		}
		else {
			echo "error: no Container selected!";
			die;  //FIX THIS?!
		}
		
		
		
		/*
		 * Get data
		 */
		$start = $container->get_attribute("EX_START");
		$end = $container->get_attribute("EX_DEADLINE");
		$ex_strt = date("d.m.Y H:i", $start);
		$ex_dead = date("d.m.Y H:i", $end);
		
		$ex_desc = $container->get_attribute("EX_DESCRIPTION");
		$ex_pnts = $container->get_attribute("EX_POINTS");
		$ex_ming = $container->get_attribute("EX_MINGROUP");
		$ex_maxg = $container->get_attribute("EX_MAXGROUP");
		# get mandatory files for solutions
		$ex_solfiles = array();
		$j = (integer)($container->get_attribute("EX_SOLFILE_AMOUNT"));
		while ( $j > 0 ) {
			
			$file = array();
			$file['NAME'] = $container->get_attribute("EX_SOLFILE_" . ($j) . "_NAME");
			$file['TYPE'] = $container->get_attribute("EX_SOLFILE_" . ($j) . "_TYPE");
			$ex_solfiles[] = $file;
			
			$j--;
		}
		$ex_solfiles = array_reverse($ex_solfiles);
		# get documents in exercise container
		$ex_documents = array();
		$fltr = array(array( '+', 'class', CLASS_DOCUMENT ));
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$document = $container->get_inventory_filtered( $fltr , $sort, 0, 0 );
		foreach ( $document as $file ) {
			
			if (!($file instanceof \steam_document))
				continue;
			
			$farr = array();
			$farr['NAME'] = $file->get_name();
			$farr['LINK'] = PATH_URL . "Download/Document/" . $file->get_id();
			$ex_documents[] = $farr;
		}
		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/"), array("name" => "Liste der Aufgaben", "link" => PATH_URL . "exercise/ListExercises/"), array("name" =>  $container_name )));
		
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array( "name" => "&Uuml;bung bearbeiten", "link" => PATH_URL . "exercise/CreateExercise/" . $container_name . "/"), ));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("DisplayExercise.template.html");
		
		if ( isset($_SESSION['SUCCESS']) && isset($_SESSION['SUCMSG']) && $_SESSION['SUCCESS'] === TRUE ) {
			
			$sucmsg = '<div id=notice><p id="ex_success" style="display:none;" >' . $_SESSION['SUCMSG'] . '</p></div>';
			$sucjs  = "$('#ex_success').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $sucmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $sucjs );
			unset($_SESSION['SUCCESS']);
			unset($_SESSION['SUCMSG']);
		}
		
		$tmplt->setVariable( "ATTR_START", $ex_strt );
		$tmplt->setVariable( "ATTR_DEADLINE", $ex_dead );
		if ($ex_maxg > 1) 
			 $tmplt->setVariable( "ATTR_GROUP", "ja&nbsp;(" . $ex_ming . "-" . $ex_maxg . "&nbsp;Personen)" );
		else $tmplt->setVariable( "ATTR_GROUP", "nein&nbsp;(Einzelabgabe)" );
		$tmplt->setVariable( "ATTR_POINTS", $ex_pnts );
		
		
		$time = time();
		if ($start > $time) {
			#is scheduled
			$t = self::timeLeft($start);
			$tmplt->setVariable("IBOX_VIEW", 'scheduled');
			$tmplt->setVariable("IBOX_MAIN", 'Abgabezeitraum hat noch nicht begonnen.');
			$tmplt->setVariable("IBOX_INFO", 'Start in: '.$t['days'].' Tag'.$t['d'].', '.$t['hours'].' Stunde'.$t['h'].' und '.$t['min'].' Minute'.$t['m']);   
		}
		else {
			
			$fltr = array(  array( '-', 'class', CLASS_USER	     ),
							array( '+', 'class', CLASS_CONTAINER )
						 );
			$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
			$has_submission = false;
			$solution = null;
			$solution = $sl_container->get_inventory_filtered( $fltr, $sort, 0, 0 );
			foreach ($solution as $doc) {

				$n = (integer)($doc->get_attribute("SL_PARTICIPANTS_AMOUNT"));
				for ( $j = 1 ; $j <= $n ; $j++ ) {
					
					$me = \lms_steam::get_current_user();
					$author = $doc->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
					if ( $author ==  $me->get_name() ) {
						$solution = $doc;
						$has_submission = true;
					}
				}
			}
			
			if ($end > $time) {
				if ($has_submission) {
					#is submitted
					$t = self::timeLeft($end);
					$link = '<a href="' . PATH_URL . 'exercise/CreateSolution/' . $container_id . '/' .$solution->get_name() . '/">L&ouml;sung bearbeiten</a>';
					$tmplt->setVariable("IBOX_VIEW", 'submitted');
					$tmplt->setVariable("IBOX_MAIN", 'L&ouml;sung wurde abgegeben.');
					$tmplt->setVariable("IBOX_INFO", $link.' (noch '.$t['days'].' Tag'.$t['de'].', '.$t['hours'].' Stunde'.$t['h'].' und '.$t['min'].' Minute'.$t['m'].')'); 
				}
				else {
					#is open
					$t = self::timeLeft($end);
					$link =  '<div class="buttons" style="margin-top: 5px;"> ' .
							 '<button type="submit" onClick="window.location.href = \'' . PATH_URL . 'exercise/CreateSolution/' . $container_id .'/\';">' .
							 '&Uuml;bungsblatt einreichen' .
					         '</button></div>';
					$tmplt->setVariable("IBOX_VIEW", 'open');
					$tmplt->setVariable("IBOX_MAIN", 'Abgabe m&ouml;glich!');
					$tmplt->setVariable("IBOX_INFO", $link.' &nbsp;(noch '.$t['days'].' Tag'.$t['de'].', '.$t['hours'].' Stunde'.$t['h'].' und '.$t['min'].' Minute'.$t['m'].')');
				}
			}
			else {
				if ($has_submission) {
					#is expired, with solution
					$link = '<a href="' . PATH_URL . 'exercise/DisplaySolution/' . $container_id . '/' .$solution->get_name() . '/">L&ouml;sung anzeigen</a>';
					$tmplt->setVariable("IBOX_VIEW", 'expired');
					$tmplt->setVariable("IBOX_MAIN", 'Abgabezeitraum abgelaufen.');
					$tmplt->setVariable("IBOX_INFO",  'L&ouml;sung wurde abgegeben ('.$link.')'); 
				}
				else {
					#is expired, no solution
					$tmplt->setVariable("IBOX_VIEW", 'expired');
					$tmplt->setVariable("IBOX_MAIN", 'Abgabezeitraum abgelaufen.');
					$tmplt->setVariable("IBOX_INFO",  'keine L&ouml;sung f&uuml;r diese &Uuml;bung vorhanden.'); 
				}
			}
		}
		
		
		foreach ($ex_solfiles as $file) {
			
			$tmplt->setCurrentBlock( "BLOCK_SOLUTIONFILES" );
			$tmplt->setVariable( "ATTR_SOLFILE_NAME", $file['NAME'] );
			$tmplt->setVariable( "ATTR_SOLFILE_TYPE", $file['TYPE'] );
			$tmplt->parse( "BLOCK_SOLUTIONFILES" );
		}
		
		$tmplt->setVariable( "ATTR_DESCRIPTION", nl2br($ex_desc) );
		
		foreach ($ex_documents as $doc) {
			
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
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
	
	/**
	 * function timeLeft()
	 * 
	 * @static
	 * @param String $deadline
	 */
	public static function timeLeft ($deadline) {
			
			$time = time();
			$left  = $deadline - $time;
			$days  = floor($left/(60*60*24));
			$d     = ($days > 1) ? 'en' : '';
			$de    = ($days > 1) ? 'e' : '';
			$left  = $left - ($days*(60*60*24));
			$hours = floor($left/(60*60));
			$h     = ($hours > 1) ? 'n' : '';
			$left  = $left - ($hours*(60*60));
			$min   = floor($left/(60));
			$m     = ($min > 1) ? 'n' : '';
			
			return array('days' => $days, 'd' => $d, 'de' => $de, 'hours' => $hours, 'h' => $h, 'min' => $min, 'm' => $m);
	}
}
?>