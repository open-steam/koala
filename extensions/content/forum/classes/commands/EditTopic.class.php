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
		$forumId=$this->params["forum"];
		$objectId=$this->id;
		$steam= $GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		$forum= \steam_factory::get_object($steamId, $forumId);

		/** log-in user */
		$steamUser =  \lms_steam::get_current_user();
		/** id of the log-in user */
		$steamUserId = $steamUser instanceof \steam_user ? $steamUser->get_id() : 0;
		/** the current category object */
		$object = \steam_factory::get_object($steamId, $objectId);
		/** the content of the category object */
		$object_content = $object->get_content(1);
		/** additional required attributes */
		$attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED, "bid:description"),1);
		/* get the annotating forum object */
		$forum = $object->get_annotating(1);
		/** check the rights of the log-in user */
		$allowed_write = $object->check_access_write($steamUser, 1);

		// flush the buffer
		$result = $steam->buffer_flush();
		$object_content = $result[$object_content];
		$attrib = $result[$attrib];
		$forum = $result[$forum];
		$allowed_write = $result[$allowed_write];

		$forum_attrib = $forum->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:description"), 1);
		$result = $steam->buffer_flush();
		$forum_attrib = $result[$forum_attrib];

		$content= $object_content;
		$title= $attrib[OBJ_DESC];
		$description =  isset($attrib["bid:description"]) ? $attrib["bid:description"]  : "";

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeite aktuelles Thema »" . getCleanName($object) . "«");

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
		$contentText->setWidth(480);
		$contentText->setData($object);
		$contentText->setContentProvider(\Widgets\DataProvider::contentProvider());
		$dialog->addWidget($contentText);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);
	
		/*$widget = new \Widgets\Checkbox();
		$widget->setLabel("Benutzer dürfen ihre Antworten bearbeiten");
		$widget->setData($object);
		$widget->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:forum:is_editable"));
		$widget->setCheckedValue("checked");
		$widget->setUncheckedValue("");
		$dialog->addWidget($widget);*/
		
		
	/*	$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("EditTopicContent");
		$ajaxForm->setSubmitNamespace("Forum");

		$ajaxForm->setHtml(<<<END
	<input type="hidden" name="id" value="{$this->id}">
	
	
	<div class="widgets_lable">Überschrift:</div>
	<div class="widgets_textinput"><input type="text" value="{$title}" name="title"></div><br clear="all">
	<div class="widgets_lable">Inhalt:</div>
	<div class="widgets_textarea"><textarea rows="10" style="width:100%" class="tinymce"  value="{$content}" name="content1" id="content1"></textarea><br clear="all">
	
END
		);
			
		$dialog->addWidget($ajaxForm);*/

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>