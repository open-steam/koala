<?php
class Asset extends AbstractStandardCommand implements IResourcesCommand {
	
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
		$extension = $this->getExtension();
		if ($extension) {
			$extension->downloadAsset($this->params);
			die;
		}
		ExtensionMaster::getInstance()->send404Error();
	}	
}
?>