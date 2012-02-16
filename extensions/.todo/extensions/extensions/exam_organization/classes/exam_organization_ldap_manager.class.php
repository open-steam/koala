<?php
/*
 * ldap manager with cache
 * 
 * @author Marcel Jakoblew
 */

class exam_organization_ldap_manager{

	private static $instance = NULL; 

	private $markedForPreload = array();
	
	private $preloadedValues = array();
	
	private function __construct() {} 
	 
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private function __clone() {}
	
	//caching functions
	
	public function markForPreload($matriculationNumber){
		$this->markedForPreload[]=$matriculationNumber;
	}
	
	//fire up an big ldap request
	public function preload(){
		//add values to local db
		$matriculationNumberSetsTmp = $this->getLdapDataForMatriculationNumbers($this->markedForPreload);
		foreach ($matriculationNumberSetsTmp as $mnrSet){
			$index = $mnrSet["upbStudentID"];
			$value = $mnrSet;
			$this->preloadedValues[$index] = $value;
		}
		
		$this->markedForPreload = array();
	}
	
	public function isInCache($mnr){
		if (isset($this->preloadedValues[$mnr])){
			return TRUE;
		}
		return FALSE;
	}
	
	public function getCacheData($mnr){
		if (isset($this->preloadedValues[$mnr])){
			$value = $this->preloadedValues[$mnr];
			return $value;
		}
		return FALSE;
	}
	
	public function matriculationNumber2imtLogin($matNr){
		if ($this->isInCache($matNr)){
			$dataSet = $this->getCacheData($matNr);
			return $dataSet["uid"];
		} else {
			$this->markForPreload($matNr);
			$this->preload();
		}
		//no cache workflow
		
		$oldErrorReporting = error_reporting(); //temporary disable error reporting
		error_reporting(0);
		$result = $this->getLdapData($matNr);
		if ($result==FALSE) return "LGN".$matNr; //dummy data
		error_reporting($oldErrorReporting);
		return $result["imtLogin"];
	}
	
	public function matriculationNumber2firstName($matNr){
		if ($this->isInCache($matNr)){
			$dataSet = $this->getCacheData($matNr);
			return $dataSet["givenName"];
		} else {
			$this->markForPreload($matNr);
			$this->preload();
		}
		//no cache workflow
		
		$oldErrorReporting = error_reporting(); //temporary disable error reporting
		error_reporting(0);
		$result = $this->getLdapData($matNr);
		if ($result==FALSE) return "FN".$matNr; //dummy data
		error_reporting($oldErrorReporting);
		return $result["firstname"];
	}
	
	public function matriculationNumber2lastName($matNr){
		if ($this->isInCache($matNr)){
			$dataSet = $this->getCacheData($matNr);
			return $dataSet["sn"];
		} else {
			$this->markForPreload($matNr);
			$this->preload();
		}
		//no cache workflow
		
		$oldErrorReporting = error_reporting(); //temporary disable error reporting
		error_reporting(0);
		$result = $this->getLdapData($matNr);
		if ($result==FALSE) return "LN".$matNr; //dummy data
		error_reporting($oldErrorReporting);
		return $result["lastname"];
	}
	
	public function getFullName($matNr){
		return $this->matriculationNumber2firstName($matNr)." ".$this->matriculationNumber2lastName($matNr);
	}
	
	
	/*
	 * get mail address from ldap
	 */
	public function matriculationNumber2mail($matNr){
		/*
		if ($this->isInCache($matNr)){
			$dataSet = $this->getCacheData($matNr);
			return $dataSet["upbMailPreferredAddress"];
		} else {
			$this->markForPreload($matNr);
			$this->preload();
		}
		*/
		//no cache workflow
		
		$oldErrorReporting = error_reporting(); //temporary disable error reporting
		error_reporting(0);
		$result = $this->getLdapData($matNr);
		
		if ($result==FALSE) return "MAIL".$matNr; //dummy data
		error_reporting($oldErrorReporting);
		return $result["upbMailPreferredAddress"];
	}
	
	
	/*
	 * get ldap data from ldap server and save it (for cache) to the database
	 * 
	 * @matriculationNumber
	 * @writeInDataBase write in database for caching
	 * 
	 * @return an array containing [matnr][imtLogin][LastName][FirstName]
	 */
	public function getLdapData($matriculationNumber){
		$user = array(); //[matnr][imtLogin][LastName][FirstName]
		require_once( PATH_CLASSES . "lms_ldap.class.php" );
		try {
		  $lms_ldap = new lms_ldap();
		  $lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
		}
		catch ( Exception $e ) {
			//paul_sync_log("PAUL_SYNC\t" . $e->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR );
			return FALSE;
		}
		
		$uid = $lms_ldap->studentid2uid($matriculationNumber);
		$user["imtLogin"]=$uid;
		$ldap_attributes = $lms_ldap->get_ldap_attribute( array( "sn", "givenName", "upbMailPreferredAddress" ), $uid );
		
		$user["upbMailPreferredAddress"]=$ldap_attributes["upbMailPreferredAddress"];
		$user["firstname"]=$ldap_attributes["givenName"];
		$user["lastname"]=$ldap_attributes["sn"];
		$user["mnr"]=$matriculationNumber;
		if(!isset($user["firstname"])) return FALSE;
		if(!isset($user["lastname"])) return FALSE;
		if(!isset($user["mnr"])) return FALSE;
		
		//return ldap data
		return $user;
	}
	
	
	
	/*
	 * get ldap data from ldap server and save it (for cache) to the database
	 * 
	 * @$arrayOfMatriculationNumbers
	 * 
	 * @return an array containing [matnr][imtLogin][LastName][FirstName]??
	 */
	private function getLdapDataForMatriculationNumbers($arrayOfMatriculationNumbers=TRUE){
		if ($arrayOfMatriculationNumbers==FALSE) return FALSE;
		
		$filterString = "";
		$filterStringFirst=TRUE;
		//build a filter string
		foreach ($arrayOfMatriculationNumbers as $matriculationNumber){
			if ($filterStringFirst){
				$filterString = "(upbStudentID=".$matriculationNumber.")";
			} else {
				$filterString = "(|".$filterString."(upbStudentID=".$matriculationNumber."))";
			}
			$filterStringFirst=FALSE;
		}
		//$filter = "(|(upbStudentID=6105319)(uid=birger))"; //sample filter string
		
		require_once( PATH_CLASSES . "lms_ldap.class.php" );
		try {
		  $lms_ldap = new lms_ldap();
		  $lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
		}
		catch ( Exception $e ) {
			return FALSE;
		}
		
		$ldap_attributes = $lms_ldap->get_ldap_attribute_for_various_data( array( "sn", "givenName", "upbStudentID", "uid", "upbMailPreferredAddress") , $filterString );
		return $ldap_attributes;
	}
	
	
	
}

?>