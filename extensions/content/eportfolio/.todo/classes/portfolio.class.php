<?php
class portfolio_old {

	public static function get_my_portfolios() {
		$portfolios = array();
		$user = lms_steam::get_current_user();
		$workroom = $user->get_workroom();
		if (!($workroom->get_object_by_name("portfolio") instanceof steam_room)){
			return array();
		}
		$portfolios[] = "1";
		return $portfolios;
	}

	public static function init() {
		$user = lms_steam::get_current_user();
		$workroom = $user->get_workroom();
		$portfolio_main_room = steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"portfolio",
			$workroom,
			"room for portfolio module"
		);
		$artefacts_room = steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"artefacts",
			$portfolio_main_room,
			"room for artefacts for portfolios"
		);
		$portfolios_room = steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"portfolios",
			$portfolio_main_room,
			"room for portfolios"
		);
	}

	//	$name;
	//	$artefacts;
	//	$goal;
	//	$topic;
	//	$conditions;
	//	$estimated_completion_date;
	//	$rights;
	//	$addressed_to;
	//	$type;
	//	$audience;
	//  $owner;
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

	public function add_artefact($artefact){
		if ($obj = $this->get_object_by_name("portfolio.xml")){
			$xml_string = $obj->get_content();
		}
		$xml_obj = simplexml_load_string($xml_string);
		if ($xml_obj->portfolio != ""){
			//node artefacts exists
			$artefacts = $xml_obj->xpath('/portfolio/artefacts');
		} else {
			$portfolio = $xml_obj->addChild('portfolio');
			$artefacts = $portfolio->addChild('artefacts');
		}
		$xml_artefact = $artefacts->addChild("artefact");
		$xml_artefact->addAttribute("id", $artefact->get_id());
		$xml_artefact->addAttribute("name", $artefact->get_name());
		$obj->set_content($xml_obj->asXML());
		//$xml_artefact->addAttribute("description", $artefact->get_description());

	}

	public function add_artefacts($artefacts){
		foreach ($artefacts as $artefact) {
			$this->add_artefact($artefact);
		}
	}

	public function remove_artefact($artefact){
		if ($obj = $this->get_object_by_name("portfolio.xml")){
			$xml_string = $obj->get_content();
		}
		$xml_obj = simplexml_load_string($xml_string);
		$artefacts = $xml_obj->portfolio->artefacts;

		foreach ($artefacts->artefact as $variable) {
			if($varible['id'] == $artefact->get_id()){
				$dom = dom_import_simplexml($variable);
				$dom->parentNode->removeChild($dom);
			}
		}
		$obj->set_content($xml_obj->asXML());
	}

	public function get_artefacts(){
		$xml_string = $this->get_object_by_name("portfolio.xml")->get_content();
		$xml_obj = simplexml_load_string($xml_string);
		$artefacts = $xml_obj->portfolio->artefacts;
		$artefacts_array = array();
		foreach ($artefacts->children() as $artefact) {
			$id = $artefact->getAttribute('id');
			$artefacts_array[] = steam_factory::get_object($GLOBALS['STEAM']->get_id(), $id, CLASS_DOCUMENT);
		}
		return $artefacts_array;
	}
	/*
	 public function authorize_discuss($pPersonOrGroup){
		if (!__isset("ARTEFACTS")){
		return false;
		} else {
		$container = $this->steamobject->get_attribute("ARTEFACTS");
		foreach ($container->get_inventory() as $item) {
		if ($item instanceof steam_link){
		$item->get_source_object()->authorize_discuss($pPersonOrGroup);
		}
		}
		}
		$this->steamobject->set_rights_annotate( $pPersonOrGroup, 1);
		if(__isset("authorized_discuss")){
		__set("authorized_discuss", array_push(__get("authorized_discuss"),$pPersonOrGroup));
		} else {
		__set("authorized_discuss", array($pPersonOrGroup));
		}
		}

		public function revoke_discuss($pPersonOrGroup){
		if (!__isset("ARTEFACTS")){
		return false;
		} else {
		$container = $this->steamobject->get_attribute("ARTEFACTS");
		foreach ($container->get_inventory() as $item) {
		if ($item instanceof steam_link){
		$item->get_source_object()->revoke_discuss($pPersonOrGroup);
		}
		}
		}
		$this->steamobject->set_rights_annotate( $pPersonOrGroup, 0);
		if(__isset("authorized_discuss")){
		$authorized_discuss = __get("authorized_discuss");
		array_unset($authorized_discuss[array_search($authorized_discuss, $pPersonOrGroup)]);
		__set("authorized_discuss", $authorized_discuss);
		}
		}

		public function authorize_read($pPersonOrGroup){
		if (!__isset("ARTEFACTS")){
		return false;
		} else {
		$container = $this->steamobject->get_attribute("ARTEFACTS");
		foreach ($container->get_inventory() as $item) {
		if ($item instanceof steam_link){
		$item->get_source_object()->authorize_read($pPersonOrGroup);
		}
		}
		}
		$this->steamobject->set_read_access($pPersonOrGroup, 1);
		if(__isset("authorized_read")){
		__set("authorized_read", array_push(__get("authorized_read"),$pPersonOrGroup));
		} else {
		__set("authorized_read", array($pPersonOrGroup));
		}
		}

		public function revoke_read($pPersonOrGroup){
		if (!__isset("ARTEFACTS")){
		return false;
		} else {
		$container = $this->steamobject->get_attribute("ARTEFACTS");
		foreach ($container->get_inventory() as $item) {
		if ($item instanceof steam_link){
		$item->get_source_object()->revoke_read($pPersonOrGroup);
		}
		}
		}
		$this->steamobject->set_read_access($pPersonOrGroup, 0);
		if(__isset("authorized_read")){
		$authorized_discuss = __get("authorized_read");
		array_unset($authorized_discuss[array_search($authorized_discuss, $pPersonOrGroup)]);
		__set("authorized_read", $authorized_discuss);
		}
		}

		public function set_goal($goal){
		$this->steamobject->set_attribute("GOAL", $goal);
		}

		public function get_goal(){
		$this->steamobject->get_attribute("GOAL");
		}
		*/
}
?>