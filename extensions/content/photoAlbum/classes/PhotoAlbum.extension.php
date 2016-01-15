<?php
class PhotoAlbum extends AbstractExtension implements IObjectExtension, IObjectModelExtension, IIconBarExtension{

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

	public function getIconBarEntries() {
		$path = strtolower($_SERVER["REQUEST_URI"]);
		$pathShort = rtrim($path, "/");
		$arr = explode('/', $pathShort);
		$id = $arr[count($arr)-1];

		if (strpos($path, "/photoalbum/explorerview/") !== false) {
			$array[] = array("name" => "<img title=\"Galerie-Ansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/gallery.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "photoAlbum/index/" . $id . "/'");
		}
		else if (strpos($path, "/photoalbum/index/") !== false) {
			$array[] = array("name" => "<img title=\"Explorer-Ansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/explorer.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "photoAlbum/explorerView/" . $id . "/'");
		}
		else{
			return;
		}

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		$envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
		$envSanction = $object->check_access(SANCTION_SANCTION);

		if ($envSanction) {
			$array[] = array("name" => "<img title=\"Neues Bild\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('Addpicture', {'id':{$id}}, '', 'popup', null, null, 'PhotoAlbum');return false;");
			$array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$id}}, '', 'popup', null, null, 'explorer');return false;");
			$array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$id}}, '', 'popup', null, null, 'explorer');return false;");
		} elseif ($envWriteable) {
			$array[] = array("name" => "<img title=\"Neues Bild\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('Addpicture', {'id':{$id}}, '', 'popup', null, null, 'PhotoAlbum');return false;");
			$array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$id}}, '', 'popup', null, null, 'explorer');return false;");
		}

		return $array;
	}
}
?>
