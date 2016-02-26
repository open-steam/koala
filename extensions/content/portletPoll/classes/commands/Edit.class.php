<?php
namespace PortletPoll\Commands;
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

		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Überschrift");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($descriptionInput);
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
		$datepickerStart->setContentProvider(new AttributeDataProviderPortletPollDates("start_date"));
		$dialog->addWidget($datepickerStart);
		$dialog->addWidget($clearer);

		$datepickerEnd = new \Widgets\DatePicker();
		$datepickerEnd->setLabel("Ende der Abstimmung");
		$datepickerEnd->setData($object);
		$datepickerEnd->setContentProvider(new AttributeDataProviderPortletPollDates("end_date"));
		$dialog->addWidget($datepickerEnd);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);

		$descLabelWidth = 140;
		$descInputWidth = 200;
		$voteLabelWidth = 10;
		$voteInputWidth = 20;

		//items
		//0:
		$item0Description = new \Widgets\TextInput();
		$item0Description->setLabelWidth($descLabelWidth);
		$item0Description->setInputWidth($descInputWidth);
		$item0Description->setInputBackgroundColor("rgb(255,120,111)");
		$item0Description->setLabel("Antworten");
		$item0Description->setData($object);
		$item0Description->setContentProvider(new AttributeDataProviderPortletPollVotes(0,"description"));
		$dialog->addWidget($item0Description);

		$item0Votes = new \Widgets\TextInput();
		$item0Votes->setLabelWidth($voteLabelWidth);
		$item0Votes->setInputWidth($voteInputWidth);
		$item0Votes->setInputBackgroundColor("rgb(255,120,111)");
		$item0Votes->setData($object);
		$item0Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(0,"votes"));
		$dialog->addWidget($item0Votes);
		$dialog->addWidget($clearer);

		//1:
		$item1Description = new \Widgets\TextInput();
		$item1Description->setLabelWidth($descLabelWidth);
		$item1Description->setInputWidth($descInputWidth);
		$item1Description->setInputBackgroundColor("rgb(250,186,97)");
		$item1Description->setData($object);
		$item1Description->setContentProvider(new AttributeDataProviderPortletPollVotes(1,"description"));
		$dialog->addWidget($item1Description);

		$item1Votes = new \Widgets\TextInput();
		$item1Votes->setLabelWidth($voteLabelWidth);
		$item1Votes->setInputWidth($voteInputWidth);
		$item1Votes->setInputBackgroundColor("rgb(250,186,97)");
		$item1Votes->setData($object);
		$item1Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(1,"votes"));
		$dialog->addWidget($item1Votes);
		$dialog->addWidget($clearer);

		//2:
		$item2Description = new \Widgets\TextInput();
		$item2Description->setLabelWidth($descLabelWidth);
		$item2Description->setInputWidth($descInputWidth);
		$item2Description->setInputBackgroundColor("rgb(244,229,123)");
		$item2Description->setData($object);
		$item2Description->setContentProvider(new AttributeDataProviderPortletPollVotes(2,"description"));
		$dialog->addWidget($item2Description);

		$item2Votes = new \Widgets\TextInput();
		$item2Votes->setLabelWidth($voteLabelWidth);
		$item2Votes->setInputWidth($voteInputWidth);
		$item2Votes->setInputBackgroundColor("rgb(244,229,123)");
		$item2Votes->setData($object);
		$item2Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(2,"votes"));
		$dialog->addWidget($item2Votes);
		$dialog->addWidget($clearer);

		//3:
		$item3Description = new \Widgets\TextInput();
		$item3Description->setLabelWidth($descLabelWidth);
		$item3Description->setInputWidth($descInputWidth);
		$item3Description->setInputBackgroundColor("rgb(194,222,102)");
		$item3Description->setData($object);
		$item3Description->setContentProvider(new AttributeDataProviderPortletPollVotes(3,"description"));
		$dialog->addWidget($item3Description);

		$item3Votes = new \Widgets\TextInput();
		$item3Votes->setLabelWidth($voteLabelWidth);
		$item3Votes->setInputWidth($voteInputWidth);
		$item3Votes->setInputBackgroundColor("rgb(194,222,102)");
		$item3Votes->setData($object);
		$item3Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(3,"votes"));
		$dialog->addWidget($item3Votes);
		$dialog->addWidget($clearer);

		//4:
		$item4Description = new \Widgets\TextInput();
		$item4Description->setLabelWidth($descLabelWidth);
		$item4Description->setInputWidth($descInputWidth);
		$item4Description->setInputBackgroundColor("rgb(113,182,255)");
		$item4Description->setData($object);
		$item4Description->setContentProvider(new AttributeDataProviderPortletPollVotes(4,"description"));
		$dialog->addWidget($item4Description);

		$item4Votes = new \Widgets\TextInput();
		$item4Votes->setLabelWidth($voteLabelWidth);
		$item4Votes->setInputWidth($voteInputWidth);
		$item4Votes->setInputBackgroundColor("rgb(113,182,255)");
		$item4Votes->setData($object);
		$item4Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(4,"votes"));
		$dialog->addWidget($item4Votes);
		$dialog->addWidget($clearer);

		//5:
		$item5Description = new \Widgets\TextInput();
		$item5Description->setLabelWidth($descLabelWidth);
		$item5Description->setInputWidth($descInputWidth);
		$item5Description->setInputBackgroundColor("rgb(207,163,224)");
		$item5Description->setData($object);
		$item5Description->setContentProvider(new AttributeDataProviderPortletPollVotes(5,"description"));
		$dialog->addWidget($item5Description);

		$item5Votes = new \Widgets\TextInput();
		$item5Votes->setLabelWidth($voteLabelWidth);
		$item5Votes->setInputWidth($voteInputWidth);
		$item5Votes->setInputBackgroundColor("rgb(207,163,224)");
		$item5Votes->setData($object);
		$item5Votes->setContentProvider(new AttributeDataProviderPortletPollVotes(5,"votes"));
		$dialog->addWidget($item5Votes);
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
