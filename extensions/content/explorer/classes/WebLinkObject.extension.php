<?php
class WebLinkObject extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "WebLinkObject";
	}
	
	public function getDesciption() {
		return "Extension for web link object.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getId() {
		return "Explorer";
	}
	
	public function getObjectReadableName() {
		return "WWW-Referenz";
	}
	
	public function getObjectReadableDescription() {
		return "WWW-Referenzen ermöglichen es Ihnen, Quellen aus dem Internet in Ihre Ordner einzubinden.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/www.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewWebLinkForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_docextern) {
			return new \Explorer\Commands\ViewDocument();
		}
	}
	
	public function getPriority() {
		return 8;
	}
}
?>