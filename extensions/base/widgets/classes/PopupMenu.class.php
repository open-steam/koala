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
            $html .= "<div style=\"position:absolute; top:{$this->y}; left:{$this->x}; width:{$this->width}\" class=\"popupmenuwrapper\" onMouseOver=\"event.stopPropagation(); \" onMouseOut=\"event.stopPropagation();\" onMouseMove=\"event.stopPropagation();\">";
            foreach ($this->items as $count => $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (isset($item["command"]) && isset($item["namespace"]) && isset($item["params"])) {
                    $onclick = "event.stopPropagation();sendRequest('{$item["command"]}', {$item["params"]}, " . (isset($item["elementId"]) ? "'" . $item["elementId"] . "'" : "'" . $this->elementId . "'") . ", " . (isset($item["type"]) ? "'" . $item["type"] . "'" : "'updater'") . ", null, null, '{$item["namespace"]}');jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px');";
                } else {
                    $onclick = "";
                }

                if (isset($item["menu"])) {
                    $triangle = "";
                    if ($item["direction"] == "left") {
                        $triangle = "<div style='color:#FFFFFF; float:right; padding-top:5px; padding-left:5px;'>◀</div>";
                    }
                    if ($item["direction"] == "right") {
                        $triangle = "<div style='color:#FFFFFF; float:right; padding-top:5px; padding-left:5px;'>▶</div>";
                    }
                    $html .= "<div class=\"popupmenuitem popupsubmenuanker {$item["direction"]}\"  onMouseOver=\"event.stopPropagation();\" onMouseOut=\"event.stopPropagation();\" onclick=\"event.stopPropagation(); $('.popupsubmenuwapper').not($('.popupsubmenuwapper', this)).hide(); $('.popupsubmenuwapper', this).toggle();\"><a href=\"#\">{$item["name"]}</a>" . $triangle;
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
                                    $onclick = "event.stopPropagation();sendRequest('{$subMenuItem["command"]}', {$subMenuItem["params"]}, " . (isset($subMenuItem["elementId"]) ? "'" . $subMenuItem["elementId"] . "'" : "'" . $this->elementId . "'") . ", " . (isset($subMenuItem["type"]) ? "'" . $subMenuItem["type"] . "'" : "'updater'") . ", null, null, '{$subMenuItem["namespace"]}');jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px');";
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
            $viewAttribute = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("EXPLORER_VIEW");
            if ($this->data->get_environment() instanceof \steam_object) {
                if ($viewAttribute && $viewAttribute == "gallery") {
                    $html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"that = this; jQuery('.popupmenuanker.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); jQuery(this).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; params.env = '{$this->data->get_environment()->get_id()}'; if (typeof getGallerySelectionAsJSON == 'function') { params.selection = getGallerySelectionAsJSON(); }; params.x = jQuery(this).offset().left; params.y = jQuery(this).offset().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); " . $this->getParamsString() . " sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalUpdater', function(response){}, null {$this->namespace} );event.stopPropagation();\"><svg style='width:16px; height:16px; pointer-events:none;'><use xlink:href='" . PATH_URL . "widgets/asset/config.svg#config'/></svg></div>";
                } else {
                    $html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"that = this; jQuery('.popupmenuanker.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); jQuery(this).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; params.env = '{$this->data->get_environment()->get_id()}'; if (typeof getSelectionAsJSON == 'function') { params.selection = getSelectionAsJSON(); }; params.x = jQuery(this).offset().left; params.y = jQuery(this).offset().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); " . $this->getParamsString() . " sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalUpdater', function(response){}, null {$this->namespace} );event.stopPropagation();\"><svg style='width:16px; height:16px; pointer-events:none;'><use xlink:href='" . PATH_URL . "widgets/asset/config.svg#config'/></svg></div>";
                }
            } else {
                if ($viewAttribute && $viewAttribute == "gallery") {
                    $html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"that = this; jQuery('.popupmenuanker.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); jQuery(this).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; if (typeof getGallerySelectionAsJSON == 'function') { params.selection = getGallerySelectionAsJSON(); }; params.x = jQuery(this).offset().left; params.y = jQuery(this).offset().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); " . $this->getParamsString() . " sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalUpdater', function(response){}, null {$this->namespace} );event.stopPropagation();\"><svg style='width:16px; height:16px; pointer-events:none;'><use xlink:href='" . PATH_URL . "widgets/asset/config.svg#config'/></svg></div>";
                } else {
                    $html = "<div id=\"popupmenu{$this->data->get_id()}\" class=\"popupmenuanker\" onclick=\"that = this; jQuery('.popupmenuanker.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); jQuery(this).addClass('popupmenuloading'); params = new Object(); params.id = '{$this->data->get_id()}'; if (typeof getSelectionAsJSON == 'function') { params.selection = getSelectionAsJSON(); }; params.x = jQuery(this).offset().left; params.y = jQuery(this).offset().top; params.height = jQuery(this).height(); params.width = jQuery(this).width(); " . $this->getParamsString() . " sendRequest('{$this->command}', params, '{$this->elementId}', 'nonModalUpdater', function(response){}, null {$this->namespace} );event.stopPropagation();\"><svg style='width:16px; height:16px;' pointer-events:none;><use xlink:href='" . PATH_URL . "widgets/asset/config.svg#config'/></svg></div>";
                }
            }
        }
        $script = "
		function showPopupMenu(){
			jQuery(that).removeClass(\"popupmenuloading\").addClass(\"popupmenuanker\").addClass(\"open\");
			jQuery(\".popupmenuwrapper\").css(\"display\", \"table\");
			adjustFooter();
		}
	 	var lastSVG = jQuery(\".popupmenuwrapper\").children().last().find(\"use\");
	 	if(lastSVG.length != 0){
			if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1 || navigator.userAgent.toLowerCase().indexOf('edge') > -1){
				//call function directly because svg load event do not fire in firefox
				showPopupMenu();
			}
			else{
		 		lastSVG.load(function(){
			 		showPopupMenu();
		 		})
	 		}
 		}";
        $html .= "<script>" . $script . "</script>";
        return $html;
    }

}

?>
