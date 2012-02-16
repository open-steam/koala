<?php
class Application extends AbstractExtension implements IApplicationExtension, IIndexExtension {
	
	public function init() {
		//lms_portal::get_instance()->add_javascript_src($this->getName(), $this->getAssetUrl() . "js/code.js");
	}
	
	public function getName() {
		return "Application";
	}
	
	public function getDesciption() {
		return "Extension for the main application controller.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function handleRequest($pathArray) {
		// handel Frame Requests
		$urlRequestObject = new UrlRequestObject();
		if (isset($pathArray[0])) {
			$urlRequestObject->setNamespace($pathArray[0]);
		}
		if (isset($pathArray[1])) {
			$urlRequestObject->setCommand($pathArray[1]);
		}
		if (count($pathArray) > 2) {
			$params = array();
			for($i=2; $i<count($pathArray); $i++) {
				$params[] = $pathArray[$i];
			}
			$urlRequestObject->setParams($params);
		}
		$namespaceExtension = ExtensionMaster::getInstance()->getExtensionForNamespace($urlRequestObject->getNamespace());
		
		if (isset($namespaceExtension)) {
			if ($urlRequestObject->getCommand() == "") {
				$urlRequestObject->setCommand($namespaceExtension->getDefaultCommandName($urlRequestObject->getNamespace()));
			}			
			$command = $namespaceExtension->getCommand($urlRequestObject->getCommand());
			if ($command == null) {
				if (strtolower($urlRequestObject->getCommand()) == "asset") {
					$command = new Asset();
				} else if (strtolower($urlRequestObject->getCommand()) == "css") {
					$command = new Css();
				} else if (strtolower($urlRequestObject->getCommand()) == "js") {
					$command = new Js();
				}
			}

			if ($command == null) {
				if (DEVELOPMENT_MODE) {
					throw new Exception("Command {$urlRequestObject->getCommand()} not found.");
				} else {
					ExtensionMaster::getInstance()->send404Error();
				}
			}
			
			//init commands extension
			$command->getExtension();
			
			if ($command->httpAuth($urlRequestObject)) {
				include_once PATH_LIB . "http_auth_handling.inc.php";
				if (!http_auth()) {
					die("Bitte anmelden.");
				}
			}
			
			$frame = lms_portal::get_instance();
			if ($command instanceof IResourcesCommand) {
				if ($command->validateData($urlRequestObject)) {
					if ($command->isGuestAllowed($urlRequestObject)) {
						$frame->initialize(GUEST_ALLOWED, $command->workOffline($urlRequestObject));
					} else {
						$frame->initialize(GUEST_NOT_ALLOWED, $command->workOffline($urlRequestObject));
					}
					ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($namespaceExtension->getCurrentObject($urlRequestObject));
					$command->processData($urlRequestObject);
					$command->resourcesResponse();
					die;
				}
			} else if ($command instanceof IFrameCommand) {
				if ($command->validateData($urlRequestObject)) {
					if ($command->isGuestAllowed($urlRequestObject)) {
						$frame->initialize(GUEST_ALLOWED, $command->workOffline($urlRequestObject));
					} else {
						$frame->initialize(GUEST_NOT_ALLOWED, $command->workOffline($urlRequestObject));
					}
					
					ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($namespaceExtension->getCurrentObject($urlRequestObject));
					$command->processData($urlRequestObject);
					$frameResponeObject = $command->frameResponse(new FrameResponseObject());
					
					if ($command->embedContent($urlRequestObject)) {
						$data = \Widgets\Widget::getData($frameResponeObject->getWidgets());
						$frame->add_css_style($data["css"]);
						$frame->add_javascript_code("Widgets", $data["js"]);
						$frame->set_page_main($frameResponeObject->getHeadline(), $data["html"] . "<script type=\"text/javascript\">{$data["postjs"]}</script>","");
						$frame->set_page_title($frameResponeObject->getTitle());
						$frame->set_confirmation($frameResponeObject->getConfirmText());
						$frame->set_problem_description($frameResponeObject->getProblemDescription(), $frameResponeObject->getProblemSolution());
						$frame->show_html();
						die;
					} else {
						$data = \Widgets\Widget::getData($frameResponeObject->getWidgets());
						echo $data["html"];
						die;
					}
				}
			} 
			
			if (DEVELOPMENT_MODE) {
				throw new Exception("Command {$urlRequestObject->getCommand()} execution error.");
			} else {
				ExtensionMaster::getInstance()->send404Error();
			}
		}
		ExtensionMaster::getInstance()->send404Error();
	}
}
?>