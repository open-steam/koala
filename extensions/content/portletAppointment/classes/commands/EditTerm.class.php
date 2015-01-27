<?php
namespace PortletAppointment\Commands;
class EditTerm extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));
		
		$termIndex = $params["termIndex"];
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Titel");
		$titelInput->setData($object);
		$titelInput->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "topic"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "description"));
		$dialog->addWidget($descriptionInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
		$loactionInput = new \Widgets\TextInput();
		$loactionInput->setLabel("Ort");
		$loactionInput->setData($object);
		$loactionInput->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "location"));
		$dialog->addWidget($loactionInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
		
		//datepicker
		$datepickerStart = new \Widgets\DatePicker();
		$datepickerStart->setLabel("Startdatum");
		$datepickerStart->setData($object);
		$datepickerStart->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"start_date"));
		$datepickerStart->setDatePicker(true);
		$datepickerStart->setTimePicker(false);
                //TODO: Bad solution
               // $datepickerStart->setWorkaraound(true);
		$dialog->addWidget($datepickerStart);
		$dialog->addWidget(new \Widgets\Clearer());
		
		$timepickerStart = new \Widgets\DatePicker();
		$timepickerStart->setLabel("Startzeit");
		$timepickerStart->setData($object);
		$timepickerStart->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"start_time"));
		$timepickerStart->setDatePicker(false);
		$timepickerStart->setTimePicker(true);
                 //TODO: Bad solution
                //$timepickerStart->setWorkaraound(true);
		
		$dialog->addWidget($timepickerStart);
		$dialog->addWidget(new \Widgets\Clearer());
		
		$datepickerEnd = new \Widgets\DatePicker();
		$datepickerEnd->setLabel("Enddatum");
		$datepickerEnd->setData($object);
		$datepickerEnd->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"end_date"));
		$datepickerEnd->setDatePicker(true);
		$datepickerEnd->setTimePicker(false);
		$dialog->addWidget($datepickerEnd);
		$dialog->addWidget(new \Widgets\Clearer());
		
		
		//url
		$linkurlInput = new \Widgets\TextInput();
		$linkurlInput->setLabel("Link-Adresse");
		$linkurlInput->setData($object);
		$linkurlInput->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "linkurl"));
		$dialog->addWidget($linkurlInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
                
                //open url extern
                $linkurlInputOpenExtern = new \Widgets\Checkbox();
		$linkurlInputOpenExtern->setLabel("Link in einem </br>neuen Fenster öffnen");
		$linkurlInputOpenExtern->setData($object);
		$linkurlInputOpenExtern->setCheckedValue("checked");
		$linkurlInputOpenExtern->setUncheckedValue("");
                $linkurlInputOpenExtern->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "linkurl_open_extern"));
		$dialog->addWidget($linkurlInputOpenExtern);
		$dialog->addWidget(new \Widgets\Clearer());
		
                
		
		$this->dialog = $dialog;
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>