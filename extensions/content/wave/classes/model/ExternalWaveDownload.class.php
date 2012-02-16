<?php
namespace Wave\Model;
class ExternalWaveDownload extends WaveDownload {
	
	private $filePath;
	
	public function __construct($filePath) {
		$this->setFilePath($filePath);
	}
	
	public function setFilePath($filePath) {
		$this->filePath = $filePath;
	}
	
	public function getFilePath() {
		return $this->filePath;
	}

	public function download() {
		\Wave::getInstance()->download($this->getFilePath());
	}
	
}
?>