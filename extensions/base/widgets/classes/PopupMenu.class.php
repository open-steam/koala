<?php
namespace Widgets;

class PopupMenu extends Widget {
	private $items;
	private $data;
	private $width;
	private $x, $y;
	private $command = "GetPopupMenu";
	private $namespace = "";
	private $params = "";
	private $elementId = "";

	public function setItems($items) {
		$this->items = $items;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setParams($params) {
		$this->params = $params;
	}

	public function setElementId($elementId) {
		$this->elementId = $elementId;
	}

	public function setWidth($width) {
		$this->width = $width;
	}

	public function setPosition($x, $y) {
		$this->x = $x;
		$this->y = $y;
	}

	public function setCommand($command) {
		$this->command = $command;
	}

	public function setNamespace($namespace) {
		$this->namespace = ", '" . $namespace . "'";
	}

	private function getParamsString() {
		$result = "";
		if (is_array($this->params)) {
			foreach ($this->params as $param) {
				if (is_array($param)) {
					$result .= "params." . $param["key"] . " = '" . $param["value"] . "';";
				}
			}
		}
		return $result;
	}

	public function getHtml() {
		$html = "";
		if ($this->items && is_array($this->items)) {
			$html .= "<div style=\"position:absolute; top:{$this->y}; left:{$this->x}; width:{$this->width}\" class=\"popupmenuwapper\" onMouseOver=\"event.stopPropagation(); \" onMouseOut=\"event.stopPropagation();\" onMouseMove=\"event.stopPropagation();\">";
			foreach ($this->items as $count => $item) {
				if (!is_array($item)) {
					continue;
				}
				if (isset($item["command"]) && isset($item["namespace"]) && isset($item["params"])) {
					$onclick = "event.stopPropagation();sendRequest('{$item["command"]}', {$item["params"]}, " . (isset($item["elementId"]) ?  "'" . $item["elementId"] . "'": "'" . $this->elementId . "'") . ", " . (isset($item["type"]) ?  "'" . $item["type"] . "'" : "'updater'") . ", null, null, '{$item["namespace"]}');jQuery('.popupmenuwapper').parent().html('');jQuery('.open').removeClass('open');";
				} else {
					$onclick = "";
				}

				if (isset($item["menu"])) {
					$html .= "<div class=\"popupmenuitem popupsubmenuanker\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\"><a href=\"#\">{$item["name"]}</a>";
					if (is_array($item["menu"])) {
						$html .= "<div class=\"popupsubmenuwapper {$item["direction"]}\">";
						foreach ($item["menu"] as $subMenuItem) {
							if (!is_array($subMenuItem)) {
								continue;
							}
							if (isset($subMenuItem["raw"])) {
								$html .= "<div class=\"popupmenuitem\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\">{$subMenuItem["raw"]}</div>";
							} else {
								if (isset($subMenuItem["command"]) && isset($subMenuItem["namespace"]) && isset($subMenuItem["params"])) {
									$onclick = "event.stopPropagation();sendRequest('{$subMenuItem["command"]}', {$subMenuItem["params"]}, " . (isset($subMenuItem["elementId"]) ?  "'" . $subMenuItem["elementId"] . "'": "'" . $this->elementId . "'") . ", " . (isset($subMenuItem["type"]) ?  "'" . $subMenuItem["type"] . "'" : "'updater'") . ", null, null, '{$subMenuItem["namespace"]}');jQuery('.popupmenuwapper').parent().html('');jQuery('.open').removeClass('open');";
								} else {
									$onclick = "";
								}
								$html .= "<div class=\"popupmenuitem\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\" onclick=\" {$onclick} return false;\"><a href=\"#\" onclick=\" {$onclick} return false;\" >{$subMenuItem["name"]}</a></div>";
							}
						}
						$html .= "</div></div>";
					}
				} else if (isset($item["raw"])) {
					$html .= "<div class=\"popupmenuitem\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\">{$item["raw"]}</div>";
				} else if (isset($item["name"]) && $item["name"] == "SEPARATOR") {
					$html .= "<div class=\"popupmenuseparator\"></div>";
				} else if (isset($item["name"]) && isset($item["link"])) {
					$html .= "<div class=\"popupmenuitem\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\"  onclick=\"location.href = '{$item["link"]}'; return false;\"><a href=\"{$item["link"]}\">{$item["name"]}</a></div>";
				} else if (isset($item["name"])) {
					$html .= "<div class=\"popupmenuitem\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\" onclick=\" {$onclick} return false;\"><a href=\"#\" onclick=\" {$onclick} return false;\" >{$item["name"]}</a></div>";
				}
			}
			$html .= "</div>";
		} else {
			if ($this->data->get_environment() instanceof \steam_object) {
				$html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"myId = this.id; jQuery('#' + myId).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; params.env = '{$this->data->get_environment()->get_id()}'; if (typeof getSelectionAsJSON == 'function') { params.selection = getSelectionAsJSON(); }; params.x = jQuery(this).position().left; params.y = jQuery(this).position().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); ".$this->getParamsString()." sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalUpdater', function(response){ jQuery('#'+myId).removeClass('popupmenuloading').addClass('popupmenuanker').addClass('open'); }, null {$this->namespace} );event.stopPropagation();\"></div>";
			} else {
				$html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"myId = this.id; jQuery('#' + myId).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; if (typeof getSelectionAsJSON == 'function') { params.selection = getSelectionAsJSON(); }; params.x = jQuery(this).position().left; params.y = jQuery(this).position().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); ".$this->getParamsString()." sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalupdater', function(response){ jQuery('#'+myId).removeClass('popupmenuloading').addClass('popupmenuanker').addClass('open'); }, null {$this->namespace} );event.stopPropagation();\"></div>";
			}
		}
		return $html;
	}
}
?>
