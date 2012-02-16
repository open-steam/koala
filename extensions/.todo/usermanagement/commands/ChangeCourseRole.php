<?php
class ChangeCourseRole implements Command {

	public function execute (Request $request, Response $response) {
		
		$userID = $request->getParameter("userID");
		
		$role = explode(".", $request->getParameter("role"));
		$role_name = $role[0];
		$courseID = $role[1];
		
		// Result data for AJAX response
		$result = array("id" => $request->getParameter("senderID"), "command" => "changeCourseRole");
		
		$is_teilnehmer = teilnehmer_role::is_role($userID, new kurs_context($courseID));
	    $is_betreuer = betreuer_role::is_role($userID, new kurs_context($courseID));
		$is_anspechpartner = ansprechpartner_role::is_role($userID, new kurs_context($courseID));
		
		$result["pre"] = $is_teilnehmer . " " . $is_betreuer . " " . $is_anspechpartner;
		
		if ($role_name == "Teilnehmer") {
			if ($is_anspechpartner) {
				ansprechpartner_role::get_role($userID, new kurs_context($courseID))->remove_role();
			}
			if ($is_betreuer) {
				betreuer_role::get_role($userID, new kurs_context($courseID))->remove_role();
			}
//			if ($is_teilnehmer) {
//				// ok
//			}
//			if (!$is_teilnehmer) {
				teilnehmer_role::make_role($userID, new kurs_context($courseID));
//			}
		} else if ($role_name == "Betreuer") {
			if ($is_anspechpartner) {
				ansprechpartner_role::get_role($userID, new kurs_context($courseID))->remove_role();
			} 
			if ($is_betreuer) {
				// ok
			} 
			if ($is_teilnehmer) {
				teilnehmer_role::get_role($userID, new kurs_context($courseID))->remove_role();
			}
//			if (!$is_betreuer) {
				betreuer_role::make_role($userID, new kurs_context($courseID));
//			}
		} else if ($role_name == "Ansprechpartner") {
			if ($is_anspechpartner) {
				// ok
			}
			if ($is_betreuer) {
				// ok
			}
			if ($is_teilnehmer) {
				teilnehmer_role::get_role($userID, new kurs_context($courseID))->remove_role();
			}
			if (!$is_betreuer) {
				benutzer_role::make_role($userID, new kurs_context($courseID));
			}
//			if (!$is_anspechpartner) {
				ansprechpartner_role::make_role($userID, new kurs_context($courseID));
//			}
		}


		$result["state"] = "ok";
		$result["role"] = $role_name;
		$result["userid"] = $userID;
		$viewHelper = new ViewHelper();
		$result["html"] = str_replace("</tr>", "", str_replace("<tr class=\"filter_user\" id=\"row[$userID]\">", "", $viewHelper->getEmployeeRow($userID, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID))));
		
			
		return $result;


	}
	
}

?>