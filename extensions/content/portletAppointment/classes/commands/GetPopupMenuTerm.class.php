<?php
namespace PortletAppointment\Commands;
class GetPopupMenuTerm extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;
	private $x, $y, $height, $width;
	private $termIndex;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->termIndex = $this->params["termIndex"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		//icons
		$deleteIcon = $explorerUrl . "icons/menu/svg/trash.svg";
		$editIcon = $explorerUrl . "icons/menu/svg/edit.svg";

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten",  "command" => "EditTerm", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}','termIndex':'{$this->termIndex}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$deleteIcon}#trash'/></svg> Löschen",  "command" => "DeleteTerm", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}','termIndex':'{$this->termIndex}'}", "type"=>"popup"));
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 105) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>
