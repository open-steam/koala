<?php
class Css extends AbstractStandardCommand implements IResourcesCommand {
	
	public function isGuestAllowed(IRequestObject $requestObject) {
		return true;
	}
	
	public function workOffline(IRequestObject $requestObject) {
		return true;
	}
	
	public function validateData(IRequestObject $requestObject) {
		return true;
	}
	
	public function resourcesResponse() {
		$extension = ExtensionMaster::getInstance()->getExtensionForNamespace($this->namespace);
		if ($extension) {
			$extension->downloadCss($this->params);
			die;
		}
		ExtensionMaster::getInstance()->send404Error();
	}
}
?>