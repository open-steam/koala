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
		return "In Fotoalben können Sie Bilder sammeln und auf verschiedene Arten betrachten";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/gallery.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/640358/";
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
		$currentObjectID = "";
		$path = strtolower($_SERVER["REQUEST_URI"]);
		$pathArray = explode("/", $_SERVER['REQUEST_URI']);
		for ($count = 0; $count < count($pathArray); $count++) {
				if (intval($pathArray[$count]) !== 0) {
						$currentObjectID = $pathArray[$count];
						break;
				}
		}
		if ($currentObjectID === "403" || $currentObjectID === "404") {
				$currentObjectID = "";
		}
		if($currentObjectID != ""){
			if (strpos($path, "/photoalbum/explorerview/") !== false) {
				$array[] = array("name" => "<div title='Galerie-Ansicht'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/gallery.svg#gallery'/></svg></div>", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "photoAlbum/index/" . $currentObjectID . "/'");
			}
			else if (strpos($path, "/photoalbum/index/") !== false) {
				$array[] = array("name" => "<div title='Explorer-Ansicht'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/explorer.svg#explorer'/></svg></div>", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "photoAlbum/explorerView/" . $currentObjectID . "/'");
			}
			else{
				return;
			}

			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
			$env = $object->get_environment();
			//$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
			$envWriteable = ($object->check_access_write(\lms_steam::get_current_user()));
			$envSanction = $object->check_access(SANCTION_SANCTION);

			if ($envSanction) {
				$array[] = array("name" => "<div title='Neues Bild'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('Addpicture', {'id':{$currentObjectID}}, '', 'popup', null, null, 'PhotoAlbum');return false;");
				$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$currentObjectID}}, '', 'popup', null, null, 'explorer');return false;");
				$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$currentObjectID}}, '', 'popup', null, null, 'explorer');return false;");
			} elseif ($envWriteable) {
				$array[] = array("name" => "<div title='Neues Bild'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('Addpicture', {'id':{$currentObjectID}}, '', 'popup', null, null, 'PhotoAlbum');return false;");
				$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$currentObjectID}}, '', 'popup', null, null, 'explorer');return false;");
			}

			$array[] = array("name" => "SEPARATOR");

			return $array;
		}
	}
}
?>
