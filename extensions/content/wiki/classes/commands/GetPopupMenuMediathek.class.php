<?php
namespace Wiki\Commands;
class GetPopupMenuMediathek extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $x, $y, $height, $width;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
		$propertiesIcon = $explorerAssetUrl . "icons/menu/svg/properties.svg";
		$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";
		$popupMenu =  new \Widgets\PopupMenu();
		$image = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$wiki_container = $image->get_environment();

		$items = array(
			array("name" => "<svg><use xlink:href='{$propertiesIcon}#properties'/></svg> Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),
			($wiki_container->check_access_write()) ? array("raw" => "<a href=\"#\" onclick=\"confirmDeletion({$this->id});\"><div><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</div></a>") : ""
		);

		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 110) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>
