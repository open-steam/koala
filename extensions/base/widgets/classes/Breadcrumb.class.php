<?php
namespace Widgets;

class Breadcrumb extends Widget {
	private $data;
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getHtml() {
		if ($this->data && is_array($this->data)) {
			foreach ($this->data as $key => $item) {
				$this->getContent()->setCurrentBlock("BREADCRUMB_ITEM");
				if (is_array($item)) {
					if (isset($item["name"]) && isset($item["link"])) {
						$this->getContent()->setCurrentBlock("BREADCRUMB_ITEM_WITH_LINK");
						$this->getContent()->setVariable("BREADCRUMB_ITEM_TEXT", $item["name"]);
						$this->getContent()->setVariable("BREADCRUMB_ITEM_LINK", $item["link"]);
						$this->getContent()->parse("BREADCRUMB_ITEM_WITH_LINK");
					} else if (isset($item["name"])) {
						$this->getContent()->setCurrentBlock("BREADCRUMB_ITEM_WITHOUT_LINK");
						$this->getContent()->setVariable("BREADCRUMB_ITEM_TEXT", $item["name"]);
						$this->getContent()->parse("BREADCRUMB_ITEM_WITHOUT_LINK");
					}
					if ($key < count($this->data)-1) {
						$this->getContent()->setCurrentBlock("BREADCRUMB_ITEM_SEPERATOR");
						$this->getContent()->setVariable("ITEM_SEPERATOR", "/");
						$this->getContent()->parse("BREADCRUMB_ITEM_SEPERATOR");
					}
				} else if ($item instanceof \steam_object) {
					$this->getContent()->setCurrentBlock("BREADCRUMB_ITEM_WITHOUT_LINK");
					$this->getContent()->setVariable("BREADCRUMB_ITEM_TEXT", $item->get_path());
					$this->getContent()->parse("BREADCRUMB_ITEM_WITHOUT_LINK");
				}
				$this->getContent()->parse("BREADCRUMB_ITEM");
			}
			return $this->getContent()->get();
		} else {
			return "";
		}
	}
}
?>