<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_user.class.php");
class exam {
	
	private $unit;
	private $unit_obj;
	private $portal;
	private $content;
	private $elearning_course;
	private $owner;
	private $exam;
	
	function __construct($unit_obj, $unit, $owner) {
		$this->owner = $owner;
		$this->unit_obj = $unit_obj;
		$this->unit = $unit;	
		$mediathek = elearning_mediathek::get_instance();
		$mediathek->set_unit($unit_obj);
		$this->elearning_course = elearning_mediathek::get_elearning_course_by_id($unit);
		$this->exam = $this->elearning_course->get_exam_by_type("final_exam");
	}
	
	function render_html() {
		$html = $this->exam->get_html();
		lms_portal::get_instance()->set_page_main( array(array("link" => "../", "name" => "zurück zum Kurs"),
									  array("link" => "", "name" => "Prüfung zum Kurs »" . $this->elearning_course->get_name() . "«")), $html, "");
		lms_portal::get_instance()->show_html(); 	
	}
	
	function render_report_html($user) {
		$this->content = new HTML_TEMPLATE_IT();
		$this->content->loadTemplateFile(PATH_TEMPLATES_UNITS_ELEARNING . "exam.template.html");
		$this->content->setVariable("ELEARNING_EXAM_CONTENT", "Hund");
		$html = $this->exam->get_report_html($user);
		lms_portal::get_instance()->set_page_main( array(array("link" => "javascript:history.back()", "name" => "zurück"),
									  array("link" => "", "name" => "Prüfung zum Kurs »" . $this->elearning_course->get_name() . "«")), $html, "");
		lms_portal::get_instance()->show_html(); 
	}
	
	function render_reporting_html() {
		$this->content = new HTML_TEMPLATE_IT();
		$this->content->loadTemplateFile(PATH_TEMPLATES_UNITS_ELEARNING . "exam.template.html");
		$this->content->setVariable("ELEARNING_EXAM_CONTENT", "Hund");
		$html = $this->get_reporting_html();
		lms_portal::get_instance()->set_page_main( array(array("link" => "../../", "name" => "zurück zum Kurs"),
									  array("link" => "", "name" => "Prüfung zum Kurs »" . $this->elearning_course->get_name() . "« verwalten")), $html, "");
		lms_portal::get_instance()->show_html();
	} 
	
	public function render_chart_html() {
		lms_portal::get_instance()->set_prototype_enabled(false);
		
		lms_portal::get_instance()->add_javascript_src("elearning_chart","http://www.google.com/jsapi");
        lms_portal::get_instance()->add_javascript_code("elearning_chart","google.load('visualization', '1', {packages: ['corechart']});");
        $count_passed = 0;
        $count_failed = 0;
        $count_nt = 0;
        $finished_learners = array();
        $learners = $this->owner->get_group_learners()->get_members();
        foreach ($learners as $learner) {
        	if ($this->exam->is_finished($learner)) {
        		$finished_learners[] = $learner;
        		$elearning_user = elearning_user::get_instance($learner->get_name(), $this->owner->get_id());
        		if ($elearning_user->has_exam_passed()) {
        			$count_passed++;
        		} else {
        			$count_failed++;
        		}
        	} else {
        		$count_nt++;
        	}
        }
        
        $red = "#EC6259";
        $green = "#B4D745";
        $gray = "#AAAAAA";
        $color = "";
        
        if ($count_passed != 0 && $count_failed != 0 && $count_nt != 0) {
        	$color = ", colors:['$green','$red','$gray']";
        } else if ($count_passed == 0 && $count_failed == 0) {
        	$color = ", colors:['$gray']";
        } else if ($count_passed == 0) {
        	$color = ", colors:['$red','$gray']";
        }  else if ($count_failed == 0) {
        	$color = ", colors:['$green','$gray']";
        } else if ($count_nt == 0) {
        	$color = ", colors:['$red','$green']";
        }
        
        // TN 
$js = <<< END
        // Create and populate the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Task');
        data.addColumn('number', 'Hours per Day');
        data.addRows(3);
        data.setValue(0, 0, 'bestanden');
        data.setValue(0, 1, $count_passed);
        data.setValue(1, 0, 'nicht bestanden');
        data.setValue(1, 1, $count_failed);
        data.setValue(2, 0, 'keine Prüfung abgelegt');
        data.setValue(2, 1, $count_nt);
      
        // Create and draw the visualization.
        new google.visualization.PieChart(document.getElementById('visualization')).
            draw(data, {title:"Teilnehmerübersicht" $color});    
END;

			

       
		$html = "<div class=\"infoBar\">Das Diagramm »Teilnehmerübersicht« zeigt, wie viele Teilnehmer die Prüfung bereits abgelegt haben sowie wie viele Teilnehmer die Prüfung bestanden bzw. nicht bestanden haben.</div>";
		

		$html .= "<div id=\"visualization\" style=\"width: 600px; height: 400px\"></div>";
		
		
		$html .= "<h2>Übersicht der aufgetretenen Fehler in den Fragen</h2>";
		$html .= "<div class=\"infoBar\">Die folgenden Diagramme zeigen zu jeder Frage des Tests, wie viele Teilnehmer die richtigen oder falschen Antworten (a, b, c, ...) angekreuzt haben. Eine grüne Säule zeigt jeweils, wie häufig die Antwort korrekt gegeben wurde; eine rote Säule zeigt jeweils, wie oft die Antwort falsch gegeben wurde.</div>";
		$questions = $this->exam->get_questions();
		for ($i = 0; $i < count($questions); $i++) {
			$js .= "var data$i = new google.visualization.DataTable();";
			$js .= "data$i.addColumn('string', 'Antworten');";
        	$js .= "data$i.addColumn('number', 'richtig');";
        	$js .= "data$i.addColumn('number', 'falsch');";
        	$answers = $questions[$i]->get_answers();
        	$answer_count = count($answers);
        	$js .= "data$i.addRows($answer_count);";
        	for($j = 0; $j < count($answers); $j++) {
        		$answer = $answers[$j];
        		$count_fails = 0;
        		$count_success = 0;
        		for($k = 0; $k < count($finished_learners); $k++) {
        			$learner = $finished_learners[$k];
        			if ($answer->load_answer($learner) == 1) {
	        			$is_checked = true;
					} else {
						$is_checked = false;
					}
	        		if ($is_checked == $answer->is_correct()) {
						$correct_answer = true;
					} else {
						$correct_answer = false;
					}
					if (!$correct_answer) {
						$count_fails++;
					} else {
						$count_success++;
					}					
        		}
        		$js .= "data$i.setValue($j, 0, '" . chr($j+97) . "');";
        		$js .= "data$i.setValue($j, 1, $count_success);";
        		$js .= "data$i.setValue($j, 2, $count_fails);";
        	}
			$js .= "new google.visualization.ColumnChart(document.getElementById('question$i')).draw(data$i, {width: 350, height: 150, title:\"Frage ".($i+1)."\", colors:['#B4D745','#EC6259'], legend:'none'});";
			$html .= "<div style=\"display: none;\" id=\"overlay_$i\"></div><div id=\"questionwrapper_$i\"style=\"display: none;box-shadow: 2px 2px 3px #666;-webkit-box-shadow: 2px 2px 3px #666;-moz-box-shadow: #666 2px 2px 3px;\"><div id=\"elearning_exam\">". $questions[$i]->get_question_html(false, false, true) ."</div><div id=\"messageCloseButtonChart\" onclick=\"close_question($i)\">[ <a href=\"javascript:close_question($i)\">Schließen</a> ]</div></div>";
			$html .= "<div style=\"float: left;text-align:center\"><div id=\"question$i\" style=\"width: 350px; height: 150px;\"></div><a href=\"javascript:show_question($i)\">Frage ".($i+1)." anzeigen</a></div>";
		}
		
		$html .= "<br clear=\"all\">";
		
$js_show_question = <<< END

function show_question(i) {
	overlay = document.getElementById("overlay_" + i);
	overlay.style.display="block";
	overlay.style.height="100%";
	overlay.style.width="100%";
	overlay.style.position="absolute";
	overlay.style.top="0";
	overlay.style.left="0";
	overlay.style.backgroundColor="white";
	overlay.style.opacity="0.85";
	overlay.style.filter="alpha(opacity=85)";
	
	wrapper = document.getElementById("questionwrapper_" + i);
    wrapper.style.position="fixed";
    wrapper.style.width="750px";
    wrapper.style.height="300px";
    wrapper.style.left="50%";
    wrapper.style.top="50%";
    wrapper.style.overflow="auto";
    wrapper.style.marginLeft="-375px";
    wrapper.style.marginTop="-155px";
    wrapper.style.border="1px solid #ccc";
    wrapper.style.backgroundColor="#FFFFFF";
    wrapper.style.display="block";
    wrapper.style.opacity="1";
	wrapper.style.filter="alpha(opacity=100)";
}

function close_question(i) {
	overlay = document.getElementById("overlay_" + i);
	overlay.style.display="none";
	
	wrapper = document.getElementById("questionwrapper_" + i);
	wrapper.style.display="none";
}

END;
		
		lms_portal::get_instance()->add_javascript_code("elearning_chart", "function drawVisualization() {". $js."}google.setOnLoadCallback(drawVisualization);");
		lms_portal::get_instance()->add_javascript_code("elearning_chart", $js_show_question);
		lms_portal::get_instance()->set_page_main( array(array("link" => "javascript:history.back()", "name" => "zurück"),
									  			   array("link" => "", "name" => "Prüfung zum Kurs »" . $this->elearning_course->get_name() . "«")), $html, "");
		lms_portal::get_instance()->show_html();
	}

	private function get_reporting_html(){
		global $STYLE;
		$html = "";
		$html .= $this->get_reset_script_html();
		$html .= $this->get_clear_script_html();
		$html .= $this->get_toggleexam_script_html();
		if ($this->elearning_course->get_exam_by_type("final_exam")->is_global_enabled()) {
			$t = "ausschalten";
		} else {
			$t = "einschalten";
		}
		$html .= "<table class=\"grid\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\">
				 <tr>
					<th colspan=\"2\" class=\"group\">Einstellungen für die Prüfung</th>
				</tr>
				<tr>
					<td class=\"label\">Für Teilnehmer freischalten:</td>
					<td class=\"value\"><a href=\"javascript:toggleexam()\" id=\"toggleexam\">".$t."</a></td>
				</tr>";
		
		$html .="<tr>
					<th colspan=\"2\" class=\"group\">Teilnehmer</th>
				</tr>
				<tr>";
		$learners = $this->owner->get_learners();
		if (count($learners) == 0) {
			$html .= "<td colspan=\"2\" class=\"value\">diese Schulung hat zur Zeit keine Teilnehmer</td>";
		} else {
			foreach ($learners as $learner) {
				$html .= "<tr><td class=\"label\">".$learner->get_attribute( "USER_FIRSTNAME" )." ".$learner->get_attribute( "USER_FULLNAME" )."</td>";
				$html .= "<td class=\"value\" id=\"elearning_learner_".$learner->get_name()."\">";
				$el_learner = elearning_user::get_instance($learner->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
				$html .= $el_learner->get_status_HTML();
				if ($el_learner->has_exam_finished()) {
					$html .= " (<a href=\"javascript:resetexam('elearning_learner_".$learner->get_name()."','".$learner->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">freischalten</a>";
					$html .=" | <a href=\"javascript:clearexam('elearning_learner_".$learner->get_name()."','".$learner->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">alles zurücksetzen</a>)";
				}
				$html .= " (<a href=\"javascript:clearexam('elearning_learner_".$learner->get_name()."','".$learner->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">alles zurücksetzen</a>)</td></tr>";
			}
		}
		
		$html .="<tr>
					<th colspan=\"2\" class=\"group\">Betreuer</th>
				</tr>
				<tr>";
		$admins = $this->owner->get_admins();
		if (count($admins) == 0) {
			$html .= "<td colspan=\"2\" class=\"value\">diese Schulung hat zur Zeit keine Betreuer</td>";
		} else {
			foreach ($admins as $admin) {
				$html .= "<tr><td class=\"label\">".$admin->get_attribute( "USER_FIRSTNAME" )." ".$admin->get_attribute( "USER_FULLNAME" )."</td>";
				$html .= "<td class=\"value\" id=\"elearning_admin_".$admin->get_name()."\">";
				$el_admin = elearning_user::get_instance($admin->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
				$html .= $el_admin->get_status_HTML();
				if ($el_admin->has_exam_finished()) {
					$html .= "(<a href=\"javascript:resetexam('elearning_admin_".$admin->get_name()."','".$admin->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">freischalten</a>";
					$html .=" | <a href=\"javascript:clearexam('elearning_admin_".$admin->get_name()."','".$admin->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">alles zurücksetzen</a>)";
				} 
				$html .= " (<a href=\"javascript:clearexam('elearning_admin_".$admin->get_name()."','".$admin->get_name()."','" .$this->elearning_course->get_id(). "','" .$this->exam->get_id(). "')\">alles zurücksetzen</a>)</td></tr>";
				$html .= "</td></tr>";
			}
		}
		
		$html .= "</tr></table>";
		$html .= "Erweiterungsversion: " . units_elearning::get_version() . "<br>";
		$html .= "Kursversion: " . $this->elearning_course->get_version();
		return $html;
	} 
	
	private function get_reset_script_html(){
		$pathprefix = "../../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/";
		$html = "";
		$html .= "<script type=\"text/javascript\">
		function resetexam(html_id, user_id, cid, exam_id) {
		apath = \"".$pathprefix."directaccess\";
		new Ajax.Request(apath,
			  {
			    method:'post',
			    parameters: \"case=resetexam&cid=\" + cid + \"&exam_id=\" + exam_id + \"&user_id=\" +user_id + \"&html_id=\" +html_id,
			    onFailure: function(){ alert('Error while telling answer.'); },
			    onSuccess: function(response){updatetable(response);}
			  });
		}
		function updatetable(response) {
				var values = response.responseText.split('@@');
        		document.getElementById(values[0]).innerHTML=(\"-\");
			
		}
		</script>";
		return $html;
	}
	
	private function get_clear_script_html(){
		$pathprefix = "../../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/";
		$html = "";
		$html .= "<script type=\"text/javascript\">
		function clearexam(html_id, user_id, cid, exam_id) {
		apath = \"".$pathprefix."directaccess\";
		new Ajax.Request(apath,
			  {
			    method:'post',
			    parameters: \"case=clearexam&cid=\" + cid + \"&exam_id=\" + exam_id + \"&user_id=\" +user_id + \"&html_id=\" +html_id,
			    onFailure: function(){ alert('Error while telling answer.'); },
			    onSuccess: function(response){clearexam_success(response);}
			  });
		}
		function clearexam_success(response) {
				var values = response.responseText.split('@@');
        		document.getElementById(values[0]).innerHTML=(\"-\");
			
		}
		</script>";
		return $html;
	}
	
	private function get_toggleexam_script_html() {
		$pathprefix = "../../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/";
		$html = "";
		$html .= "<script type=\"text/javascript\">
		function toggleexam() {
		apath = \"".$pathprefix."directaccess\";
		new Ajax.Request(apath,
			  {
			    method:'post',
			    parameters: \"case=toggleexam\",
			    onFailure: function(){ alert('Error while telling answer.'); },
			    onSuccess: function(response){updateanker(response);}
			  });
		}
		function updateanker(response) {
				var values = response.responseText.split('@@');
				if (values[0] == \"true\") {
        			document.getElementById(\"toggleexam\").innerHTML=(\"ausschalten\");
        		} else {
        			document.getElementById(\"toggleexam\").innerHTML=(\"einschalten\");
				}
			
		}
		</script>";
		return $html;
	}
	
	function get_exam() {
		return $this->exam;
	}
	
}
?>