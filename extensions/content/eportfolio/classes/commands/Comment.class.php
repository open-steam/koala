<?php
namespace Portfolio\Commands;

class Comment extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $entry;

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
		if (!isset($this->id) || $this->id === "") {
			throw new \Exception("no valid id");
		} else {
			$room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($room instanceof \steam_room) {
				$this->entry = \Portfolio\Model\Entry::getEntryByRoom($room);
			}
		}
		$this->id = $this->entry->get_id();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$currentUserName = $currentUser->get_name();
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eintrag kommentieren");
		$dialog->setDescription("...");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);		
		
		// get discussion thread between portfolio owner and current user or create empty thread
		$threads = $this->entry->get_annotations();
		$discussion = null;
		foreach ($threads as $thread) {
			if ($thread->get_name() === $currentUserName) {
				$discussion = $thread;
			}
		} 
		if (!isset($discussion)) {
			$discussion = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $currentUserName, "Discussing with " . $currentUserName . ".", "text/plain");
			$this->entry->add_annotation($discussion);
		}
		
		$chat = new \Widgets\Chat();
		$chat->setData($discussion);
		$dialog->addWidget($chat);
		
		$dialog->addWidget(new \Widgets\Clearer());
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("<hr>");
		$dialog->addWidget($rawHtml);
		
		$textinput = new \Widgets\TextInput();
		$textinput->setData($discussion);
		$textinput->setContentProvider(\Widgets\DataProvider::annotationDataProvider());
		$textinput->setLabel("Kommentar schreiben");
		$dialog->addWidget($textinput);
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>