<?php
namespace Widgets;

class Chat extends Widget {
	private $label;
	private $data;
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getHtml() {
		$portal = \lms_portal::get_instance();
		$annotations = $this->data->get_annotations();
		$annotations = array_reverse($annotations);
		$lastAnnotation = 0;
		foreach ($annotations as $annotation) {
			$this->getContent()->setCurrentBlock("BLOCK_CHAT");
			if ($lastAnnotation === 0) {
				$lastAnnotation = $annotation->get_attribute("OBJ_CREATION_TIME");
				$this->getContent()->setCurrentBlock("BLOCK_STATUS");
				$this->getContent()->setVariable("STATUS_MESSAGE", "Diskussion mit " . $portal->get_user()->get_forename() . " " . $portal->get_user()->get_surname() . ".<br>" . getReadableDate($lastAnnotation));
				$this->getContent()->parse("BLOCK_STATUS");
			} else {
				$tmp = $lastAnnotation;
				$lastAnnotation = $annotation->get_attribute("OBJ_CREATION_TIME");
				if ($lastAnnotation - $tmp > 600) {
					$this->getContent()->setCurrentBlock("BLOCK_STATUS");
					$this->getContent()->setVariable("STATUS_MESSAGE", getReadableDate($lastAnnotation));
					$this->getContent()->parse("BLOCK_STATUS");
				}
			}
			
			$this->getContent()->setCurrentBlock("BLOCK_OUTGOING");
			$this->getContent()->setVariable("OUTGOING_MESSAGE", $annotation->get_content());
			$this->getContent()->setVariable("OUTGOING_IMG", \lms_user::get_user_image_url(32, 32));
			$this->getContent()->setVariable("OUTGOING_TITLE", $portal->get_user()->get_forename() . " " . $portal->get_user()->get_surname());
			$this->getContent()->parse("BLOCK_OUTGOING");
			$this->getContent()->parse("BLOCK_CHAT");
		}
		return $this->getContent()->get();
	}
	
}
?>