<?php

/*
 * exam organization database implements the abstraction layer for all data handled
 * 
 * @author Marcel Jakoblew
 */

class exam_organization_database{
	
	//singleton handling
	
	private static $instance = NULL;
	private $courseId = NULL;
	
	private function __construct(){
		//connect to database
	}
	
	private function __clone(){}
	
	/*
	 * connect to the database with a given courseID
	 * 
	 * @courseId the key for the course (to be specified)
	 */
	public static function getInstance(){
		//singleton
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/*
	 * connect the database to a course id
	 * 
	 * @courseId equals the number of the koala object
	 * 
	 */
	public function connect($courseId){
		$this->courseId = $courseId;
		//connect to database
		$dbLink = mysql_connect(EXAM_ORGANIZATION_DATABASE_URL,EXAM_ORGANIZATION_DATABASE_USERNAME,EXAM_ORGANIZATION_DATABASE_PASSWORD,true);
		if ($dbLink==FALSE) {echo "Exam organization error: database not reachable - please configure database in exam_organization_conf.php";exit();}
		
		$dbSelected = mysql_select_db(EXAM_ORGANIZATION_DATABASE_NAME, $dbLink );
		
		if ($dbSelected==FALSE) {echo "Exam organization error: database error";exit();}
		return true;
	}
	
	/*
	 * returns the courseId which equals the koala object id
	 */
	public function getCourseId(){
		return $this->courseId;
	}
	
	/*
	 * get the list of participants for the course
	 * 
	 * @return the list of participants
	 */
	public function getParticipants(){
		if ($this->courseId==NULL) return FALSE; 
		$result = mysql_query("SELECT * FROM course WHERE courseId='".$this->courseId."'");
		$ary = array();
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
 		}
		mysql_free_result($result);
		return $ary;
	}
	
	/*
	 * add a participant to course in the database
	 * 
	 * @return true if success
	 */
	public function addParticipantToCourse($imtLogin="NA",$name="NA",$forename="NA",$matriculationNumber=-1,$bonus=0){
		//echo "addParticipantToCourse";
		if ($this->courseId==NULL) return FALSE;
		
		//abort if participant is already in db and send a true!
		$sqlQuery = "SELECT * FROM course WHERE imtLogin='".$imtLogin."' AND courseId='".$this->courseId."'";
		$result = mysql_query($sqlQuery);
		if (!(mysql_fetch_array($result)===FALSE)){
			//echo "Abort";
			return TRUE;
		}
		
		//write in db
		$sqlQuery2 = "INSERT INTO course(courseId,imtLogin,name,forename,matriculationNumber,bonus) VALUES('".$this->courseId."','".$imtLogin."','".$name."','".$forename."','".$matriculationNumber."','".$bonus."')";
		//echo "info: ".$sqlQuery2;
		$result2 = mysql_query($sqlQuery2);
		return $result2;
	}
	
	
	
	
	/*
	 * add a participant to exam in the database
	 * 
	 * @return true if success
	 */
	public function addParticipantToExam($imtLogin="NA",$term=-1,$place="not set",$room="not set",$maxPoints=-1, $reachedPoints=-1, $examResult=-1.0){
		if ($this->courseId==NULL) return FALSE;
		
		//abort if participant is already in db and send a true!
		$sqlQuery = "SELECT imtLogin, term FROM exam WHERE courseId='".$this->courseId."' AND imtLogin='".$imtLogin."' AND term='".$term."'";
		$result = mysql_query($sqlQuery);
		if (!(mysql_fetch_array($result)==FALSE)){return TRUE;}
		
		//write in db
		$sqlQuery2 = "INSERT INTO exam(courseId,imtLogin,term,place,room,maxPoints,reachedPoints,result,isNT) VALUES('".$this->courseId."','".$imtLogin."','".$term."','".$place."','".$room."','".$maxPoints."','".$reachedPoints."','".$examResult."','1')";
		$result = mysql_query($sqlQuery2);
		if($result==FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	
	
	
	/*
	 * get the rooms to choose one
	 * 
	 * @return an array containing the rooms
	 */
	public function getRoomList(){
		if ($this->courseId==NULL) return FALSE;
		$result = mysql_query("SELECT room FROM room WHERE maxPlaces>0");
		$arrayResultRow = array();
		$arrayResult = array();
		$i=0;
		while($arrayResultRow = mysql_fetch_array($result)){
			$arrayResult[]=$arrayResultRow["room"];
			$i++;
		}
		$arrayResult[] = "other";
		
		return $arrayResult;
	}
	
	/*
	 * @return the number of places for a specified room
	 */
	public function getNumberOfPlaces($room){
		if($room=="other") return 0; //"other rooms" case
		if ($this->courseId==NULL) return FALSE;
		$result = mysql_query("SELECT maxPlaces FROM room WHERE room='".$room."'");
		$arrayResultRow = array();
		$arrayResult = array();
		$i=0;
		while($arrayResultRow = mysql_fetch_array($result)){
			$arrayResult[]=$arrayResultRow["maxPlaces"];
			$i++;
		}
		return (int) $arrayResult[0];
	}
	
	
	/*
	 * get the list of available places
	 * 
	 * @rooms names of the rooms to use as one string sepaerated by spaces, for example "C1 AudiMax"
	 * 
	 * @return an array for each place containing an array with the place details [ID, Room, Place]
	 */
	public function getPlaceList($rooms){
		if ($this->courseId==NULL) return FALSE;
		$roomsArray = array();
		$roomsArray = explode(" ",$rooms);
		
		//generate sql part for rooms
		$sqlRoomQuery =" AND (";
		$i=0;
		foreach ($roomsArray as $room){
			if ($room!=""){
				if ($i!=0) $sqlRoomQuery .=" OR ";
				$sqlRoomQuery.=" room.room='".$room."'";
				$i++;
			}
		}
		$sqlRoomQuery .=" )";
		
		//main sql query
		$mysqlQueryString = "SELECT ID, room, place FROM room, roomplaces WHERE roomplaces.roomID = room.ID ".$sqlRoomQuery." ORDER BY room, place ASC";
		$result = mysql_query($mysqlQueryString);
		
		if ($result==FALSE) return FALSE;
		
		//put the result into an array
		$resultArray = array();
		while ($rowArray = mysql_fetch_array($result)){
			$resultArray[] = $rowArray;
		}
		return $resultArray;
	}
	
	/*
	 * get the list of participants for specified term of a course
	 * 
	 * @termNumber the number of the term (1, 2, 3)
	 * 
	 * @return a list of participants
	 */
	public function getParticipantsForTerm($termNumber, $order = " ORDER BY name, forename ASC"){
		if ($this->courseId==NULL) return FALSE;
		$sqlQueryString = "SELECT * FROM course,exam  WHERE course.imtLogin=exam.imtLogin AND course.courseId=exam.courseID AND course.courseId='".$this->courseId."' AND exam.term=".$termNumber. $order;
		$result = mysql_query($sqlQueryString);
		$ary = array();
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
		}
		mysql_free_result($result);
		
		//recalculate summed up points
		$participantsArrayRealPoints = array();
		foreach ($ary as $participant){
			$participantsTmp = $participant;
			$participantsTmp["reachedPoints"] = $participantsTmp["reachedPoints"] / 10;
			$participantsArrayRealPoints[] = $participantsTmp;
		}
		return $participantsArrayRealPoints;
	}
	
	/*
	 * get a name-place correlation for an exam term
	 * 
	 * @return an array[][] containing name, forename, imtlogin, matriculaton, room, place for each participant
	 */
	private function getParticipantPlaceCorrelation($rooms, $termNumber){
		$otherRooms = (trim($rooms)=="other");
		
		$numberOfAvailablePlaces = count($this->getPlaceList($rooms));
		$numberOfParticipants = count($this->getParticipantsForTerm($termNumber));
		
		if ($numberOfAvailablePlaces<$numberOfParticipants && !$otherRooms) return FALSE;
		
		//find a correlation
		if(!$otherRooms) $placeList = $this->getPlaceList($rooms);
		$participantsList = $this->getParticipantsForTerm($termNumber);
		
		$correlationArray = array(); //place, room, imtlogin, name, forename, matnr, 
		$i=0;
		foreach ($participantsList as $participant){
			$element = array();
			if(!$otherRooms) {
				$element['place']= $placeList[$i]['place'];
				$element['room']= $placeList[$i]['room'];
			}
			if($otherRooms) {
				$element['room']= "not set";
				$element['place']= "not set";
			}
			$element['imtLogin']= $participant['imtLogin'];
			$element['name']= $participant['name'];
			$element['forename']= $participant['forename'];
			$element['matnr']= $participant['matriculationNumber'];
			
			$correlationArray[] = $element;
			$i++;
		}
		return $correlationArray;
	}
	
	
	/*
	 * get a place/room for each participant and save it to the database
	 * 
	 * $rooms is a string of rooms "Audimax C1" for example
	 * $termNumber is the number of the term (1, 2, 3)
	 * 
	 */
	public function createPlaceListAndSaveToDatabase($rooms, $termNumber){
		$loginsAndPlaces = $this->getParticipantPlaceCorrelation($rooms,$termNumber);
		if($loginsAndPlaces===FALSE) return FALSE; // abourt if no correlation is possible
		foreach($loginsAndPlaces as $dataSet){
			$imtLogin = $dataSet["imtLogin"];
			$room = $dataSet["room"];
			$place = $dataSet["place"];
			$sqlQuery1 = "UPDATE exam SET room='".$room."' WHERE imtLogin='".$imtLogin."' AND term='".$termNumber."'";
			$sqlQuery2 = "UPDATE exam SET place='".$place."' WHERE imtLogin='".$imtLogin."' AND term='".$termNumber."'";
			$result1 = mysql_query($sqlQuery1);
			$result2 = mysql_query($sqlQuery2);
		}
		return true;
	}
	
	
	
	/*
	 * saves the bonus in the database
	 * 
	 * @imtLogin key to get the users bonus
	 * @bonus the bonus to return
	 * 
	 * @return true if success false else
	 */
	public function setBonus($imtLogin,$bonus){
		if ($this->courseId==NULL) return FALSE;
		
		$bonusString = "0";
		if ($bonus==0.3) $bonusString = "0.3"; 
		if ($bonus==0.7) $bonusString = "0.7";
		if ($bonus==1) $bonusString = "1";
		
		$result = mysql_query("UPDATE course SET bonus=".$bonusString." WHERE imtLogin='".$imtLogin."'");
		return $result;
	}
	
	/*
	 * function for adding a result for one assignment to the database
	 * this is only for one assignment
	 * the whole exam result is calculated later from the sum of these assignment results
	 * 
	 * @matriculationNumber
	 * @assignmentNumber 
	 * @points is the number of reached points for this assignment
	 * @term the exam term (1, 2, 3)
	 * 
	 * @return true if sucessfully added
	 * 
	 * DOMINIK ruft diese Methode auf!
	 */
	public function setAssignmentResult($matriculationNumber, $assignmentNumber, $points, $term){
		//convert and save value with factor 10 in the db
		$pointsString = (String) $points;
		$pointsString = str_replace(",",".",$pointsString);
		$pointsFloat = floatval($pointsString);
		$pointsDbRepresentation = intval($pointsFloat * 10);
		$pointsDbRepresentationString = (String) $pointsDbRepresentation;
		
		//save to db
		$reachedPoints = $pointsDbRepresentationString;
		$maxPoints = 0; //TODO
		
		//get imtLogin for a matriculation number
		$imtLogin = $this->getLogin($matriculationNumber);
		
		$sqlQuery1 = "SELECT * FROM examassignment WHERE courseId='$this->courseId' and term='$term' AND imtLogin='$imtLogin' AND assignmentNumber='$assignmentNumber'";
		$sqlResult1 = mysql_query($sqlQuery1);
		$arrayResult1 = mysql_fetch_array($sqlResult1);
		if($arrayResult1==FALSE){
			//not found in db, write new
			$sqlQuery2 = "INSERT INTO examassignment VALUES('".$this->courseId."','".$term."','".$imtLogin."','".$assignmentNumber."','".$maxPoints."','".$reachedPoints."')";
			$sqlResult2 = mysql_query($sqlQuery2);
			if ($sqlResult2==FALSE) return FALSE;
		} else {
			//found in db, update
			$sqlQuery3 = "UPDATE examassignment SET reachedPoints=$reachedPoints WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin' AND assignmentNumber='$assignmentNumber'";
			$sqlResult3 = mysql_query($sqlQuery3);
			if ($sqlResult3==FALSE) return FALSE;
		}
		
		//set NOT NT
		$sqlQuery4 = "UPDATE exam SET isNT=0 WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
		$sqlResult4 = mysql_query($sqlQuery4);
		if ($sqlResult4==FALSE) return FALSE;	
		
		//var_dump( $pointsDbRepresentationString);exit(0);
		//echo "setAssignmentResult-end";
		return TRUE; 
	}
	
	/*
	 * sets the maximum number of points for an assignment
	 * 
	 * NOT TESTED
	 * TODO
	 */
	public function setAssignmentMaxPoints($term,$assignmentNumber,$maxPoints){
		echo "Dummy";
		$sqlQuery = "UPDATE examassignment SET maxPoints='".$maxPoints."' WHERE term='".$term."' AND assignmentNumber='".$assignmentNumber."'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	
	/*
	 * returns the bonus from the database
	 * 
	 * @imtLogin username for the bonus
	 * 
	 * @return a bonus as float value (0, 0.3, 0.7. 1)
	 */
	public function getBonus($imtLogin){
		if ($this->courseId==NULL) return FALSE;
		$result = mysql_query("SELECT bonus FROM course WHERE imtLogin='".$imtLogin."'");
		$arrayResult = array();
		$arrayResult = mysql_fetch_array($result);
		return (float)$arrayResult["bonus"];
	}

		
	/*
	 * a function to delete and cleanup a course from the database
	 * 
	 * @courseId the id of the course to be deleted
	 * @return TRUE if everthing is deleted else FALSE
	 */
	public function deleteCourse(){
		if ($this->courseId==NULL) return FALSE;
		$courseId=$this->courseId;
		
		//delete everthing from course
		$result1 = mysql_query("DELETE FROM course WHERE courseId='$courseId'");
		//delete everthing from exam
		$result2 = mysql_query("DELETE FROM exam WHERE courseId='$courseId'");
		//delete everthing from examassignment
		$result3 = mysql_query("DELETE FROM examassignment WHERE courseId='$courseId'");
		
		if ($result1==FALSE) return FALSE;
		if ($result2==FALSE) return FALSE;
		if ($result3==FALSE) return FALSE;
		return TRUE;
	}
	
	
	/*
	 * a function to delete and cleanup a course from the database
	 * 
	 * @courseId the id of the course to be deleted
	 * @return TRUE if everthing is deleted else FALSE
	 * 
	 * TODO: test it
	 */
	public function deleteTerm($termNumber){
		if ($this->courseId==NULL) return FALSE;
		$courseId=$this->courseId;
		
		//delete everthing from exam
		$result1 = mysql_query("DELETE FROM exam WHERE courseId='$courseId' AND term=$termNumber");
		//delete everthing from examassignment
		$result2 = mysql_query("DELETE FROM examassignment WHERE courseId='$courseId' AND term=$termNumber");
		
		if ($result1==FALSE) return FALSE;
		if ($result2==FALSE) return FALSE;
		$this->cleanUpDataBase();
		return TRUE;
	}
	
	//TODO: test it
	public function deleteParticipantFromTerm($termNumber, $login){
		if ($this->courseId==NULL) return FALSE;
		$courseId=$this->courseId;
		
		//delete everthing from exam
		$result1 = mysql_query("DELETE FROM exam WHERE courseId='$courseId' AND imtLogin='$login' AND term='$termNumber'");
		//delete everthing from examassignment
		$result2 = mysql_query("DELETE FROM examassignment WHERE courseId='$courseId' AND imtLogin='$login' AND term='$termNumber'");
		
		if ($result1==FALSE) return FALSE;
		if ($result2==FALSE) return FALSE;
		$this->cleanUpDataBase();
		return TRUE;
	}
	
	public function deleteAllParticipantsFromTerm($termNumber){
		if ($this->courseId==NULL) return FALSE;
		$courseId=$this->courseId;
		
		//delete everthing from exam
		$result1 = mysql_query("DELETE FROM exam WHERE courseId='$courseId' AND term='$termNumber'");
		//delete everthing from examassignment
		$result2 = mysql_query("DELETE FROM examassignment WHERE courseId='$courseId' AND term='$termNumber'");
		
		if ($result1==FALSE) return FALSE;
		if ($result2==FALSE) return FALSE;
		$this->cleanUpDataBase();
		return TRUE;
	}
	
	
	/*
	 * get the rooms to choose one
	 * 
	 * @return an array containing the rooms
	 */
	public function getUsedRooms($courseId, $term){
		if ($this->courseId==NULL) return FALSE;
		$result = mysql_query("SELECT DISTINCT room FROM exam WHERE courseId='".$courseId."' AND term='".$term."' ORDER BY room ASC");
		$ary = array();
		
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
 		}
		
 		mysql_free_result($result);
 		return $ary;
	}
	
	public function getParticipantsByRoom($courseId, $room, $term)
	{
		if ($this->courseId==NULL) return FALSE;
		$result = mysql_query("SELECT imtLogin FROM exam WHERE courseId='".$courseId."' AND room='".$room."' AND term='".$term."' ORDER BY place ASC");
		$ary = array();
		
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
 		}
		mysql_free_result($result);
		return $ary;
	}
	
	public function getParticipantData($imtLogin)
	{
		$result = mysql_query("SELECT name, forename, matriculationNumber, room, place, isNT FROM course, exam WHERE course.imtLogin = exam.imtLogin AND course.imtLogin='" . $imtLogin . "'");
		
		$rowArray = mysql_fetch_array($result);
		mysql_free_result($result);
		return $rowArray;
	}
	
	public function getMatriculationNumber($forename, $name){
		$sqlQuery = "SELECT matriculationNumber FROM course WHERE forename='".$forename."' AND name='".$name."'";
		$result = mysql_query($sqlQuery);
		if ($result==FALSE) return FALSE;
		$arrayResult = array();
		$arrayResult = mysql_fetch_array($result);
		if ($arrayResult==FALSE) return FALSE;
		return $arrayResult['matriculationNumber'];
	}
	
	/*
	 * get the imtlogin for a matriculation number
	 * 
	 * @matriculationNumber
	 * 
	 * @return imtlogin
	 */
	public function getLogin($matriculationNumber){
		$sqlQuery = "SELECT imtLogin, matriculationNumber FROM course WHERE matriculationNumber='".$matriculationNumber."' AND courseId='".$this->courseId."'";
		$result = mysql_query($sqlQuery);
		if ($result==FALSE) return FALSE;
		$arrayResult = array();
		$arrayResult = mysql_fetch_array($result);
		if ($arrayResult==FALSE) return FALSE;
		return $arrayResult['imtLogin'];
	}
	
	/*
	 * calculate the exam results for the points
	 * 
	 * @courseObject the steam course object
	 * 
	 * @return true if results are calculated
	 */
	public function calculateExamResults($courseObject){
		$sqlQuery1="SELECT * FROM examassignment WHERE courseId='" . $this->courseId . "'"; //get all assignments
		$examAssignmentsArray = array();
		$sqlResult1 = mysql_query($sqlQuery1);
		while($rowArray = mysql_fetch_array($sqlResult1)){
			$examAssignmentsArray[] = $rowArray;
 		}
		mysql_free_result($sqlResult1);
		
		//sum up the assignment points
		$summedUpPoints = array(); //[imtLogin][term]=[points]
		foreach ($examAssignmentsArray as $examAssignment){
			$imtLogin = $examAssignment["imtLogin"];
			$term = (int) $examAssignment["term"];
			$points = (int) $examAssignment["reachedPoints"];
			@$savedPoints = (int) $summedUpPoints[$imtLogin][$term];
			$summedUpPoints[$imtLogin][$term]=$savedPoints + $points;
		}
		
		//write the summed up points into the database
		foreach ($summedUpPoints as $login => $summedUpPointsForLogin){
			foreach ($summedUpPointsForLogin as $term => $summedPoints){
				$sqlQuery2 = "UPDATE exam SET reachedPoints=$summedPoints WHERE term=$term AND imtLogin='$login' AND courseId='".$this->courseId."'";
				$sqlResult2 = mysql_query($sqlQuery2);
			} 
		}
		
		//calculate note
		
		for($examTerm=1;$examTerm<4;$examTerm++){
			$pointsRequiredFor40[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_40");
			$pointsRequiredFor37[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_37");
			$pointsRequiredFor33[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_33");
			$pointsRequiredFor30[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_30");
			$pointsRequiredFor27[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_27");
			$pointsRequiredFor23[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_23");
			$pointsRequiredFor20[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_20");
			$pointsRequiredFor17[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_17");
			$pointsRequiredFor13[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_13");
			$pointsRequiredFor10[$examTerm] = $courseObject->get_attribute("EXAM".$examTerm."_exam_key_10");
		}
				
		foreach ($summedUpPoints as $login => $summedUpPointsForLogin){
			foreach ($summedUpPointsForLogin as $term => $summedPoints){
				$examResult=5.0;
				switch (true){
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor10[$term]))):$examResult="1.0";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor13[$term]))):$examResult="1.3";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor17[$term]))):$examResult="1.7";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor20[$term]))):$examResult="2.0";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor23[$term]))):$examResult="2.3";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor27[$term]))):$examResult="2.7";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor30[$term]))):$examResult="3.0";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor33[$term]))):$examResult="3.3";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor37[$term]))):$examResult="3.7";break;
					case (intval($summedPoints) >= (10 * intval($pointsRequiredFor40[$term]))):$examResult="4.0";break;
				}
				$sqlQuery2 = "UPDATE exam SET result=$examResult WHERE term=$term AND imtLogin='$login' AND courseId='".$this->courseId."'";
				$sqlResult2 = mysql_query($sqlQuery2);
			} 
		}		
		return true;
	}

	
	/*
	 * get the exam result with calculated bonus for an user
	 * 
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * 
	 * @return the float value of the result or false if not found
	 */
	public function getExamResultWithBonus($termNumber, $imtLogin){
		$participants = $this->getParticipantsForTerm($termNumber);
		$bonus = $this->getBonus($imtLogin);
		$resultWithoutBonus=10.0;
		foreach($participants as $participant){
			if($participant["imtLogin"]==$imtLogin){
				$resultWithoutBonus = (float) $participant["result"];
			}
		}
		if($resultWithoutBonus==-1) {return -1;}//TODO
		
		$resultWithBonus=$resultWithoutBonus-$bonus;
		$test = round($resultWithBonus-floor($resultWithBonus),1);
		
		if( 0.4==$test ) {$resultWithBonus=$resultWithBonus-0.1;}
		if( 0.6==$test ) {$resultWithBonus=$resultWithBonus+0.1;}
		
		if( $resultWithoutBonus==10.0 ) return FALSE;
		if( $resultWithoutBonus==5.0 ) return 5.0;
		if( $resultWithBonus<1.0 && $resultWithBonus>-1.0) return 1.0;
		if($bonus==FALSE) return $resultWithoutBonus;
		return ($resultWithBonus);
	}
	
	/*
	 * get the exam result without bonus for an user
	 * 
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * 
	 * @return the float value of the result or false if not found
	 */
	public function getExamResultWithoutBonus($termNumber, $imtLogin){
		$participants = $this->getParticipantsForTerm($termNumber);
		foreach($participants as $participant){
			if($participant["imtLogin"]==$imtLogin){
				$resultWithoutBonus = (float) $participant["result"];
				return $resultWithoutBonus;
			}
		}
		return false;
	}
	
	
	/* 
	 * get the exam points for an user
	 * 
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * 
	 * @return an array of points
	 */
	public function getExamPoints($courseId, $termNumber, $imtLogin){
		if ($this->courseId==NULL) return FALSE;
		$sqlQueryString = "SELECT assignmentNumber, reachedPoints FROM examassignment WHERE imtLogin='" . $imtLogin . "' AND courseId='" . $courseId . "' AND term=" . $termNumber . " ORDER BY assignmentNumber ASC";
		$result = mysql_query($sqlQueryString);

		$ary = array();
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
		}
		mysql_free_result($result);
		
		//restore original values by dividing with factor 10
		$pointsArray = array();
		foreach ($ary as $assignmentResult){
			$assignmentResultTmp = $assignmentResult;
			$assignmentResultTmp["reachedPoints"] = $assignmentResultTmp["reachedPoints"] / 10;
			$pointsArray[] = $assignmentResultTmp;
		}
		return $pointsArray;
	}
	
	
	/* 
	 * get the exam points for an user for one assignment
	 * 
	 * @courseID
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * @assingmentNumber
	 * 
	 * @return an array of points
	 */
	public function getExamPointsForAssignment($courseId, $termNumber, $imtLogin, $assignmentNumber){
		if ($this->courseId==NULL) return FALSE;
		$sqlQueryString = "SELECT * FROM examassignment WHERE imtLogin='" . $imtLogin . "' AND courseId='" . $courseId . "' AND term='" . $termNumber .  "' AND assignmentNumber='" . $assignmentNumber."'";
		$result = mysql_query($sqlQueryString);
		
		$ary = array();
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
		}
		if($result==0) return 0;
		mysql_free_result($result);
		
		if(isset($ary["0"]["reachedPoints"])){
			$reachedPointsDb = $ary["0"]["reachedPoints"];
			$reachedPoints = $reachedPointsDb / 10;
			return $reachedPoints;
		} else {
			return 0;
		}
		
	}
	
	
	
	/* 
	 * get the room and place for an user
	 * 
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * 
	 * @return an array with room and place
	 */
	public function getRoomAndPlace($termNumber, $imtLogin){
		$participants = $this->getParticipantsForTerm($termNumber);
		$rp= array();
		$rp["room"]="(not set)";
		$rp["place"]="(not set)";
		foreach($participants as $participant){
			if($participant["imtLogin"]==$imtLogin){
				$rp["room"] =  $participant["room"];
				$rp["place"] =  $participant["place"];
			}
		}
		return $rp;
	}
	
	
	/* 
	 * get boolean value for exam participation
	 * 
	 * @$termNumber is the number of the term (1,2,3)
	 * @imtLogin
	 * 
	 * @return true if user participates in an exam, else false
	 */
	public function isParticipantForTerm($termNumber, $imtLogin){
		$participants = $this->getParticipantsForTerm($termNumber);
		$isParticipant = FALSE;
		foreach($participants as $participant){
			if($participant["imtLogin"]==$imtLogin){
				$isParticipant = TRUE;
			}
		}
		return $isParticipant;
	}
	
	/*
	 * get the number of correctly entered results
	 * 
	 * @term is the term number
	 * 
	 * @return is an int with the number of correctly entered results
	 */
	public function getNumberOfEnteredResultsForTerm($term){
		$numberOfEnteredResults = 0;
		$participants = $this->getParticipantsForTerm($term);
		foreach ($participants as $participant){
			if($participant["reachedPoints"] >= 0){
				$numberOfEnteredResults++;
			}
			//echo "<br/><br/>";
			//var_dump($participant);
			//echo "<br/><br/>";
		}
		return $numberOfEnteredResults;
	}
	
	//--- update table functions --- 
	
	public function updateLastName($login, $value){
		$sqlQuery = "UPDATE course SET name='$value' WHERE courseId='$this->courseId' AND imtLogin='$login'";
		echo "sqlQuery $sqlQuery";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	public function updateFirstName($login, $value){
		$sqlQuery = "UPDATE course SET forename='$value' WHERE courseId='$this->courseId' AND imtLogin='$login'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	public function updateMatriculationNumber($login, $value){
		$sqlQuery = "UPDATE course SET matriculationNumber='$value' WHERE courseId='$this->courseId' AND imtLogin='$login'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	public function updateRoom($term, $login, $value){
		$sqlQuery = "UPDATE exam SET room='$value' WHERE courseId='$this->courseId' AND imtLogin='$login' AND term='$term'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	public function updatePlace($term, $login, $value){
		$sqlQuery = "UPDATE exam SET place='$value' WHERE courseId='$this->courseId' AND imtLogin='$login' AND term='$term'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	public function updateBonus($login, $value){
		$value = str_replace(",",".",$value);
		$bonusValue = floatval($value);
		if($bonusValue<0) return FALSE;
		$sqlQuery = "UPDATE course SET bonus='$value' WHERE courseId='$this->courseId' AND imtLogin='$login'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	/*
	 * set status for participant to NT
	 */
	public function setNT($term,$matriculationNumeber,$value){
		$imtLogin = $this->getLogin($matriculationNumeber);

		switch($value){
			case "NULL":
				$sqlQuery1 = "UPDATE exam SET isNT=0 WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
				$sqlResult1 = mysql_query($sqlQuery1);
				if ($sqlResult1==FALSE) return FALSE;
				return TRUE;
				break;

			case "NT":
				$sqlQuery1 = "UPDATE exam SET isNT='NT' WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
				$sqlResult1 = mysql_query($sqlQuery1);
				if ($sqlResult1==FALSE) return FALSE;
				return TRUE;
				break;

			case "BV":
				$sqlQuery1 = "UPDATE exam SET isNT='BV' WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
				$sqlResult1 = mysql_query($sqlQuery1);
				if ($sqlResult1==FALSE) return FALSE;
				return TRUE;
				break;

			case "SICK":
				$sqlQuery1 = "UPDATE exam SET isNT='SICK' WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
				$sqlResult1 = mysql_query($sqlQuery1);
				if ($sqlResult1==FALSE) return FALSE;
				return TRUE;
				break;
		}
		
		/*
		if($value){
			//set NT
			$sqlQuery1 = "UPDATE exam SET isNT=1 WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
			$sqlResult1 = mysql_query($sqlQuery1);
			if ($sqlResult1==FALSE) return FALSE;
		} else {
			//set not NT
			$sqlQuery1 = "UPDATE exam SET isNT=0 WHERE courseId='$this->courseId' AND term='$term' AND imtLogin='$imtLogin'";
			$sqlResult1 = mysql_query($sqlQuery1);
			if ($sqlResult1==FALSE) return FALSE;
		}
		*/
		return FALSE;
	}
	
	
	/*
	 * get the number of correctly entered results
	 * 
	 * @term is the term number
	 * @assignmentNumber is the assignment number
	 * 
	 * @return is an array with the point allocation
	 */
	public function getAssignmentStatistics($term,$assignmentNumber){
		$sqlQueryString = "SELECT reachedPoints, COUNT(*) as count FROM examassignment WHERE term=" . $term . " AND assignmentNumber=" . $assignmentNumber . " GROUP BY reachedPoints";
		$result = mysql_query($sqlQueryString);

		$ary = array();
		while($rowArray = mysql_fetch_array($result)){
			$ary[] = $rowArray;
		}
		mysql_free_result($result);
		return $ary;
	}
	
	//function for HIS import/export
	
	/*
	 * saves a row from the excel file to the db
	 */
	public function saveExcelRow($term, $matriculationNumber, $excelRowString){
		$login = $this->getLogin($matriculationNumber);
		$sqlQuery = "UPDATE exam SET excelRow='$excelRowString' WHERE courseId='$this->courseId' AND imtLogin='$login' AND term='$term'";
		$sqlResult = mysql_query($sqlQuery);
		if ($sqlResult==FALSE) return FALSE;
		return TRUE;
	}
	
	/*
	 * loads a row form the db for his excel export
	 */
	public function loadExcelRow($term, $matriculationNumber){
		$login = $this->getLogin($matriculationNumber);
		$sqlQuery = "SELECT * FROM exam WHERE courseId='$this->courseId' AND imtLogin='$login' AND term='$term'";
		$sqlResult = mysql_query($sqlQuery);
		
		$ary = array();
		while($rowArray = mysql_fetch_array($sqlResult)){
			$ary[] = $rowArray;
		}
		mysql_free_result($sqlResult);
		
		$excelRowString = $ary[0]["excelRow"];
		return $excelRowString;
	}
	
	
	/*
	 * delete all participants form course, which are not in an exam
	 */
	private function cleanUpDataBase(){
		if ($this->courseId==NULL) return FALSE;
		$courseId = $this->courseId;
		
		//get all fields to delete
		$innerRequest = "SELECT course.imtLogin, exam.imtLogin, term FROM exam RIGHT JOIN course ON exam.imtLogin = course.imtLogin WHERE term IS NULL";
		$sqlResult = mysql_query($innerRequest);
		
		$ary = array();
		while($rowArray = mysql_fetch_array($sqlResult)){
			$ary[] = $rowArray;
		}
		mysql_free_result($sqlResult);
		
		$imtLoginList = array();
		foreach ($ary as $deletedParticipant){
			$imtLogin = $deletedParticipant[0];
			$imtLoginList[] = $imtLogin;
		}
		
		//build up a delete request
		$sqlDeleteRequest = "DELETE FROM course WHERE ";
		$first = true;
		foreach ($imtLoginList as $login){
			if ($first){
				$sqlDeleteRequest.= "imtLogin='$login' ";
				$first=false;
			} else {
				$sqlDeleteRequest.= "OR imtLogin='$login' ";
			}
			 
		}
		
		$sqlResult = mysql_query($sqlDeleteRequest);
		return $sqlResult;
	}
	
}
?>