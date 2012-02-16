<?php

abstract class PortfolioExtensionDocumentModel {
	private $link;
	
	public function __construct(steam_link $link) {
		self::init();
		$this->link = $link;
	}

	public function __call($name, $param) {
		if (is_callable(array($this->room, $name))) {
			return call_user_func_array(array($this->room, $name), $param);
		} else {
			throw new Exception("Method " . $name . " can be called.");
		}
	}
}
?>