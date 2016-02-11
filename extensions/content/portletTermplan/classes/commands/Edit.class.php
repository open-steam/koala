<?php
namespace PortletTermplan\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

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

		$clearer = new \Widgets\Clearer();

		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);

		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([poll_topic])"));
		$dialog->addWidget($descriptionInput);
		$dialog->addWidget($clearer);

		//datepicker
		$datepickerStart = new \Widgets\DatePicker();
		$datepickerStart->setLabel("Start der Abstimmung");
		$datepickerStart->setData($object);
		$datepickerStart->setContentProvider(new AttributeDataProviderPortletTermplanDates("start_date"));
		$dialog->addWidget($datepickerStart);
		$dialog->addWidget($clearer);

		$datepickerEnd = new \Widgets\DatePicker();
		$datepickerEnd->setLabel("Ende der Abstimmung");
		$datepickerEnd->setData($object);
		$datepickerEnd->setContentProvider(new AttributeDataProviderPortletTermplanDates("end_date"));
		$dialog->addWidget($datepickerEnd);
		$dialog->addWidget($clearer);

		//term descriptions
		$term0 = new \Widgets\DatePicker();
		$term0->setLabel("Termin 1");
		$term0->setData($object);
		$term0->setTimePicker(true);
		$term0->setContentProvider(new AttributeDataProviderPortletTermplanEntries("0"));
		$dialog->addWidget($term0);
		$dialog->addWidget($clearer);

		$term1 = new \Widgets\DatePicker();
		$term1->setLabel("Termin 2");
		$term1->setData($object);
		$term1->setTimePicker(true);
		$term1->setContentProvider(new AttributeDataProviderPortletTermplanEntries("1"));
		$dialog->addWidget($term1);
		$dialog->addWidget($clearer);

		$term2 = new \Widgets\DatePicker();
		$term2->setLabel("Termin 3");
		$term2->setData($object);
		$term2->setTimePicker(true);
		$term2->setContentProvider(new AttributeDataProviderPortletTermplanEntries("2"));
		$dialog->addWidget($term2);
		$dialog->addWidget($clearer);

		$term3 = new \Widgets\DatePicker();
		$term3->setLabel("Termin 4");
		$term3->setData($object);
		$term3->setTimePicker(true);
		$term3->setContentProvider(new AttributeDataProviderPortletTermplanEntries("3"));
		$dialog->addWidget($term3);
		$dialog->addWidget($clearer);

		$term4 = new \Widgets\DatePicker();
		$term4->setLabel("Termin 5");
		$term4->setData($object);
		$term4->setTimePicker(true);
		$term4->setContentProvider(new AttributeDataProviderPortletTermplanEntries("4"));
		$dialog->addWidget($term4);
		$dialog->addWidget($clearer);

		$term5 = new \Widgets\DatePicker();
		$term5->setLabel("Termin 6");
		$term5->setData($object);
		$term5->setTimePicker(true);
		$term5->setContentProvider(new AttributeDataProviderPortletTermplanEntries("5"));
		$dialog->addWidget($term5);
		$dialog->addWidget($clearer);

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
