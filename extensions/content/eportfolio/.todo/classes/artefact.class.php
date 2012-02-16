<?php
class artefact {

	private $steamobject;

	public function __construct(steam_object $so) {
		$this->steamobject = $so;
	}

	public function __get($name) {
		return $this->steamobject->get_attribute($name);
	}

	public function __set($name, $value) {
		return $this->steamobject->set_attribute($name, $value);
	}

	public function __isset($name) {
		$names = $this->steamobject->get_attribute_names();
		return in_array($name, $names);
	}

	public function __unset($name) {
		$this->steamobject->delete_attribute($name);
	}

	public function __call($name, $param) {
		if (is_callable(array($this->steamobject, $name), $param)) {
			return call_user_func_array(array($this->steamobject, $name), $param);
		} else {
			throw new Exception("Method " . $name . " can be called.");
		}
	}

	public function authorize_discuss($pPersonOrGroup){
		$this->steamobject->set_rights_annotate( $pPersonOrGroup, 1);
		if(__isset("authorized_discuss")){
			__set("authorized_discuss", array_push(__get("authorized_discuss"),$pPersonOrGroup));
		} else {
			__set("authorized_discuss", array($pPersonOrGroup));
		}
	}

	public function revoke_discuss($pPersonOrGroup){
		$this->steamobject->set_rights_annotate( $pPersonOrGroup, 0);
		if(__isset("authorized_discuss")){
			$authorized_discuss = __get("authorized_discuss");
			array_unset($authorized_discuss[array_search($authorized_discuss, $pPersonOrGroup)]);
			__set("authorized_discuss", $authorized_discuss);
		}
	}

	public function authorize_read($pPersonOrGroup){
		$this->steamobject->set_read_access($pPersonOrGroup, 1);
		if(__isset("authorized_read")){
			__set("authorized_read", array_push(__get("authorized_read"),$pPersonOrGroup));
		} else {
			__set("authorized_read", array($pPersonOrGroup));
		}
	}

	public function revoke_read($pPersonOrGroup){
		$this->steamobject->set_read_access($pPersonOrGroup, 0);
		if(__isset("authorized_read")){
			$authorized_discuss = __get("authorized_read");
			array_unset($authorized_discuss[array_search($authorized_discuss, $pPersonOrGroup)]);
			__set("authorized_read", $authorized_discuss);
		}
	}

	public function in_portfolio(){
		return __get("in_portfolio");
	}

	public function get_portfolios(){
		__get("portfolios");
	}

	public function set_portfolio($portfolio){
		$portfolios = array();
		if (__isset("portfolios")){
			$portfolios = __get("portfolios");
			array_push($portfolios, $portfolio);
			__set("portfolios", $portfolios);
		} else {
			__set("portfolios", array($portfolio));
		}
		__set("in_portfolio", true);
	}
}
?>