<?php

class DirectorFormat extends DirectorWrapper {
	public function add($arr) {
		$this->parent->sizes[] = $arr;
	}
	
	public function user($arr) {
		$this->parent->user_sizes[] = $arr;
	}
	
	public function preview($arr) {
		$this->parent->preview = $arr;
	}
	
	public function clear() {
		$this->parent->preview = array();
		$this->parent->sizes = array();
	}	
}

?>