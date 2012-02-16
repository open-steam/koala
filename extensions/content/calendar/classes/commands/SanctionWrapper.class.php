<?php
namespace Calendar\Commands;
class SanctionWrapper{
	private $extensions;

	
	
//TODO:FUNKTIONIERT FÜR KALENDER
	public function setExtensions($extent){
		$this->extensions=$extent;
	}
	
	public function getSanction(){
		if(!is_array($this->extensions)){
			throw new \Exception("extensions not type of array");
		}
		$sanction = array();
		$currentUser = \lms_steam::get_current_user();
		$currentUserId = $currentUser->get_id();			
		foreach($this->extensions as $id=>$extension){
			if($extension instanceof \steam_group){
				$parentGroup = $extension->get_parent_group();
				$parentGroupName = $parentGroup->get_name();
				//IF SEMESTER
				if($parentGroupName == "Courses"){
					$sanction[$id] = 1;
				}elseif($parentGroupName == "PrivGroups" || $parentGroupName == "PublicGroups"){
					if($extension->is_admin($currentUser)){
						$sanction[$id] = 2;
					}else if($extension->is_member($currentUser)){
						$sanction[$id] = 1;
					}else{
						$sanction[$id] = 0;
					}	
				}
			}
			else if($extension instanceof \steam_room){
				$objType=$extension->get_attribute("OBJ_TYPE");
				$sanctionValue = $extension->get_sanction();
				$SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;
				if($objType == "calendar"){
					if($sanctionValue < SANCTION_READ){
						$sanction[$id] = 0;
					}else if($sanctionValue < $SANCTION_WRITE_FOR_CURRENT_OBJECT){
						$sanction[$id] = 1;
					}else{
						$sanction[$id] = 2;
					}
				} 
			}
		}
		return $sanction;
	}
}
?>