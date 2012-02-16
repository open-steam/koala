<?php

/**
 * 	The usermanagement main template
 */

class koala_html_usermanagement extends koala_html {
	
	private $templateName = "";
	private $isAdmin = false;
	private $isCustomerAdmin = false;

	
	
	public function __construct($templateName) {
		
		// Name of the template to load
		$this->templateName = $templateName;
		
		// Get current user ID and set rights
		$currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
		$this->isCustomerAdmin 	= $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID);
		$this->isAdmin 			= $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID);
		
		parent::__construct(PATH_TEMPLATES . "usermanagement/" . $templateName . ".template.html");
	}
	
	public function get_headline () {
		return "Benutzerverwaltung";
	}
	
	public function get_menu( $params = array() ) {
		global $usermanagementHTMLTemplate;
		$context = $usermanagementHTMLTemplate->get_context();

		$menu = array();
		
		if ($context == "user") {
			$menu["user"] = array ("link" => "/usermanagement/user-password", "name" => "Meine Benutzerdaten");
		}
		
		if ($context == "user" && ($this->isCustomerAdmin || $this->isAdmin)) {
			$menu["admin"] = array ("link" => "/usermanagement/admin", "name" => "Administration");
		}
		
		if ($context != "user") {
			if ($this->isCustomerAdmin || $this->isAdmin) {
				$menu["admin"] = array ("link" => "/usermanagement/admin", "name" => "Aufgaben");
			}
			
			if ($this->isCustomerAdmin || $this->isAdmin) {
				$menu["employees"] = array ("link" => "/usermanagement/employees-modify", "name" => "Benutzer");
			}
			
			if ($this->isCustomerAdmin || $this->isAdmin) {
				$menu["courses"] = array ("link" => "/usermanagement/courses-overview", "name" => "Lizenzen");
			}
			
			//if ($this->isCustomerAdmin || $this->isAdmin) {
			//	$menu["branches"] = array ("link" => "/usermanagement/branches", "name" => "Filialen");
			//}
			
			if ($this->isAdmin && USERMANAGEMENT_CUSTOMERS) {
				$menu["customers"] = array ("link" => "/usermanagement/customers", "name" => "Unternehmen");
			}
			
			if ($this->isAdmin && USERMANAGEMENT_CONFIGURATION) {
				$menu["setup"] = array ("link" => "/usermanagement/setup", "name" => "Konfiguration");
			}
		}
		
		return $menu;
		
	}
	
	public function get_context_menu( $context, $params = array() ) {
		
		$context_menu = array ();
		
		switch ($context) {
			case "user" : 
				if ($this->isAdmin) {
					$context_menu[] = array ("link" => "/usermanagement/user-password", "name" => "eigenes Passwort");
					$context_menu[] = array ("link" => "/usermanagement/user-changeAdminPerspective", "name" => "Unternehmern wechseln");
				}	
				$context_menu[] =  array();
				break;
			case "employees" : 
				$context_menu[] = array ("link" => "/usermanagement/employees-create", "name" => "Benutzer anlegen");
				$context_menu[] = array ("link" => "/usermanagement/employees-import", "name" => "Excel-Liste importieren");
				$context_menu[] = array ("link" => "/usermanagement/employees-export", "name" => "Benutzerliste exportieren");
				$context_menu[] = array ("link" => "/usermanagement/employees-history", "name" => "Verlauf");
				break;
			case "branches" : 
				break;
			case "customers" : 
				break;
			case "courses" : 
				//$context_menu[] = array ("link" => "/usermanagement/courses-assign", "name" => "Mitarbeiter zuweisen");
				//$context_menu[] = array ("link" => "/usermanagement/courses-assignCSV", "name" => "Mitarbeiter per Excel-Liste zuweisen");
				//$context_menu[] = array ("link" => "/usermanagement/courses-remove", "name" => "Mitarbeiter entfernen");
				if ($this->isAdmin) {
					$context_menu[] = array ("link" => "/usermanagement/courses-activate", "name" => "Lizenzschlüssel eingeben");
					//$context_menu[] = array ("link" => "/usermanagement/courses-quota", "name" => "Kontingente verwalten");
				}
				break;
		}
		
		return $context_menu;
	}
	
}
?>