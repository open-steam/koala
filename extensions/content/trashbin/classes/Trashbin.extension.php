<?php
class Trashbin extends AbstractExtension implements IObjectExtension, IObjectModelExtension{

	public function getName() {
		return "Trashbin";
	}

	public function getDesciption() {
		return "Extension to manage files.";
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
		return "Trashbin";
	}

	public function getObjectReadableDescription() {
		return "Verwaltung von Daten, welche gelöscht werden können.";
	}

	public function getObjectIconUrl() {
		//TODO:Update Icon
		return null;
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
                return false; //quick fix TODO: test object for trashbin
		return new \Trashbin\Commands\Index();
	}


	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Trashbin\Model\Trashbin";
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
			//$type = getObjectType($object);
			//if (array_search($type, array("calendar")) !== false) {
			return $object;
			//}
		}
		return null;
	}
	public function getPriority() {
		return 6;
	}
	public function deletePyramiddiscussion($object){
			$group = $object->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
			$user = $GLOBALS["STEAM"]->get_current_steam_user();

			if ($group->check_access(SANCTION_WRITE, $user)) {
					$id = $object->get_id();
					$instances = $group->get_attribute("PYRAMIDDISCUSSION_INSTANCES");
					if (!is_array($instances)) {
							$instances = array($id);
					}

					foreach ($instances as $key => $value) {
							if ($value == $id) {
									unset($instances[$key]);
							}
					}
					$instances = array_values($instances);

					if (!empty($instances)) {
							$group->set_attribute("PYRAMIDDISCUSSION_INSTANCES", $instances);
					} else {
							// no other instances of this pyramiddiscussion exist, delete groups
							$group->delete();
					}
					$object->delete();
			}
	}
}
?>
