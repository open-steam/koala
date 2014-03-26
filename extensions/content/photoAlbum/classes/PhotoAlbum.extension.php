<?php
class PhotoAlbum extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "PhotoAlbum";
	}

	public function getDesciption() {
		return "Extension to view pictures.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Fotoalbum";
	}
	
	public function getObjectReadableDescription() {
		return "In Fotoalben können Sie Bilder zeigen, seien es Fotos vom letzten Schulfest oder Folien einer Präsentation.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/gallery.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PhotoAlbum\Commands\NewGallery();
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
		$objectModels = array();
		$objectModels[] = "\PhotoAlbum\Model\PhotoAlbum";
		return $objectModels;
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