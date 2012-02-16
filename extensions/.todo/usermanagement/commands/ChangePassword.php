<?php

class ChangePassword implements Command {
	
	private $portal;
	
	
	public function execute (Request $request, Response $response) {
		
		$password = $request->getParameter("passwordNew");
		$passwordConfirm = $request->getParameter("passwordConfirm");
		
		if (strlen($password) == 0) {
			throw new UsermanagementException("Kein neues Passwort angegeben", "W&auml;hle ein Passwort mit mindestens 6 Zeichen");
		}
		
		else if (strlen($password) < 6) {
			throw new UsermanagementException("Das eingegebene Passwort ist zu kurz", "W&auml;hle ein Passwort mit mindestens 6 Zeichen");
		}
		
		else if ($password != $passwordConfirm) {
			throw new UsermanagementException("Das eingegebene Passwort stimmt nicht mit dem wiederholten &uuml;berein", "Gib das Passwort erneut ein");
		}
		
		else {
			
			// Get the current user id
			$userID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();

			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->changePassword($userID, $password);
			
			if ($_SESSION[ "LMS_USER" ]->get_login() == $GLOBALS["STEAM"]->get_current_steam_user()->get_name()) {
				$_SESSION[ "LMS_USER" ]->set_password($password);
			}
			
			return "Das neue Passwort wurde gesetzt";
		}
	}
	
	
	
	public function setPortal ($portal) {
		$this->portal = $portal;
	}
	
}

?>