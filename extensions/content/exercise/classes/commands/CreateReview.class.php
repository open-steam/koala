<?php
namespace Exercise\Commands;
class CreateReview extends \AbstractCommand implements \IFrameCommand {

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
	public static function saveData ( &$container, &$errmsg, $prm) {
		
		#retrieve data
		$attrib = $_POST["attributes"];
		
		#validate data
		/*
		 * TODO: Validation is not complete
		 */
		$max_points = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $container->get_environment()->get_name())->get_attribute("EX_POINTS");
		//$max_points = $c->get_attribute("EX_POINTS");
		$got_points = (integer)$attrib['RESULT'];
		if ($got_points > (integer)$max_points) 
			{ $errmsg = "Die maximale Punktzahl von ".$max_points." darf nicht &uuml;berschritten werden."; return false; }
		
		if ($got_points < 0)
			{ $errmsg = "Die Punktzahl muss zwischen 0 und ".$max_points." liegen."; return false; }
		
		#Rights Management
		$learners = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".learners");
		$staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".staff");
		$user = \lms_steam::get_current_user();
		
		$koala_learners = new \koala_group_default( $learners );
		if ($koala_learners->is_member($user)) {
			
			echo "No Access for Usergroup: learners";
			die;
		}
		
		$container->set_acquire(0);
		$container->set_write_access( $learners, FALSE );
		$container->set_read_access( $learners, FALSE );
		$container->set_insert_access( $learners, FALSE );
		
		$path_2_sol = 	"/home/Courses." . $prm[0] . "." . $prm[1] . ".learners/solutions/"
					  	. $container->get_environment()->get_name() . "/" . $container->get_name();
		$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $path_2_sol );
		$j = (integer)($sl_container->get_attribute("SL_PARTICIPANTS_AMOUNT"));
		while ( $j > 0 ) {
			
			$f = $sl_container->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
			$person = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $f);
			$container->set_read_access( $person, TRUE );
			
			$j--;
		}	
			
		#save data
		$container->set_attribute( "RV_RESULT", $got_points );
		$container->set_attribute( "RV_DESCRIPTION", $attrib["DESCRIPTION"] );
		
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
				if ( (isset($_SESSION['RV_CREATE'])) && ($_SESSION['RV_CREATE'] === TRUE) ) 
						define('OPERATION', 'CREATE');
				else	define('OPERATION', 'EDIT');
				$operation_context = "NORMAL"; #normal operation, can be "ABORT" in specific cases
				
				$ex_container_name = $this->params[0];
				$sl_container_name = $this->params[1];
				
				if ( Index::existsContainer($sl_path . $ex_container_name) ) {
					
					$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $ex_container_name);
					$ex_container_id = $ex_container->get_id();
					
					if ( Index::existsContainer($sl_path . $ex_container_name. "/" . $sl_container_name) ) {
						
						$sl_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path . $ex_container_name. "/" . $sl_container_name);
						$sl_container_id = $sl_container->get_id();
						
						if ( Index::existsContainer($rv_path . $ex_container_name . "/" . $sl_container_name) ) {
							
							$rv_container_name = $sl_container_name;
							$rv_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path . $ex_container_name. "/" . $rv_container_name);
							$rv_container_id = $rv_container->get_id();
						}
						else {
							
							#initialize
							#create container for new review
							define('OPERATION', 'CREATE');  
							$_SESSION['RV_CREATE'] = TRUE; 
							
							$proceed = false;
							while (!$proceed) {
								$rv_container_name = $sl_container_name;
								if (!Index::existsContainer($rv_path.$ex_container_name.$rv_container_name))
									$proceed = true;
							}
							
							$parent = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path.$ex_container_name);
							$rv_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $rv_container_name, $parent);
							$rv_container_id = $rv_container->get_id();
							
							#Rights Management
							$learners = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".learners");
							$staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1] . ".staff");
							$user = \lms_steam::get_current_user();
							
							$koala_learners = new \koala_group_default( $learners );
							if ($koala_learners->is_member($user)) {
								
								$rv_container->delete();
								echo "No Access for Usergroup: learners";
								die;
							}
							
							$rv_container->set_acquire(0);
							$rv_container->set_write_access( $learners, FALSE );
							$rv_container->set_read_access( $learners, FALSE );
							$rv_container->set_insert_access( $learners, FALSE );
							
							#set creator and link from solution object
							$me = \lms_steam::get_current_user();
							$rv_container->set_attribute("RV_REVIEWER", $me->get_name());
							$sl_container->set_attribute("SL_REVIEW_ID", $rv_container_id);
							
							#instant reload after creating container
							session_write_close();
							header("Location: " . PATH_URL . "exercise/CreateReview/" . $ex_container_name . "/" . $rv_container_name);
							exit;
						}
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
				$success = self::saveData($rv_container, $errmsg, $prm);
				if (!$success) {
					
					$_SESSION['ERROR'] 	= TRUE;
					$_SESSION['ERRMSG'] = $errmsg;
					session_write_close();
					header("Location: " . PATH_URL . "exercise/CreateReview/" . $ex_container_name . "/" . $rv_container_name);
					exit;
				}
				
				$rv_container_name = $rv_container->get_name();
				if (OPERATION=='CREATE') {
					$rv_container->set_attribute("OBJ_CREATION_TIME", time());
				}
				$rv_container->set_attribute("RV_STAFF_LAST_MODIFIED", time());
				
				$_SESSION['SUCCESS'] = TRUE;
				$_SESSION['SUCMSG']  = 'Die Korrektur wurde gespeichert.';
				unset($_SESSION['RV_CREATE']);
				session_write_close();
				header("Location: " . PATH_URL . "exercise/DisplayReview/" . $ex_container_name . "/" . $rv_container_name);
				exit;
			}
		}
		
		/*
		 * Get data of existing container
		 */
		if (OPERATION=='EDIT' && $operation_context=='NORMAL') {

			$rv_desc = $rv_container->get_attribute("RV_DESCRIPTION");
			$rv_points = $rv_container->get_attribute("RV_RESULT");
		}
		
		/*
		 * Get data from form POST after a leave attempt
		 */
		if ($operation_context=='ABORT') {

			$rv_desc = $_POST["attributes"]["DESCRIPTION"];
			$rv_points = $_POST["attributes"]["RESULT"];
		}
		
		/*
		 * Template
		 */
		if (OPERATION=='CREATE') {
			$operation_mode_string = "erstellen";
			$operation_button_label = "Korrektur erstellen";
		}
		else {
			$operation_mode_string = "bearbeiten";
			$operation_button_label = "Korrektur speichern";
		}
		$changed_flag = ($operation_context=='ABORT') ? 'true' : 'false';
		if (($changed_flag=='false') && (OPERATION=='CREATE'))
			 $changed_flag = 'true';
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Lösung " . $rv_container_name, "link" => PATH_URL . "exercise/DisplaySolution/" . $ex_container_name . "/" . $rv_container_name ), array("name" => "Korrektur ".$operation_mode_string)));
	
		$tmplt = \Exercise::getInstance()->loadTemplate("CreateReview.template.html");
		
		if ( isset($_SESSION['ERROR']) && isset($_SESSION['ERRMSG']) && $_SESSION['ERROR'] === TRUE ) {
			
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >' . $_SESSION['ERRMSG'] . '</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
			$changed_flag = 'true';
			unset($_SESSION['ERROR']);
			unset($_SESSION['ERRMSG']);
		}
		
		$tmplt->setVariable( "FORM_ACTION", PATH_URL . "exercise/CreateReview/" . $ex_container_name . "/" . $rv_container_name . "/" );
		#make operation mode available in js
		$tmplt->setVariable( "OPERATION" , OPERATION );
		#preserve change flag in case of aborted leave
		$tmplt->setVariable( "CHANGED_FLAG" , $changed_flag );
		
		$tmplt->setVariable( "ATTR_DESCRIPTION" , "Bewertungstext" );
		$tmplt->setVariable( "ATTR_RESULT" , "Erreichte Punkte" );
		$mp = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $rv_container->get_environment()->get_name())->get_attribute("EX_POINTS");
		$tmplt->setVariable( "MP" , "&nbsp;&nbsp;von " . $mp );
		
		#set values
		if (OPERATION=='EDIT' || $operation_context=='ABORT') {
			
			$tmplt->setVariable( "ATTR_DESCRIPTION_VALUE", $rv_desc );
			$tmplt->setVariable( "ATTR_RESULT_VALUE", $rv_points );
		}
		$tmplt->setVariable( "SUBMIT_BUTTON_LABEL", $operation_button_label );
		
		
		/*
		 * Get existing documents in the exercise container for the documentUploader
		 */
		$fltr = array(array( '-', 'attribute', 'DELETEFLAG', '!=', 'FALSE' ),
					  array( '+', 'class', CLASS_DOCUMENT ));
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		
		$document = $rv_container->get_inventory_filtered( $fltr , $sort, 0, 0 );
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
		$tmplt->setVariable("BASEROOM", $rv_container_id );
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
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>