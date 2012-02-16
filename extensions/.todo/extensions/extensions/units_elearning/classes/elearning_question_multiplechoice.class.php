<?php

global $STYLE;
define("IMAGE_CHECKBOX_SELECTED_ENABLED", "/styles/$STYLE/images/box_s.gif");
define("IMAGE_CHECKBOX_SELECTED_DISABLED", "/styles/$STYLE/images/box_s_dis.gif");
define("IMAGE_CHECKBOX_UNSELECTED_ENABLED", "/styles/$STYLE/images/box_u.gif");
define("IMAGE_CHECKBOX_UNSELECTED_DISABLED", "/styles/$STYLE/images/box_u_dis.gif");
define("IMAGE_CHECKBOX_LOADER", "/styles/$STYLE/images/loader_29.gif");


class elearning_question_multiplechoice extends elearning_question {
	
	function __construct($parent_tmp, $steamObject_tmp, $xml_tmp) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		$this->xml = $xml_tmp;
	}
	
	function get_answers() {
		$answers = $this->xml->answers->children();
		$result = array();
		$i = 0;
		foreach ($answers as $answer) {
			$result[] = new elearning_question_multiplechoice_answer($this, $this->steamObject, $answer, $i);
			$i++;
		}
		return $result;
	}
	
	function get_answer_by_id($id) {
		$answers = $this->get_answers();
		return $answers[$id];
	}
	
	function get_question_script_html($pathprefix) {
		global $STYLE;
		$content = new HTML_TEMPLATE_IT();
		$content->loadTemplateFile(PATH_TEMPLATES_UNITS_ELEARNING . "elearning_question_multiplechoice.template.html");
		
		$content->setCurrentBlock("BLOCK_CHECKBOX");
		$content->setVariable("IMAGE_CHECKBOX_SELECTED_ENABLED", IMAGE_CHECKBOX_SELECTED_ENABLED);
		$content->setVariable("IMAGE_CHECKBOX_SELECTED_DISABLED", IMAGE_CHECKBOX_SELECTED_DISABLED);
		$content->setVariable("IMAGE_CHECKBOX_UNSELECTED_ENABLED", IMAGE_CHECKBOX_UNSELECTED_ENABLED);
		$content->setVariable("IMAGE_CHECKBOX_UNSELECTED_DISABLED", IMAGE_CHECKBOX_UNSELECTED_DISABLED);
		$content->setVariable("IMAGE_CHECKBOX_LOADER", IMAGE_CHECKBOX_LOADER);
		$content->parseCurrentBlock();
		
		$content->setCurrentBlock("BLOCK_QUESTION_JAVASCRIPT");
		$content->setVariable("PATHPREFIX", $pathprefix);
		$content->setVariable("STYLE", $STYLE);
		$content->parseCurrentBlock();
		return $content->get();
	}
	
	function get_question_script_exam_html($pathprefix, $eid) {
		$content = new HTML_TEMPLATE_IT();
		$content->loadTemplateFile(PATH_TEMPLATES_UNITS_ELEARNING . "elearning_question_multiplechoice.template.html");
		
		$content->setCurrentBlock("BLOCK_CHECKBOX");
		$content->setVariable("IMAGE_CHECKBOX_SELECTED_ENABLED", IMAGE_CHECKBOX_SELECTED_ENABLED);
		$content->setVariable("IMAGE_CHECKBOX_SELECTED_DISENABLED", IMAGE_CHECKBOX_SELECTED_DISABLED);
		$content->setVariable("IMAGE_CHECKBOX_UNSELECTED_ENABLED", IMAGE_CHECKBOX_UNSELECTED_ENABLED);
		$content->setVariable("IMAGE_CHECKBOX_UNSELECTED_DISENABLED", IMAGE_CHECKBOX_UNSELECTED_DISABLED);
		$content->setVariable("IMAGE_CHECKBOX_LOADER", IMAGE_CHECKBOX_LOADER);
		$content->parseCurrentBlock();
		
		$content->setCurrentBlock("BLOCK_EXAM_QUESTION_JAVASCRIPT");
		$content->setVariable("PATHPREFIX", $pathprefix);
		$content->setVariable("EID", $eid);
		$content->parseCurrentBlock();
		return $content->get();
	}
	
	function get_question_html($load = false, $result = false, $disabled = false) {
		global $STYLE;
		if ($result) {
			$r = "disabled";
			$res = "Result";
		} else {
			$r = "";
			$res = "";
		}
		$html = "<div class=\"q\"><form class=\"multipleChoice\">
        		 <div class=\"question\">" . $this->get_question_text() . "</div>";
		if ($this->get_question_image() != null) {
			$url = $this->get_question_image();
			$html .= "<img style=\"float:left;height:150px\" src=\"".$url."\" />";
		}
		$answers = $this->get_answers();
		$i = 0;
		foreach($answers as $answer) {
			$img = "";
			if ($result) {
				if ($answer->load_answer(self::get_user()) == 1) {
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
					$img = "<img src=\"/styles/" . $STYLE . "/images/richtig_16.png\">";
				} else {
					$img = "<img src=\"/styles/" . $STYLE . "/images/falsch_16.png\">";
				}
			}
			$s = "0";
			if (!$disabled) {
				$box_img = IMAGE_CHECKBOX_UNSELECTED_ENABLED;
			} else {
				$box_img = IMAGE_CHECKBOX_UNSELECTED_DISABLED;
			}
			if ($load) {
				if ($answer->load_answer(self::get_user()) == 1) {
					$s = "1";
					if (!$disabled) {
						$box_img = IMAGE_CHECKBOX_SELECTED_ENABLED;
					} else {
						$box_img = IMAGE_CHECKBOX_SELECTED_DISABLED;
					}
				} else {
					$s = "0";
					if (!$disabled) {
						$box_img = IMAGE_CHECKBOX_UNSELECTED_ENABLED;
					} else {
						$box_img = IMAGE_CHECKBOX_UNSELECTED_DISABLED;
					}
				}
			}
			//$html .= "<div class=\"answer".$res."\">".$img."<img style=\"display:none;margin:0px 0px 0px 0px;\" src=\"/styles/" . $STYLE . "/images/loader_15.gif\" id=\"questionIdLoader" . $this->get_id() . "-" . $i ."\"> <input onclick=\"checkAnswer('". $this->parent->get_id() ."','". $this->get_id() ."','". $i ."');\" type=\"checkbox\" id=\"questionId" . $this->get_id() . "-" . $i ."\" name=\"question\"".$s." ".$r."> ". $answer->get_answertext() ."</div>";
			if (!$disabled) {
				$html .= "<div class=\"answer".$res."\">".$img.
					 	 "<input type=\"hidden\" value=\"$s\" id=\"questionId" . $this->get_id() . "-" . $i ."\" name=\"question\"><img id=\"questionCheckbox". $this->get_id() . "-" . $i ."\" style=\"float:left;cursor:pointer;margin:0px 10px 0px 0px\" src=\"$box_img\" onMouseOver=\"window.status='Viel Erfolg bei der Prüfung!';return true;\" onMouseOut=\"window.status='';return true;\" onclick=\"checkAnswer('". $this->parent->get_id() ."','". $this->get_id() ."','". $i ."');return false;\"><img id=\"questionCheckboxLoader". $this->get_id() . "-" . $i ."\" style=\"float:left;display:none;margin:0px 10px 0px 0px\" src=\"".IMAGE_CHECKBOX_LOADER."\" ><div style=\"display:block;margin-top:5px\">" . $answer->get_answertext() ."</div></div>";
			} else {
				$html .= "<div class=\"answer".$res."\">".$img.
					 	 "<input type=\"hidden\" value=\"$s\" id=\"questionId" . $this->get_id() . "-" . $i ."\" name=\"question\"><img id=\"questionCheckbox". $this->get_id() . "-" . $i ."\" style=\"float:left;margin:0px 10px 0px 0px\" src=\"$box_img\"><img id=\"questionCheckboxLoader". $this->get_id() . "-" . $i ."\" style=\"float:left;display:none;margin:0px 10px 0px 0px\" src=\"".IMAGE_CHECKBOX_LOADER."\" ><div style=\"display:block;margin-top:5px\">" . $answer->get_answertext() ."</div></div>";
			}
			$i++;
		}         
		if ($this->get_question_image() != null) {
			$html .= "<br clear=\"all\" />";
		}
        $html .= "</form></div>";
		return $html;
	}
	
	function get_question_exam_html($load = false, $result = false, $count = null, $sum = null) {
		global $STYLE;
		$html = "";
		if ($result) {
			$enabled = false;
			$res = "Result";
		} else {
			$enabled = true;
			$res = "";
		}
		if (!$result) {
			$html = "<div class=\"q\" id=\"q_$count\"><form class=\"multipleChoice\">
        		 <div class=\"question\">" . (($count==null || $sum==null) ? "" : "Frage $count von $sum - ") . $this->get_question_text() . "</div>";
		}
		if ($this->get_question_exam_image() != null) {
			$url = $this->get_question_exam_image();
			$found = strpos($url, "../");
			if ($found === false) {
			} else {
				$prefix = "../units/" . elearning_mediathek::get_instance()->get_unit()->get_id() . "/elearning/" . $this->parent->get_id() . "/";
				$url = str_replace("../", $prefix,$url);
			}
			if (stristr($_SERVER["REQUEST_URI"], "/units_elearning/report/") === false) {
				$html .= "<img style=\"float:left;height:150px\" src=\"".$url."\" />";
			} else {
				$html .= "<img style=\"float:left;height:150px\" src=\"../../".$url."\" />";
			}
		}
		$answers = $this->get_answers();
		$i = 0;
		$is_correct = TRUE;
		foreach($answers as $answer) {
			$img = "";
			if ($result) {
				if ($answer->load_answer(self::get_user()) == 1) {
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
					//$img = "<img src=\"/styles/" . $STYLE . "/images/richtig_16.png\">";
					$img = "<img style=\"float:left;margin: 6px 10px 0px 0px\" src=\"/styles/" . $STYLE . "/images/blank_16.png\">";
				} else {
					$img = "<img style=\"float:left;margin: 6px 10px 0px 0px\" src=\"/styles/" . $STYLE . "/images/falsch_16.png\">";
					$is_correct = FALSE;
				}
			}
			$s = "";
			if ($load) {
				if ($answer->load_answer(self::get_user()) == 1) {
					$s = "1";
					if ($enabled) {
						$box_img = IMAGE_CHECKBOX_SELECTED_ENABLED;
					} else {
						$box_img = IMAGE_CHECKBOX_SELECTED_DISABLED;
					}
				} else {
					$s = "0";
					if ($enabled) {
						$box_img = IMAGE_CHECKBOX_UNSELECTED_ENABLED;
					} else {
						$box_img = IMAGE_CHECKBOX_UNSELECTED_DISABLED;
					}	
				}
			}
			//<img style=\"display:none;position:relative;float:left;top:0px;left:0px;height:15px;width:15px\" src=\"/styles/" . $STYLE . "/images/loader_15.gif\" id=\"questionIdLoader" . $this->get_id() . "-" . $i ."\">".
			if ($enabled) {
				$html .= "<div class=\"answer".$res."\">".$img.
					 "<input type=\"hidden\" value=\"$s\" id=\"questionId" . $this->get_id() . "-" . $i ."\" name=\"question\"><img id=\"questionCheckbox". $this->get_id() . "-" . $i ."\" style=\"float:left;cursor:pointer;margin:0px 10px 0px 0px\" src=\"$box_img\" onMouseOver=\"window.status='Viel Erfolg bei der Prüfung!';return true;\" onMouseOut=\"window.status='';return true;\" onclick=\"checkAnswer('". $this->parent->get_id() ."','". $this->get_id() ."','". $i ."');return false;\"><img id=\"questionCheckboxLoader". $this->get_id() . "-" . $i ."\" style=\"float:left;display:none;margin:0px 10px 0px 0px\" src=\"".IMAGE_CHECKBOX_LOADER."\" ><div style=\"display:block;margin-top:5px;margin-left:39px\">" . $answer->get_answertext() ."</div></div>";
			} else {
				$html .= "<div class=\"answer".$res."\">".$img.
					 "<input type=\"hidden\" value=\"$s\" id=\"questionId" . $this->get_id() . "-" . $i ."\" name=\"question\"><img id=\"questionCheckbox". $this->get_id() . "-" . $i ."\" style=\"float:left;margin:0px 10px 0px 0px\" src=\"$box_img\"><div style=\"display:block;margin-top:5px\">" . $answer->get_answertext() ."</div></div>";
			}
			$i++;
		}   
		if ($result) {
			if ($is_correct) {
				$html = "<div class=\"q_right\" id=\"q_$count\"><form class=\"multipleChoice\">
        			<div class=\"question\">" . (($count==null || $sum==null) ? "" : "Frage $count von $sum - ") . $this->get_question_text() . "</div>" . $html;
			} else {
				$html = "<div class=\"q_wrong\" id=\"q_$count\"><form class=\"multipleChoice\">
        			<div class=\"question\">" . (($count==null || $sum==null) ? "" : "Frage $count von $sum - ") . $this->get_question_text() . "</div>" . $html;
			}
		}      
		if ($this->get_question_image() != null) {
			$html .= "<br clear=\"all\">";
		}
        $html .= "</form></div>";
		return $html;
	}
	
}

?>