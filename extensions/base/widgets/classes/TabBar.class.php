<?php
namespace Widgets;

class TabBar extends Widget {
	private $tabs;
	private $activeTab;
	
	public function setTabs($tabs) {
		$this->tabs = $tabs;
	}
	
	public function setActiveTab($index) {
		$this->activeTab = $index;
	}
	
	public function getHtml() {
		if (!isset($this->activeTab)) {
			$this->activeTab = 0;
		}
		if ($this->tabs && is_array($this->tabs)) {
			foreach ($this->tabs as $key => $item) {
				if (is_array($item)) {
					if (isset($item["name"]) && isset($item["link"])) {
						$this->getContent()->setCurrentBlock("BLOCK_TABBAR_ITEM");
						if ($this->activeTab == $key) {
							$this->getContent()->setVariable("TABBAR_ACTIVE", "tabOut");
						} else {
							$this->getContent()->setVariable("TABBAR_ACTIVE", "tabIn");
						}
						$this->getContent()->setVariable("TABBAR_NAME", $item["name"]);
						$this->getContent()->setVariable("TABBAR_LINK", $item["link"]);
						$this->getContent()->parse("BLOCK_TABBAR_ITEM");
					} 
				}
			}
			return $this->getContent()->get();
		} else {
			return "";
		}
	}
}
?>