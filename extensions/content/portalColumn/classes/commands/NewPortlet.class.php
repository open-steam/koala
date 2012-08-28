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
		$dialog->setCloseButtonLabel(null);
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		$html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px\">";
		
		foreach ($commands as $command) {
			$namespaces = $command->getExtension()->getUrlNamespaces();
			$html .= "<a href=\"\" onclick=\"sendRequest('{$command->getCommandName()}', {'id':{$this->id}}, 'wizard', 'wizard', null, null, '{$namespaces[0]}');return false;\" title=\"{$command->getExtension()->getObjectReadableDescription()}\"><img src=\"{$command->getExtension()->getObjectIconUrl()}\"> {$command->getExtension()->getObjectReadableName()}</a><br>";
		}
		$html .= "<div style=\"float:right\"><a class=\"button pill negative\" onclick=\"closeDialog();return false;\" href=\"#\">Abbrechen</a></div></div><div id=\"wizard_wrapper\"></div>";
		
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		
		$dialog->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}
}
?>