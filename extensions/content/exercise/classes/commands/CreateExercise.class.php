<?php
namespace Exercise\Commands;
class CreateExercise extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	
	/**
	 * function: saveData
	 * 
	 * Processes and saves the data received from a form to the sTeam Backend.
	 * 
	 * @static
	 * 
	 * @param steam_object container Container to which the Attributes/Files should be saved
	 * @param string errmsg reference to the error msg variable
	 * @return Boolean true, if successful
	 */
	public static function saveData ( &$container, &$errmsg ) {
		
		#retrieve data
		$attrib = $_POST["attributes"];
		$sol_filenames = $_POST["sf_names"];
		$sol_types = $_POST["sf_types"];
		$sol_ids = $_POST["sf_ids"];
		
		#validate data
		/*
		 * TODO: Validation is not complete
		 */
		if ( empty($attrib["NAME"]) ) 		
			{ $errmsg = "Ein Name muss angegeben werden.";	return false; }
		if ( empty($attrib["STARTDT"]) || empty($attrib["STARTTM"]) )	
			{ $errmsg = "Der Startzeitpunkt muss festgelegt werden."; return false; }
		if ( empty($attrib["DEADLINEDT"]) || empty($attrib["DEADLINETM"]) )	
			{ $errmsg = "Der Abgabetermin muss festgelegt werden."; return false; }
		if ( empty($attrib["POINTS"]) )		
			{ $errmsg = "Die erreichbaren Punkte m&uuml;ssen angegeben werden."; return false; }
		settype($attrib["MINGROUP"], "integer");
		settype($attrib["MAXGROUP"], "integer");
		if ( (!empty($attrib["MINGROUP"]) 
		&& !empty($attrib["MAXGROUP"])) 
		&& ($attrib["MINGROUP"] > $attrib["MAXGROUP"]) ) 
			{ $errmsg = "Die max. Gruppenst&auml;rke muss gr&ouml;&szlig;er als die minimale sein."; return false; }
			
		$sdt = explode('.', $attrib['STARTDT'], 3);
		if(!(checkdate((integer)$sdt[1], (integer)$sdt[0], (integer)$sdt[2]))) 
			{ $errmsg = "Das angegebene Startdatum ist ung&uuml;ltig."; return false; }
		$stm = explode(':', $attrib['STARTTM'], 2);
		if(!(((integer)$stm[0] >= 0) && ((integer)$stm[0] < 24) && ((integer)$stm[1] >= 0) && ((integer)$stm[1] < 60)))
			{ $errmsg = "Die angegebene Startzeit ist ung&uuml;ltig."; return false; }
		$ddt = explode('.', $attrib['DEADLINEDT'], 3);
		if(!(checkdate((integer)$ddt[1], (integer)$ddt[0], (integer)$ddt[2]))) 
			{ $errmsg = "Das angegebene Abgabedatum ist ung&uuml;ltig."; return false; }
		$dtm = explode(':', $attrib['DEADLINETM'], 2);
		if(!(((integer)$dtm[0] >= 0) && ((integer)$dtm[0] < 24) && ((integer)$dtm[1] >= 0) && ((integer)$dtm[1] < 60)))
			{ $errmsg = "Die angegebene Abgabezeit ist ung&uuml;ltig."; return false; }
			
		$start = mktime((integer)$stm[0], (integer)$stm[1], 0, (integer)$sdt[1], (integer)$sdt[0], (integer)$sdt[2]);
		$end = mktime((integer)$dtm[0], (integer)$dtm[1], 0, (integer)$ddt[1], (integer)$ddt[0], (integer)$ddt[2]);
		if($start>=$end)
			{ $errmsg = "Der Abgabezeitpunkt muss nach dem Startzeitpunkt liegen."; return false; }
			
		#process data
		if ( empty($attrib["MINGROUP"]) ) {
			if ( empty($attrib["MAXGROUP"]) ) {
				$attrib["MINGROUP"] = 1;
				$attrib["MAXGROUP"] = 1;
			}
			else {
				$attrib["MINGROUP"] = $attrib["MAXGROUP"];
			}
		}
		else {
			if ( empty($attrib["MAXGROUP"]) ) {
				$attrib["MAXGROUP"] = $attrib["MINGROUP"];
			}
		}
		for ( $i=0 ; $i < count($sol_types) ; $i++ ) {
			if (empty($sol_types[$i]))	
				$sol_types[$i] = '*';
			if (preg_match('/\*/', $sol_types[$i]))
				$sol_types[$i] = '*';
		}
		
		#save data
		$sol_file_amt = 0;
		foreach ( $sol_types as $filetype ) {
			
			$sol_file_amt++;
			$container->set_attribute( "EX_SOLFILE_" . $sol_file_amt . "_TYPE", $filetype );
		}
		for ( $i = 0 ; $i < $sol_file_amt ; $i++ ) {
			
			if ( empty($sol_filenames[$i]) ) {
				
				$container->set_attribute( "EX_SOLFILE_" . ($i + 1) . "_NAME", "Abgabedatei " . ($i + 1) );
			}
			else {
				
				$container->set_attribute( "EX_SOLFILE_" . ($i + 1) . "_NAME", $sol_filenames[$i] );
			}
			
			if ( empty($sol_ids[$i]) ) {
				
				$proceed = false;
				do {
					$new_id = (string)(mt_rand(10000, 99999));
					$proceed = true;
					for ( $y = 0 ; $y < $sol_file_amt ; $y++ ) {
						if ( $new_id == $sol_ids[$y] ) {
							$proceed == false;
						}
					}
				} while (!$proceed);
				
				$container->set_attribute( "EX_SOLFILE_" . ($i + 1) . "_ID", $new_id );
			}
			else {
				
				$container->set_attribute( "EX_SOLFILE_" . ($i + 1) . "_ID", $sol_ids[$i] );
			}
		}
		$container->set_attribute( "EX_SOLFILE_AMOUNT", $sol_file_amt );
		
		$container->set_attribute( "EX_DESCRIPTION", $attrib["DESCRIPTION"] );
		$container->set_attribute( "EX_START", $start );
		$container->set_attribute( "EX_DEADLINE", $end );
		$container->set_attribute( "EX_POINTS", $attrib["POINTS"] );
		$container->set_attribute( "EX_MINGROUP", $attrib["MINGROUP"] );
		$container->set_attribute( "EX_MAXGROUP", $attrib["MAXGROUP"] );
		$container->set_attribute( "OBJ_NAME", $attrib["NAME"] );
		
		#delete flagged files & flag files as not new
		Delete::deleteCommand((string)$container->get_id(), 'flagged');
		$pick  = array(array( '-', 'attribute', 'IS_NEW', '!=', 'TRUE' ),
					   array( '+', 'class', CLASS_DOCUMENT ));
		$order = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$newFiles = $container->get_inventory_filtered( $pick, $order, 0, 0 );
		foreach ( $newFiles as $storeMe ) {
			
			$storeMe->set_attribute("IS_NEW", "FALSE");
		}
		
		return true;
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
		 * decide if create or edit mode
		 */
		if (isset($this->params[1]) && isset($this->params[2]) && $this->params[1] === "create") {
			
			#set operation mode and operation context
			if ( (isset($_SESSION['EX_CREATE'])) && ($_SESSION['EX_CREATE'] === TRUE) ) 
					define('OPERATION', 'CREATE'); 
			else	define('OPERATION', 'EDIT');
			$operation_context = "NORMAL"; #normal operation, can be "ABORT" in specific cases
			
			$container_name = $this->params[2];
			
			if ( Index::existsContainer($ex_path.$container_name) ) {
				
				$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path . $container_name);
				$container_id = $container->get_id();
			}
			else {
				
				$fail = TRUE;
				if ((isset($_SESSION['EX_CREATE']))&&($_SESSION['EX_CREATE']===TRUE)) {
					#wait a maximum of 5 seconds for the sTeam server to create the container
					for ($i=0;$i<100;$i++) {
						usleep(50000);
						if (Index::existsContainer($ex_path.$container_name)) {
							$fail = FALSE;
							$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path . $container_name);
							$container_id = $container->get_id();
							break;
						}
					}
				} 
				if ($fail) {
					echo "error: container does not exist";
					die;
				}
			}
		}
		else {
			#initialize
			#create container for new exercise
			define('OPERATION', 'CREATE');  
			$_SESSION['EX_CREATE'] = TRUE;
			
			$fltr = array(array( '+', 'class', CLASS_CONTAINER ));
			$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
			$parent = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path);
			$children = $parent->get_inventory_filtered( $fltr , $sort, 0, 0 );
			$max = 0;
			foreach ( $children as $child ) {
				
				$n = $child->get_name();
				if ( preg_match('/^Exercise_[0-9]+\Z/', $n) ) {
					
					$dynamite = explode( '_', $n );
					if (((integer)$dynamite[1]) > $max)
						$max = (integer)$dynamite[1];
				}
			}
			
			#create exercise containers for all types of objects (exercise/solution/review)
			$container_name = "Exercise_" . (string)($max + 1);
			$container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $container_name, $parent);
			$container_id = $container->get_id();
			
			$sl_parent = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
			$rv_parent = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path);
			$rv_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), (string)($container_id), $rv_parent);
			$sl_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), (string)($container_id), $sl_parent);
			
			#Rights Management
                        $url = $exerciseObject->get_environment()->get_path();
                        $urlArray = explode("/", $url);
                        $learnersGroupName = $urlArray[2];
                        $courseGroupName = str_replace(".learners", "", $learnersGroupName);
                        
			$learners = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $courseGroupName . ".learners");
			$staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $courseGroupName . ".staff");
		
			$sl_container->set_insert_access( $learners, TRUE );
			$sl_container->set_insert_access( $staff, FALSE );
			
			#instant reload after creating container
			session_write_close();
			header("Location: " . PATH_URL . "exercise/CreateExercise/" . $this->id . "/create/". $container_name);
			exit;
		}
		
		/*
		 * BRANCH on form input
		 * (if form was submitted we handle the data, else the page will load normally)
		 */
		if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
			
			#check for an aborted leave attempt
			if (isset($_POST["CMD_ABORT"]) && ($_POST["CMD_ABORT"]=="TRUE")) {
				
				$operation_context = "ABORT";
			}
			else {
			
				#save data and react to result
				$errmsg = "";
				$success = self::saveData($container, $errmsg);
				if (!$success) {
					
					$_SESSION['ERROR'] 	= TRUE;
					$_SESSION['ERRMSG'] = $errmsg;
					session_write_close();
					header("Location: " . PATH_URL . "exercise/CreateExercise/" . $container_name);
					exit;
				}
				
				$container_name = $container->get_name();  // refetch name in case it was changed by form input
				$_SESSION['SUCCESS'] = TRUE;
				$_SESSION['SUCMSG']  = 'Die &Uuml;bung wurde gespeichert.';
				unset($_SESSION['EX_CREATE']);
				session_write_close();
				header("Location: " . PATH_URL . "exercise/DisplayExercise/" . $container_name);
				exit;
			}
		}
		
		/*
		 * Get data of existing container
		 */
		if (OPERATION=='EDIT' && $operation_context=='NORMAL') {
			
			$start = $container->get_attribute("EX_START");
			$end = $container->get_attribute("EX_DEADLINE");
			$ex_strtdt = date("d.m.Y", $start);
			$ex_strttm = date("H:i", $start);
			$ex_deaddt = date("d.m.Y", $end);
			$ex_deadtm = date("H:i", $end);
			
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
				$file['ID']   = $container->get_attribute("EX_SOLFILE_" . ($j) . "_ID");
				$ex_solfiles[] = $file;
				
				$j--;
			}
			$ex_solfiles = array_reverse($ex_solfiles);
		}
		
		/*
		 * Get data from form POST after a leave attempt
		 */
		if ($operation_context=='ABORT') {
			
			$ex_desc = $_POST["attributes"]["DESCRIPTION"];
			$ex_strtdt = $_POST["attributes"]["STARTDT"];
			$ex_strttm = $_POST["attributes"]["STARTTM"];
			$ex_deaddt = $_POST["attributes"]["DEADLINEDT"];
			$ex_deadtm = $_POST["attributes"]["DEADLINETM"];
			$ex_pnts = $_POST["attributes"]["POINTS"];
			$ex_ming = $_POST["attributes"]["MINGROUP"];
			$ex_maxg = $_POST["attributes"]["MAXGROUP"];
			# get mandatory files for solutions
			$sf_types = $_POST["sf_types"];
			$sf_names = $_POST["sf_names"];
			$sf_ids	  = $_POST["sf_ids"];
			$ex_solfiles = array();
			$j = 0;
			foreach ( $sf_names as $nm ) {
				
				$file = array();
				$file['NAME'] = $nm;
				$file['TYPE'] = $sf_types[$j];
				$file['ID']   = $sf_ids[$j];
				$ex_solfiles[] = $file;
				
				$j++;
			}
		}
		
		/*
		 * Template
		 */
		if (OPERATION=='CREATE') {
			$operation_mode_string = "erstellen";
			$operation_button_label = "&Uuml;bung erstellen";
		}
		else {
			$operation_mode_string = "bearbeiten";
			$operation_button_label = "&Uuml;bung speichern";
		}
		$changed_flag = ($operation_context=='ABORT') ? 'true' : 'false';
		if (($changed_flag=='false') && (OPERATION=='CREATE'))
			 $changed_flag = 'true';
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/index/" . $this->id), array("name" => "&Uuml;bung ".$operation_mode_string)));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("CreateExercise.template.html");
		
		if ( isset($_SESSION['ERROR']) && isset($_SESSION['ERRMSG']) && $_SESSION['ERROR'] === TRUE ) {
			
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >' . $_SESSION['ERRMSG'] . '</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
			$changed_flag = 'true';
			unset($_SESSION['ERROR']);
			unset($_SESSION['ERRMSG']);
		}
		
		$tmplt->setVariable( "ICON_BASE_PATH", PATH_URL );
		$tmplt->setVariable( "FORM_ACTION", PATH_URL . "exercise/CreateExercise/" . $container_name . "/" );
		#make operation mode available in js
		$tmplt->setVariable( "OPERATION" , OPERATION );
		#preserve change flag in case of aborted leave
		$tmplt->setVariable( "CHANGED_FLAG" , $changed_flag );
		
		$tmplt->setVariable( "ATTR_NAME" , "Bezeichnung" );
		$tmplt->setVariable( "ATTR_MINGROUP" , "min. Gruppenst&auml;rke" );
		$tmplt->setVariable( "ATTR_MAXGROUP" , "max. Gruppenst&auml;rke" );
		$tmplt->setVariable( "ATTR_START" , "Startzeitpunkt" );
		$tmplt->setVariable( "ATTR_DEADLINE" , "Abgabezeitpunkt" );
		$tmplt->setVariable( "ATTR_POINTS" , "erreichbare Punkte" );
		$tmplt->setVariable( "ATTR_DESCRIPTION" , "Beschreibung" );
		$tmplt->setVariable( "ATTR_SOLUTIONFILES" , "abzugebende Dateien" );
		#set values
		$tmplt->setVariable( "ATTR_NAME_VALUE", $container_name );
		if (OPERATION=='EDIT' || $operation_context=='ABORT') {
			
			$tmplt->setVariable( "ATTR_MINGROUP_VALUE", $ex_ming );
			$tmplt->setVariable( "ATTR_MAXGROUP_VALUE", $ex_maxg );
			$tmplt->setVariable( "ATTR_START_DT_VALUE", $ex_strtdt );
			$tmplt->setVariable( "ATTR_START_TM_VALUE", $ex_strttm );
			$tmplt->setVariable( "ATTR_DEADLINE_DT_VALUE", $ex_deaddt );
			$tmplt->setVariable( "ATTR_DEADLINE_TM_VALUE", $ex_deadtm );
			$tmplt->setVariable( "ATTR_POINTS_VALUE", $ex_pnts );
			$tmplt->setVariable( "ATTR_DESCRIPTION_VALUE", $ex_desc );
			$amt = 0;
			foreach ($ex_solfiles as $entry) {
				
				$tmplt->setCurrentBlock( "BLOCK_SOLUTIONFILES" );
				$tmplt->setVariable( "SF_ENTRYNR", $amt+1 );
				$tmplt->setVariable( "SF_NAME", $entry['NAME'] );
				$tmplt->setVariable( "SF_TYPE", $entry['TYPE'] );
				$tmplt->setVariable( "SF_ID", 	$entry['ID']   );
				$tmplt->parse( "BLOCK_SOLUTIONFILES" );
				$amt++;
			}
			$tmplt->setVariable( "SF_ENTRYAMT", $amt );
		}
		else {
			
			$tmplt->setVariable( "SF_ENTRYNR", "1" );
			$tmplt->setVariable( "SF_NAME", "Abgabedatei 1" );
			$tmplt->setVariable( "SF_TYPE", "pdf" );
			$tmplt->setVariable( "SF_ENTRYAMT", "1" );
			$tmplt->setVariable( "SF_ID", "99999");
		}
		$tmplt->setVariable( "SUBMIT_BUTTON_LABEL", $operation_button_label );
		
		
		
		
		
		
		/*
		 * Get existing documents in the exercise container for the documentUploader
		 */
		$fltr = array(array( '-', 'attribute', 'DELETEFLAG', '!=', 'FALSE' ),
					  array( '+', 'class', CLASS_DOCUMENT ));
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));  // '<' alphabetisch aufsteigend
		
		$document = $container->get_inventory_filtered( $fltr , $sort, 0, 0 );  //@see steam_container for more filter options
		$nofile = true;
		$preload = "";
		$preload_array = array();

		foreach ( $document as $file ) {
			
			$nofile = false;
			$preload_array[] = "new Array('" . $file->get_name() . "', '" . $file->get_id() . "')";
		}
		
		if ($nofile) {
			$preload = "new Array(false)";
		}
		else {
			
			$preload  = "new Array(";
			$preload .= implode(", " , $preload_array); 
			$preload .= ")";
		}
		
		$tmplt->setVariable( "PRELOAD", $preload );
		
		
		
		
		
		/*
		 * Set parameters for exerciseForm and fileUploaders
		 */
		$tmplt->setVariable("BACKEND", PATH_URL . "exercise/" );
		$tmplt->setVariable("BASEROOM", $container_id );
		$tmplt->setVariable("FIXED_UPLOADERS", 'new Array(false)');
		$tmplt->setVariable("SIZELIMIT", '5242880');
		
		
		/*
		 * assemble frameResponse
		 */
		$exerciseFormCss = Index::readFile( PATH_URL . "exercise/css/exerciseForm.css" );
		$exerciseFormJs  = Index::readFile( PATH_URL . "exercise/js/exerciseForm.js" );
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setCss($exerciseFormCss);
		$rawHtml->setJs($exerciseFormJs);
		$rawHtml->setHtml($tmplt->get());
		
		$frameResponseObject->setTitle("Exercise");
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>