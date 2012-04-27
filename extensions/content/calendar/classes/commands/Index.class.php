<?php
namespace Calendar\Commands;
class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rawHtml = new \Widgets\RawHtml();
		$html = "<div><a onclick=\"sendRequest('subView', {'id':'".$this->id."'}, '', 'popup', null, null);return false;\" href=\"#\">Übersicht der Abonnements</a></div><br />";
		$html .= "<div><a onclick=\"sendRequest('AddSubMenu', {'id':'".$this->id."'}, '', 'popup', null, null);return false;\" href=\"#\">Kalender einer Extension abonnieren</a></div><br />";
		$html .= "<div><a onclick=\"sendRequest('AddExtSubMenu', {'id':'".$this->id."'}, '', 'popup', null, null);return false;\" href=\"#\">Kalender von Außerhalb abonnieren</a></div><br />";
		$html .= "<div>Diesen Kalender mit einer externen Kalenderapplikation abonnieren:</div>";
		$html .= "<div>".PATH_URL."calendar/GenerateICS/".$this->id."/</div>";
		$rawHtml->setHtml($html);
		
		$frameResponseObject->addWidget($rawHtml);
		//HIER KÖNNTE DIE GRAFISCHE AUSGABE BEGINNEN, Rechte und Daten stehen bereit

		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}

}
?>