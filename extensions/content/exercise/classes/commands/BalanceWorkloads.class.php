<?php
namespace Exercise\Commands;
class BalanceWorkloads extends \AbstractCommand implements \IFrameCommand {

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

		$prm = array("WS1011", "Ext-01");
		$basepath = "/home/Courses." . $prm[0] . "." . $prm[1] . ".learners/";
		$path_to_course = "/home/Courses." . $prm[0] . "." . $prm[1] . "/";
		$ex_path = $basepath . "exercises/";
		$sl_path = $basepath . "solutions/";
		$rv_path = $basepath . "reviews/";
		
		$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $path_to_course);
		$container_id = $container->get_id();
		$sl_base_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path);
		
		
		/*
		 * Get Data from Exercise Management
		 */
		$err = false;	
		
		# get reviewers
		$ex_reviewers = array();
		$j = (integer)($container->get_attribute("EX_REVIEWER_COUNT"));
		while ( $j > 0 ) {
			
			$reviewer = array();
			$id = $container->get_attribute("EX_REVIEWER_" . ($j) . "_ID");
			$reviewer['WL'] = 0;
			$reviewer['FN'] = $container->get_attribute("EX_REVIEWER_" . ($j) . "_FULLNAME");
			$reviewer['INDEX'] = $j;
			$ex_reviewers[$id] = $reviewer;
			
			$j--;
		}
		$ex_reviewers = array_reverse($ex_reviewers);
		
		# get exercises with solutions, that have no reviews yet
		$fltr = array(  array( '-', 'class', CLASS_USER	     ),
						array( '+', 'class', CLASS_CONTAINER )
					 );
		$sort = array(array( '<', 'attribute', 'OBJ_NAME' ));
		$exercise = $sl_base_container->get_inventory_filtered( $fltr , $sort, 0, 0 ); 
		$wl_exercises = array();
		$wl_solutions = array();
		
		foreach ($exercise as $ex) {
			
			$exid = $ex->get_name();
			$wl_solutions[$exid] = array();
			$select = false;
			$solution = $ex->get_inventory_filtered( $fltr, $sort, 0, 0 );
			foreach ($solution as $doc) {

				$rv = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path.$ex->get_name().'/'.$doc->get_name());
				if ($rv === 0) {	
					$select = true;
					$wl_solutions[$exid][] = $doc;
					$rvid = $doc->get_attribute("SL_REVIEWER");
					if (isset($ex_reviewers[$rvid])) {
						$ex_reviewers[$rvid]['WL']++;
					}
					else {
						$ex_reviewers[$rvid] = array('WL' => 1, 'FN' => ' ');
					}
				}
			}
			if ($select) {
				$wl_exercises[] = $ex;
			}
		}
		if (empty($wl_exercises)) {
			$err = true;
			$errmsg = 'Es sind keine Abgaben vorhanden.';
		}
		
		
		/*
		 * ACTION on form input
		 */
		if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
			
			#do
			$action = $_POST["action"];
			$src_exercise = $action['EXERCISE'];
				
			# selected action
			if ($action['ACTION']=='distribute') {
				
				# get all reviewers that are not relieved for the selected exercise
				$new_reviewer = array();
				foreach ($ex_reviewers as $id => $rv) {
					
					$relieved = $container->get_attribute("EX_REVIEWER_" . ($rv['INDEX']) . "_RELIEVED");
					$relieved_list = explode('+', $relieved);
					if (!in_array($src_exercise, $relieved_list)) {
						
						$new_reviewer[] = $id;
					}
				}
				$max = count($new_reviewer)-1;
				$nxt = 0;
				
				# distribute all solution through all reviewers
				foreach ($wl_solutions[$src_exercise] as $sol) {
					
					$sol->set_attribute("SL_REVIEWER", $new_reviewer[$nxt]);
					$nxt++;
					if ($nxt > $max)
						$nxt = 0;
				}
				$_SESSION['SUCCESS'] = TRUE;
				$_SESSION['SUCMSG']  = 'Alle Abgaben wurden gleichmäßig aufgeteilt.';
			}
			else { //$action['ACTION']=='relieve' 
				
				$src_reviewer = $action['SOURCE'];
				
				# Relieve Reviewer if selected
				if (isset($action["RELIEVE"]) && $action["RELIEVE"]=='TRUE') {
					
					$id = $ex_reviewers[$src_reviewer]['INDEX']; 								
					$relieved = $container->get_attribute("EX_REVIEWER_" . ($id) . "_RELIEVED");   
					$set_relieved_list = explode('+', $relieved);				
					array_push($set_relieved_list, $src_exercise);									
					$set_relieved = implode('+', $set_relieved_list);
					
					$container->set_attribute("EX_REVIEWER_" . ($id) . "_RELIEVED", $set_relieved);
				}
				
				# selected subaction
				if ($action['SUBACTION']=='distribute') {
					
					# get all reviewers that are not relieved for the selected exercise
					$new_reviewer = array();
					foreach ($ex_reviewers as $id => $rv) {
						
						$relieved = $container->get_attribute("EX_REVIEWER_" . ($rv['INDEX']) . "_RELIEVED");
						$relieved_list = explode('+', $relieved);
						if (!in_array($src_exercise, $relieved_list)) {
							if ($src_reviewer != $id) {
								$new_reviewer[] = $id;
							}
						}
					}
					$max = count($new_reviewer)-1;
					$nxt = 0;
					
					# distribute solutions of selected reviewer through all reviewers
					foreach ($wl_solutions[$src_exercise] as $sol) {
						
						if ($sol->get_attribute("SL_REVIEWER")==$src_reviewer) {
							$sol->set_attribute("SL_REVIEWER", $new_reviewer[$nxt]);
							$nxt++;
							if ($nxt > $max)
								$nxt = 0;
						}
					}
					$_SESSION['SUCCESS'] = TRUE;
					$_SESSION['SUCMSG']  = 'Workload von '.$src_reviewer.' wurde gleichmäßig aufgeteilt.';
				}
				else {//$action['SUBACTION']=='assign' 
					
					$dst_reviewer = $action['TARGET'];
					
					# assign solutions of selected reviewer to another reviewer
					foreach ($wl_solutions[$src_exercise] as $sol) {
						
						if ($sol->get_attribute("SL_REVIEWER")==$src_reviewer) {
							
							$sol->set_attribute("SL_REVIEWER", $dst_reviewer);
						}
					}
					$_SESSION['SUCCESS'] = TRUE;
					$_SESSION['SUCMSG']  = 'Workload von '.$src_reviewer.' wurde an '.$dst_reviewer.' &uuml;bergeben.';
				}
			}
			session_write_close();
			header("Location: " . PATH_URL . "exercise/BalanceWorkloads/");
			exit;
		}	
		
		/*
		 * Template
		 */
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name" => "SoSe12", "link" => PATH_URL . "exercise/Index/"), array("name" => "Vorlesung A", "link" => PATH_URL . "exercise/Index/"), array("name" => "&Uuml;bungsaufgaben", "link" => PATH_URL . "exercise/index/"), array("name" => "Abgaben verteilen")));
		
		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array( "name" => "-", "ajax" => array( "onClick" => array( "command" => "none", "params" => array( "1" , "2" ), "requestType" => "data" )))));
		
		$tmplt = \Exercise::getInstance()->loadTemplate("BalanceWorkloads.template.html");
		
		if ( $err ) {
			$errmsg = '<div id=notice><p id="ex_err" style="display:none;" >' . $errmsg . '</p></div>';
			$errjs  = "$('#ex_err').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $errmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $errjs );
		}
		if ( isset($_SESSION['SUCCESS']) && isset($_SESSION['SUCMSG']) && $_SESSION['SUCCESS'] === TRUE ) {
			$sucmsg = '<div id=notice><p id="ex_success" style="display:none;" >' . $_SESSION['SUCMSG'] . '</p></div>';
			$sucjs  = "$('#ex_success').fadeIn(2000);";
			$tmplt->setVariable( "NOTIFICATION_TEXT", $sucmsg );
			$tmplt->setVariable( "NOTIFICATION_VIEW", $sucjs );
			unset($_SESSION['SUCCESS']);
			unset($_SESSION['SUCMSG']);
		}

		#set values
		if (!$err) {
			
			$tmplt->setVariable( "FORM_ACTION", PATH_URL . "exercise/BalanceWorkloads/" );
			$ex_options = '';
			foreach ($wl_exercises as $ex) {
				
				$id = $ex->get_name();
				$c = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
				$name = $c->get_name();
				$ex_options .= '<option value="'.$id.'">'.$name.'</option>';
			}
			$tmplt->setVariable("EXERCISES", $ex_options);
			
			$rv_options = '';
			$rv_workloads = '';
			foreach ($ex_reviewers as $id => $rv) {
				
				$rv_options .= '<option value="'.$id.'">'.$id.' ('.$rv['FN'].')</option>';
				$rv_workloads .= '<span class="nm">'.$id.' ('.$rv['FN'].')</span><span class="wl">'.$rv['WL'].'</span><br />';
			}
			$tmplt->setVariable("WORKLOADS", $rv_workloads);
			$tmplt->setVariable("SOURCES", $rv_options);
			$tmplt->setVariable("TARGETS", $rv_options);
			
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