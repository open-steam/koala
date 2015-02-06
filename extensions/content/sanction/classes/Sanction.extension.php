<?php
class Sanction extends AbstractExtension{

	public function getName() {
		return "Sanction";
	}

	public function getDesciption() {
		return "Extension to view detailed sanctions of a steam Object.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Andreas", "Schultz", "schultza@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Rechteverwaltung";
	}
	
	public function getObjectReadableDescription() {
		return "Hier können sie die Rechte eines s-Team Objektes verwalten.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/gallery.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Gallery\Commands\NewGallery();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$galleryObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$galleryType = $galleryObject->get_attribute("bid:collectiontype");
		if ($galleryType==="gallery") {
			return new \PhotoAlbum\Commands\Index();
		}
		return null;
	}
	
	public function getObjectModels() {
		return null;
	}
	
	public function getCurrentObject(UrlRequestObject $urlRequestObject) {
		$params = $urlRequestObject->getParams();
		$id = $params[0];
		if (isset($id)) {
			if (!isset($GLOBALS["STEAM"])) {
				return null;
			}
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
			if (!($object instanceof steam_object)) {
				return null;
			}
			$type = getObjectType($object);
			if (array_search($type, array("gallery")) !== false) {
				return $object;
			}
		}
		return null;
	}
	
	public function getPriority() {
		return 5;
	}
}
?>