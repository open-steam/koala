<?php

class DeleteMultipleEmployees implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$userNames = array_keys($request->getParameter("user"));
		
		$logfileName = $request->getParameter("logfile");
		
		$logfiles = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserCreationLogFiles();
		
		$oldXML = null;
		
		foreach ($logfiles as $logfile) {
			if ($logfile["name"] == $logfileName) {
				$oldXML = simplexml_load_string($logfile["content"]);
			}
		}
		
		if ($oldXML == null) {
			echo " --- No logfile found on server<br>";
		}
		
		else {
			
			foreach ($userNames as $login) {
	
				$userID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->login2ID($login);
				
				if ($userID != "-1") {
					
					$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUserFromAllGroups($userID);
					$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUser($userID);
					
					foreach ($oldXML as $user) {
						
						if ($login == $user["login"]) {
							$user["state"] = "deleted";
						}
						
					}
				}
			} 
			
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->updateUserCreationLogFile($logfileName, $oldXML->asXML());
		}
		

	}
	
}

?>