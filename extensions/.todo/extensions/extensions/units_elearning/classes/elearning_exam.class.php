<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_certificate.class.php");

class elearning_exam extends elearning_object {
	
	private $type;
	private $myUser;
	
	function __construct($parent_tmp, $steamObject_tmp, $type) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		$this->type = $type;
		$this->myUser = lms_steam::get_current_user();
		//get meta data
		$doc = steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), $this->steamObject->get_path() . "/exam.xml");
		$this->xml = simplexml_load_string($doc->get_content());
	}
	
	function get_type() {
		return $this->type;	
	}
	
	function get_epilogue() {
		return (string)$this->xml->epilogue;
	}
	
	function get_threshold() {
		return (integer)$this->xml->threshold;
	}
	
	function get_html() {
		if (!$this->is_finished()) {
			// set onload js to portal
			$mediathek = elearning_mediathek::get_instance();
/*
 * **********************************************
 * ********* CSS STYLE **************************
 * **********************************************
 */			
			$css = <<< END
#loading_overlay {
	display:block;
	height:100%;
	width:100%;
	position:absolute;
	top:0;
	left:0;
	background-color:white;
	opacity:0.75;
	filter: alpha(opacity=75);
	z-index: 300;
}

#loader img{
	left:50%;
	margin-left:-17px;
	margin-top:-17px;
    position:fixed;
    top:50%;
}
		
.q {
	display:none;
	opacity: 0;
}

.q_right {
	display:none;
	opacity: 0;
}

.q_wrong {
	display:none;
	opacity: 0;
}

.q_active {
	display: block;
	opacity: 0; 
}

.green{
	display:block;
	position:absolute;
	top:0px;
	left:0px;
	z-index:1;
	width:0px;
	height:25px;
	background-color:#A5A12B;
}

.progressbar{
	font-family: 'Lucida Grande', Verdana, Arial, Helvetica, sans-serif;
	font-size: 9px;
	position: relative;
	top:50px;
	left:210px;
}

.progressbar_left{
	position:absolute;
	top:0px;
	left:0px;
	height:26px;
	width:13px;
	z-index:2;
	background-image:url(/styles/stahl-orange/images/pr_l.png);
}

.progressbar_middle{
	position:absolute;
	padding-top:5px;
	text-align:center;
	top:0px;
	left:13px;
	height:21px;
	width:250px;
	z-index:2;
	background-image:url(/styles/stahl-orange/images/pr_m.png);
}

.progressbar_right{
	position:absolute;
	top:0px;
	left:263px;
	height:26px;
	width:13px;
	z-index:2;
	background-image:url(/styles/stahl-orange/images/pr_r.png);
}
END;
			lms_portal::get_instance()->add_css_style($css);
/*
 * **********************************************
 * ********* ONLOAD JAVASCRIPT ******************
 * **********************************************
 */	
			$js_onload = <<< END
document.getElementById('loading_overlay').style.display = 'none';
showQuestion(1);
END;
			lms_portal::get_instance()->add_javascript_onload("elearning_exam",$js_onload);
/*
 * **********************************************
 * ********* JAVASCRIPT *************************
 * **********************************************
 */	
			$js_code = <<< END
function hide_all_questions() {
	var allElems = document.getElementsByTagName('div');
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && thisElem.className == 'q_active') {
			thisElem.className = 'q';
			thisElem.style.display = "none";
			thisElem.style.opacity = 0;
		}
	} 
}

function showQuestion(qnr) {
	setprogress((qnr / 19)*100);
	hide_all_questions();
	document.getElementById('q_'+qnr).className = 'q_active';
	document.getElementById('q_'+qnr).style.display = "block";
	Effect.Fade('q_'+qnr, { duration: 1.0, from: 0, to: 0.9 });
	updateButtons();
}

function actualQuestion() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && thisElem.className == 'q') {
			j++;
		} else if (thisElem.className && thisElem.className == 'q_active') {
			j++;
			return j;
		}
	}
}

function countQuestions() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && (thisElem.className == 'q' || thisElem.className && thisElem.className == 'q_active')) {
			j++;
		}
	}
	return j;
}

function showNextQuestion() {
	showQuestion(actualQuestion() + 1);
}

function showPrevQuestion() {
	showQuestion(actualQuestion() - 1);
}

function updateButtons() {
	if (actualQuestion() == 1) {
		document.images['img_prev'].style.display = 'none';
		document.images['img_next'].style.display = 'block';
		document.getElementById('finish_button').style.display = 'none';
		document.getElementById('back_button').style.display = 'none';
	} else if (actualQuestion() == countQuestions()) {
		document.images['img_prev'].style.display = 'block';
		document.images['img_next'].style.display = 'none';
		document.getElementById('finish_button').style.display = 'block';
		document.getElementById('back_button').style.display = 'block';
	} else {
		document.images['img_prev'].style.display = 'block';
		document.images['img_next'].style.display = 'block';
		document.getElementById('finish_button').style.display = 'none';
		document.getElementById('back_button').style.display = 'none';
	}
}

function setprogress(i) {
	i = Math.round(i);
	if (i > 100) {
		i = 100;
	}
	if (i < 0) {
		i = 0;
	}
	var min = 4;
	var max = 277;
	var stepsize = (max - min) / 100;
	document.getElementById("green").style.width = Math.round(i * stepsize) + "px";
	document.getElementById("progressbar_middle").innerHTML = "Fortschritt " + i + "%";
}

END;
			
			lms_portal::get_instance()->add_javascript_code("elearning_exam", $js_code);
			
			$html  = "<div class=\"printonly\" id=\"noprint_exam\"><b>Die Prüfung kann nicht gedruckt werden.</b></div><div id=\"loading_overlay\"><div id=\"loader\"><img src=\"/styles/stahl-orange/images/loader_gr.gif\" /></div></div>";
			$html .= "<div class=\"noprint\" id=\"elearning_exam\">";
			$html .= "<h1>" . $this->get_name() . "</h1>";
			$html .= "<img style=\"float:right\" src=\"/styles/stahl-orange/images/exam.jpg\" />";
			$html .= "<p>" . $this->get_description() . "</p>";
			$html .= "<div class=\"progressbar\">
					<div class=\"green\" id=\"green\"/></div>
					<div class=\"progressbar_left\"></div>
					<div class=\"progressbar_middle\" id=\"progressbar_middle\">Fortschritt 0%</div>
					<div class=\"progressbar_right\"></div>
			</div><br clear=\"all\"/>";
			$html .= "<table style=\"width:100%;\"><tr><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;display:none;margin-top:50px;\" name=\"img_prev\" onclick=\"showPrevQuestion()\" onmouseover=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev.png';\" src=\"/styles/stahl-orange/images/prev.png\"></td><td style=\"width:100%;text-align:left\">";
			$html .= $this->get_questions_exam_html();
			$html .= "</td><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;margin-top:50px\" name=\"img_next\" onclick=\"showNextQuestion()\" onmouseover=\"document.images['img_next'].src='/styles/stahl-orange/images/next_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_next'].src='/styles/stahl-orange/images/next.png';\" src=\"/styles/stahl-orange/images/next.png\"></td></table>";
	 		$html .= "</div>";
	 		$html .= "
			<div id=\"overlay\"></div>
			<div id=\"message\">
	    		<div id=\"messagetext\"></div>
	    		<div id=\"messageCloseButton\" onclick=\"closeMessageWindow()\">[<a href=\"javascript:closeMessageWindow()\">Schließen</a>]</div>
			</div>
			";
	 		
	 		$pathprefix = "../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/";
	 		$html .= "<script type=\"text/javascript\">
	 		function finishexam() {
				apath = \"".$pathprefix."directaccess\";
				new Ajax.Request(apath,
					  {
					    method:'post',
					    parameters: \"case=finishexam&&eid=" .$this->get_id()."\",
					    onFailure: function(){ alert('Error while storing answer.'); },
					    onSuccess: function(response){window.location.reload();}
					  });
				}
	 		</script>";
	 		$html .= "<a style=\"display:none;\" class=\"back_button\" id=\"back_button\" href=\"javascript:showQuestion(1);\">Zur ersten Frage</a>";
	 		$html .= "<a style=\"display:none;\" class=\"finish_button\" id=\"finish_button\" href=\"javascript:if (confirm('Wollen Sie die Prüfung jetzt wirklich abschließen?')) {document.getElementById('loading_overlay').style.display = 'block';finishexam();}\">Prüfung abschließen</a>";
			return $html;
		} else {
			// set onload js to portal
			$mediathek = elearning_mediathek::get_instance();
/*
 * **********************************************
 * ********* CSS STYLE **************************
 * **********************************************
 */			
			$css = <<< END
#loading_overlay {
	display:block;
	height:100%;
	width:100%;
	position:absolute;
	top:0;
	left:0;
	background-color:white;
	opacity:0.75;
	filter: alpha(opacity=75);
	z-index: 300;
}

#loader img{
	left:50%;
	margin-left:-17px;
	margin-top:-17px;
    position:fixed;
    top:50%;
}
		
.q {
	display:none;
	opacity: 0;
}

.q_right {
	display:none;
	opacity: 0;
}

.q_wrong {
	display:none;
	opacity: 0;
}

.q_active {
	display: block;
	opacity: 0; 
}
END;
			lms_portal::get_instance()->add_css_style($css);
			
/*
 * **********************************************
 * ********* ONLOAD JAVASCRIPT ******************
 * **********************************************
 */	
			$js_onload = <<< END
showQuestion(1);
END;
			lms_portal::get_instance()->add_javascript_onload("elearning_exam",$js_onload);
/*
 * **********************************************
 * ********* JAVASCRIPT *************************
 * **********************************************
 */	
			$js_code = <<< END
function hide_all_questions() {
	var allElems = document.getElementsByTagName('div');
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && thisElem.className == 'q_right_active') {
			thisElem.className = 'q_right';
			thisElem.style.opacity = 0;
			//Effect.Hide(thisElem.id);
		} else if (thisElem.className && thisElem.className == 'q_wrong_active') {
			thisElem.className = 'q_wrong';
			thisElem.style.opacity = 0;
			//Effect.Hide(thisElem.id);
		}
	} 
}

function showQuestion(qnr) {
	if (countWorngQuestions() == 0) {
		hide_all_questions();
		document.images['img_prev'].style.display = 'none';
		document.images['img_next'].style.display = 'none';
		document.getElementById('question_headline').innerHTML = 'Sie haben alle Fragen richtig beantwortet!';
		return;
	}

	if (countWorngQuestions() == 1) {
		document.getElementById('question_headline').innerHTML = 'Nicht korrekt beantwortete Frage';
	} else {
		document.getElementById('question_headline').innerHTML = 'Nicht korrekt beantwortete Fragen';
	}
	
	if (qnr > countQuestions()) {
		return;
	}
	if (document.getElementById('q_'+qnr).className == 'q_right') {
		if (actualQuestion() > qnr) {
			showQuestion(qnr-1);
		} else {
			showQuestion(qnr+1);
		}
		return;
	} 
	
	hide_all_questions();
	document.getElementById('q_'+qnr).className = document.getElementById('q_'+qnr).className + '_active';
	Effect.Appear('q_'+qnr);
	updateButtons();
}

function actualQuestion() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && (thisElem.className == 'q_right' || thisElem.className == 'q_wrong')) {
			j++;
		} else if (thisElem.className && (thisElem.className == 'q_right_active' || thisElem.className == 'q_wrong_active')) {
			j++;
			return j;
		}
	}
}

function countQuestions() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && (thisElem.className == 'q_right' || thisElem.className == 'q_wrong' || thisElem.className && thisElem.className == 'q_right_active' || thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
		}
	}
	return j;
}

function countWorngQuestions() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
		}
	}
	return j;
}

function showNextQuestion() {
	showQuestion(actualQuestion() + 1);
}

function showPrevQuestion() {
	showQuestion(actualQuestion() - 1);
}

function updateButtons() {
	if (hasPrevWrongQuestion()) {
		document.images['img_prev'].style.display = 'block';
	} else {
		document.images['img_prev'].style.display = 'none';
	}
	
	if (hasNextWrongQuestion()) {
		document.images['img_next'].style.display = 'block';
	} else {
		document.images['img_next'].style.display = 'none';
	}
}

function hasPrevWrongQuestion() {
	var allElems = document.getElementsByTagName('div');
	var j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_right') || (thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_right_active') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
			if (j == actualQuestion()) {
				for (var k = i-1; k >= 0; k--) {
					var nthisElem = allElems[k];
					if ((nthisElem.className && nthisElem.className == 'q_wrong') || (nthisElem.className && nthisElem.className == 'q_wrong_active')) {
						return true;
					}
				}
				return false;
			}
		}
	}
	return false;
}

function hasNextWrongQuestion() {
	var allElems = document.getElementsByTagName('div');
	var j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_right') || (thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_right_active') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
			if (j == actualQuestion()) {
				for (var k = i+1; k < allElems.length; k++) {
					var nthisElem = allElems[k];
					if ((nthisElem.className && nthisElem.className == 'q_wrong') || (nthisElem.className && nthisElem.className == 'q_wrong_active')) {
						return true;
					}
				}
				return false;
			}
		}
	}
	return false;
}

END;
			
			lms_portal::get_instance()->add_javascript_code("elearning_exam", $js_code);
			
			$html = "<div class=\"printonly\" id=\"noprint_exam\"><b>Die Prüfungsergbnisse können nicht gedruckt werden.</b></div><div class=\"noprint\" id=\"elearning_exam\">";
			$elearning_user = elearning_user::get_instance($this->myUser->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
			
			if ($elearning_user->has_exam_passed()) {
				
				$html .= "<h2>Herzlichen Glückwunsch!</h2>";
				$html .= "Sie haben bei der Prüfung zum Kurs »".$this->get_parent()->get_name()."« <b>". $elearning_user->get_exam_sum_score() ." von ". $elearning_user->get_exam_sum_points() ."</b> möglichen Punkten erreicht. Dieses Prüfungsergebnis wird auch den Ansprechpartnern zu dem Kurs angezeigt.<br/><br/>";
				$html .= "Zur abgelegten Prüfung haben Sie ein Zertifikat erhalten, das Sie auch später noch auf Ihrer persönlichen Einstiegsseite abrufen können. Klicken Sie auf das Vorschaubild des Zertifikats bzw. auf »Herunterladen«, wenn Sie es auf Ihrem Computer speichern oder ausdrucken möchten.<br/><br/>";
				$html .= "Falls Sie nicht alle Fragen richtig beantwortet haben, sehen Sie im Folgenden die nicht vollständig korrekt beantworteten Fragen. Sie können auch später erneut auf »Prüfung« klicken, um sich die nicht vollständig korrekt beantworteten Fragen anzeigen zu lassen.<br/><br/>";
				$html .= "Wenn Sie zum Kurs oder zu der Prüfung eine Frage haben, können Sie auf den Namen eines Ansprechpartners für diesen Kurs klicken, um mit ihm bzw. ihr in Kontakt zu treten.";
				$html .= "<div style=\"float:right;margin-top:15px;text-align:center\"><a href=\"" . "/download/" . $elearning_user->get_exam_cert()->get_id() . "/" . $elearning_user->get_exam_cert()->get_name() . "\"><img src=\"" . "/download/" . $elearning_user->get_exam_cert_preview()->get_id() . "/" . $elearning_user->get_exam_cert_preview()->get_name() . "\" /><br /> <small>Herunterladen</a></small></div>";
			} else {
				$html .= "<h2>Leider hat es nicht gereicht!</h2>";
				$html .= "Sie haben bei der Prüfung zum Kurs »".$this->get_parent()->get_name()."« die benötigten Punkte nicht erreicht. Dieses Prüfungsergebnis wird auch den Ansprechpartnern zu dem Kurs angezeigt.<br/><br/>";
				$html .= "Im Folgenden können Sie sich die nicht vollständig korrekt beantworteten Fragen ansehen. Sie können auch später erneut auf »Prüfung« klicken, um sich die nicht vollständig korrekt beantworteten Fragen anzeigen zu lassen. Auch die Inhalte des Kurses können Sie jederzeit anschauen und sich mit den Inhalten noch einmal beschäftigen.<br/><br/>";
				$html .= "Wenn Sie zum Kurs oder zu der Prüfung eine Frage haben, können Sie auf den Namen eines Ansprechpartners für diesen Kurs klicken, um mit ihm bzw. ihr in Kontakt zu treten. Er bzw. sie wird Ihnen auch mitteilen, wie Sie die Prüfung zu gegebener Zeit noch einmal ablegen können.";
			}
			
			global $course;
			$content = new HTML_TEMPLATE_IT();
			$content->loadTemplateFile( PATH_EXTENSIONS . "units_elearning/templates/exam.staff.template.html" );
			$admins = $course->get_staff();
			$hidden_members = $course->get_steam_group()->get_attribute("COURSE_HIDDEN_STAFF");
			if (!is_array($hidden_members)) $hidden_members = array();
			
			$visible_staff = 0;
			foreach( $admins as $admin ) {
			  if( ! in_array( $admin->get_id(), $hidden_members ))
			  {
			    $content->setCurrentBlock( "BLOCK_ADMIN" );
			    if (COURSE_START_ADMIN_PROFILE_ANKER) {
			    	$content->setCurrentBlock( "PROFILE_ANKER" );
			    } else {
			    	$content->setCurrentBlock( "PROFILE_NO_ANKER" );
			    }
			    $admin_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON", "OBJ_DESC", "OBJ_NAME" ) );
			    if ( $admin instanceof steam_user )
			    {
			      $content->setVariable( "ADMIN_NAME", $admin_attributes[ "USER_FIRSTNAME" ] . " " . $admin_attributes[ "USER_FULLNAME" ] );
			      (!COURSE_START_ADMIN_PROFILE_ANKER) or  $content->setVariable( "ADMIN_LINK", PATH_URL . "user/" . $admin->get_name() . "/" );
			    }
			    else
			    {
			      $content->setVariable( "ADMIN_NAME", $admin_attributes[ "OBJ_NAME" ] );
			      (!COURSE_START_ADMIN_PROFILE_ANKER) or $content->setVariable( "ADMIN_LINK", PATH_URL . "groups/" . $admin->get_id() . "/" );
			    }
			    $icon_link = ( is_object( $admin_attributes[ "OBJ_ICON" ] ) ) ? PATH_URL . "cached/get_document.php?id=" . $admin_attributes[ "OBJ_ICON" ]->get_id() . "&type=usericon&width=40&height=47" : PATH_STYLE . "images/anonymous.jpg";
			    $content->setVariable( "ADMIN_ICON", $icon_link );
			
				$adminDescription = $admin_attributes["OBJ_DESC"];
				switch ($adminDescription){
					case "student":$adminDescription = gettext("student");break;
					case "staff member":$adminDescription = gettext("staff member");break;
					case "alumni":$adminDescription = gettext("alumni");break;
					case "guest":$adminDescription = gettext("guest");break;
					case "":$adminDescription = gettext("student");break;
					default:break;
				}
				
				if (COURSE_START_SEND_MESSAGE && (!COURSE_SHOW_ONLY_EXTERN_MAIL || (COURSE_SHOW_ONLY_EXTERN_MAIL && is_string($admin->get_attribute("USER_EMAIL")) && ($admin->get_attribute("USER_EMAIL")) != "") && ($admin->get_attribute("USER_FORWARD_MSG") === 1)) ) {
					$adminDescription = $adminDescription . " - <a href=\"/messages_write.php?to=".$admin->get_name()."\">".gettext("Nachricht senden")."</a>";
				}
				
			    $content->setVariable( "ADMIN_DESC", $adminDescription );
			    if (COURSE_START_ADMIN_PROFILE_ANKER) {
			    	$content->parse( "PROFILE_ANKER" );
			    } else {
			    	$content->parse( "PROFILE_NO_ANKER" );
			    }
			    $content->parse( "BLOCK_ADMIN" );
			    $visible_staff++;
			  }
			}
			if ($visible_staff > 0) {
			  $content->setCurrentBlock("BLOCK_ADMIN_HEADER");
			  $content->setVariable( "LABEL_ADMINS", gettext( "Staff members" ) );
			  $content->parse("BLOCK_ADMIN_HEADER");
			}
			$html.= $content->get();
			
			$html .= "<br clear=\"all\"><h3 id=\"question_headline\"></h3>";
			$html .= "<table style=\"width:100%;\"><tr><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;display:none;margin-top:50px;\" name=\"img_prev\" onclick=\"showPrevQuestion()\" onmouseover=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev.png';\" src=\"/styles/stahl-orange/images/prev.png\"></td><td style=\"width:100%;text-align:left\">";
			$html .= $this->get_questions_exam_result_html();
			$html .= "</td><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;margin-top:50px\" name=\"img_next\" onclick=\"showNextQuestion()\" onmouseover=\"document.images['img_next'].src='/styles/stahl-orange/images/next_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_next'].src='/styles/stahl-orange/images/next.png';\" src=\"/styles/stahl-orange/images/next.png\"></td></table>";
			$html .= "</div>";
			return $html;
		}
	}
	
	public function get_report_html($user) {
			$mediathek = elearning_mediathek::get_instance();
/*
 * **********************************************
 * ********* CSS STYLE **************************
 * **********************************************
 */			
			$css = <<< END
#loading_overlay {
	display:block;
	height:100%;
	width:100%;
	position:absolute;
	top:0;
	left:0;
	background-color:white;
	opacity:0.75;
	filter: alpha(opacity=75);
	z-index: 300;
}

#loader img{
	left:50%;
	margin-left:-17px;
	margin-top:-17px;
    position:fixed;
    top:50%;
}
		
.q {
	display:none;
	opacity: 0;
}

.q_right {
	display:none;
	opacity: 0;
}

.q_wrong {
	display:none;
	opacity: 0;
}

.q_active {
	display: block;
	opacity: 0; 
}
END;
			lms_portal::get_instance()->add_css_style($css);
			
/*
 * **********************************************
 * ********* ONLOAD JAVASCRIPT ******************
 * **********************************************
 */	
			$js_onload = <<< END
showQuestion(1);
END;
			lms_portal::get_instance()->add_javascript_onload("elearning_exam",$js_onload);
/*
 * **********************************************
 * ********* JAVASCRIPT *************************
 * **********************************************
 */	
			$js_code = <<< END
function hide_all_questions() {
	var allElems = document.getElementsByTagName('div');
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && thisElem.className == 'q_right_active') {
			thisElem.className = 'q_right';
			thisElem.style.opacity = 0;
			//Effect.Hide(thisElem.id);
		} else if (thisElem.className && thisElem.className == 'q_wrong_active') {
			thisElem.className = 'q_wrong';
			thisElem.style.opacity = 0;
			//Effect.Hide(thisElem.id);
		}
	} 
}

function showQuestion(qnr) {
	if (countWorngQuestions() == 0) {
		hide_all_questions();
		document.images['img_prev'].style.display = 'none';
		document.images['img_next'].style.display = 'none';
		document.getElementById('question_headline').innerHTML = 'Sie haben alle Fragen richtig beantwortet!';
		return;
	}

	if (countWorngQuestions() == 1) {
		document.getElementById('question_headline').innerHTML = 'Nicht korrekt beantwortete Frage';
	} else {
		document.getElementById('question_headline').innerHTML = 'Nicht korrekt beantwortete Fragen';
	}
	
	if (qnr > countQuestions()) {
		return;
	}
	if (document.getElementById('q_'+qnr).className == 'q_right') {
		if (actualQuestion() > qnr) {
			showQuestion(qnr-1);
		} else {
			showQuestion(qnr+1);
		}
		return;
	} 
	
	hide_all_questions();
	document.getElementById('q_'+qnr).className = document.getElementById('q_'+qnr).className + '_active';
	Effect.Appear('q_'+qnr);
	updateButtons();
}

function actualQuestion() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && (thisElem.className == 'q_right' || thisElem.className == 'q_wrong')) {
			j++;
		} else if (thisElem.className && (thisElem.className == 'q_right_active' || thisElem.className == 'q_wrong_active')) {
			j++;
			return j;
		}
	}
}

function countQuestions() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if (thisElem.className && (thisElem.className == 'q_right' || thisElem.className == 'q_wrong' || thisElem.className && thisElem.className == 'q_right_active' || thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
		}
	}
	return j;
}

function countWorngQuestions() {
	var allElems = document.getElementsByTagName('div');
	j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
		}
	}
	return j;
}

function showNextQuestion() {
	showQuestion(actualQuestion() + 1);
}

function showPrevQuestion() {
	showQuestion(actualQuestion() - 1);
}

function updateButtons() {
	if (hasPrevWrongQuestion()) {
		document.images['img_prev'].style.display = 'block';
	} else {
		document.images['img_prev'].style.display = 'none';
	}
	
	if (hasNextWrongQuestion()) {
		document.images['img_next'].style.display = 'block';
	} else {
		document.images['img_next'].style.display = 'none';
	}
}

function hasPrevWrongQuestion() {
	var allElems = document.getElementsByTagName('div');
	var j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_right') || (thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_right_active') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
			if (j == actualQuestion()) {
				for (var k = i-1; k >= 0; k--) {
					var nthisElem = allElems[k];
					if ((nthisElem.className && nthisElem.className == 'q_wrong') || (nthisElem.className && nthisElem.className == 'q_wrong_active')) {
						return true;
					}
				}
				return false;
			}
		}
	}
	return false;
}

function hasNextWrongQuestion() {
	var allElems = document.getElementsByTagName('div');
	var j = 0;
	for (var i = 0; i < allElems.length; i++) {
		var thisElem = allElems[i];
		if ((thisElem.className && thisElem.className == 'q_right') || (thisElem.className && thisElem.className == 'q_wrong') || (thisElem.className && thisElem.className == 'q_right_active') || (thisElem.className && thisElem.className == 'q_wrong_active')) {
			j++;
			if (j == actualQuestion()) {
				for (var k = i+1; k < allElems.length; k++) {
					var nthisElem = allElems[k];
					if ((nthisElem.className && nthisElem.className == 'q_wrong') || (nthisElem.className && nthisElem.className == 'q_wrong_active')) {
						return true;
					}
				}
				return false;
			}
		}
	}
	return false;
}

END;
			
			lms_portal::get_instance()->add_javascript_code("elearning_exam", $js_code);
			$elearning_user = elearning_user::get_instance($this->myUser->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
			
			$html = "<div class=\"printonly\" id=\"noprint_exam\"><b>Der Prüfungsbericht kann nicht gedruckt werden.</b></div><div class=\"noprint\" id=\"elearning_exam\">";
			$html .= "<h2>Prüfungsbericht " . $user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME") . "</h2>";
			if ($elearning_user->has_exam_passed()) {
				$html .= "Der Nutzer " . $user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME") . " (" . $user->get_name() . ") hat den Kurs »".$this->get_parent()->get_name()."« mit <b>". $elearning_user->get_exam_sum_score() ." von ". $elearning_user->get_exam_sum_points() ."</b> möglichen Punkten <b>bestanden</b>.";
			} else {
				$html .= "Der Nutzer " . $user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME") . " (" . $user->get_name() . ") hat den Kurs »".$this->get_parent()->get_name()."« mit <b>". $elearning_user->get_exam_sum_score() ." von ". $elearning_user->get_exam_sum_points() ."</b> möglichen Punkten <b>nicht bestanden</b>.";
			}
			$html .= "<br clear=\"all\"><h3 id=\"question_headline\"></h3>";
			$html .= "<table style=\"width:100%;\"><tr><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;display:none;margin-top:50px;\" name=\"img_prev\" onclick=\"showPrevQuestion()\" onmouseover=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_prev'].src='/styles/stahl-orange/images/prev.png';\" src=\"/styles/stahl-orange/images/prev.png\"></td><td style=\"width:100%;text-align:left\">";
			$html .= $this->get_questions_exam_result_html($user);
			$html .= "</td><td style=\"width:0%;vertical-align:top\"><img style=\"width:44px;height:44px;margin-top:50px\" name=\"img_next\" onclick=\"showNextQuestion()\" onmouseover=\"document.images['img_next'].src='/styles/stahl-orange/images/next_hover.png';style.cursor='pointer'\" onmouseout=\"document.images['img_next'].src='/styles/stahl-orange/images/next.png';\" src=\"/styles/stahl-orange/images/next.png\"></td></table>";
			$html .= "</div>";
			return $html;
	}
	
	
	private function exec_enabled() {
	  $disabled = explode(', ', ini_get('disable_functions'));
	  return !in_array('exec', $disabled);
	}
	
	public function get_questions($user = null) {
		isset($user) or $user = $this->myUser;
		$array = $this->get_xmlhelper()->xml_to_array($this->xml->playlist->array);
		$questions = array();
		foreach ($array as $item) {
			$question = $this->create_question($item["id"], $user);
			$questions[] = $question;
		}
		return $questions;
	}
	
	private function get_questions_exam_html() {
		$questions = $this->get_questions();
		if ($questions != null && count($questions) > 0) {
			$html = $questions[0]->get_question_script_exam_html("../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/", $this->get_id());
			$i = 0;
			$j = count($questions);
			foreach ($questions as $question) {
				$i++;
				$html .= $question->get_question_exam_html(true, false, $i, $j);
			}
			$html .= "<div style=\"margin:25px\" class=\"q\" id=\"q_" . ($j+1) . "\">".$this->get_epilogue()."<img style=\"float:right\" src=\"/styles/stahl-orange/images/exam_end.jpg\" /></div>";
			return $html;
		}
	}
	
	private function get_questions_exam_result_html($user = null) {
		isset($user) or $user = $this->myUser;
		$questions = $this->get_questions($user);
		if ($questions != null && count($questions) > 0) {
			$html = $questions[0]->get_question_script_exam_html("../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/", $this->get_id());
			$i = 0;
			$j = count($questions);
			foreach ($questions as $question) {
				$i++;
				$html .= $question->get_question_exam_html(true, true, $i, $j);
			}
			return $html;
		}
	}
	
	private function create_question($id, $user = null) {
		isset($user) or $user = $this->myUser;
		$elements = explode(".", $id);
		return $this->parent->get_chapter_by_id($elements[2])->get_question_by_id($elements[4], $user);
	}
	
	function is_finished($user = null) {
		isset($user) or $user = $this->myUser;
		$elearning_user = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
		
		return $elearning_user->has_exam_finished();
	}
	
	function set_finished($finished, $user = null) {
		isset($user) or $user = $this->myUser;
		$elearning_user = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
		if ($finished) {
			$elearning_user->set_exam_finished();
			$elearning_user->set_exam_sum_points($this->sum_points());
			$elearning_user->set_exam_sum_score($this->sum_score());
			
			if (($elearning_user->get_exam_sum_score() / $elearning_user->get_exam_sum_points())*100 >= $this->get_threshold()) {
				$elearning_user->set_exam_passed();
			} else {
				$elearning_user->set_exam_passed(false);
			}
			
			$this->generate_certificate($user);
			
		} else {
			$elearning_user->set_exam_finished(false);
		}
	}
	
	function clear_finished($user = null) {
		isset($user) or $user = $this->myUser;
		$user->delete_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_FINISHED");
	}
	
	function is_global_enabled() {
		$attribute = $this->get_parent()->get_steam_object()->get_attribute("ELEARNING_UNIT_EXAM_ENABLED");
		if ($attribute === "true") {
			return true;
		} else {
			return false;
		}
	}
	
	function set_global_enabled($enabled) {
		if ($enabled) {
			 $this->get_parent()->get_steam_object()->set_attribute("ELEARNING_UNIT_EXAM_ENABLED", "true");
		} else {
			 $this->get_parent()->get_steam_object()->set_attribute("ELEARNING_UNIT_EXAM_ENABLED", "false");
		}
	}
	
/*	function is_enabled($user = null) {
		if (!$this->is_global_enabled()) {
			return false;
		}
		isset($user) or $user = $this->myUser;
		
		$mediathek = elearning_mediathek::get_instance();
		$course = $mediathek->get_course();
		if (isset($course) && ($course instanceof koala_group_course) && $course->is_staff($user)) {
			return true;
		}
		
		
		$attribute = $user->get_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_ENABLED");
		if ($attribute === "true") {
			return true;
		}
		return false;	
	}
	
	function set_enabled($enabled, $user = null) {
		isset($user) or $user = $this->myUser;
		if ($enabled) {
			$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_ENABLED", "true");
		} else {
			$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_ENABLED", "false");
		}
	}
	
	function clear_enabled($user = null) {
		isset($user) or $user = $this->myUser;
		$user->delete_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_ENABLED");
	}
	
	function is_passed($user = null) {
		isset($user) or $user = $this->myUser;
		$attribute = $user->get_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_PASSED");
		if ($attribute === "true") {
			return true;
		}
		return false;	
	}
	
	function set_passed($user = null) {
		isset($user) or $user = $this->myUser;
		if (($this->get_sum_score() / $this->get_sum_points())*100 > $this->get_threshold()) {
			$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_PASSED", "true");
		} else {
			$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_PASSED", "false");
		}
	}
	
	function clear_passed($user = null) {
		isset($user) or $user = $this->myUser;
		$user->delete_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_PASSED");
	}
	
	function set_sum_points() {
		$user = $this->myUser;
		$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_POINTS", $this->sum_points());
	}
	
	function get_sum_points($user = null) {
		isset($user) or $user = $this->myUser;
		$attribute = $user->get_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_POINTS");
		if (is_int($attribute)) {
			return $attribute;
		}
		return 0;	
	}
	
	function clear_sum_points($user = null) {
		isset($user) or $user = $this->myUser;
		$user->delete_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_POINTS");
	}*/
	
	private function sum_points() {
		$questions = $this->get_questions();
		$sum = 0;
		foreach($questions as $question) {
			$answers = $question->get_answers();
			foreach($answers as $answer){
				//if ($answer->is_correct()) {
					//error_log($question->get_id() . " " . $answer->get_id());
					$sum += $answer->get_score_correct();
				//}
			}
		}
		return $sum;
	}
	
/*	function set_sum_score() {
		$user = $this->myUser;
		$user->set_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_SCORE", $this->sum_score());
	}
	
	function get_sum_score($user = null) {
		isset($user) or $user = $this->myUser;
		$attribute = $user->get_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_SCORE");
		if (is_int($attribute)) {
			return $attribute;
		}
		return 0;	
	}
	
	function clear_sum_score($user = null) {
		isset($user) or $user = $this->myUser;
		$user->delete_attribute("ELEARNING_UNIT_EXAM_" . $this->parent->get_id() . "_SUM_SCORE");
	}*/
	
	private function sum_score($user = null) {
		isset($user) or $user = $this->myUser;
		$questions = $this->get_questions();
		$sum = 0;
		foreach($questions as $question) {
			$answers = $question->get_answers();
			foreach($answers as $answer){
			if ($answer->load_answer($user) == 1) {
					$is_checked = true;
				} else {
					$is_checked = false;
				}
				if ($is_checked == $answer->is_correct()) {
					$correct_answer = true;
				} else {
					$correct_answer = false;
				}
				if ($correct_answer) {
					$sum += $answer->get_score_correct();
				} else {
					$sum += $answer->get_score_wrong();
				}
			}
		}
		return $sum;
	}
	
	function delete_cert($user = null) {
		isset($user) or $user = $this->myUser;
		$this->get_parent()->delete_my_course_room($user);
	}
	
	function generate_certificate($user = null) {
		isset($user) or $user = $this->myUser;
		$elearning_user = elearning_user::get_instance($user->get_name(), elearning_mediathek::get_instance()->get_course()->get_id());
		if ($elearning_user->has_exam_passed()) {
			//generating cert
			$cert = new elearning_certificate();
			$filename_data = $cert->create_certificate($_SESSION[ "LMS_USER" ]->get_login(), $_SESSION[ "LMS_USER" ]->get_forename()." ".$_SESSION[ "LMS_USER" ]->get_surname(), $elearning_user->get_exam_sum_score(), $elearning_user->get_exam_sum_points(), date("d.m.Y"));
			
			//save cert to server
			$elearning_user->get_exam_cert()->set_content($filename_data);
			
			//save pdf to temp
			$login =  $_SESSION[ "LMS_USER" ]->get_login();
			$myFile = PATH_TEMP . "zertifikat_$login.pdf";
			$fh = fopen($myFile, 'w') or die("can't open file");
			fwrite($fh, $filename_data);
			fclose($fh);
			//chmod($myFile, 0777);
			
			//create Thumb
			//$command = "/opt/local/bin/convert $filename -geometry 550 -quality 80 jpg:$filename.jpg";
			putenv("MAGICK_TMPDIR=".PATH_TEMP);
			$im = new imagick($myFile.'[0]');
			/* Convert to jpg */
			$im->setImageFormat( "jpg" );
			/* Thumbnail the image */
			$im->thumbnailImage( 150, null );
			 
			//save Thumb
			$elearning_user->get_exam_cert_preview()->set_content((String)$im);
			
			// delete file
			unlink($myFile);
		}
	}
}
?>