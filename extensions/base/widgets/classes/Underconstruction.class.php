<?php
namespace Widgets;

class Underconstruction extends Widget {
	
	public function getHtml() {
		\lms_portal::get_instance()->add_css_style_link(PATH_URL . "widgets/css/underconstruction.css");
		$this->getContent()->setVariable("DUMMY", "");	
		return $this->getContent()->get();
	}
}
?>