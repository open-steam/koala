<?php
namespace PortalColumn\Commands;
class NewPortlet extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->id = $this->params["portletId"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$idRequestObject = new \IdRequestObject();
		$idRequestObject->setId($this->id);

		$extensions = \ExtensionMaster::getInstance()->getExtensionByType("IObjectExtension");
		$commands = array();

		foreach ($extensions as $extension) {
			if (strstr(strtolower(get_class($extension)), "portlet")) {
				$command = $extension->getCreateNewCommand($idRequestObject);
				if ($command) {
					$commands[] = $command;
				}
			}
		}

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Erstelle ein neues Objekt in Spalte " . $object->get_name());
		$dialog->setCancelButtonLabel(NULL);
		$dialog->setSaveAndCloseButtonLabel(NULL);

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px; margin-top: 20px;\">";

		$user = \lms_steam::get_current_user();
		$homeId = $user->get_attribute("HOME_PORTAL")->get_id();

		$array = array();
		$pathArray = explode("/", $this->params["path"]);
		$portalID = "";
		for ($count = 0; $count < count($pathArray); $count++) {
			$currentID = intval($pathArray[$count]);
			if($currentID !== 0){
				$portalID = $currentID;
				break;
			}
		}

		if ($user->get_name() == "root"){
			$isRoot = true;
		} else {
			$isRoot = false;
		}

		foreach ($commands as $command) {
			$namespaces = $command->getExtension()->getUrlNamespaces();

			//forbid creation of the portlets userPicture, Chronic and Bookmarks outside of the home portal
			if($portalID != $homeId && ($namespaces[0] == "portletuserpicture" || $namespaces[0] == "portletchronic" || $namespaces[0] == "portletbookmarks")) continue;

			//forbid creation of portlets which can only be created by the root user
			if (defined("CREATE_RESTRICTED_TO_ROOT") && !$isRoot && strstr(strtolower(CREATE_RESTRICTED_TO_ROOT), strtolower($namespaces[0]))) continue;

			$html .= "<a href=\"\" onclick=\"sendRequest('{$command->getCommandName()}', {'id':{$this->id}}, 'wizard', 'wizard', null, null, '{$namespaces[0]}');return false;\" title=\"{$command->getExtension()->getObjectReadableDescription()}\" style=\"display:block; clear:both;\"><img src=\"{$command->getExtension()->getObjectIconUrl()}\" style=\"float:left;\"><p style='float:left; top: -10px; position: relative; left: 5px;'>{$command->getExtension()->getObjectReadableName()}</p></a>";

			$helpurl = $command->getExtension()->getHelpUrl();
			if($helpurl != "") $html .= "<a href=\"\" onclick=\"window.open('" . $helpurl . "', '_blank');\" title=\"mehr Informationen\"><img src='" . PATH_URL . "explorer/asset/icons/help.png\' style=\"float:right; width: 16px; height: 16px;\"></a>";
			$html .= "<br>";
		}
		$html .= "<br><div style=\"float:right\"><a class=\"bidButton negative\" onclick=\"closeDialog();return false;\" href=\"#\">Abbrechen</a></div></div><div id=\"wizard_wrapper\"></div>";

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);

		$dialog->addWidget($rawHtml);

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>
