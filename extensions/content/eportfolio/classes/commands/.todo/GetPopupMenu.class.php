<?php
namespace Portfolio\Commands;
class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	//private $object;
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

		//$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$popupMenu =  new \Widgets\PopupMenu();
		$renameIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rename.png";
		$transparanceImage = \Explorer::getInstance()->getAssetUrl() . "icons/transparent.png";
		$redImage = \Explorer::getInstance()->getAssetUrl() . "icons/red.png";
		$orangeImage = \Explorer::getInstance()->getAssetUrl() . "icons/orange.png";
		$yellowImage = \Explorer::getInstance()->getAssetUrl() . "icons/yellow.png";
		$greenImage = \Explorer::getInstance()->getAssetUrl() . "icons/green.png";
		$blueImage = \Explorer::getInstance()->getAssetUrl() . "icons/blue.png";
		$purpleImage = \Explorer::getInstance()->getAssetUrl() . "icons/purple.png";
		$greyImage = \Explorer::getInstance()->getAssetUrl() . "icons/grey.png";
		$portfolioUrl = "/portfolio/SetCompetence/"; //Portfolio::getInstance()->get
		$items = array(array("raw" => " <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparenz'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$transparanceImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$redImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$orangeImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$yellowImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$greenImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$blueImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$purpleImage}\"></a>
					   					<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$greyImage}\"></a>"),
		array("raw" => "<a href=\"#\" onclick=\"event.stopPropagation(); removeAllDirectEditors(); if (!jQuery('#{$this->id}_2').hasClass('directEditor')) { jQuery('#{$this->id}_2').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_2', 'updater'); } jQuery('.popupmenuwapper').parent().html('');jQuery('.open').removeClass('open'); return false;\">Umbenennen<img src=\"{$renameIcon}\"></a>"),
		array("raw" => "<a href=\"{$portfolioUrl}{$this->id}\">Kompetenzen zuordnen</a>"),
		array("name" => "in den Papierkorb legen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
		);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("150px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>