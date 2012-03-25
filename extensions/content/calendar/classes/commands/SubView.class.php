<?php
namespace Calendar\Commands;
class SubView extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

	private $params;
	private $id;

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
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$html = "";

		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$this->id);

		$subscriptions = $obj->get_attribute("CALENDAR_SUBSCRIPTIONS");
		if(count($subscriptions)>0){
			$html .= "<table>";
			$html .= "<tr>";
			$html .= "<td>#ID</td>";
			$html .= "<td>Name</td>";
			$html .= "<td>Löschen</td>";
			$html .= "</tr>";
			foreach($subscriptions as $s){
				$id = $s->get_id();
				$html .= "<tr>";
				$html .= "<td>#".$id."</td>";
				$html .= "<td>".$s->get_name()."</td>";
				$html .= "<td><a onclick=\"sendRequest('DeleteSubscription', {'id':'".$id."', 'calendar':'".$this->id."'}, '', 'popup', null, null);return false;\" href='#'>Löschen</td>";
				$html .= "</tr>";
			}
			$html .= "</table>";
		}
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$dialog = new \Widgets\Dialog();
		$dialog->addWidget($rawHtml);
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		//$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;

	}

}
?>
