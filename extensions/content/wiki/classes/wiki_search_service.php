<?php

class wiki_search_service extends search_service {

	private function __construct() {
		$this->result_converter = new wiki_result_converter();
	}

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new wiki_search_service();
		}
		return self::$instance;
	}
}