<?php
namespace Widgets;

class Scheduler extends Widget {

	public function getHtml() {
		$this->getContent()->setVariable("DUMMY", "");
		return $this->getContent()->get();
	}
}
?>