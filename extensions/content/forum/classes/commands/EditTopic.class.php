<?php
namespace Forum\Commands;

class EditTopic extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$objectId=$this->id;
		$steam= $GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		/** the current category object */
		$object = \steam_factory::get_object($steamId, $objectId);
		
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeite aktuelles Thema »" . getCleanName($object) . "«");

		$clearer = new \Widgets\Clearer();
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
                $titelInput->setIsNotEmpty(true);
                //$titelInput->setSuccessMethodForDataProvider("if($('#".$titelInput->getId()."').val()=='') sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': 'Neues Thema'}, '', 'data');");
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		
		$contentText = new \Widgets\Textarea();
		$contentText->setLabel("Inhalt");
		$contentText->setTextareaClass("mce-small");
		$contentText->setWidth(480);
		$contentText->setData($object);
		$contentText->setContentProvider(\Widgets\DataProvider::contentProvider());
		$dialog->addWidget($contentText);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>