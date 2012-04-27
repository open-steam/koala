<?php
namespace Exercise\Commands;
class EditSettings extends \AbstractCommand implements \IFrameCommand {

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
		$ex_reviewers = $_POST["tt_names"];
		$ex_reviewers_full = array();
		$ex_groups = $_POST["gr_names"];
		$ex_group_tutors = $_POST["gr_tts"];
		
		#validate data
		/*
		 * TODO: Validation is not complete
		 */
		settype($attrib["MINGROUP"], "integer");
		settype($attrib["MAXGROUP"], "integer");
		if ( (!empty($attrib["MINGROUP"]) 
		&& !empty($attrib["MAXGROUP"])) 
		&& ($attrib["MINGROUP"] > $attrib["MAXGROUP"]) ) 
			{ $errmsg = "Die max. Gruppenst&auml;rke muss gr&ouml;&szlig;er als die minimale sein."; return false; }
			
		for ( $i=0 ; $i < count($ex_groups) ; $i++) {
			if (empty($ex_groups[$i]))
			  { $errmsg = "Der Name einer &Uuml;bungsgruppe darf nicht leer sein."; return false; }
		}
			
		for ( $i=0 ; $i < count($ex_reviewers) ; $i++ ) {
			if (empty($ex_reviewers[$i])) 	
			  { $errmsg = "Alle Tutoren m&uuml;ssen durch ihren Benutzernamen identifiziert werden."; return false; }
		}
		
		for ( $i=0 ; $i < count($ex_reviewers) ; $i++ ) {
			$user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $ex_reviewers[$i]);
			if ($user === 0) {
				
				$errmsg = "Der Tutor \"" . $ex_reviewers[$i] . "\" konnte nicht gefunden werden."; 
				return false;
			} 
			$ex_reviewers_full[$i] = $user->get_full_name();	
		}
		
		for ( $i=0 ; $i < count($ex_group_tutors) ; $i++ ) {
			if (empty($ex_group_tutors[$i])) 	
			  { $errmsg = "Jede &Uuml;bungsgruppe muss einen Tutor zugewiesen bekommen."; return false; }
		}
		
		for ( $i=0 ; $i < count($ex_group_tutors) ; $i++ ) {
			
			$user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $ex_group_tutors[$i]);
			if ($user === 0) {
				
				$errmsg = "Der Tutor \"" . $ex_group_tutors[$i] . "\" konnte nicht gefunden werden."; 
				return false;
			} 	
		}
		$bon1 = (empty($attrib["BON1"])) ? "0" : (integer)$attrib["BON1"];
		$bon2 = (empty($attrib["BON2"])) ? "0" : (integer)$attrib["BON2"];
		$bon3 = (empty($attrib["BON3"])) ? "0" : (integer)$attrib["BON3"];
		if ( $bon1 > $bon2 || $bon2 > $bon3 || $bon1 > $bon3 )
			{ $errmsg = "H&ouml;here Bonusnotenschritte m&uuml;ssen h&ouml;here Punktezahlen erfordern."; return false; }
		
		#save data
		$reviewer_count = 0;
		foreach ( $ex_reviewers as $rv ) {
			
			$reviewer_count++;
			$container->set_attribute( "EX_REVIEWER_" . $reviewer_count . "_ID", $rv );
			$container->set_attribute( "EX_REVIEWER_" . $reviewer_count . "_FULLNAME", $ex_reviewers_full[$reviewer_count-1] );
		}
		$container->set_attribute( "EX_REVIEWER_COUNT", $reviewer_count );
		
		$group_count = 0;
		foreach ( $ex_groups as $grp ) {
			
			$group_count++;
			$container->set_attribute( "EX_TUTORIAL_" . $group_count . "_NAME", $grp );
			$container->set_attribute( "EX_TUTORIAL_" . $group_count . "_TUTORID", $ex_group_tutors[$group_count-1] );
		}
		$container->set_attribute( "EX_TUTORIAL_COUNT", $group_count );
		
		if (isset($attrib["ENABLED"]) && $attrib["ENABLED"]=='TRUE') {
			$container->set_attribute( "EX_ENABLED", "1" );
		}
		else {
			$container->set_attribute( "EX_ENABLED", "0" );
		}
		$container->set_attribute( "EX_CYCLE", $attrib["CYCLE"] );
		$container->set_attribute( "EX_MINPARTICIPANTS", $attrib["MINGROUP"] );
		$container->set_attribute( "EX_MAXPARTICIPANTS", $attrib["MAXGROUP"] );
		$container->set_attribute( "EX_WORKLOAD_DISTRIBUTION", $attrib["WLMODE"] );
		$bonus = $bon1 . '#' . $bon2 . '#' . $bon3;
		$container->set_attribute( "EX_BONI", $bonus );
		
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
            
		/*
		$ex_path = $basepath . "exercises/";
		$sl_path = $basepath . "solutions/";
		$rv_path = $basepath . "reviews/";
		*/
		
		$container = $exerciseObject;
		
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
					header("Location: " . PATH_URL . "exercise/EditSettings/" . $this->id);
					exit;
				}
				
				$_SESSION['SUCCESS'] = TRUE;
				$_SESSION['SUCMSG']  = 'Die Einstellungen wurden gespeichert.';
				session_write_close();
				header("Location: " . PATH_URL . "exercise/EditSettings/". $this->id);
				exit;
			}
		}
		else {
			
			$operation_context = "NORMAL";
		}
		
		/*
		 * Initialize Settings if there were none saved previously
		 */
		if ($container->get_attribute("EX_ENABLED") === 0) {
			define('OPERATION', 'INITIALIZE');
		}
		else {
			define('OPERATION', 'EDIT');
		}
		
		/*
		 * Get Exercise settings for this Course
		 */
		if ($operation_context=='NORMAL') {
			
			$ex_enabled = $container->get_attribute("EX_ENABLED");
			$ex_cycle = (integer)$container->get_attribute("EX_CYCLE");
			$ex_partmin = $container->get_attribute("EX_MINPARTICIPANTS");
			$ex_partmax = $container->get_attribute("EX_MAXPARTICIPANTS");
			$ex_wlmode = $container->get_attribute("EX_WORKLOAD_DISTRIBUTION");
			$bonus = $container->get_attribute("EX_BONI");
			$bonus = explode("#", $bonus);
			$ex_bonus1 = $bonus[0];
			$ex_bonus2 = isset($bonus[1]) ? $bonus[1] : null;
			$ex_bonus3 = isset($bonus[2]) ? $bonus[2] : null;
			
			# get reviewrs
			$ex_reviewers = array();
			$j = (integer)($container->get_attribute("EX_REVIEWER_COUNT"));
			while ( $j > 0 ) {
				
				$reviewer = array();
				$reviewer['ID'] = $container->get_attribute("EX_REVIEWER_" . ($j) . "_ID");
				$reviewer['FN'] = $container->get_attribute("EX_REVIEWER_" . ($j) . "_FULLNAME");
				$ex_reviewers[] = $reviewer;
				
				$j--;
			}
			$ex_reviewers = array_reverse($ex_reviewers);
			
			# get tutorial groups
			$ex_groups = array();
			$j = (integer)($container->get_attribute("EX_TUTORIAL_COUNT"));
			while ( $j > 0 ) {
				
				$tutorial = array();
				$tutorial['NAME'] = $container->get_attribute("EX_TUTORIAL_" . ($j) . "_NAME");
				$tutorial['TUTORID'] = $container->get_attribute("EX_TUTORIAL_" . ($j) . "_TUTORID");
				$ex_groups[] = $tutorial;
				
				$j--;
			}
			$ex_groups = array_reverse($ex_groups);
		}
		
		/*
		 * Get data from form POST after a leave attempt
		 */
		if ($operation_context=='ABORT') {
			
			$ex_enabled = (isset($_POST["attributes"]["ENABLED"])) ? '1' : '0';
			$ex_cycle = (integer)$_POST["attributes"]["CYCLE"];
			$ex_partmin = $_POST["attributes"]["MINGROUP"];
			$ex_partmax = $_POST["attributes"]["MAXGROUP"];
			$ex_wlmode = $_POST["attributes"]["WLMODE"];
			$ex_bonus1 = $_POST["attributes"]["BON1"];
			$ex_bonus2 = $_POST["attributes"]["BON2"];
			$ex_bonus3 = $_POST["attributes"]["BON3"];
			
			# get reviewers
			$tt_names = $_POST["tt_names"];
			$tt_fnames = $_POST["tt_fnames"];
			$ex_reviewers = array();
			$j = 0;
			foreach ( $tt_names as $nm ) {
				
				$rev = array();
				$rev['ID'] = $nm;
				$rev['FN'] = $tt_fnames[$j];
				$ex_reviewers[] = $rev;
				
				$j++;
			}
			
			# get tutorials
			$gr_nm = $_POST["gr_names"];
			$gr_tt = $_POST["gr_tts"];
			$ex_groups = array();
			$j = 0;
			foreach ( $gr_nm as $nm ) {
				
				$rev = array();
				$rev['NAME'] = $nm;
				$rev['TUTORID'] = $gr_tt[$j];
				$ex_groups[] = $rev;
				
				$j++;
			}
		}
		
		/*
		 * Template
		 */
		$changed_flag = ($operation_context=='ABORT') ? 'true' : 'false';
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/index/" . $this->id), array("name" => "Einstellungen")));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("EditSettings.template.html");
		
		if ( isset($_SESSION['ERROR']) && isset($_SESSION['ERRMSG']) && $_SESSION['ERROR'] === TRUE ) {
			
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >' . $_SESSION['ERRMSG'] . '</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
			$changed_flag = 'true';
			unset($_SESSION['ERROR']);
			unset($_SESSION['ERRMSG']);
		}
		if ( isset($_SESSION['SUCCESS']) && isset($_SESSION['SUCMSG']) && $_SESSION['SUCCESS'] === TRUE ) {
			
			$sucmsg = '<div id=notice><p id="ex_success" style="display:none;" >' . $_SESSION['SUCMSG'] . '</p></div>';
			$sucjs  = "$('#ex_success').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $sucmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $sucjs );
			unset($_SESSION['SUCCESS']);
			unset($_SESSION['SUCMSG']);
		}
		
		$tmplt->setVariable( "FORM_ACTION", PATH_URL . "exercise/EditSettings/" . $this->id );
		#preserve change flag in case of aborted leave
		$tmplt->setVariable( "CHANGED_FLAG" , $changed_flag );

		#set values
		if (OPERATION=='EDIT' || $operation_context=='ABORT') {
			
			if ($ex_enabled=="1")
				$tmplt->setVariable( "ATTR_ENABLED_VALUE", "checked=\"checked\"" );
			$tmplt->setVariable( "ATTR_MINGROUP_VALUE", $ex_partmin );
			$tmplt->setVariable( "ATTR_MAXGROUP_VALUE", $ex_partmax );
			$tmplt->setVariable( "ATTR_BON1_VALUE", $ex_bonus1 );
			$tmplt->setVariable( "ATTR_BON2_VALUE", $ex_bonus2 );
			$tmplt->setVariable( "ATTR_BON3_VALUE", $ex_bonus3 );
			
			$sel1 = ($ex_cycle==1) ? 'selected' : ' ';
			$sel2 = ($ex_cycle==2) ? 'selected' : ' ';
			$sel3 = ($ex_cycle==4) ? 'selected' : ' ';
			$cycleList = '<option value="1" ' . $sel1 . '>w&ouml;chentlich</option>
						  <option value="2" ' . $sel2 . '>2-w&ouml;chentlich</option>
						  <option value="4" ' . $sel3 . '>4-w&ouml;chentlich</option>';
			$tmplt->setVariable( "ATTR_CYCLE_VALUE", $cycleList );
			
			$sel1 = ($ex_wlmode=='shared') ? 'selected' : ' ';
			$sel2 = ($ex_wlmode=='groups') ? 'selected' : ' ';
			$wlList = '<option value="shared" ' . $sel1 . '>fair verteilt</option>
					   <option value="groups" ' . $sel2 . '>Gruppenintern</option>';
			$tmplt->setVariable( "ATTR_WLMODE_VALUE", $wlList );
			
			$amt = 0;
			foreach ($ex_reviewers as $entry) {
				
				$tmplt->setCurrentBlock( "BLOCK_REVIEWERS" );
				$tmplt->setVariable( "TT_ENTRYNR", $amt+1 );
				$tmplt->setVariable( "TT_ID", $entry['ID'] );
				$tmplt->setVariable( "TT_FN", $entry['FN'] );
				$tmplt->parse( "BLOCK_REVIEWERS" );
				$amt++;
			}
			$tmplt->setVariable( "TT_ENTRYAMT", $amt );
			
			$amt = 0;
			foreach ($ex_groups as $entry) {
				
				$tmplt->setCurrentBlock( "BLOCK_TUTORIALS" );
				$tmplt->setVariable( "GR_ENTRYNR", $amt+1 );
				$tmplt->setVariable( "GR_NAME", $entry['NAME'] );
				$tmplt->setVariable( "GR_TT_ID", $entry['TUTORID'] );
				$tmplt->parse( "BLOCK_TUTORIALS" );
				$amt++;
			}
			$tmplt->setVariable( "GR_ENTRYAMT", $amt );
		}
		else {
			
			$me = \lms_steam::get_current_user();
			$tmplt->setVariable( "TT_ENTRYNR", "1" );
			$tmplt->setVariable( "TT_ID", $me->get_name() );
			$tmplt->setVariable( "TT_FN", $me->get_full_name() );
			$tmplt->setVariable( "TT_ENTRYAMT", "1" );
			$tmplt->setVariable( "GR_ENTRYNR", "1" );
			$tmplt->setVariable( "GR_NAME", "&Uuml;bungsgruppe 1" );
			$tmplt->setVariable( "GR_TT_ID", $me->get_name() );
			$tmplt->setVariable( "GR_ENTRYAMT", "1" );
			
			$cycleList = '<option value="1">w&ouml;chentlich</option>
						  <option value="2">2-w&ouml;chentlich</option>
						  <option value="4">4-w&ouml;chentlich</option>';
			$tmplt->setVariable( "ATTR_CYCLE_VALUE", $cycleList );
			$wlList = '<option value="shared">fair verteilt</option>
					   <option value="groups">Gruppenintern</option>';
			$tmplt->setVariable( "ATTR_WLMODE_VALUE", $wlList );
		}
		
		/*
		 * assemble frameResponse
		 */
		$exerciseFormCss = Index::readFile( PATH_URL . "exercise/css/editSettings.css" );
		$exerciseFormJs  = Index::readFile( PATH_URL . "exercise/js/editSettings.js" );
		
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