<?php
function sort_xmllist($t1, $t2) {
	$start1 = (int) $t1->start;
	$start2 = (int) $t2->start;
	$end1 = (int) $t1->end;
	$end2 = (int) $t2->end;
	
	if ($start1 > $start2) {
		return 1;
	} else if ($start2 > $start1) {
		return -1;
	} else if ($end1 > $end2) {
		return 1;
	} else if ($end2 > $end1) {
		return -1;
	} else return 0;
}

function sort_workplans($w1, $w2) {
	$id1 = $w1->get_id();
	$id2 = $w2->get_id();
	
	if ($id1 > $id2) {
		return 1;
	} else return -1;
}

function sort_objects( $a, $b )
{
	if ( $a[ "OBJ_NAME" ] == $b[ "OBJ_NAME" ] )
	{
		$ret = 0;
	} else 	{
    $ret = ( strtoupper( $a[ "OBJ_NAME" ] ) < strtoupper( $b[ "OBJ_NAME" ] ) ) ? -1 : 1;
  }
  return $ret;
}

function sort_objects_new( $a, $b )
{
	if ( $a->get_name() == $b->get_name() )
	{
		return 0;
	}
	return ( strtoupper( $a->get_name() ) < strtoupper( $b->get_name() ) ) ? -1 : 1;
}

function sort_buddies( $a, $b )
{
	if ( $a[ "USER_FULLNAME" ] == $b[ "USER_FULLNAME" ] ) {
		if ($a["OBJ_NAME"] == $b["OBJ_NAME"]) {
			return 0;
		}
		return ( strtoupper( $a[ "OBJ_NAME" ] ) < strtoupper( $b[ "OBJ_NAME" ] ) ) ? -1 : 1;
	}
	return ( strtoupper( $a[ "USER_FULLNAME" ] ) < strtoupper( $b[ "USER_FULLNAME" ] ) ) ? -1 : 1;
}

function sort_dates_asc( $a, $b )
{
	if ( $a[ "DATE_START_DATE" ] == $b[ "DATE_START_DATE" ] )
	{
		return 0;
	}
	return ( $a[ "DATE_START_DATE" ] < $b[ "DATE_START_DATE" ] ) ? -1 : 1;
}


function sort_semester_desc( $a, $b )
{
	if ( $a[ "SEMESTER_START_DATE" ] == $b[ "SEMESTER_START_DATE" ] )
	{
		return 0;
	}
	return ( $a[ "SEMESTER_START_DATE" ] > $b[ "SEMESTER_START_DATE" ] ) ? -1 : 1;
}

function sort_courses( $a, $b )
{
  $keya = $a[ "SORTKEY" ];
  $keyb = $b[ "SORTKEY" ];
  
	if ( $keya == $keyb )
	{
		$ret = 0;
	} else 	{
    $ret = ( strtoupper( $keya ) < strtoupper( $keyb ) ) ? -1 : 1;
  }
  return $ret;
}


function sortTopicsByDate($annotationA, $annotationB) {
		
		$dateA = $annotationA->get_attribute("OBJ_LAST_CHANGED");
		$dateB = $annotationB->get_attribute("OBJ_LAST_CHANGED");

		if($dateA == $dateB) 
			return 0;

		return ($dateA > $dateB)? -1:1;
}

function sortRepliesByDate($annotationA, $annotationB) {
		
		$dateA = $annotationA->get_attribute("OBJ_CREATION_TIME");
		$dateB = $annotationB->get_attribute("OBJ_CREATION_TIME");

		if($dateA == $dateB) 
			return 0;

		return ($dateA < $dateB)? -1:1;
}

function sortExtensions($extensionA, $extensionB) {
	$priorityA = $extensionA->getPriority();
	$priorityB = $extensionB->getPriority();
	
	if($priorityA == $priorityB) {
		return 0;
	}
	
	return ($priorityA < $priorityB) ? 1:-1;
}


function sortPortletAppointments($appointmentA, $appointmentB){
    $log=false;
    if ($log) \logging::write_log( LOG_ERROR, "ap-sortPortletAppointments");
    
    
    //timestamp a
    $startTime = $appointmentA["start_time"];
    $startDate = $appointmentA["start_date"];
    
    $aYear = $startDate["year"];
    $aMonth = $startDate["month"];
    $aDay = $startDate["day"];
    $aHour = $startTime["hour"];
    $aMinute = $startTime["minutes"];
    
    if ($aYear==0) $bYear="1990"; 
    if ($aMonth==0) $bMonth="1";
    if ($aDay==0) $bDay="1";
    if ($aHour==0) $bHour="0";
    if ($aMinute==0) $bMinute="00";
    
    $format = 'Y-m-d H:i:s';
    $dateA = new DateTime();
    $dateAString = $aYear.'-'.$aMonth.'-'.$aDay.' '.$aHour.':'.$aMinute.':00';
    $dateA = DateTime::createFromFormat($format, $dateAString);
    
    if(($dateA===NULL) | ($dateA===FALSE)){
        if ($log) \logging::write_log( LOG_ERROR, "ap-Fehler date A null !!!"); //test
        $md5a = md5(serialize($appointmentA));
        $md5b = md5(serialize($appointmentB));
        return strcmp($md5a, $md5b);
        //return 0;
    }
    if ($log) \logging::write_log( LOG_ERROR, "ap-Datum A richtig erstellt"); //test
    $timestampA = $dateA->getTimestamp();
    
    

    //timestamp b
    $startTime = $appointmentB["start_time"];
    $startDate = $appointmentB["start_date"];
    
    $bYear = $startDate["year"];
    $bMonth = $startDate["month"];
    $bDay = $startDate["day"];
    $bHour = $startTime["hour"];
    $bMinute = $startTime["minutes"];
    
    if ($bYear==0) $bYear="1990"; 
    if ($bMonth==0) $bMonth="1";
    if ($bDay==0) $bDay="1";
    if ($bHour==0) $bHour="00";
    if ($bMinute==0) $bMinute="00";
    
    $format = 'Y-m-d H:i:s';
    $dateB = new DateTime();
    $dateBString = $bYear.'-'.$bMonth.'-'.$bDay.' '.$bHour.':'.$bMinute.':00';
    $dateB = DateTime::createFromFormat($format, $dateBString);
    
    
    if(($dateB===NULL) | ($dateB===FALSE)){
        if ($log) \logging::write_log( LOG_ERROR, "ap-Fehler date B null !!!"); //test
        $md5a = md5(serialize($appointmentA));
        $md5b = md5(serialize($appointmentB));
        return strcmp($md5a, $md5b);
        //return 0;
    }
    if ($log) \logging::write_log( LOG_ERROR, "ap-Datum B richtig erstellt"); //test
    $timestampB = $dateB->getTimestamp();
    
    
    //comparision
    if ($timestampA == $timestampB) {
        if ($log) \logging::write_log( LOG_ERROR, "ap-sortfunc-0-OK"); //test
        
        //alternative sort
        $md5a = md5(serialize($appointmentA));
        $md5b = md5(serialize($appointmentB));
        return strcmp($md5a, $md5b);
        //return 0;
    }
    if ($log) \logging::write_log( LOG_ERROR, "ap-sortfunc-1+1-OK"); //test
    
    return ($timestampA < $timestampB) ? -1 : 1;
}


function sortExplorerNewDialog($extensionA, $extensionB){
    $nameA = $extensionA->getName();
    $nameB = $extensionB->getName();
    
    if(!defined("EXTENSIONS_WHITELIST")){
        return 0;
    }
    
    $posA = strpos(strtolower(EXTENSIONS_WHITELIST), strtolower($nameA));
    $posB = strpos(strtolower(EXTENSIONS_WHITELIST), strtolower($nameB));
    
    if(FALSE === $posA || FALSE === $posB) return 0;
    
    if ($posA == $posB) {
        return 0;
    }
    return ($posA < $posB) ? -1 : 1;
}
?>
