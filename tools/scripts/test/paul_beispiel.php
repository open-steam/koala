<?php

require_once( "../../etc/koala.def.php");
ini_set( "include_path", ini_get( "include_path" ) . PATH_SEPARATOR . PATH_CLASSES. PATH_SEPARATOR . PATH_CLASSES . "PEAR" . PATH_SEPARATOR . PATH_CLASSES . "PHPsTeam");
require_once( PATH_CLASSES . "paul_soap.class.php" );

  $paul_client = new paul_soap();

	// IMT-uid in person_no umwandeln
	$person_no = $paul_client->get_person_no_by_uid("wilf");
	echo "umgewandelte person_no: " . $person_no . "\n";

	//zu Demonstrationszwecken der umgekehrte Weg
	echo "UID zu person_no $person_no: " . $paul_client->get_uid_by_person_no($person_no) . "\n";

	// person_no in person_id umwandeln
	$person_id = $paul_client->get_person_id_by_person_no($person_no);
	echo "umgewandelte Id: " . $person_id . "\n";

	// mit person_id alle Kurse dieser Person holen
	//$courses = $paul_client->get_all_courses_by_person('333096745539477'); //hat eine Vorlesung
	//$courses = $paul_client->get_all_courses_by_person('333096745206094'); //hat mehrere Vorlesungen
	//$courses = $paul_client->get_all_courses_by_person($person_id); //hat keine vorlesungen
	//print_r($courses);

	/*
   	foreach ($courses as $course)
   	{
   		$infos = $paul_client->get_course_information($course);
		print_r($infos);
   	}
   	*/

   	///////////////////////////////////////////////////////////////////////////////////////////////////

   	$participants = $paul_client->get_participants('333115115949863'); //mehrere Teilnehmer
	//$participants = $paul_client->get_participants('333143751484962'); //nur ein Teilnehmer
	print_r($participants);
?>
