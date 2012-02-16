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
		/** log-in user */
		$steamUser =  \lms_steam::get_current_user();
		/** id of the log-in user */
		$steamUserId = $steamUser->get_id();
		/** name of the log-in user */
		$steamUserName = $steamUser->get_name();

		/** the current object */
		$object = \steam_factory::get_object($steamId, $object_id);
		/** the content of the message object */
		$object_content = $object->get_content(1);
		/** additional required attributes */
		$attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, OBJ_LAST_CHANGED),1);
		/** get the annotating category */
		$category = $object->get_annotating(1);
		/** check the rights of the log-in user */
		$allowed_write = $object->check_access_write($steamUser, 1);

		// flush the buffer
		$result = $steam->buffer_flush();
		$object_content = $result[$object_content];
		$attrib = $result[$attrib];
		$category = $result[$category];
		$allowed_write = $result[$allowed_write];

		$category_attributes = $category->get_attributes(array(OBJ_NAME, OBJ_DESC), 1);
		$result = $steam->buffer_flush();
		$category_attributes = $result[$category_attributes];

		$content= $object_content;
		$title= $attrib[OBJ_DESC];



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
		/*$ajaxForm = new \Widgets\AjaxForm();
		 $ajaxForm->setSubmitCommand("EditReplyContent");
		 $ajaxForm->setSubmitNamespace("Forum");

		 $ajaxForm->setHtml(<<<END
		 <input type="hidden" name="id" value="{$this->id}">
		 <input type="hidden" name="forum" value="{$this->params["forum"]}">
		 <div class="widgets_lable">Überschrift:</div>
		 <div class="widgets_textinput"><input type="text" value="{$title}" name="title"></div><br clear="all">
		 <div class="widgets_lable">Inhalt:</div>
		 <div class="widgets_textarea"><textarea rows="10" style="width:100%" class="tinymce"  value="{$content}" name="content1" id="content1"></textarea><br clear="all">
		 <input id="content_id" type="hidden" name="content" value="">
		 <script type="text/javascript">
		 tinyMCE.init({
		 mode : "textareas",

		 // General options
		 theme : "advanced",
		 skin: "o2k7",
		 id: "content1",
		 name:"content1",
		 force_br_newlines : true,
		 force_p_newlines : false,
		 forced_root_block : '',

		 language: "de",
		 plugins : "emotions,paste",

			//Specific options
			setup : function(ed) {
			ed.onKeyUp.add(function(ed, o) {
			var content = "<p>"+ed.getContent()+"</p>";
			$("#content_id").val(content);
			});
			ed.onInit.add(function(ed) {
			ed.setContent('{$content}');
			});

			},

			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,|,image,link,unlink,|,forecolor,removeformat,|,undo,redo,pasteword,|,code",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "none",
			theme_advanced_resizing : false
			});

			</script>
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
