<?php
namespace Widgets;

class ActionBar extends Widget {
	private $actions;
	
	public function setActions($actions) {
		$this->actions = $actions;
	}
	
	public function getHtml() {
		if ($this->actions && is_array($this->actions)) {
			foreach ($this->actions as $key => $item) {
				if (is_array($item)) {
					$this->getContent()->setCurrentBlock("ACTIONBAR_ITEM");
					$this->getContent()->setVariable("ACTIONBAR_ITEM_TEXT", $item["name"]);
					if (count($this->actions) == 1) {
						$this->getContent()->setVariable("POSITION_CLASS", "");
					} else if ($key == 0 && count($this->actions) > 1) {
						$this->getContent()->setVariable("POSITION_CLASS", "left");
					} else if ($key == count($this->actions)-1 && count($this->actions) > 1) {
						$this->getContent()->setVariable("POSITION_CLASS", "right");
					} else {
						$this->getContent()->setVariable("POSITION_CLASS", "middle");
					}
					if (isset($item["name"]) && isset($item["ajax"])) {
						$this->getContent()->setVariable("ACTIONBAR_ITEM_LINK", "#");
						$keys = array_keys($item["ajax"]);
						$ajax_arrays = $item["ajax"];
						$html = "";
						foreach ($keys as $key) {
							$ajax_array = $ajax_arrays[$key];
							$html = $key . "=\"sendRequest('" . $ajax_array["command"] . "', " . str_replace("\"", "'", json_encode($ajax_array["params"])) . ", '', '" . $ajax_array["requestType"]. "', null, null".(isset($ajax_array["namespace"])?",'".$ajax_array["namespace"]."'":"").");return false;\"";  
						}
						$this->getContent()->setVariable("AJAX", $html);
						$this->getContent()->parse("ACTIONBAR_ITEM");
					} else if (isset($item["name"]) && isset($item["onclick"])) {
						$this->getContent()->setVariable("ACTIONBAR_ITEM_LINK", "#");
                                                $this->getContent()->setVariable("AJAX", "onclick=\"" . $item["onclick"] . "\"");
						$this->getContent()->parse("ACTIONBAR_ITEM");
					} else if (isset($item["name"]) && isset($item["link"])) {
						$this->getContent()->setVariable("ACTIONBAR_ITEM_LINK", $item["link"]);
						$this->getContent()->parse("ACTIONBAR_ITEM");
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