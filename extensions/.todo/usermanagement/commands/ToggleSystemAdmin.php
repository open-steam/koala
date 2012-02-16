<?php
class ToggleSystemAdmin implements Command {

	public function execute (Request $request, Response $response) {
		
		$userID = $request->getParameter("userID");
		
		$state = $request->getParameter("state");
		
		// Result data for AJAX response
		$result = array("id" => $request->getParameter("senderID"), "command" => "toggleSystemAdmin");
		

		if ($state == "true") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setAdminRights($userID);
		} else {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeAdminRights($userID);
		}

		$result["state"] = "ok";
			
		return $result;


	}
	
}

?>