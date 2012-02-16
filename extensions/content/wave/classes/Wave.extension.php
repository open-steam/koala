<?php
class Wave extends AbstractExtension implements IObjectExtension, IIndexExtension{
	
	public function getName() {
		return "Wave";
	}
	
	public function getDesciption() {
		return "Extension for wave-cms.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Webseite";
	}
	
	public function getObjectReadableDescription() {
		return "Erstelle eine Webseite.";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/wave.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		//return new \Wave\Commands\NewWaveForm();
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$waveObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$waveType = $waveObject->get_attribute("OBJ_TYPE");
		if ($waveType==="container_waveside") {
			return new \Wave\Commands\Index();
		}
		return null;
	}
	
	public function getUrlNamespaces() {
		return array("wave", "");
	}
	
	public function getPriority() {
		$p = WAVE_PRIORITY;
		return $p;
	}
	
	public function handleRequest($pathArray) {
		$frame = lms_portal::get_instance();
		//$frame->initialize(GUEST_NOT_ALLOWED, false);
		$frame->init_login(GUEST_ALLOWED, false);
		lms_steam::connect( STEAM_SERVER, STEAM_PORT, $frame->get_user()->get_login(), $frame->get_user()->get_password() );
		$urlRequestObject = new UrlRequestObject();
		$urlRequestObject->setParams($pathArray);
		$command = new \Wave\Commands\Index();
		if ($command->validateData($urlRequestObject)) {
			$command->processData($urlRequestObject);
			try {
				$frameResponeObject = $command->frameResponse(new FrameResponseObject());
			} catch (steam_exception $e) {
				if ($e->get_code() === 300) {
					die ("no read access");
				}
			}
			$data = \Widgets\Widget::getData($frameResponeObject->getWidgets());
			echo $data["html"];
		}
	}
}
?>