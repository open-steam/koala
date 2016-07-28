<?php
class Portal extends AbstractExtension implements IObjectExtension, IIconBarExtension,  IObjectModelExtension{

	private $portalObject;

	public function setPortalObject($portalObject) {
		$this->portalObject = $portalObject;
	}

	public function getPortalObject() {
		return $this->portalObject;
	}

	public function getName() {
		return "Portal";
	}

	public function getDesciption() {
		return "Extension for portal.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Portal";
	}

	public function getObjectReadableDescription() {
		return "Erstellen Sie gemeinsam oder alleine eine Webseite mit Meldungen, Medienelementen, Terminen, Abstimmungen und vielem mehr";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portal.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Portal\Commands\NewPortalForm();
	}

	public function getIconBarEntries() {
		$object = self::getInstance()->getPortalObject();
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		if (isset($object) && $object->check_access_write($currentUser)) {
			$env = $object->get_environment();
			return array(
                                array("name" => "<img title=\"Breite bearbeiten und Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/portal_sort_white.png\">", "onclick"=>"sendRequest('Sort', {'id':{$object->get_id()}}, '', 'popup', null, null, 'portal');return false;"),
                                array("name" => "<img title=\"Bearbeiten\" src=\"" . PATH_URL . "styles/bid/images/icons/portlet/edit.gif\">", "link"=>"", "onclick"=>"portalLockButton({$object->get_id()}); return false;"),
                                array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;"),
                                array("name" => "<img title=\"Farben des Portals anpassen\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/colorpicker_white.png\">", "onclick"=>"sendRequest('ColorOptions', {'id':'" . $object->get_id() . "'}, '', 'popup', null, null, 'portal');return false;"),
                                array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;"),
																array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'")
                            );
		}
	}

	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Portal\Model\Portal";
		return $objectModels;
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portalObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());


		//is this a portlet? then abort TEST
		$portletType = $portalObject->get_attribute("bid:portlet");


		//TODO: replace this with a better test
		if ($portletType==="msg" || $portletType==="rss" || $portletType==="headline" || $portletType==="appointment" || $portletType==="media" || $portletType==="poll" || $portletType==="topic") {
			return ;
		} else {
			//return false;
		}

		$portalType = $portalObject->get_attribute("OBJ_TYPE");
		if ($portalType==="container_portal_bid") {
			if ($idRequestObject->getMethod() == "view") {
				return new \Portal\Commands\Index();
			}
			if ($idRequestObject->getMethod() == "properties") {
				return new \Portal\Commands\Index();
			}
		}
	}

	// resync with bid 2 portal data

	/*
	 * load bid 2 attributes and store them into bid 3 structure
	 */
	public function migrateBid2Attributes($portlet){
		echo "migrateBid2Attributes";
		$objType = $portlet->get_attribute("OBJ_TYPE");
		if (!("container_portlet_bid"===$objType)) return false;
		echo "</br>hier";

		$bidContent = $portlet->get_attribute("bid:portlet:content");
		var_dump($bidContent);


		return true;
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
			if (array_search($type, array("portal")) !== false) {
				return $object;
			}
		}
		return null;
	}

	public function getPriority() {
		return 6;
	}

        public function getReferenceTooltip(){
            $text = "Dieser Pfeil zeigt an das dies eine referenzierte Komponente ist. ";
            $text.= "Die Komponente befinden sich in einem anderen Portal, hier sehen Sie lediglich ein Abbild der Orginalkomponente. ";
            $text.= "Die Komponente kann nur in ihrem ursprünglichen Portal bearbeitet werden. ";
            $text.= "Ein Klick auf den Pfeil leitet Sie zum ursprünglichen Portal weiter. ";
            return $text;
        }

}
?>
