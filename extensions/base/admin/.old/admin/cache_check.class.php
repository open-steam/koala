<?php
class cache_check extends check {
	
	public function get_html() {
		$this->content->setCurrentBlock("TABLE_CHECK_STATUS_BLOCK");
		$this->content->setVariable("CHECK_HEADLINE", "Cache Status");
		$this->content->setVariable("CHECK_CONTENT", "Anzahl der Cache Dateien: " . count(glob(PATH_CACHE . "*")));
		$this->content->parseCurrentBlock();
		return $this->content->get();
	}

}