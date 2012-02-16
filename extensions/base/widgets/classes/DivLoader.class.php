<?php
namespace Widgets;

class DivLoader extends Widget {
	
	private $wrapperId;
	private $message;
	private $command;
	private $namespace = "";
	private $params;
	private $elementId;
	private $type;
	private $height = 50;
	
	public function setWrapperId($wrapperId) {
		$this->wrapperId = $wrapperId;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}
	
	public function setCommand($command) {
		$this->command = $command;
	}
	
	public function setNamespace($namespace) {
		$this->namespace = ", \"" . $namespace . "\"";
	}
	
	public function setParams($params) {
		$this->params = $params;
	}
	
	public function setElementId($elementId) {
		$this->elementId = $elementId;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function setheight($height) {
		$this->height = $height;
	}
	
	public function getHtml() {
		$this->getContent()->setVariable("WRAPPER_ID", $this->wrapperId);
		$this->getContent()->setVariable("MESSAGE", $this->message);
		$this->getContent()->setVariable("IMAGE_SRC", PATH_URL . "widgets/asset/loading.gif");
		$this->getContent()->setVariable("COMMENT", $this->command);
		$this->getContent()->setVariable("NAMESPACE", $this->namespace);
		$this->getContent()->setVariable("ELEMENT_ID", $this->elementId);
		$this->getContent()->setVariable("TYPE", $this->type);
		$this->getContent()->setVariable("HEIGHT", $this->height);
		$params = "";
		if (isset($this->params)) {
			foreach(array_keys($this->params) as $key) {
				$value = $this->params[$key];
				$params .= "\"" . $key . "\":\"" . $value . "\",";
			}	
			$params = substr($params, 0, -1);
		}	
		$this->getContent()->setVariable("PARAMS", "{" . $params . "}");
		return $this->getContent()->get();
	}
}
?>