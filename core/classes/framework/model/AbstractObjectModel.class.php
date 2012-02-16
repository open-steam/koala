<?php
abstract class AbstractObjectModel implements IObjectModel {
	protected $object;
	private static $instances = array();
	
	public function __call($name, $param) {
		if (is_callable(array($this->object, $name))) {
			return call_user_func_array(array($this->object, $name), $param);
		} else {
			throw new \Exception("Method " . $name . " can be called.");
		}
	}
	
	private function __construct($steamObject) {
		$this->object = $steamObject;
	}
	
	public static function getInstance($steamObject) {
		$calledClass = get_called_class();
		if ($calledClass::isObject($steamObject)) {
			if (isset(self::$instances[$steamObject->get_id()])) {
				$instance = self::$instances[$steamObject->get_id()];
			} else {
				$instance = new static($steamObject);
				self::$instances[$steamObject->get_id()] = $instance;
			}
			return $instance;
		}
		return null;
	}
	
	public static function getObjectModel($steamObject) {
		$extensions = \ExtensionMaster::getInstance()->getExtensionByType("IObjectModelExtension");
		$objectModels = array();
		foreach($extensions as $extension) {
			$objectModels = array_merge($objectModels, $extension->getObjectModels());
		}
		foreach ($objectModels as $objectModel) {
			$om = $objectModel::getInstance($steamObject);
			if ($om) {
				return $om;
			}
		}
		return null;
	}
	
	public function getReadableName() {
		return $this->getCleanName($object);
	}
	
	private function getCleanName() {
		if ($this->object instanceof steam_user) {
			$title = $this->object->get_attribute(USER_FIRSTNAME) . " " . $this->object->get_attribute(USER_FULLNAME);
		} else {
			$user = isUserHome($this->object);
			if ($user) {
				$title = getCleanName($user);
			} else if ($this->object instanceof steam_trashbin) {
				$title = "Papierkorb";
			} else {
				$desc = $this->object->get_attribute(OBJ_DESC);
				if ($desc !== 0 && trim($desc) !== "") {
					$title = $desc;
				} else {
					$title = $this->object->get_name();
				}
				$title = str_replace("'s workarea", "", stripslashes($title));
				$title = str_replace(" workarea", "", stripslashes($title));
				$title= str_replace("s workroom.", "", $title);
				$title = str_replace("s workroom", "", $title);
				$title = preg_replace("/.*'s bookmarks/", "Lesezeichen", $title);
			}
		}
		return $title;
	}
	
}
?>