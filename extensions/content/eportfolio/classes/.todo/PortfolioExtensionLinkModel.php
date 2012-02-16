<?php

abstract class PortfolioExtensionLinkModel {
	private $room;
	private $forum;
	private static $initOnce =  false;
	
	public function __construct(steam_room $room) {
		self::init();
		$this->room = $room;
		$this->forum = $room->get_object_by_name("forum");
	}

	public function __call($name, $param) {
		if (is_callable(array($this->room, $name))) {
			return call_user_func_array(array($this->room, $name), $param);
		} else {
			throw new Exception("Method " . $name . " can be called.");
		}
	}

	public function getRoom(){
		return $this->room;
	}

	public function delete() {
		//TODO: Also delete created links to the room?
		$type = $this->getTypeString();
		$privategroups = steam_factory::get_group($GLOBALS[ "STEAM" ]->get_id(), "PrivGroups");
		$groups = steam_factory::groupname_to_object(
			$GLOBALS[ "STEAM" ]->get_id(),
			$privategroups->get_parent_and_group_name() . "." . $username . "." . $type
		);
		$groups->get_subgroup_by_name($this->getId())->delete();
		$this->room->delete();
		
	}

	public function move($destination) {
		$this->room->move($destination);
	}

	public function getName(){
		return $this->room->get_name();
	}

	public function setName($name){
		$this->room->set_name($name);
	}

	public function getDescription(){
		return $this->room->get_attribute(OBJ_DESC);
	}

	public function setDescription($description){
		$this->room->set_attribute(OBJ_DESC, $description);
	}

	public function getId(){
		return $this->room->get_id();
	}

	public function getObjType(){
		return $this->room->get_attribute(OBJ_TYPE);
	}

	public function setObjType($type){
		$this->room->set_attribute(OBJ_TYPE, $type);
	}

	public function getObjIcon(){
		return $this->room->get_attribute(OBJ_ICON);
	}

	public function setObjIcon($icon){
		$this->room->set_attribute(OBJ_ICON, $icon);
	}

	public function getCreationTime(){
		return $this->room->get_attribute(OBJ_CREATED);
	}

	public function setCreationTime($time){
		$this->room->set_attribute(OBJ_CREATED, $time);
	}

	public function getModificationTime(){
		return $this->room->get_attribute(OBJ_LAST_CHANGED);
	}

	public function setModificationTime($time){
		$this->room->set_attribute(OBJ_LAST_CHANGED, $time);
	}
	
	//used for distinguishing between group folders
	//public abstract function getTypeString();
	
	public function groupsExists(){
		return ($this->getAuthorizeGroupParent() == false) ?  false : true;
	}
	
	public function getAuthorizeGroupParent(){
		$username = lms_steam::get_current_user()->get_name();
        $privateGroups = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PRIVATE_GROUP );
        $groupParent = steam_factory::groupname_to_object(
			$GLOBALS[ "STEAM" ]->get_id(),
			$privateGroups->get_parent_and_group_name() . "." . $username . "." . $this->getTypeString()
		);
		$objectGroup = $groupParent->get_subgroup_by_name(strval($this->getId()));
		if ($objectGroup === 0){
			return false;
		} else {
			return $objectGroup;
		}
//		$group = $privateGroups->get_subgroup_by_name($this->getTypeString())->get_subgroup($user->get_name())->get_subgroup(strval($this->getId()));
	}

	public function authorizeRead($pPersonOrGroup, $grant = 1){
		if (!$this->groupsExists())
			$this->createGroups();
		$readGroup = $this->getAuthorizeGroupParent()->get_subgroup_by_name("read");
		$grant ? $readGroup->add_member($pPersonOrGroup) : $readGroup->remove_member($pPersonOrGroup); 
	}

	public function authorizeForum($pPersonOrGroup, $grant = 1){
		if (!$this->groupsExists())
			$this->createGroups();
		$readGroup = $this->getAuthorizeGroupParent()->get_subgroup_by_name("forum");
		$grant ? $readGroup->add_member($pPersonOrGroup) : $readGroup->remove_member($pPersonOrGroup); 
	}
	
	public function authorizeEdit($pPersonOrGroup, $grant = 1){
		if (!$this->groupsExists())
			$this->createGroups();
		$readGroup = $this->getAuthorizeGroupParent()->get_subgroup_by_name("edit");
		$grant ? $readGroup->add_member($pPersonOrGroup) : $readGroup->remove_member($pPersonOrGroup); 
	}
	
	public function createGroups(){
		$user = lms_steam::get_current_user();
		$username = $user->get_name();
		//get private group
		$privategroups = steam_factory::get_group($GLOBALS[ "STEAM" ]->get_id(), "PrivGroups");
		
		$groups = steam_factory::groupname_to_object(
			$GLOBALS[ "STEAM" ]->get_id(),
			$privategroups->get_parent_and_group_name() . "." . $username . "." . $this->getTypeString()
		);

		//user groups
//		$usergroups = $privategroups->get_subgroup($user->get_name());
		//artefact or portfolio groups
//		$groups = $privategroups->get_subgroup(getTypeString());
		//object specific group
		$objectGroup = $groups->create_subgroup(strval($this->getId()));
		//different groups for different sharing szenarios
		$forum = 	$objectGroup->create_subgroup("forum");
		$read = 	$objectGroup->create_subgroup("read");
		$edit =		$objectGroup->create_subgroup("edit");

		//Assign Groups to rooms and forum
		$this->getRoom()->set_read_access($read);
		$this->getForum()->set_annotate_access($forum);
		$this->getRoom()->set_write_access($edit);
		$this->getRoom()->set_read_access($edit);
	}
	
	public function createForum(){
		$forum = steam_factory::create_messageboard(
			$GLOBALS[ "STEAM" ]->get_id(),
			"forum",
			$this->getRoom()
		);
		$this->forum = $forum;
	}
	
	public function getForum(){
		return $this->forum;
	}
	
	public static function getById($id){
		self::init();
		$obj = steam_factory::get_object(
			$GLOBALS[ "STEAM" ]->get_id(), 
			$id
		);
		if (!($obj instanceof \steam_object)) {
			error_log("Object is null.");
			return null;
		}
		switch ($obj->get_attribute(PORTFOLIO_PREFIX . "TYPE")){
		case "ARTEFACT":
			return Artefacts::getArtefactByRoom($obj);
			break;
		case "PORTFOLIO":
			return new PortfolioModel($obj);
			break;
		default:
			error_log("Object is no E-Portfolio Object. ID:" . $id);
			return null;
		}
	}
	
	public static function init() {
		if (self::$initOnce) {
			return false;
		} else {
			self::$initOnce = true;
		}
		$user = \lms_steam::get_current_user();
		$workroom = $user->get_workroom();
		$html = "";
		if ($workroom->get_object_by_name("portfolio")){
			return false;
		}
		
		//create rooms
		$portfolio_main_room = \steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"portfolio",
			$workroom,
			"room for portfolio module"
		);
		$artefacts_room = \steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"artefacts",
			$portfolio_main_room,
			"room for artefacts for portfolios"
		);
		$portfolios_room = \steam_factory::create_room(
			$GLOBALS[ "STEAM" ]->get_id(),
			"portfolios",
			$portfolio_main_room,
			"room for portfolios"
		);
		
		//create groups
		$parentgroup = \steam_factory::get_group($GLOBALS[ "STEAM" ]->get_id(), "PrivGroups");
		$groups = $parentgroup->get_subgroups();
		$names = array();
		foreach ($groups as $group) {
			$names[] = $group->get_name();
		}
		if (!in_array($user->get_name(), $names)){
			$group = $parentgroup->create_subgroup($user->get_name());
			$html .= "Private Gruppe erstellt<br>";
			$artefactsGroup = $group->create_subgroup("artefacts");
			$html .= "Private Gruppe für Belege erstellt<br>";
			$portfoliosGroup = $group->create_subgroup("portfolios");
			$html .= "Private Gruppe für Portfolios erstellt<br>";
		}

		
		/*
		//create groups
		$parentgroup = \steam_factory::get_group($GLOBALS[ "STEAM" ]->get_id(), "PrivGroups");
		if (!(($group = $parentgroup->get_subgroup_by_name($user->get_name())) instanceof \steam_group)){
			$group = $parentgroup->create_subgroup($user->get_name());
			$html .= "Private Gruppe erstellt<br>";
		}
		if (!($group->get_subgroup_by_name("artefacts") instanceof \steam_group)){
			$artefactsGroup = $group->create_subgroup("artefacts");
			$html .= "Private Gruppe für Belege erstellt<br>";
		}
		if (!($group->get_subgroup_by_name("portfolios") instanceof \steam_group)){
			$portfoliosGroup = $group->create_subgroup("portfolios");
			$html .= "Private Gruppe für Portfolios erstellt<br>";
		}
		*/
		return true;
	}
}