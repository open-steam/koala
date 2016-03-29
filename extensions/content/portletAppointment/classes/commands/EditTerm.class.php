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
		$dialog->setWidth(400);

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

		$datepickerStart = new \Widgets\DatePicker();
		$datepickerStart->setLabel("Start (Datum):");
		$datepickerStart->setData($object);
		$datepickerStart->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"start_date"));
		$datepickerStart->setDatePicker(true);
		$datepickerStart->setTimePicker(false);
		$dialog->addWidget($datepickerStart);
		$dialog->addWidget(new \Widgets\Clearer());

		$timepickerStart = new \Widgets\DatePicker();
		$timepickerStart->setLabel("Start (Uhrzeit):");
		$timepickerStart->setData($object);
		$timepickerStart->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"start_time"));
		$timepickerStart->setDatePicker(false);
		$timepickerStart->setTimePicker(true);
		$dialog->addWidget($timepickerStart);
		$dialog->addWidget(new \Widgets\Clearer());

		$datepickerEnd = new \Widgets\DatePicker();
		$datepickerEnd->setLabel("Ende (Datum):");
		$datepickerEnd->setData($object);
		$datepickerEnd->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"end_date"));
		$datepickerEnd->setDatePicker(true);
		$datepickerEnd->setTimePicker(false);
		$dialog->addWidget($datepickerEnd);
		$dialog->addWidget(new \Widgets\Clearer());

		$timepickerEnd = new \Widgets\DatePicker();
		$timepickerEnd->setLabel("Ende (Uhrzeit):");
		$timepickerEnd->setData($object);
		$timepickerEnd->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex,"end_time"));
		$timepickerEnd->setDatePicker(false);
		$timepickerEnd->setTimePicker(true);
		$dialog->addWidget($timepickerEnd);
		$dialog->addWidget(new \Widgets\Clearer());

		$linkurlInput = new \Widgets\TextInput();
		$linkurlInput->setLabel("URL (Titel als Link)");
		$linkurlInput->setData($object);
		$linkurlInput->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "linkurl"));
		$dialog->addWidget($linkurlInput);
		$dialog->addWidget(new \Widgets\Clearer());

		$linkurlInputOpenExtern = new \Widgets\Checkbox();
		$linkurlInputOpenExtern->setLabel("In neuem Tab öffnen");
		$linkurlInputOpenExtern->setData($object);
		$linkurlInputOpenExtern->setCheckedValue("checked");
		$linkurlInputOpenExtern->setUncheckedValue("");
		$linkurlInputOpenExtern->setContentProvider(new AttributeDataProviderPortletAppointmentTerm($termIndex, "linkurl_open_extern"));
		$dialog->addWidget($linkurlInputOpenExtern);

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
