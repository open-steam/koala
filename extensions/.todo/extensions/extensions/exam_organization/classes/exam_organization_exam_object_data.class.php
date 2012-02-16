<?php
class exam_organization_exam_object_data{
	
	//singleton handling
	
	private static $instance = NULL;
	private static $course = NULL;
	
	private function __construct(){
		//connect to database
	}
	
	private function __clone(){}
	
	/*
	 * connect to the database with a given courseID
	 * 
	 * @courseId the key for the course (to be specified)
	 */
	public static function getInstance($courseObj){
		self::$course = $courseObj; 
		//singleton
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function getRoomsDescriptionText($term){
		if (self::$course->get_attribute("EXAM".$term."_checkbox_room_other")==="on"){return gettext("Individual");}
		
		$examRoomsText = "";
		$eoDatabase = exam_organization_database::getInstance();
		$availableRooms = $eoDatabase->getRoomList();
		foreach($availableRooms as $room){
			if (self::$course->get_attribute("EXAM".$term."_checkbox_room_".$room)==="on"){
				$examRoomsText .= $room.", ";
			} else {
				//do nothing
			}
		}
		$examRoomsText = substr($examRoomsText,0,-2);
		if ($examRoomsText==FALSE || $examRoomsText=="") $examRoomsText=gettext("no rooms choosen");
		return $examRoomsText;
	}
	
	public function isIndividualForRooms($term){
		if (self::$course->get_attribute("EXAM".$term."_checkbox_room_other")==="on"){return TRUE;}
		return FALSE;
	}
	
	public function getTimeDescriptionText($term){
		$startHour = self::$course->get_attribute("EXAM".$term."_exam_time_start_hour");
		$startMinute = self::$course->get_attribute("EXAM".$term."_exam_time_start_minute");
		$endHour = self::$course->get_attribute("EXAM".$term."_exam_time_end_hour");
		$endMinute = self::$course->get_attribute("EXAM".$term."_exam_time_end_minute");
		if ($startHour==0 && $startMinute==0 && $endHour==0 && $endMinute==0) return gettext("Time")." ".gettext("not set");
		
		$examTimeText  = $startHour.".".$startMinute." ".gettext("til")." ".$endHour.".".$endMinute." ".gettext("o'clock");
		return $examTimeText;
	}
	
	public function getDateDescriptionText($term){
		$day = self::$course->get_attribute("EXAM".$term."_exam_date_day");
		$month = self::$course->get_attribute("EXAM".$term."_exam_date_month");
		$year = self::$course->get_attribute("EXAM".$term."_exam_date_year");
		if($day==0 && $month==0 && $year==0) return gettext("Date")." ".gettext("not set");
		
		$examDateText  = $day.".".$month.".".$year;
		return $examDateText;
	}
	
	public function getDateAndTimeVisibleStatus($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_checkbox_datetime_visible");
		if($returnValue==="on"){
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	public function getRoomsVisibleStatus($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_checkbox_rooms_visible");
		if($returnValue==="on"){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function setDateAndTimeVisibleStatus($term, $boolStatus){
		if($boolStatus) {$statusString="on";} else {$statusString="off";}
		$returnValue = self::$course->set_attribute("EXAM".$term."_checkbox_datetime_visible",$statusString);
		return $returnValue;	
	}
	
	public function setRoomsVisibleStatus($term, $boolStatus){
		if($boolStatus) {$statusString="on";} else {$statusString="off";}
		$returnValue = self::$course->set_attribute("EXAM".$term."_checkbox_rooms_visible",$statusString);
		return $returnValue;
	}
	
	public function activateTerm($term){
		$status = self::$course->set_attribute("EXAM".$term."_activated",TRUE);
		return $status;
	}
	
	public function deactivateTerm($term){
		$eoDatabase = exam_organization_database::getInstance();
		
		// RESET ROOMS
		$availableRooms = $eoDatabase->getRoomList();
		foreach($availableRooms as $room)
		{
			self::$course->set_attribute("EXAM".$term."_checkbox_room_".$room, "off");
		}
		self::$course->set_attribute("EXAM".$term."_checkbox_room_other", "off");
		
		// RESET DATE
		self::$course->set_attribute("EXAM".$term."_exam_date_day", "");
		self::$course->set_attribute("EXAM".$term."_exam_date_month", "");
		self::$course->set_attribute("EXAM".$term."_exam_date_year", "");
		
		// RESET TIME
		self::$course->set_attribute("EXAM".$term."_exam_time_start_hour", "");
		self::$course->set_attribute("EXAM".$term."_exam_time_start_minute", "");
		self::$course->set_attribute("EXAM".$term."_exam_time_end_hour", "");
		self::$course->set_attribute("EXAM".$term."_exam_time_end_minute", "");
		
		// RESET EXAM ASSIGNMENTS
		$numberOfAssignments = (int) $this->getNumberOfAssignments($term);
		self::$course->set_attribute("EXAM".$term."_number_of_assignments", 10);
		
		for ($n = 1 ; $n <= $numberOfAssignments ; $n++)
		{
			if ($n <= 10)
			{
				self::$course->set_attribute("EXAM".$term."_assignment_max_points_" . $n, 10);
			}
			else
			{
				self::$course->set_attribute("EXAM".$term."_assignment_max_points_" . $n, NULL);
			}
		}
		
		// RESET FREETEXT
		self::$course->set_attribute("EXAM".$term."_freetext", "");
		
		// RESET CHECKBOXES
		self::$course->set_attribute("EXAM".$term."_checkbox_datetime_visible", "off");
		self::$course->set_attribute("EXAM".$term."_checkbox_rooms_visible", "off");
		
		// RESET EXAMKEY
		self::$course->set_attribute("EXAM".$term."_exam_key_10","");
		self::$course->set_attribute("EXAM".$term."_exam_key_13","");
		self::$course->set_attribute("EXAM".$term."_exam_key_17","");
		self::$course->set_attribute("EXAM".$term."_exam_key_20","");
		self::$course->set_attribute("EXAM".$term."_exam_key_23","");
		self::$course->set_attribute("EXAM".$term."_exam_key_27","");
		self::$course->set_attribute("EXAM".$term."_exam_key_30","");
		self::$course->set_attribute("EXAM".$term."_exam_key_33","");
		self::$course->set_attribute("EXAM".$term."_exam_key_37","");
		self::$course->set_attribute("EXAM".$term."_exam_key_40","");
		
		// RESET ICONS
		self::$course->set_attribute("EXAM".$term."_status_room", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_time", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_date", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_assignments", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_places", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_bonus", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_examkey", FALSE);
		self::$course->set_attribute("EXAM".$term."_status_enterpoints", FALSE);
		
		$eoDatabase->deleteAllParticipantsFromTerm($term);
		self::$course->set_attribute("EXAM".$term."_activated", FALSE);
	}
	
	public function termIsActivated($term){
		$status = self::$course->get_attribute("EXAM".$term."_activated");
		if ($status==TRUE){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function setDate($term,$day,$month,$year){
		self::$course->set_attribute("EXAM".$term."_exam_date_day",$day);
		self::$course->set_attribute("EXAM".$term."_exam_date_month",$month);
		self::$course->set_attribute("EXAM".$term."_exam_date_year",$year);
		return TRUE;
	}
	
	public function getDateDay($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_date_day");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function getDateMonth($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_date_month");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function getDateYear($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_date_year");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function setTime($term,$startMinute,$startHour,$endMinute,$endHour){
		self::$course->set_attribute("EXAM".$term."_exam_time_start_minute",$startMinute);
		self::$course->set_attribute("EXAM".$term."_exam_time_start_hour",$startHour);
		self::$course->set_attribute("EXAM".$term."_exam_time_end_minute",$endMinute);
		self::$course->set_attribute("EXAM".$term."_exam_time_end_hour",$endHour);
		return TRUE;
	}
	
	public function getTimeStartMinute($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_time_start_minute");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function getTimeStartHour($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_time_start_hour");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function getTimeEndMinute($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_time_end_minute");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function getTimeEndHour($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_exam_time_end_hour");
		if ($returnValue===intval(0)) return "-";
		return $returnValue;
	}
	
	public function setRooms($term,$inputRoomsArray){
		$eoDatabase = exam_organization_database::getInstance();
		$availableRooms = $eoDatabase->getRoomList();
		
		//other rooms case
		if (isset($inputRoomsArray["checkbox_room_other"]) && $inputRoomsArray["checkbox_room_other"]=="on"){
			foreach($availableRooms as $room){
				self::$course->set_attribute("EXAM".$term."_checkbox_room_".$room, "off");
			}
			self::$course->set_attribute("EXAM".$term."_checkbox_room_other", "on");
			return TRUE;
		}
		
		//normal rooms case
		foreach($availableRooms as $room){
			if (isset($inputRoomsArray["checkbox_room_".$room]) && $inputRoomsArray["checkbox_room_".$room]=="on"){
				self::$course->set_attribute("EXAM".$term."_checkbox_room_".$room, "on");
			} else {
				self::$course->set_attribute("EXAM".$term."_checkbox_room_".$room, "off");
			}
		} 
		return TRUE;
	}
	
	public function getRooms($term){
		$returnArray = array();
		$eoDatabase = exam_organization_database::getInstance();
		$availableRooms = $eoDatabase->getRoomList();
		foreach($availableRooms as $room){
			if (self::$course->get_attribute("EXAM".$term."_checkbox_room_".$room)==="on"){
				$returnArray["checkbox_room_".$room]="checked";
			} else {
				$returnArray["checkbox_room_".$room]="";
			}
		}
		return $returnArray;
	}
	
	//assignments
	public function getNumberOfAssignments($term){
		$value = self::$course->get_attribute("EXAM".$term."_number_of_assignments");
		if ($value==0) return 1;
		return $value;
	}
	
	public function setNumberOfAssignments($term,$number){
		return self::$course->set_attribute("EXAM".$term."_number_of_assignments",$number);
	}
	
	
	public function getAssignmentMaxPoints($term){
		$returnArrayAssingments = array();
		$numberOfAssignments = $this->getNumberOfAssignments($term);
		
		$oneFound = FALSE;
		
		for ($n = 1 ; $n <= $numberOfAssignments ; $n++)
		{
			$returnValue = self::$course->get_attribute("EXAM".$term."_assignment_max_points_" . $n);
			if ($returnValue == FALSE || $returnValue === NULL) { break; } 
			$returnArrayAssingments[$n]=$returnValue;
			$oneFound = true;
		}
		if (!$oneFound){
			$ary = array();
			$ary[1]=10;
			return $ary; 
		} else {
			//echo "Points on course object found";
		}
		return $returnArrayAssingments;
	}
	
	public function setAssignmentMaxPoints($term, $maxPointsArray){
		$n=1;
		while ($n<1+(int)$maxPointsArray["number_of_assignments"]){
			//echo 
			if (!isset($maxPointsArray["field_assignment_max_points_".$n])) {break;}
			//if (!isset($maxPointsArray["input_exam_results_a".$n])) {break;}
			self::$course->set_attribute("EXAM".$term."_assignment_max_points_".$n, $maxPointsArray["field_assignment_max_points_".$n]);
			$n++;
		}
	}
	
	public function getExamKey($term){
		$returnArray=array();
		$returnArray["10"] = self::$course->get_attribute("EXAM".$term."_exam_key_10");
		$returnArray["13"] = self::$course->get_attribute("EXAM".$term."_exam_key_13");
		$returnArray["17"] = self::$course->get_attribute("EXAM".$term."_exam_key_17");
		$returnArray["20"] = self::$course->get_attribute("EXAM".$term."_exam_key_20");
		$returnArray["23"] = self::$course->get_attribute("EXAM".$term."_exam_key_23");
		$returnArray["27"] = self::$course->get_attribute("EXAM".$term."_exam_key_27");
		$returnArray["30"] = self::$course->get_attribute("EXAM".$term."_exam_key_30");
		$returnArray["33"] = self::$course->get_attribute("EXAM".$term."_exam_key_33");
		$returnArray["37"] = self::$course->get_attribute("EXAM".$term."_exam_key_37");
		$returnArray["40"] = self::$course->get_attribute("EXAM".$term."_exam_key_40");
		return $returnArray;
	}
	
	public function setExamKey($term,$step,$pointsValue){
		switch ($step){
			case "10": self::$course->set_attribute("EXAM".$term."_exam_key_10",$pointsValue); return TRUE; break;
			case "13": self::$course->set_attribute("EXAM".$term."_exam_key_13",$pointsValue); return TRUE; break;
			case "17": self::$course->set_attribute("EXAM".$term."_exam_key_17",$pointsValue); return TRUE; break;
			case "20": self::$course->set_attribute("EXAM".$term."_exam_key_20",$pointsValue); return TRUE; break;
			case "23": self::$course->set_attribute("EXAM".$term."_exam_key_23",$pointsValue); return TRUE; break;
			case "27": self::$course->set_attribute("EXAM".$term."_exam_key_27",$pointsValue); return TRUE; break;
			case "30": self::$course->set_attribute("EXAM".$term."_exam_key_30",$pointsValue); return TRUE; break;
			case "33": self::$course->set_attribute("EXAM".$term."_exam_key_33",$pointsValue); return TRUE; break;
			case "37": self::$course->set_attribute("EXAM".$term."_exam_key_37",$pointsValue); return TRUE; break;
			case "40": self::$course->set_attribute("EXAM".$term."_exam_key_40",$pointsValue); return TRUE; break;
			default: return FALSE; 
		}
		return FALSE;
	}
	
	public function getExamKeyMaxPoints($term){
		$sum = 0;
		$returnArrayAssingments = array();
		$n=1;
		while (true){
			$returnValue = self::$course->get_attribute("EXAM".$term."_assignment_max_points_".$n);
			$returnValueInt = (int) $returnValue;
			$sum += $returnValueInt;
			if ($n == $this->getNumberOfAssignments($term)) break;
			$n++;
		}
		//$returnValue = self::$course->set_attribute("EXAM".$term."_assignment_max_points_".,$sum); //TODO: is this required?
		return $sum;
	}
	
	public function getStatus($term,$case){
		switch($case){
			case "room": return self::$course->get_attribute("EXAM".$term."_status_room"); break;
			case "time": return self::$course->get_attribute("EXAM".$term."_status_time"); break;
			case "date": return self::$course->get_attribute("EXAM".$term."_status_date"); break;
			case "assignments": return self::$course->get_attribute("EXAM".$term."_status_assignments"); break;
			case "places": return self::$course->get_attribute("EXAM".$term."_status_places"); break;
			case "bonus": return self::$course->get_attribute("EXAM".$term."_status_bonus"); break;
			case "examkey": return self::$course->get_attribute("EXAM".$term."_status_examkey"); break;
			case "enterpoints": return self::$course->get_attribute("EXAM".$term."_status_enterpoints"); break;
		}
		return false;
		
		
	}
	
	public function setStatus($term,$case,$status){
		if (!($status===TRUE || $status===FALSE)) return FALSE; //allow only true and false
		switch($case){
			case "room": {self::$course->set_attribute("EXAM".$term."_status_room",$status);} return TRUE; break;
			case "time": self::$course->set_attribute("EXAM".$term."_status_time",$status); return TRUE; break;
			case "date": self::$course->set_attribute("EXAM".$term."_status_date",$status); return TRUE; break;
			case "assignments":self::$course->set_attribute("EXAM".$term."_status_assignments",$status); return TRUE; break;
			case "places":self::$course->set_attribute("EXAM".$term."_status_places",$status); return TRUE; break;
			case "bonus":self::$course->set_attribute("EXAM".$term."_status_bonus",$status); return TRUE; break;
			case "examkey":self::$course->set_attribute("EXAM".$term."_status_examkey",$status); return TRUE; break;
			case "enterpoints":self::$course->set_attribute("EXAM".$term."_status_enterpoints",$status); return TRUE; break;
		}
		return FALSE;
	}
	
	
	/*
	 * set a free text for an exam
	 * 
	 * @term is the term number
	 * @text a free text
	 * 
	 * @return true if set is successful
	 */
	public function setFreeText($term,$text){
		return self::$course->set_attribute("EXAM".$term."_freetext",$text);
	}
	
	
	/*
	 * get a free text for an exam
	 * 
	 * @term is the term number
	 * 
	 * @return a text
	 */
	public function getFreeText($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_freetext");
		if($returnValue===0) return "";
		return $returnValue;
	}
	
	
	public function markForDataDeletion($term){
		$date = date("Y").date("m").date("d");
		$returnValue = self::$course->set_attribute("EXAM".$term."_delete_database",$date);
		return $returnValue;
	}
	
	public function unMarkForDataDeletion($term){
		$returnValue = self::$course->set_attribute("EXAM".$term."_delete_database",0);
		return $returnValue;
	}
	
	public function getMarkForDataDeletionDate($term){
		$returnValue = self::$course->get_attribute("EXAM".$term."_delete_database");
		return $returnValue;
	}
	
	
	//master password for exam organization
	public function setMasterPassword($clearTextPassword){
		$encodedPassword = md5($clearTextPassword);
		$returnValue = self::$course->set_attribute("EXAM_master_password",$encodedPassword);
		return $returnValue;
	}
	
	public function clearMasterPassword(){
		$returnValue = self::$course->set_attribute("EXAM_master_password",0);
		return $returnValue;
	}
	
	public function checkMasterPassword($clearTextPassword){
		$savedPasswordHash = self::$course->get_attribute("EXAM_master_password");
		if($savedPasswordHash==0) return true; //password not set
		if($savedPasswordHash==md5($clearTextPassword)) return true;
		return false;  
	}
	
	public function isSetMasterPassword(){
		$savedPasswordHash = self::$course->get_attribute("EXAM_master_password");
		if($savedPasswordHash==0) return false; //password not set
		return true;  
	}
	
	public function resetDateAndTime($term){
		$rv1 = self::$course->set_attribute("EXAM".$term."_exam_date_day",0);
		$rv2 = self::$course->set_attribute("EXAM".$term."_exam_date_month",0);
		$rv3 = self::$course->set_attribute("EXAM".$term."_exam_date_year",0);
		
		$rv4 = self::$course->set_attribute("EXAM".$term."_exam_time_start_minute",0);
		$rv5 = self::$course->set_attribute("EXAM".$term."_exam_time_start_hour",0);
		$rv6 = self::$course->set_attribute("EXAM".$term."_exam_time_end_minute",0);
		$rv7 = self::$course->set_attribute("EXAM".$term."_exam_time_end_hour",0);
		$returnValue = $rv1 && $rv2 && $rv3 && $rv4 && $rv5 && $rv6 && $rv7; 
		return $returnValue;
	}
}
?>