<?php
namespace Widgets;

class AjaxUploader extends Widget {
	
	private $backend = "";
	private $command = "Upload";
	private $namespace = "Explorer";
	private $descId;
	private $sizeLimit = 2000000;
	private $allowedExtensions = "[]";
	private $preview;
	private $multiUpload = true;
	private $onComplete = "function(id, fileName, responseJSON){}";
	
	public function setNamespace($namespace) {
		$this->backend = PATH_URL . $namespace . "/";
		$this->namespace = $namespace;
	}
	
	public function setDestId($destId) {
		$this->destId = $destId;
	}
	
	public function setSizeLimit($size) {
		$this->sizeLimit = $size;
	}
	
	public function setAllowedExtensions($extensions) {
		$this->allowedExtensions = $extensions;
	}
	
	public function setPreview($preview) {
		$this->preview = $preview;
	}
	
	public function setMultiUpload($multiUpload) {
		$this->multiUpload = $multiUpload;
	}
	
	public function setCommand($commandName) {
		$this->command = $commandName;
	}
	
	public function setOnComplete($onComplete) {
		$this->onComplete = $onComplete;
	}
	
	public function getHtml() {
		$this->getContent()->setVariable("BACKEND", $this->backend);
		$this->getContent()->setVariable("COMMAND", $this->command);
		$this->getContent()->setVariable("NAMESPACE", $this->namespace);
		$this->getContent()->setVariable("DESTID", $this->destId);
		$this->getContent()->setVariable("SIZELIMIT", $this->sizeLimit);
		$this->getContent()->setVariable("ALLOWEDEXTENSIONS", $this->allowedExtensions);
		$this->getContent()->setVariable("ONCOMPLETE", $this->onComplete);
		if (isset($this->preview)) {
			$this->getContent()->setVariable("PREVIEW", $this->preview->getHtml());
		}
		if ($this->multiUpload) {
			$this->getContent()->setVariable("MULTIPLE", "true");
		} else {
			$this->getContent()->setVariable("MULTIPLE", "false");
		}
		return $this->getContent()->get();
	}
}
?>