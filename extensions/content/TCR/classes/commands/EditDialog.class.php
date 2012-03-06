<?php
namespace TCR\Commands;
class EditDialog extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		// delete uploaded file
		if ($this->params["action"] == "delete") {
			$element->delete();
			$ajaxResponseObject->setStatus("ok");
			return $ajaxResponseObject;
		}
		
		// release a thesis, set the critic
		if ($this->params["action"] == "setCritic") {
			$critics = array();
			$critics[$this->params["critic"]] = 0;
			$element->set_attribute("TCR_REVIEWS", $critics);
			$element->set_attribute("TCR_RELEASED", time());
			$ajaxResponseObject->setStatus("ok");
			return $ajaxResponseObject;
		}
		
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["tcr"]);
		// add a comment
		if ($this->params["action"] == "addComment") {
			$basegroup = $TCR->get_attribute("TCR_GROUP");
			$new_comment = \steam_factory::create_textdoc($GLOBALS[ "STEAM" ]->get_id(), $this->params["title"], stripslashes($this->params["content"]));
			$new_comment->set_read_access($basegroup);
			$new_comment->set_write_access($basegroup);
			$element->add_annotation($new_comment);
			$ajaxResponseObject->setStatus("ok");
			return $ajaxResponseObject;
		}
		// edit a comment
		if ($this->params["action"] == "editComment") {
			$comment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["edit"]);
			$comment->set_content($this->params["content"]);
			$comment->set_name($this->params["title"]);
			$ajaxResponseObject->setStatus("ok");
			return $ajaxResponseObject;
		}
		// get the container and set the dialog label based on the type of the document
		if ($this->params["type"] == 0) {
			$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
			$dialogtitle = "These bearbeiten";
		} else if ($this->params["type"] == 1) {
			$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/reviews");
			$dialogtitle = "Kritik bearbeiten";
		} else if ($this->params["type"] == 2) {
			$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/responses");
			$dialogtitle = "Replik bearbeiten";
		}
		
		// display thesis release dialog (type = 0), release review/response (type = 1/2)
		if ($this->params["action"] == "release") {
			if (($this->params["type"] == 0)) {
				$users = $TCR->get_attribute("TCR_USERS");
				$round = $element->get_attribute("TCR_ROUND");
				$theses = $container->get_inventory();
				foreach ($theses as $thesis) {
					if ($thesis->get_attribute("TCR_ROUND") == $round) {
						$reviews = $thesis->get_attribute("TCR_REVIEWS");
						foreach ($reviews as $critic => $review) {
							if (in_array($critic, $users)) {
								$key = array_search($critic, $users);
								unset($users[$key]);
							}
						}
					}
				}
				$current_user = $GLOBALS["STEAM"]->get_current_steam_user();
				if (in_array($current_user->get_id(), $users)) {
					$key = array_search($current_user->get_id(), $users);
					unset($users[$key]);
				}
				
				$htmlWidget = new \Widgets\RawHtml();
				$htmlCode = "";
				foreach ($users as $user) {
					$member = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $user);
					$pic_id = $member->get_attribute("OBJ_ICON")->get_id();
					$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/30/40";
					$htmlCode = $htmlCode . '
					<p style="margin-bottom: 2px;">
						<input type="radio"  style="vertical-align:middle;" name="critic" id="radio' . $user . '" value="' . $user . '">
						<label for="radio' . $user . '" style="vertical-align:middle;">
							<img style="vertical-align:middle;" src=' . $pic_link .'> ' . $member->get_full_name() . '
						</label>
					</p>';
				}
				$htmlCode = $htmlCode . 'Bitte beachten: Sie können die These nach dem Veröffentlichen nicht mehr bearbeiten.<br>
					<a class="button" onclick="getCritic(' . $element->get_id() . ');" style="margin-top:5px;">These veröffentlichen</a>';
				$htmlCode = $htmlCode . '<script type="text/javascript">
					function getCritic(id) {
						var radio = document.getElementsByName("critic");
						var critic = 0;
						for (var i = 0; i < radio.length; i++) {
							if (radio[i].checked) {
								critic = radio[i].value;
							}
						}
						if (critic != 0) {
							setCritic(id, critic);
						} else {
							alert("Bitte einen Kritiker auswählen.");
						}
					}

					function setCritic(id, critic) {
						var params = {};
						params.id = id;
						params.critic = critic;
						params.action = "setCritic";
						sendRequest("EditDialog", params, "", "reload");
					}</script>';
				$htmlWidget->setHtml($htmlCode);
				
				$dialog = new \Widgets\Dialog();
				$dialog->setTitle("Bitte einen Kritiker auswählen");
				$dialog->setWidth("300");
				$dialog->addWidget($htmlWidget);
				
				$ajaxResponseObject->addWidget($dialog);
				$ajaxResponseObject->setStatus("ok");
				return $ajaxResponseObject;
			} else {
				$element->set_attribute("TCR_RELEASED", time());
				$ajaxResponseObject->setStatus("ok");
				return $ajaxResponseObject;
			}
		}
		// display upload file dialog
		if ($this->params["action"] == "upload") {
			$uploadid = $element->get_attribute("TCR_FILES");
			if ($uploadid != "0") {
				$container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $uploadid);
			} else {
				$container = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $element->get_id() . "_files", $container, $element->get_id() . "_files");
				$element->set_attribute("TCR_FILES", $container->get_id());
			}
			
			$dialog = new \Widgets\Dialog();
			$dialog->setTitle("Dateien anhängen");
			$dialog->setWidth("410");
			$dialog->setForceReload(true);
			
			$upload = new \Widgets\AjaxUploader();
			$upload->setNamespace("explorer");
			$upload->setDestId($container->get_id());
			$dialog->addWidget($upload);
			
			$ajaxResponseObject->addWidget($dialog);
			$ajaxResponseObject->setStatus("ok");
			return $ajaxResponseObject;
		}
		
		// display document edit dialog
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle($dialogtitle);
		$dialog->setWidth("600");
		
		$title = new \Widgets\TextInput();
		$title->setLabel("Titel");
		$title->setData($element);
		$title->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($title);
		
		$clearer = new \Widgets\Clearer();
		$dialog->addWidget($clearer);
		
		$textarea = new \Widgets\Textarea();
		$textarea->setTextareaClass("mce-small");
		$textarea->setWidth("580");
		$textarea->setData($element);
		$textarea->setContentProvider(\Widgets\DataProvider::contentProvider());
		$dialog->addWidget($textarea);
		$dialog->addWidget($clearer);
			
		$ajaxResponseObject->addWidget($dialog);
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}