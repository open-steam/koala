<?php
namespace Exercise\Commands;
class CreateSolution extends \AbstractCommand implements \IFrameCommand {

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
	public static function saveData ( &$container, &$errmsg, $prm ) {
		
		#retrieve data
		$attrib = $_POST["attributes"];
		$author_names = $_POST["pp_names"];
		$author_full = array();
		
		#validate data
		/*
		 * TODO: Validation is not complete
		 */
		for ( $i=0 ; $i < count($author_names) ; $i++ ) {
			if (empty($author_names[$i])) 	
			  { $errmsg = "Alle Teilnehmer müssen durch ihren Namen identifiziert werden."; return false; }
		}
		
		for ( $i=0 ; $i < count($author_names) ; $i++ ) {
			
			$user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $author_names[$i]);
			if ($user === 0) {
				
				$errmsg = "Der Teilnehmer \"" . $author_names[$i] . "\" konnte nicht gefunden werden."; 
				return false;
			} 
			$author_full[$i] = $user->get_full_name();	
		}
		
		#Rights Management
		$learners = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".learners");
		$staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".staff");
		$user = \lms_steam::get_current_user();
		
		$koala_staff = new \koala_group_default( $staff );
		if ($koala_staff->is_member($user)) {
			
			echo "Als Tutor darfst du keine L&ouml;sungen bearbeiten!";
			die;
		}
		
		$container->set_acquire(0);
		$container->set_read_access( $staff, TRUE );
		$container->set_write_access( $staff, TRUE );
		$container->set_write_access( $learners, FALSE );
		$container->set_read_access( $learners, FALSE );
		$container->set_insert_access( $learners, FALSE );
		
		$container->set_write_access( $user, TRUE );
		$container->set_read_access( $user, TRUE );
		$container->set_insert_access( $user, TRUE );
		
		#save data
		$pp_amt = 0;
		foreach ( $author_names as $author ) {
			
			$pp_amt++;
			$container->set_attribute( "SL_PARTICIPANTS_" . $pp_amt . "_ID", $author );
			$container->set_attribute( "SL_PARTICIPANTS_" . $pp_amt . "_FN", $author_full[$pp_amt-1] );
			
			#Rights for each author
			$person = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $author);
			$container->set_write_access( $person, TRUE );
			$container->set_read_access( $person, TRUE );
			$container->set_insert_access( $person, TRUE );
		}
		$container->set_attribute( "SL_PARTICIPANTS_AMOUNT", $pp_amt );
		$container->set_attribute( "SL_DESCRIPTION", $attrib["DESCRIPTION"] );
		
		
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
		
		/*
		 * for testing purpose preselect course EXT-01: 
		 */
		$prm = array("WS1011", "Ext-01");
		$basepath = "/home/Courses." . $prm[0] . "." . $prm[1] . ".learners/";
		$ex_path = $basepath . "exercises/";
		$sl_path = $basepath . "solutions/";
		$rv_path = $basepath . "reviews/";
		
		/*
		 * decide if create or edit mode
		 */
		if ( isset ( $this->params[0] ) ) {
			if ( isset ( $this->params[1] ) ) {
				
				#set operation mode and operation context
				if ( (isset($_SESSION['SL_CREATE'])) && ($_SESSION['SL_CREATE'] === TRUE) ) 
						define('OPERATION', 'CREATE');
				else	define('OPERATION', 'EDIT');
				$operation_context = "NORMAL"; #normal operation, can be "ABORT" in specific cases
				
				$ex_container_name = $this->params[0];
				$sl_container_name = $this->params[1];
				
				if ( Index::existsContainer($sl_path .$ex_container_name) ) {
					
					$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name); //id of container is name of the container in sl path.
					$ex_container_id = $ex_container->get_id();
					
					if ( Index::existsContainer($sl_path .$ex_container_name. "/" .$sl_container_name) ) {
						
						$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path .$ex_container_name. "/" .$sl_container_name);
						$sl_container_id = $sl_container->get_id();
					}
					else {
						echo "error: solution does not exist";  //FIX THIS?!!
						die;
					}
				}
				else {
					echo "error: exercise does not exist";  //FIX THIS?!!
					die;
				}
			}
			else {
				
				$ex_container_name = $this->params[0];
				if ( Index::existsContainer($sl_path .$ex_container_name) ) {
				
					$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name);
					$ex_container_id = $ex_container->get_id();
					
					#initialize
					#create container for new solution
					define('OPERATION', 'CREATE');  
					$_SESSION['SL_CREATE'] = TRUE; 
					
					$proceed = false;
					while (!$proceed) {
						$sl_container_name = (string)(mt_rand(10000, 99999));
						if (!Index::existsContainer($sl_path.$ex_container_name.$sl_container_name))
							$proceed = true;
					}
					
					$parent = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path.$ex_container_name);
					$sl_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $sl_container_name, $parent);
					$sl_container_id = $sl_container->get_id();
					
					#Rights Management
					$learners = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".learners");
					$staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".staff");
					$user = \lms_steam::get_current_user();
					
					$koala_staff = new \koala_group_default( $staff );
					if ($koala_staff->is_member($user)) {
						
						$sl_container->delete();
						echo "Als Tutor darfst du keine L&ouml;sungen erstellen!";
						die;
					}
					
					$sl_container->set_acquire(0);
					$sl_container->set_read_access( $staff, TRUE );
					$sl_container->set_write_access( $staff, TRUE );
					$sl_container->set_insert_access( $staff, FALSE );
					$sl_container->set_write_access( $learners, FALSE );
					$sl_container->set_read_access( $learners, FALSE );
					$sl_container->set_insert_access( $learners, FALSE );
					
					$sl_container->set_write_access( $user, TRUE );
					$sl_container->set_read_access( $user, TRUE );
					$sl_container->set_insert_access( $user, TRUE );
					
					#select reviewer for solution
					$course_path = "/home/Courses." . $prm[0] . "." . $prm[1] . "/";
					$course_room = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $course_path);
					$sl_base_container = $sl_container->get_environment()->get_environment();
					$workload_mode = $course_room->get_attribute("EX_WORKLOAD_DISTRIBUTION");
					
					if ($workload_mode == 'shared') {
						
						# get reviewers in this course
						$reviewers = array();
						$j = (integer)($course_room->get_attribute("EX_REVIEWER_COUNT"));
						$reviewer_count = $j;
						while ( $j > 0 ) {
							
							$rvid = $course_room->get_attribute("EX_REVIEWER_" . ($j) . "_ID");
							$relieved = $course_room->get_attribute("EX_REVIEWER_" . ($j) . "_RELIEVED");
							$relieved_list = explode('+', $relieved);
							if (!in_array($ex_container_name, $relieved_list)) {
								
								$reviewers[$rvid] = 0;
							}
							$j--;
						}
						
						# get workloads
						$fltr = array(  array( '-', 'class', CLASS_USER	     ),
										array( '+', 'class', CLASS_CONTAINER )
									 );
						$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
						$exercise = $sl_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 );
						
						foreach ($exercise as $folder) {
							
							$solution = $folder->get_inventory_filtered( $fltr, $sort, 0, 0 );
							foreach ($solution as $doc) {
				
								$sl_reviewer = $doc->get_attribute("SL_REVIEWER");
								if (!($sl_reviewer === 0)) {
									if (isset($reviewers[$sl_reviewer])) {
										$reviewers[$sl_reviewer]++;
									}
									else {
										$user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $sl_reviewer);
										$course_room->set_attribute("EX_REVIEWER_COUNT", ++$reviewer_count);
										$course_room->set_attribute("EX_REVIEWER_" . ($reviewer_count) . "_ID", $sl_reviewer); 
										$course_room->set_attribute("EX_REVIEWER_" . ($reviewer_count) . "_FULLNAME", $user->get_full_name()); 
										$reviewers[$sl_reviewer] = 1;
									}
								}
							}
						}
						
						# get min workload
						$minWL = 999999;
						$newRV = null;
						foreach ( $reviewers as $revid => $workload ) {
							
							if ( $workload < $minWL  ) {
								$minWL = $workload;
								$newRV = $revid;
							}
						}
						$sl_container->set_attribute("SL_REVIEWER", $newRV);
						$sl_container->set_attribute("SL_REVIEW_ID", "NONE");
					}
					elseif ($workload_mode == 'groups') {
						echo "error: EX_WORKLOAD_DISTRIBUTION hat den Wert 'Gruppenintern'. Diese Verwaltung ist noch nicht implementiert.";
						die;
					}
					else {
						echo "error: Es konnte kein Tutor zugewiesen werden (EX_WORKLOAD_DISTRIBUTION).";
						die; 
					}
					
					#instant reload after creating container
					session_write_close();
					header("Location: " . PATH_URL . "exercise/CreateSolution/" . $ex_container_name . "/" . $sl_container_name);
					exit;
				}
				else {
					
					echo "error: exercise does not exist";  //FIX THIS?!!
					die;
				}
			}
		}
		else {
			
			echo "error: no Exercise selected!";
			die;  //FIX THIS?!
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
				$success = self::saveData($sl_container, $errmsg, $prm);
				if (!$success) {
					
					$_SESSION['ERROR'] 	= TRUE;
					$_SESSION['ERRMSG'] = $errmsg;
					session_write_close();
					header("Location: " . PATH_URL . "exercise/CreateSolution/" . $ex_container_name . "/" . $sl_container_name);
					exit;
				}
				
				$sl_container_name = $sl_container->get_name();
				if (OPERATION=='CREATE') {
					$sl_container->set_attribute("OBJ_CREATION_TIME", time());
				}
				$sl_container->set_attribute("SL_LEARNER_LAST_MODIFIED", time());
				
				$_SESSION['SUCCESS'] = TRUE;
				$_SESSION['SUCMSG']  = 'Die L&ouml;sung wurde gespeichert.';
				unset($_SESSION['SL_CREATE']);
				session_write_close();
				header("Location: " . PATH_URL . "exercise/DisplaySolution/" . $ex_container_name . "/" . $sl_container_name);
				exit;
			}
		}
		
		/*
		 * Get data of existing container
		 */
		if (OPERATION=='EDIT' && $operation_context=='NORMAL') {

			$sl_desc = $sl_container->get_attribute("SL_DESCRIPTION");
			# get participants
			$sl_participants = array();
			$j = (integer)($sl_container->get_attribute("SL_PARTICIPANTS_AMOUNT"));
			while ( $j > 0 ) {
				
				$author = array();
				$author['ID'] = $sl_container->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
				$author['FULLNAME'] = $sl_container->get_attribute("SL_PARTICIPANTS_" . ($j) . "_FN");
				$sl_participants[] = $author;
				
				$j--;
			}
			$sl_participants = array_reverse($sl_participants);
		}
		$ex_name = $ex_container->get_attribute("OBJ_NAME");
		$ex_dead = $ex_container->get_attribute("EX_DEADLINE");
		$ex_pnts = $ex_container->get_attribute("EX_POINTS");
		$ex_ming = $ex_container->get_attribute("EX_MINGROUP");
		$ex_maxg = $ex_container->get_attribute("EX_MAXGROUP");
		
		/*
		 * Get data from form POST after a leave attempt
		 */
		if ($operation_context=='ABORT') {

			$sl_desc = $_POST["attributes"]["DESCRIPTION"];
			# get mandatory files for solutions
			$sl_pp = $_POST["pp_names"];
			$sl_pp_names = $_POST["sf_names"];
			$sl_participants = array();
			$j = 0;
			foreach ( $sl_pp as $id ) {
				
				$author = array();
				$author['ID'] = $id;
				$author['FULLNAME'] = $sl_pp_names[$j];
				$sl_participants[] = $author;
				
				$j++;
			}
		}
		
		/*
		 * Template
		 */
		if (OPERATION=='CREATE') {
			$operation_mode_string = "erstellen";
			$operation_button_label = "L&ouml;sung erstellen";
		}
		else {
			$operation_mode_string = "bearbeiten";
			$operation_button_label = "L&ouml;sung speichern";
		}
		$changed_flag = ($operation_context=='ABORT') ? 'true' : 'false';
		if (($changed_flag=='false') && (OPERATION=='CREATE'))
			 $changed_flag = 'true';
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/"), array("name" => $ex_container->get_name(), "link" => PATH_URL . "exercise/DisplayExercise/" . $ex_container->get_name()), array("name" => "L&ouml;sung ".$operation_mode_string)));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("CreateSolution.template.html");
		
		if ( isset($_SESSION['ERROR']) && isset($_SESSION['ERRMSG']) && $_SESSION['ERROR'] === TRUE ) {
			
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >' . $_SESSION['ERRMSG'] . '</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
			$changed_flag = 'true';
			unset($_SESSION['ERROR']);
			unset($_SESSION['ERRMSG']);
		}
		
		$tmplt->setVariable( "FORM_ACTION", PATH_URL . "exercise/CreateSolution/" . $ex_container_name . "/" . $sl_container_name . "/" );
		#make operation mode available in js
		$tmplt->setVariable( "OPERATION" , OPERATION );
		#preserve change flag in case of aborted leave
		$tmplt->setVariable( "CHANGED_FLAG" , $changed_flag );
		
		$tmplt->setVariable( "ATTR_DESCRIPTION" , "Notizen" );
		$tmplt->setVariable( "ATTR_PARTICIPANTS" , "Autoren" );
		#set values
		$tmplt->setVariable( "EX_NAME", $ex_name );
		$tmplt->setVariable( "EX_DEADLINE", date("d.m.Y H:i", $ex_dead) );
		$tmplt->setVariable( "EX_POINTS", $ex_pnts );
		if ($ex_maxg > 1) {
			if ($ex_maxg > $ex_ming) {
				$tmplt->setVariable( "EX_GROUP", $ex_ming . " bis " . $ex_maxg . " Autoren" );
			}
			else {
				$tmplt->setVariable( "EX_GROUP", $ex_maxg . " Autoren" );
			}
		}
		else {
			$tmplt->setVariable( "EX_GROUP", $ex_maxg . " Autor" );
		}
		if (OPERATION=='EDIT' || $operation_context=='ABORT') {
			
			$tmplt->setVariable( "ATTR_DESCRIPTION_VALUE", $sl_desc );
			$amt = 0;
			foreach ($sl_participants as $entry) {
				
				$me = \lms_steam::get_current_user();
				
				$tmplt->setCurrentBlock( "BLOCK_PARTICIPANTS" );
				$tmplt->setVariable( "PP_ENTRYNR", $amt+1 );
				if ( $entry['ID'] == $me->get_name() ) {
					$tmplt->setVariable( "PP_XBUTTON", " " );
					$tmplt->setVariable( "PP_RO", "readonly=\"readonly\"" );
				}
				else {
					$xbutton = "<button type=\"button\" class=\"dynamicAction\" onClick=\"changed();dynamicList.removeEntry('dynamicEntry_" . ($amt+1) . "');\">X</button>";
					$tmplt->setVariable( "PP_XBUTTON", $xbutton );
				}
				$tmplt->setVariable( "PP_NAME", $entry['ID'] );
				$tmplt->setVariable( "PP_FULLNAME", $entry['FULLNAME'] );
				$tmplt->parse( "BLOCK_PARTICIPANTS" );
				$amt++;
			}
			$tmplt->setVariable( "PP_ENTRYAMT", $amt );
		}
		else {
			
			$me = \lms_steam::get_current_user();
			
			$tmplt->setVariable( "PP_ENTRYNR", "1" );
			$tmplt->setVariable( "PP_NAME", $me->get_name() );
			$tmplt->setVariable( "PP_FULLNAME", $me->get_full_name() );
			$tmplt->setVariable( "PP_RO", "readonly=\"readonly\"" );
			$tmplt->setVariable( "PP_ENTRYAMT", "1" );
		}
		$tmplt->setVariable( "SUBMIT_BUTTON_LABEL", $operation_button_label );
		
		
		/*
		 * Set the fileUploaders according to the files specified in the exercise
		 */
		$fixed_files = array();
		$j = (integer)($ex_container->get_attribute("EX_SOLFILE_AMOUNT"));
		while ( $j > 0 ) {
			
			$file = array();
			$file['NAME'] = $ex_container->get_attribute("EX_SOLFILE_" . ($j) . "_NAME");
			$file['TYPE'] = $ex_container->get_attribute("EX_SOLFILE_" . ($j) . "_TYPE");
			$file['ID']   = $ex_container->get_attribute("EX_SOLFILE_" . ($j) . "_ID"  );
			$fixed_files[] = $file;
			
			$j--;
		}
		$fixed_files = array_reverse($fixed_files);
		$uploaders = "";
		$uploader_array = array();
		foreach ( $fixed_files as $file ) {
			
			$uploader_array[] = "new Array('" . $file['NAME'] . "', '" . $file['TYPE'] . "', '" . $file['ID'] . "')";
		}
		$uploaders  = "new Array(";
		$uploaders .= implode(", " , $uploader_array);
		$uploaders .= ")";
		
		$tmplt->setVariable("FIXED_UPLOADERS", $uploaders);
		
		
		/*
		 * Get existing documents in the solution container for the documentUploader
		 */
		$fltr = array(array( '-', 'attribute', 'DELETEFLAG', '!=', 'FALSE' ),
					  array( '+', 'class', CLASS_DOCUMENT ));
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		
		$document = $sl_container->get_inventory_filtered( $fltr , $sort, 0, 0 );
		$nofile = true;
		$preload = "";
		$preload_array = array();

		foreach ( $document as $file ) {
			
			$nofile = false;
			$preload_array[] = "new Array('" . $file->get_name() . "', '" . $file->get_id() . "', '" . $file->get_attribute("SL_SOLFILE_ID") . "')";
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
		$tmplt->setVariable("BASEROOM", $sl_container_id );
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
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>