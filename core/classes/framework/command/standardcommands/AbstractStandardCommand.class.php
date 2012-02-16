<?php
abstract class AbstractStandardCommand extends AbstractCommand {
	
	protected $namespace, $command, $params;
	
	public function processData(IRequestObject $requestObject) {
		$this->namespace = $requestObject->getNamespace();
		$this->command = $requestObject->getCommand();
		$this->params = $requestObject->getParams();
	}
	
	public function getCommandName() {
		return $this->command;
	}
	
	public function getNamespace() {
		return $this->namespace;
	}
	
	public function getExtension() {
		return ExtensionMaster::getInstance()->getExtensionForNamespace($this->getNamespace());
	}
	
	public function getExtensionName() {
		return get_class($this->getExtension());
	}
}
?>