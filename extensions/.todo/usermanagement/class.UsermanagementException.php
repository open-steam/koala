<?php

class UsermanagementException extends Exception {
	
	private $problem;
	
	private $hint;
	
	public function __construct ($problem, $hint) {
		$this->problem = $problem;
		$this->hint = $hint;
		parent::__construct("", 0);
	}
	
	public function getProblem () {
		return $this->problem;
	}
	
	public function getHint () {
		return $this->hint;
	}
	
}

?>