<?php
abstract class AbstractCommand implements ICommand{
	
	public function isGuestAllowed(IRequestObject $requestObject) {
		return true;
	}
	
	public function serverAdminOnly(IRequestObject $requestObject) {
		return false;
	}
	
	public function embedContent(IRequestObject $requestObject) {
		return true;
	}
	
	public function workOffline(IRequestObject $requestObject) {
		return false;
	}
	
	public function httpAuth(IRequestObject $requestObject) {
		return false;
	}
	
	public function getCommandName() {
		$commandNameArray = explode("\\", get_class($this));
		$commandName = $commandNameArray[count($commandNameArray)-1];
		return $commandName;
	}
	
	public function getExtensionName() {
		$commandNameArray = explode("\\", get_class($this));
		$extensionName = $commandNameArray[0];
		return "\\" . $extensionName;
	}
	
	public function getExtension() {
		$name = $this->getExtensionName();
		return $name::getInstance();
	}
}
?>