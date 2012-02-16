<?php

class TrashRestoreUser implements Command {
	
	public function execute (Request $request, Response $response) {
		
		// The user to (un)lock
		$userID = $request->getParameter("userID");
		
		// Check if to lock or unlock
		$trashState = $request->getParameter("trashState");
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "trashRestoreUser");
		
		// Lock user
		if ($trashState == "trash") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->trashUser($userID);
			$result["value"] = "trash";
		}
		
		// Unlock user
		else {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->restoreUser($userID);
			$result["value"] = "restore";
		}
		
		$result["status"] = usermanangement::get_instance()->get_user_status_html($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID));
		$result["trashdate"] = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getTrashDate($userID);
		$result["state"] = "ok";
		$viewHelper = new ViewHelper();
		$result["html"] = str_replace("</tr>", "", str_replace("<tr class=\"filter_user\" id=\"row[$userID]\">", "", $viewHelper->getEmployeeRow($userID, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID))));
		
		return $result;
		
	}
	
}

?>