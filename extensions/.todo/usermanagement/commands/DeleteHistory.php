<?php

class DeleteHistory implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->deleteUserCreationLogFiles();
		
		return "Die History wurde gel&ouml;scht";
		
	}
	
}

?>