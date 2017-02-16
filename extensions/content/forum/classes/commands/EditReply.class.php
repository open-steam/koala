<?php
namespace Forum\Commands;

class EditReply extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$object_id=$this->id;
		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		
		/** the current object */
		$object = \steam_factory::get_object($steamId, $object_id);
		
		/** additional required attributes */
		$attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED),1);
		
		$steam->buffer_flush();
		
		$title= $attrib[OBJ_DESC];

		$dialog = new \Widgets\Dialog();
                if($title !== ""){
                    $dialog->setTitle("Bearbeite aktuelles Thema »" . getCleanName($object) . "«");
                }else{
                    $dialog->setTitle("Bearbeite aktuelles Thema");
                }
		

		$clearer = new \Widgets\Clearer();

		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);

		$contentText = new \Widgets\Textarea();
		$contentText->setLabel("Inhalt");
		$contentText->setTextareaClass("mce-small");
		$contentText->setWidth(374);
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
