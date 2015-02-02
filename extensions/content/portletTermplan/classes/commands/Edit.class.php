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
		
		
		$termLabelWidth = 50;
		$termInputWidth = 300;
		
		//term descriptions
		$term0 = new \Widgets\TextInput();
		$term0->setLabelWidth($termLabelWidth);
		$term0->setInputWidth($termInputWidth);
		$term0->setLabel("Eintrag 1");
		$term0->setData($object);
		$term0->setContentProvider(new AttributeDataProviderPortletTermplanEntries("0"));
		$dialog->addWidget($term0);
		$dialog->addWidget($clearer);
		
		$term1 = new \Widgets\TextInput();
		$term1->setLabelWidth($termLabelWidth);
		$term1->setInputWidth($termInputWidth);
		$term1->setLabel("Eintrag 2");
		$term1->setData($object);
		$term1->setContentProvider(new AttributeDataProviderPortletTermplanEntries("1"));
		$dialog->addWidget($term1);
		$dialog->addWidget($clearer);
		
		$term2 = new \Widgets\TextInput();
		$term2->setLabelWidth($termLabelWidth);
		$term2->setInputWidth($termInputWidth);
		$term2->setLabel("Eintrag 3");
		$term2->setData($object);
		$term2->setContentProvider(new AttributeDataProviderPortletTermplanEntries("2"));
		$dialog->addWidget($term2);
		$dialog->addWidget($clearer);
		
		$term3 = new \Widgets\TextInput();
		$term3->setLabelWidth($termLabelWidth);
		$term3->setInputWidth($termInputWidth);
		$term3->setLabel("Eintrag 4");
		$term3->setData($object);
		$term3->setContentProvider(new AttributeDataProviderPortletTermplanEntries("3"));
		$dialog->addWidget($term3);
		$dialog->addWidget($clearer);
		
		$term4 = new \Widgets\TextInput();
		$term4->setLabelWidth($termLabelWidth);
		$term4->setInputWidth($termInputWidth);
		$term4->setLabel("Eintrag 5");
		$term4->setData($object);
		$term4->setContentProvider(new AttributeDataProviderPortletTermplanEntries("4"));
		$dialog->addWidget($term4);
		$dialog->addWidget($clearer);
		
		$term5 = new \Widgets\TextInput();
		$term5->setLabelWidth($termLabelWidth);
		$term5->setInputWidth($termInputWidth);
		$term5->setLabel("Eintrag 6");
		$term5->setData($object);
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