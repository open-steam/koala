<?php
class Databinding extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public static function getAttributeValue($object, $attribute) {
		if (preg_match("/\(\[(.*?)\]\)/", $attribute, $match)) {
			$attribute = str_replace($match[0], "", $attribute);
			$arrayId = $match[1];
			$array = $object->get_attribute($attribute);
			if (is_array($array)) {
				if (isset($array[$arrayId])) {
					return $array[$arrayId];
				}
			}
			return "";
		} else {
			return $object->get_attribute($attribute);
		}
	}

	public static function setAttributeValue($object, $attribute, $value) {
		if (preg_match("/\(\[(.*?)\]\)/", $attribute, $match)) {
			$attribute = str_replace($match[0], "", $attribute);
			$arrayId = $match[1];
			$array = $object->get_attribute($attribute);
			if (!is_array($array)) {
				$array = array();
			}
			$array[$arrayId] = $value;
			return $object->set_attribute($attribute, $array); //html decodeing
		} else {
			return $object->set_attribute($attribute, html_entity_decode($value)); //html decodeing
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

		$data = array();
		if (isset($this->params["attribute"]) && isset($this->params["value"])) {
			$oldValue = self::getAttributeValue($this->object, $this->params["attribute"]);
			try {
				self::setAttributeValue($this->object, $this->params["attribute"], $this->params["value"]);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");

			$newValue = self::getAttributeValue($this->object, $this->params["attribute"]);

			if ($newValue === $this->params["value"]) {
				$data["oldValue"] = $oldValue;
				$data["newValue"] = $newValue;
				$data["error"] = "none";
				$data["undo"] = true;
			 } else {
				 if ($oldValue !== $newValue) {
					 self::setAttributeValue($this->object, $this->params["attribute"], $oldValue);
				 }
			 	$data["oldValue"] = $oldValue;
			 	$data["error"] = "Data could not be saved.";
				$data["undo"] = false;
			 }
			 $ajaxResponseObject->setData($data);
		} else if (isset($this->params["value"]) && !isset($this->params["attribute"]) && ($this->object instanceof steam_document)) {
			$oldValue = $this->object->get_content();
			try {
				$this->object->set_content(cleanHTML($this->params["value"]));
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");

			$newValue = $this->object->get_content();

			$data["oldValue"] = $oldValue;
			$data["newValue"] = $newValue;
			$data["error"] = "none";
			$data["undo"] = true;

			 $ajaxResponseObject->setData($data);
		} else if (isset($this->params["annotate"])) {
			$newValue = $this->params["annotate"];
			$oldValue = "";
			try {
				$annotation = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Annotation", $newValue, "text/plain");
				$annotation->set_sanction_all(\steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "steam"));
				$this->object->add_annotation($annotation);
				$data["oldValue"] = "";
				$data["newValue"] = "";
				$data["error"] = "none";
				$data["undo"] = false;
			} catch (steam_exception $e) {
				$data["oldValue"] = "";
				$data["error"] = $e->get_message();
				$data["undo"] = false;
			}
			$ajaxResponseObject->setStatus("ok");
			$ajaxResponseObject->setData($data);
			return $ajaxResponseObject;

		} else {
			$ajaxResponseObject->setStatus("error");
		}
		return $ajaxResponseObject;
	}
}
?>
