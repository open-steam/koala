<?php
namespace Questionnaire\Model;
class Survey extends \AbstractObjectModel {

	private $questions = array();
	private $name = "";
	private $begintext = "";
	private $endtext = "";
	private $starttype = 0;
	private $begin = 0;
	private $end = 0;
	private $questionnaire;

	public static function isObject(\steam_object $steamObject) {

	}

	function __construct($container) {
		$this->questionnaire = $container;
	}

	public function setName($input) {
		$this->name = $input;
	}

	public function getName() {
		return $this->name;
	}

	public function setBeginText($input) {
		$this->begintext = $input;
	}

	public function getBeginText() {
		return $this->begintext;
	}

	public function setEndText($input) {
		$this->endtext = $input;
	}

	public function getEndText() {
		return $this->endtext;
	}

	public function setStartType($starttype, $begin = 0, $end = 0) {
		$this->starttype = $starttype;
		$this->begin = $begin;
		$this->end = $end;
	}

	public function addQuestion($question) {
		array_push($this->questions, $question);
	}

	public function getQuestions() {
		return $this->questions;
	}

	public function createSurvey($old = null) {
		if ($old == null) {
			$survey_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "questionnaire_" . time(), $this->questionnaire, $this->name);
			$results_container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "results", $survey_container, "container for results");
			$groups = $this->questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
			foreach ($groups as $group) {
				$results_container->set_sanction($group, SANCTION_READ | SANCTION_WRITE | SANCTION_INSERT);
			}
		} else {
			$survey_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $old);
			$survey_container->set_attribute("OBJ_DESC", $this->name);
			$results_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey_container->get_path() . "/results");
		}

		if ($survey_container->get_attribute("QUESTIONNAIRE_STATE") == 0 || $survey_container->get_attribute("QUESTIONNAIRE_STATE") == "0") {
			$survey_container->set_attribute("QUESTIONNAIRE_STATE", 0);
			$results_container->set_attribute("QUESTIONNAIRE_RESULTS", 0);
			$results_container->set_attribute("QUESTIONNAIRE_PARTICIPANTS", array());
		}

		if ($this->starttype == 0) {
			$survey_container->set_attribute("QUESTIONNAIRE_STARTTYPE", 0);
		} else {
			$begin = $this->begin;
			$end = $this->end;
			$begin = mktime(substr($begin,11,2), substr($begin,14,2), 0, substr($begin,3,2), substr($begin,0,2), substr($begin,6,4));
			$end = mktime(substr($end,11,2), substr($end,14,2), 0, substr($end,3,2), substr($end,0,2), substr($end,6,4));
			$times = array();
			array_push($times, $end);
			array_push($times, $begin);
			$survey_container->set_attribute("QUESTIONNAIRE_STARTTYPE", $times);
		}

		$xml = new \SimpleXMLElement("<survey></survey>");
       	$xml->addChild("name", $this->name);
       	$xml->addChild("begintext", $this->begintext);
       	$xml->addChild("endtext", $this->endtext);
       	$questionCount = 0;
       	$pageCount = 1;
		foreach ($this->questions as $question) {
			$xml_question = $xml->addChild("question");
			$question->saveXML($xml_question);
			if ($question instanceof AbstractQuestion) {
				$questionCount++;
			}
			if ($question instanceof PageBreakLayoutElement) {
				$pageCount++;
			}
		}
		$survey_container->set_attribute("QUESTIONNAIRE_QUESTIONS", $questionCount);
		$survey_container->set_attribute("QUESTIONNAIRE_PAGES", $pageCount);

		if ($old == null) {
			$xml_document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "survey.xml", $xml->saveXML(), "text/xml", $survey_container);
		} else {
			$xml_document = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey_container->get_path() . "/survey.xml");
			$xml_document->set_content($xml->saveXML());
		}

		return $survey_container;
	}

	public function parseXML($xml) {
		$simpleXML = simplexml_load_string($xml->get_content());
		$this->name = $simpleXML->name;
		$this->begintext = $simpleXML->begintext;
		$this->endtext = $simpleXML->endtext;
		$questions = array();
		foreach ($simpleXML->question as $question) {
			switch ($question->type) {
				case 0:
					$new_question = new \Questionnaire\Model\TextQuestion($question);
					break;
				case 1:
					$new_question = new \Questionnaire\Model\TextareaQuestion($question);
					break;
				case 2:
					$new_question = new \Questionnaire\Model\SingleChoiceQuestion($question);
					break;
				case 3:
					$new_question = new \Questionnaire\Model\MultipleChoiceQuestion($question);
					break;
				case 4:
					$new_question = new \Questionnaire\Model\MatrixQuestion($question);
					break;
				case 5:
					$new_question = new \Questionnaire\Model\GradingQuestion($question);
					break;
				case 6:
					$new_question = new \Questionnaire\Model\TendencyQuestion($question);
					break;
				case 7:
					$new_question = new \Questionnaire\Model\DescriptionLayoutElement($question);
					break;
				case 8:
					$new_question = new \Questionnaire\Model\HeadlineLayoutElement($question);
					break;
				case 9:
					$new_question = new \Questionnaire\Model\PageBreakLayoutElement($question);
					break;
                                case 10:
                                    $new_question = new \Questionnaire\Model\JumpLabel($question);
			}
			array_push($questions, $new_question);
		}
		$this->questions = $questions;
	}

	public function generateResults($container) {
		$results = array();
		for ($count = 0; $count < count($this->questions); $count++) {
			$results[$count] = array();
		}
		$result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $container->get_path() . "/results");
		$result_objects = $result_container->get_inventory();
		foreach ($result_objects as $result_object) {
			if ($result_object instanceof \steam_document && $result_object->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
				$questionCount = 0;
				foreach ($this->questions as $question) {
					if ($question instanceof AbstractQuestion) {
						if ($result_object->get_attribute("QUESTIONNAIRE_ANSWER_" . $questionCount) != -1) {
							array_push($results[$questionCount], $result_object->get_attribute("QUESTIONNAIRE_ANSWER_" . $questionCount));
						}
						$questionCount++;
					}
				}
			}
		}
		$questionCount = 0;
		foreach ($this->questions as $question) {
			if ($question instanceof AbstractQuestion) {
				$question->setResults($results[$questionCount]);
				$questionCount++;
			}
		}
	}

	public function getIndividualResult($resultfile) {
		$result = array();
		$questionCount = 0;
		foreach ($this->questions as $question) {
			if ($question instanceof AbstractQuestion) {
				if ($resultfile->get_attribute("QUESTIONNAIRE_ANSWER_" . $questionCount) != -1) {
					$result[$questionCount] = $question->getIndividualResult($resultfile->get_attribute("QUESTIONNAIRE_ANSWER_" . $questionCount));
				} else {
					$result[$questionCount] = array("");
				}
				$questionCount++;
			}
		}
		return $result;
	}
}
?>
