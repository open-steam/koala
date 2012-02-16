<?php
abstract class check {
	
	public $content;
	
	function __construct() {
		$this->content = new HTML_TEMPLATE_IT();
		$this->content->loadTemplateFile(dirname(__FILE__) . "/templates/index.template.html");
	}
	
	abstract function get_html();
	
}