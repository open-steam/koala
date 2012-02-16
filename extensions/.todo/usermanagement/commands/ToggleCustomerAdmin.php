<?php
class ToggleCustomerAdmin implements Command {

	public function execute (Request $request, Response $response) {
		
		$userID = $request->getParameter("userID");
		
		$state = $request->getParameter("state");
		
		// Result data for AJAX response
		$result = array("id" => $request->getParameter("senderID"), "command" => "toggleCustomerAdmin");
		

		if ($state == "true") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setCustomerAdminRights($userID);
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($userID);
		} else {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeCustomerAdminRights($userID);
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserStatus($userID);
		}

		$result["userid"] = $userID;
		$result["state"] = "ok";
		$viewHelper = new ViewHelper();
		$result["html"] = str_replace("</tr>", "", str_replace("<tr class=\"filter_user\" id=\"row[$userID]\">", "", $viewHelper->getEmployeeRow($userID, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID))));
			
		return $result;


	}
	
}

?>