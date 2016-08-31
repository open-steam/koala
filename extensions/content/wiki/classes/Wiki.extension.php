<?php
class Wiki extends AbstractExtension implements IObjectExtension, IIconBarExtension {

	public function getName() {
		return "Wiki";
	}

	public function getDesciption() {
		return "Extension for Wiki View.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Wiki/Glossar/A-Z Liste";
	}

	public function getObjectReadableDescription() {
		return "In Wikis können Sie Artikel erstellen, die beliebig untereinander referenzierbar sind. Eine Versionsverwaltung ermöglicht es zudem, den Entstehungsprozess nachvollziehen zu können";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/wiki.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/728048/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Wiki\Commands\NewWikiForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$wikiObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$objectType = $wikiObject->get_attribute("OBJ_TYPE");
		if ($objectType != "0" && $objectType == "container_wiki_koala") {
			return new \Wiki\Commands\Index();
		}
		return null;
	}

	public function getPriority() {
		return 8;
	}

	public function getIconBarEntries() {
		$array = array();
		$path = strtolower($_SERVER["REQUEST_URI"]);
		if(strpos($path, "wiki") !== false){
			$pathArray = explode("/", $path);
			$wikiID = "";
			for ($count = 0; $count < count($pathArray); $count++) {
				if(strpos($pathArray[$count], '?') !== false){
					$id = intval(explode('?', $pathArray[$count])[0]);
					break;
				}
				elseif(intval($pathArray[$count]) !== 0) {
					$id = $pathArray[$count];
					break;
				}
			}
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
			$env = $object->get_environment();
			if($object instanceof steam_document){
				$object = $env;
				$wikiID = $env->get_id();
				$env = $env->get_environment();
			}
			else{
				$wikiID = $id;
			}

			//$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");

			if(strpos($path, "mediathek") == false){
				$array[] = array("name" => "<div title='Mediathek'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/gallery.svg#gallery'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/mediathek/{$wikiID}/'");
			}

			if(strpos($path, "glossary") == false){
				$array[] = array("name" => "<div title='Glossar'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/explorer.svg#explorer'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/glossary/{$wikiID}/'");
			}

			$user = lms_steam::get_current_user();
			if($object->check_access_write($user)){
				if(strpos($path, "configuration") == false){
					$array[] = array("name" => "<div title='Einstellungen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/configuration/{$wikiID}/'");
				}
				if(strpos($path, "mediathek") !== false){
					$array[] = array("name" => "<div title='Bild hinzufügen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('Upload', {'id':{$wikiID}}, '', 'popup');return false;");
				}
				if(strpos($path, "glossary") !== false){
					$array[] = array("name" => "<div title='Neuer Eintrag'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/edit/{$wikiID}'");
				}
				if(strpos($path, "entry") !== false){
					if(strpos($path, ".wiki") !== false){
						$path = $_SERVER["REQUEST_URI"];
						$pathArray = explode("/", $path);
						$wiki_doc = $object->get_object_by_name($pathArray[count($pathArray)-1]);
						$array[] = array("name" => "<div title='Bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/edit.svg#edit'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/edit/{$wiki_doc->get_id()}'");
					}
					else{
						$array[] = array("name" => "<div title='Bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/edit.svg#edit'/></svg></div>", "onclick"=>"location.href='" . PATH_URL . "wiki/edit/{$id}'");
					}
				}
			}

			$array[] = array("name" => "SEPARATOR");

			return $array;
		}
	}
}
?>
