<?php
namespace Questionnaire\Model;
abstract class AbstractQuestion extends \AbstractObjectModel {

	protected $questionText;
	protected $helpText;
	protected $required = 0;
	protected $results;

	public static function isObject(\steam_object $steamObject) {

	}

	function __construct() {

	}

	public function setQuestionText($input) {
		$this->questionText = $input;
	}

	public function getQuestionText() {
		return $this->questionText;
	}

	public function setHelpText($input) {
		$this->helpText = $input;
	}

	public function getHelpText() {
		return $this->helpText;
	}

	public function setRequired($input) {
		$this->required = $input;
	}

	public function getRequired() {
		return $this->required;
	}
}
?>
