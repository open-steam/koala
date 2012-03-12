<?php
namespace Exercise\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

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
	
	/**
	 * function: readFile
	 * 
	 * Reads the file at $pPath into a string
	 * 
	 * @static
	 * @param String $pPath The path to the file
	 * @return String The contents of the file
	 */
	public static function readFile( $pPath ) {
		
		$fileString = "";
		$fileRowsasArray = file( $pPath );
		
		foreach ( $fileRowsasArray as $row ) {
			
			$fileString .= $row;
		}
		
		return $fileString;
	}
	
	/**
	 * function: existsContainer
	 * 
	 * Checks if a Container specified by a path exists on the sTeam
	 * backend.
	 * 
	 * @static
	 * @param String $pPath The path to the container
	 * @return Boolean The result of the check 
	 */
	public static function existsContainer ( $pPath ) {
		
		#Check if Container/room exists
		$chkexist = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pPath);
		$exists = TRUE;
		if ($chkexist == null) {
			$exists = FALSE;
		}
		
		return $exists;
	}

	public function execute( \FrameResponseObject $frameResponseObject ){		
		
		/*
		 * for testing purpose preselect course EXT-01: 
		 * in live environment use $this->params[0]
		 */
		$prm = array("WS1011", "Ext-01");
		$basepath = "/home/Courses." . $prm[0] . "." . $prm[1] . ".learners/";
		$ex_path = $basepath . "exercises/";
		$sl_path = $basepath . "solutions/";
		$rv_path = $basepath . "reviews/";
		
		/*
		 * Get Data
		 */
		
		# Get Exercise List and check if one exercise is active
		$has_current_exercise = FALSE;
		$current_exercise_deadline = " ";
		$current_exercise_group = " ";
		$current_exercise_name = " ";
		$current_exercise_id = " ";
		$current_exercise_info = " ";
		
		$ex_room = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $ex_path);
		$fltr = array(  array( '-', 'class', CLASS_USER	     ),
						array( '+', 'class', CLASS_CONTAINER )
					 );
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$exercises = $ex_room->get_inventory_filtered( $fltr , $sort, 0, 0 );
		
		$now = time();
		foreach ($exercises as $e) {
			
			$start = $e->get_attribute("EX_START");
			$end = $e->get_attribute("EX_DEADLINE");
			if (($now >= $start) && ($end >= $now)) {
				
				$has_current_exercise = TRUE;
				$current_exercise_name = $e->get_name();
				$current_exercise_id = $e->get_id();
				$ex_ming = $e->get_attribute("EX_MINGROUP");
				$ex_maxg = $e->get_attribute("EX_MAXGROUP");
				if ($ex_maxg > 1) 
					 $current_exercise_group = $ex_ming . "&nbsp;bis&nbsp;" . $ex_maxg . "&nbsp;Personen";
				else $current_exercise_group = "Nur Einzelabgabe";
				$current_exercise_deadline = 'bis ' . date("d.m.Y", $end) . ', um ' . date("H:i", $end) . 'Uhr';
				$current_exercise_info = $e->get_attribute("EX_DESCRIPTION");
				break;
			}
		}
		
		# Check for Usergroup
		$user = \lms_steam::get_current_user();
		$course_group = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $prm[0] . "." . $prm[1]);
		$group = new \koala_group_course( $course_group );
		
		if ($group->is_learner($user)) {
			
			$user_is_learner = TRUE;
		}
		elseif ($group->is_staff($user)) {
			
			$user_is_learner = FALSE;
		}
		else {
			
			echo "You shall not pass!";
			exit;
		}
		
		# Check for Solution of a learner and get Data
		if ($user_is_learner && $has_current_exercise) {
			
			$learner_has_solution = FALSE;
			$learner_solution_id = 0;
			$path_2_con = $basepath . "solutions/" . $current_exercise_id . "/";
			$solution_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $path_2_con);
			$solution = $solution_container->get_inventory_filtered( $fltr, $sort, 0, 0 );
			
			foreach ($solution as $doc) {

				$n = (integer)($doc->get_attribute("SL_PARTICIPANTS_AMOUNT"));
				for ( $j = 1 ; $j <= $n ; $j++ ) {
					
					$author = $doc->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
					if ( $author ==  $user->get_name() ) {
						
						$learner_has_solution = TRUE;
						$learner_solution_id = $doc->get_id();
					}
				}
			}
		}
		
		# Get Points of learner
		if ($user_is_learner) {
			
			$sl_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
			$exercise = $sl_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
			$pnts_usr = 0;
			$pnts_tot = 0;
			
			foreach ($exercise as $folder) {
				
				$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $folder->get_name());
				$max_points = (integer)($ex_container->get_attribute("EX_POINTS"));
				$pnts_tot += $max_points;
				
				$solution = $folder->get_inventory_filtered( $fltr, $sort, 0, 0 );
				foreach ($solution as $doc) {
	
					$n = (integer)($doc->get_attribute("SL_PARTICIPANTS_AMOUNT"));
					for ( $j = 1 ; $j <= $n ; $j++ ) {
						
						$author = $doc->get_attribute("SL_PARTICIPANTS_" . ($j) . "_ID");
						if ( $author ==  $user->get_name() ) {
							
							$review_path = $rv_path . $folder->get_name() . '/' . $doc->get_name() . '/';
							if ( $this->existsContainer( $review_path ) ) {
								
								$review = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $review_path);
								$my_points = (integer)($review->get_attribute("RV_RESULT"));
								$pnts_usr += $my_points;
							}
						}
					}
				}
			}

			$learner_current_points = (string)($pnts_usr);
			$learner_total_points = (string)($pnts_tot);
			
			$path_2_course = "/home/Courses." . $prm[0] . "." . $prm[1] . "/";
			$course_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $path_2_course);
			$thresholds = $course_container->get_attribute("EX_BONI");
			$bonus = explode("#", $thresholds);
			
			if ($learner_current_points >= (integer)$bonus[2]) {
				
				$learner_bonus_text = 'Aktuell hast du 3 Bonusnotenschritte erreicht.';
			}
			elseif ($learner_current_points >= (integer)$bonus[1]) {
				
				$learner_bonus_text = 'Aktuell hast du 2 Bonusnotenschritte erreicht.';
			}
			elseif ($learner_current_points >= (integer)$bonus[0]) {
				
				$learner_bonus_text = 'Aktuell hast du 1 Bonusnotenschritte erreicht.';
			}
			else {
				
				$learner_bonus_text = 'Es wurde noch kein Bonus erreicht.';
			}
		}
		
		# Check for Workload of staff member and get Data
		if (! $user_is_learner) {
			
			$workload = array();
			$sl_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
			$exercise = $sl_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 );
			
			foreach ($exercise as $folder) {
				
				$ex_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $folder->get_name() );
				$submission_closed = (($ex_container->get_attribute("EX_DEADLINE") < time())) ? true : false ;
				
				if ($submission_closed) {
					
					$solution = $folder->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
					$wcount = 0;
					
					foreach ($solution as $d) {
					
						$assigned = $d->get_attribute("SL_REVIEWER");
						if ( $assigned ==  $user->get_name() ) {
							
							$wcount++;
						}
					}
					
					if ($wcount == 0)
						continue;
					
					$wl_for_ex = array();
					$wl_for_ex['EX_NAME'] = $ex_container->get_name();
					$wl_for_ex['EX_ID'] = $ex_container->get_id();
					$wl_for_ex['SL_AMT'] = $wcount;
					
					$workload[] = $wl_for_ex;
				}
			}
		}
		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "&Uuml;bungsaufgaben")));
		
		if (!$user_is_learner) {
			
			$actionBar = new \Widgets\ActionBar();
			$actionBar->setActions(array(array( "name" => "&Uuml;bungsbetrieb konfigurieren", "link" => PATH_URL . "exercise/EditSettings")));
		}
		
		$tmplt = \Exercise::getInstance()->loadTemplate("Index.template.html");
		
		if ($has_current_exercise) {
			
			$tmplt->setVariable( "CURRENT_EXERCISE", $current_exercise_name );
			$tmplt->setVariable( "DISPLAY_EXERCISE_LINK", PATH_URL . 'exercise/DisplayExercise/' . $current_exercise_name );
			
			if ($user_is_learner) {
				
				$tmplt->setCurrentBlock("BLOCK_LEARNERS_EMPTY_BUTTON");
				$tmplt->setVariable( "NOBUTTON", '<br />' );
				$tmplt->parse("BLOCK_LEARNERS_EMPTY_BUTTON");
				
				$tmplt->setCurrentBlock("BLOCK_LEARNERS_CREATE_SOLUTION");
				if (!$learner_has_solution) {
					
					$tmplt->setVariable( "CREATE_SOLUTION_LINK", PATH_URL . 'exercise/CreateSolution/' . $current_exercise_id );
					$tmplt->setVariable( "CREATE_OR_EDIT", 'einreichen' );
				}
				else {
					
					$tmplt->setVariable( "CREATE_SOLUTION_LINK", PATH_URL . 'exercise/CreateSolution/' . $current_exercise_id . '/' . $learner_solution_id );
					$tmplt->setVariable( "CREATE_OR_EDIT", 'bearbeiten' );
				}
				$tmplt->parse("BLOCK_LEARNERS_CREATE_SOLUTION");
				
				$tmplt->setCurrentBlock("BLOCK_LEARNERS_VIEW_SOLUTION");
				if (!$learner_has_solution) {
					
					$tmplt->setVariable( "DISABLE_SOL_LINK", "disabled" );
					$tmplt->setVariable( "DISABLE_SOL_LINK_2", " disabled=\"disabled\"" );
					$tmplt->setVariable( "VIEW_SOLUTION_LINK", "" );
				}
				else {
					
					$tmplt->setVariable( "DISABLE_SOL_LINK", "" );
					$tmplt->setVariable( "DISABLE_SOL_LINK_2", "" );
					$tmplt->setVariable( "VIEW_SOLUTION_LINK", PATH_URL . 'exercise/DisplaySolution/' . $current_exercise_id . '/' . $learner_solution_id );
				}
				$tmplt->parse("BLOCK_LEARNERS_VIEW_SOLUTION");
				
			}
			else {
				
				$tmplt->setCurrentBlock("BLOCK_STAFF_EDIT_EXERCISE");
				$tmplt->setVariable( "EDIT_EXERCISE_LINK", PATH_URL . 'exercise/CreateExercise/' . $current_exercise_name );
				$tmplt->parse("BLOCK_STAFF_EDIT_EXERCISE");
			}
			
			$tmplt->setCurrentBlock("BLOCK_INFO");
			$tmplt->setVariable( "DEADLINE", $current_exercise_deadline );
			$tmplt->setVariable( "GROUP", $current_exercise_group );
			$tmplt->setVariable( "INFO", $current_exercise_info );
			$tmplt->parse("BLOCK_INFO");
		}
		else {
			
			$tmplt->setVariable( "CURRENT_EXERCISE", '<em>kein &Uuml;bungsblatt aktiv!</em>' );
			
			$tmplt->setVariable( "DISABLE_VIEW_LINK" , "disabled" );
			$tmplt->setVariable( "DISABLE_VIEW_LINK_2" , " disabled=\"disabled\"" );
			
			$tmplt->setCurrentBlock("BLOCK_NO_EX");
			$tmplt->setVariable( "NONE", '<br />' );
			$tmplt->parse("BLOCK_NO_EX");
		}
		
		if ($user_is_learner) {
			
			$tmplt->setCurrentBlock("BLOCK_LEARNERS_POINTS");
			$tmplt->setVariable( "MYPOINTS", $learner_current_points );
			$tmplt->setVariable( "TOTALPOINTS", $learner_total_points );
			$tmplt->setVariable( "BONUS", $learner_bonus_text );
			$tmplt->parse("BLOCK_LEARNERS_POINTS");
			
			$tmplt->setCurrentBlock("BLOCK_LEARNERS_ACTIONS");
			$tmplt->setVariable( "ICON_BASE_PATH", PATH_URL );
			$tmplt->setVariable( "LIST_SOLUTIONS_LINK", PATH_URL . 'exercise/ListSolutions/' );
			$tmplt->setVariable( "LIST_EXERCISES_LINK", PATH_URL . 'exercise/ListExercises/' );
			$tmplt->parse("BLOCK_LEARNERS_ACTIONS");
		}
		else {
			
			$tmplt->setCurrentBlock("BLOCK_STAFF_WORKLOAD");
			foreach ($workload as $w) {
				
				$tmplt->setCurrentBlock("BLOCK_WORKLOAD_ENTRY");
				$tmplt->setVariable( "EX_NAME", $w['EX_NAME'] );
				$tmplt->setVariable( "WL_LINK", PATH_URL . 'exercise/ListWorkload/' . $w['EX_ID'] );
				$tmplt->setVariable( "SL_AMOUNT", $w['SL_AMT'] );
				$tmplt->parse("BLOCK_WORKLOAD_ENTRY");
			}
			
			$tmplt->parse("BLOCK_STAFF_WORKLOAD");
			
			$tmplt->setCurrentBlock("BLOCK_STAFF_ACTIONS");
			$tmplt->setVariable( "ICON_BASE_PATH", PATH_URL );
			$tmplt->setVariable( "LIST_REVIEWS_LINK", PATH_URL . 'exercise/ListReviews/' );
			$tmplt->setVariable( "LIST_EXERCISES_LINK", PATH_URL . 'exercise/ListExercises/' );
			$tmplt->setVariable( "CREATE_EXERCISE_LINK", PATH_URL . "exercise/CreateExercise/" );
			$tmplt->setVariable( "BALANCE_WORKLOADS_LINK", PATH_URL . "exercise/BalanceWorkloads/" );
			$tmplt->parse("BLOCK_STAFF_ACTIONS");
		}
		
		/*
		 * assemble frameResponse
		 */
		$exerciseFormCss = Index::readFile( PATH_URL . "exercise/css/index.css" );
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setCss($exerciseFormCss);
		$rawHtml->setHtml($tmplt->get());
		
		$frameResponseObject->setTitle("Exercise");
		$frameResponseObject->addWidget($breadcrumb);
		if (isset($actionBar))
			$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>