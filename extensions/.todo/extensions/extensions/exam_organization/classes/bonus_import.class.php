<?php
/*
 * bonus import imports the bonus steps from an activated pointlist
 * 
 * @author Marcel Jakoblew
 */
class bonus_import{
	
	private static $instance = NULL; 
	 
	private function __construct() {} 
	 
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private function __clone() {}
	
	
	
	/*
	 * @return true if successful
	 */
	public function importBonusSteps($course){
		$pointlistsArray = $this->getPointlistsForCourse($course);
		if ($pointlistsArray==FALSE) {return FALSE;}
		foreach($pointlistsArray as $pointlistContainer){
			$examBonusArray = $this->getExamBonus($pointlistContainer);
			$this->addBonusToDatabase($examBonusArray);
		}
		return true;
	}

	
	/*
	 * @course a course object
	 * 
	 * @return an array of pointlist containers
	 */
	private function getPointlistsForCourse($course){
		$courseSteamGroup = $course->get_steam_group();
		$courseSteamSubgroups = $courseSteamGroup->get_subgroups();
		
		//get learnes subgroup
		$learnersSubgroup = 0;
		foreach($courseSteamSubgroups as $courseSteamSubgroup){
			$courseSteamSubgroupName = $courseSteamSubgroup->get_Name();
			if($courseSteamSubgroupName=="learners"){
				$learnersSubgroup=$courseSteamSubgroup;
				break;
			}
		}
		
		$learnersSubgroupWorkspace = $learnersSubgroup->get_workroom();
		$learnersSubgroupWorkspaceInventory = $learnersSubgroupWorkspace->get_inventory();
		
		//find the pointlists
		$pointListsArray = array();
		$foundList = FALSE;
		foreach($learnersSubgroupWorkspaceInventory as $inventoryElement){
			$inventoryElementObjType = $inventoryElement->get_attribute("OBJ_TYPE");
			if ($inventoryElementObjType==="container_pointlist_unit_koala" || $inventoryElementObjType==="container_pointlist_unit_kola"){
				$pointListsArray[]=$inventoryElement;
				$foundList = TRUE;
			}
			
		}
		if (!$foundList) return FALSE;
		return $pointListsArray;
	}
	
	
	/*
	 * @pointlistContainer the proxy object contain the data for the pointlist
	 * 
	 * @return an array imtLogin=>bonus (FLOAT)
	 */
	private function getExamBonus($pointlistContainer){
		$pointlistProxy = $pointlistContainer->get_attribute("UNIT_POINTLIST_PROXY");
		$proxyDataAttibutes = $pointlistProxy->get_all_attributes();
		
		//proxy
		$maxPoints = $proxyDataAttibutes["UNIT_POINTLIST_MAXPOINTS"]; //maxpoint in an array for each sheet
		
		//unit
		$numberOfSheets = $pointlistContainer->get_attribute("UNIT_POINTLIST_COUNT");
		$bonus1Threshold = $pointlistContainer->get_attribute("UNIT_POINTLIST_BONUS_1");
		$bonus2Threshold = $pointlistContainer->get_attribute("UNIT_POINTLIST_BONUS_2");
		$bonus3Threshold = $pointlistContainer->get_attribute("UNIT_POINTLIST_BONUS_3");
		if ($bonus3Threshold==0 || $bonus3Threshold==FALSE) $bonus3Threshold=1000000; //fix until pointlist supports three bonus steps
		
		$userPointlistArray = $this->getPointlistFromAllAttributes($proxyDataAttibutes);
		
		
		//sum up the points in the user pointlist array
		$usersSummedPointsArray = array(); 
		foreach($userPointlistArray as $userId => $userPoints){
			$usersSummedPointsArray[$userId]=0;
			foreach ($userPoints as $point){
				$usersSummedPointsArray[$userId]+=(int) $point;
			}
		}
		
		
		//calculate the exam bonus per userId
		$usersIdExamBonusArray = array(); //key is userId
		foreach ($usersSummedPointsArray as $userId => $userPoints){
			switch($userPoints){
				case ($userPoints>=$bonus3Threshold):
					$usersIdExamBonusArray[$userId]=$this->getExamBonusPerStep(3);break;
				case ($userPoints>=$bonus2Threshold):
					$usersIdExamBonusArray[$userId]=$this->getExamBonusPerStep(2);break;
				case ($userPoints>=$bonus1Threshold):
					$usersIdExamBonusArray[$userId]=$this->getExamBonusPerStep(1);break;
				default:$usersIdExamBonusArray[$userId]=0;
			}
		}
		
		//get the user name for an userId
		$userNameExamBonusArray = array();
		foreach ($usersIdExamBonusArray as $userId => $userBonus){
			$userObject = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $userId );
			$userName = $userObject->get_name();
			$userNameExamBonusArray[$userName]=$userBonus;
		}
		return $userNameExamBonusArray; // array: username as key; bonus (FLOAT) as value
	}
	
	
	/*
	 * $usernameBonusArray an array containing username and bonus as float
	 * 
	 * @return true if successful else false
	 */
	private function addBonusToDatabase($usernameBonusArray){
		$eoDatabase = exam_organization_database::getInstance();
		foreach ($usernameBonusArray as $userName => $bonus){
			$eoDatabase->setBonus($userName, $bonus);
		}
	}
	
	
	/*
	 * @proxyDataAllAttributes
	 * 
	 * @return an two dimensional array [koalaUserID][sheetnumber]=reachedPoints
	 */
	function getPointlistFromAllAttributes( $proxyDataAllAttributes ) {
		$pointlist = array();
    	$akey = "";
		foreach($proxyDataAllAttributes as $key => $values) {
			if ( preg_match( "#UNIT_POINTLIST_POINTS_([0-9]*)#", $key, $akey ) > 0 ) {
			$pointlist[$akey[1]] = $values;
			}
		}
		return $pointlist;
 		}
 	
 	/*
 	 * calculates the exan bonus from a bonus step
 	 * 
 	 * @step a bonus step
 	 * 
 	 * @return a exam bonus
 	 */	
 	function getExamBonusPerStep($step){
 		switch ($step){
 			case 1:return 0.3;
 			case 2:return 0.7;
 			case 3:return 1.0;
 			default: return 0;
 		}
 	}
	
	
}
?>