<?php
abstract class GenericObject{
	
	public function __call($name, $arguments) {
		$reflect = new ReflectionClass($this);
		$props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
		$fields = array();
		foreach ($props as $key => $value) {
			$fields[] = $value->getName();
		}
		if (strStartsWith($name, "get")) {
			$field = lcfirst(preg_replace("#get(.*)#", "$1", $name));
			if (in_array($field, $fields, true)) {
				return $this->$field;
			} else {
				throw new Exception("Field $field not defined.");
			}
		}
		
		if (strStartsWith($name, "set")) {
			$field = lcfirst(preg_replace("#set(.*)#", "$1", $name));
			if (in_array($field, $fields, true)) {
				$this->$field = $arguments[0];
			} else {
				throw new Exception("Field $field not defined.");
			}
		}
	}
	
}
?>