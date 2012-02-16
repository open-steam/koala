<?php
namespace Wave\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	private $params;
	private $calledNamespace;
	private $calledCommand;
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function embedContent(\IRequestObject $requestObject) {
		return false;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->calledNamespace = $requestObject->getNamespace();
		$this->calledCommand = $requestObject->getCommand();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portal = \lms_portal::get_instance();
		$content = \Wave::getInstance()->loadTemplate("wave.template.html");
		
		if ((strtolower($this->calledNamespace) === "wave") && (strtolower($this->calledCommand) === "index")) {
			$sideId = $this->params[0];
			$internalPath = $this->params;
			array_shift($internalPath);
			$sideUrl = $this->getExtension()->getExtensionUrl() . "Index/" . $sideId . "/";
		} else {
			$sideObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), WAVE_PATH_INTERN);
			if ($sideObject instanceof \steam_object) {
				$sideId = $sideObject->get_id();
				$internalPath = $this->params;
				$sideUrl = PATH_URL;
			} else {
				die ("WAVE config broken (WAVE_PATH_INTERN: " . WAVE_PATH_INTERN . ")");
			}
		}
		if (!isset($internalPath)) {
			$internalPath = array();
		}
		$waveEngine =  new \Wave\Model\WaveEngine($sideId, $internalPath, $sideUrl);
		$waveSide = $waveEngine->getSide();
		$waveObject = $waveEngine->getCurrentObject();
		if ($waveObject instanceof  \Wave\Model\WavePage) {
			$content->setVariable("CONTENT", $waveObject->getHtml());
			$rawHtml = new \Widgets\RawHtml();
			$rawHtml->setHtml($content->get());
			$frameResponseObject->addWidget($rawHtml);
			return $frameResponseObject;
		} else if ($waveObject instanceof \Wave\Model\WaveDownload) {
			$waveObject->download();
			die;
		} else {
			die ("Not Wave-Object detected.");
		}
	}
}
?>