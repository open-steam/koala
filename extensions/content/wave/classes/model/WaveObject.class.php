<?php
namespace Wave\Model;
abstract class WaveObject {
	protected $object;
	
	public function __call($name, $param) {
		if (is_callable(array($this->object, $name))) {
			return call_user_func_array(array($this->object, $name), $param);
		} else {
			throw new \Exception("Method " . $name . " can be called.");
		}
	}
	
}
?>