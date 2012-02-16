<?php
abstract class context {
	
	private $context_object;
	
	function __construct($context_object) {
		$this->context_object = $context_object;
	}
	
	public function get_contexts($context_object) {
		$contexts = array();
		
		return $contexts;
	}
	
	public function get_context_object() {
		return $this->context_object;
	}
	
}
?>