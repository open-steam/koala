<?php

class ViewHelper {
	
	private $dataAccess;
	
	private $comboboxCSSStyle;
	
	private $currentUserID;
	
	
	public function __construct () {
		$this->dataAccess = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"];
		$this->comboboxCSSStyle = "font-family:Verdana; font-size:11px; width:300px; height:20px; margin-right:10px;";
		$this->currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
	}
	
	public function getBranchesAsOptions ($insertEmptyEntryFirst = true, $preselectedBranch = "", $blacklist = array()) {
		
		$options = ($insertEmptyEntryFirst) ? "<option value=\"0\">---</option>" : "";
		
		if ($this->dataAccess->isAdmin($this->currentUserID)) {
			foreach ($this->dataAccess->getAllCustomers() as $customerID => $customerName) {
				$options .= "<optgroup label=\"" . $customerName . "\" style=\"font-style:normal;\">\n";
				foreach ($this->dataAccess->getAllBranches($customerID) as $branchID => $branchName) {
					$selected = ($preselectedBranch == $branchID) ? " SELECTED" : "";
					$options .= "<option" . $selected . " value=\"" . $branchID . "\">" . $branchName . "</option>";
				}
				$options .= "</optgroup>";
			}
		}
		
		else if ($this->dataAccess->isCustomerAdmin($this->currentUserID)) {
			foreach ($this->dataAccess->getAllBranches($this->dataAccess->getEmployeeCustomerID($this->currentUserID)) as $branchID => $branchName) {
				$selected = ($preselectedBranch == $branchID) ? " SELECTED" : "";
				$options .= "<option" . $selected . " value=\"" . $branchID . "\">" . $branchName . "</option>";
			}
		}

		return $options;
	}
	
	public function getBranchesComboBox ($name, $customerID, $emptyFirst = true) {
		
		$html = "<select name=\"" . $name . "\">\n";
		
		foreach ($this->dataAccess->getAllBranches($customerID) as $branchID => $branchName) {
			$html .= "	<option value=\"" . $branchID . "\">" . $branchName . "</option>\n";
		}
			
		$html .= "</select>\n";
		
		return $html;
	}
	
	public function getEmployeesAsOptions ($preselectedEmployeeID = "", $blacklist = array()) {
		$options = "";
		
		if ($this->dataAccess->isAdmin($this->currentUserID)) {
			$userlist = $this->dataAccess->getAllEmployees();	
		}
		
		else if ($this->dataAccess->isCustomerAdmin($this->currentUserID)) {
			$userlist = $this->dataAccess->getAllEmployees($this->dataAccess->getEmployeeCustomerID($this->currentUserID));
		}
		
		asort($userlist);
		foreach ($userlist as $userID => $userName) {
			if (!in_array($userID, $blacklist) && !in_array($userName, $blacklist)) {
				$selected = ($preselectedEmployeeID == $userID) ? " SELECTED" : "";
				$options .= "<option" . $selected . " value=\"" . $userID . "\">" . $userName . "</option>";
			}
		}
		return $options;		
	}
	
	
	
	public function getEmployeesTable ($customerID) {
		
		$userlist = $this->dataAccess->getGroupMembers($customerID);
		
		$isAdmin = $this->dataAccess->isAdmin($this->currentUserID);
		
		$js = <<< END
		
		<script type="text/javascript">
			function closeAll() {
			    var hasClassName = new RegExp("(?:^|\\s)" + "edit"+ "(?:$|\\s)");
				var allElements = document.getElementsByTagName("div");
				var element;
				for (var i = 0; (element = allElements[i]) != null; i++) {
					var elementClass = element.className;
					if (elementClass && elementClass.indexOf("edit") != -1 && hasClassName.test(elementClass)) {
						if (element.style.display == "block") {
							element.style.display = "none";
							element.parentNode.style.backgroundColor = "#EEEEEE";
							document.getElementById(element.id.replace("edit", "readonly")).style.display = "block";
						}
					}
				}
			}
		
			function toggleEditMode(userID) {
				if (document.getElementById("readonly_" + userID).style.display != "none") {
					closeAll();
					document.getElementById("readonly_" + userID).style.display = "none";
					document.getElementById("edit_" + userID).parentNode.style.backgroundColor = "#FFFFFF";
					document.getElementById("edit_" + userID).style.display = "block";
				} else {
					document.getElementById("edit_" + userID).parentNode.style.backgroundColor = "#EEEEEE";
					document.getElementById("readonly_" + userID).style.display = "block";
					document.getElementById("edit_" + userID).style.display = "none";
				}
			}
		</script>
		
END;
		
		$html = $js;
		
		$html .= "<table class=\"grid\" id=\"participantsTable\" style=\"width:100%\">\n";
		$html .= "	<tr id=\"header\">\n";
		$html .= "		<th></th>";
		$html .= "		<th>Name</th>";
		$html .= "		<th>Passwort</th>";
		$html .= "		<th>Rolle</th>";
		$html .= "		<th>sperren</th>";
		$html .= "		<th>Papierkorb</th>";
		$html .= "	</tr>\n";
					
		foreach ($userlist as $userID => $login) {
			if (!$this->dataAccess->isAdmin($GLOBALS["STEAM"]->get_current_steam_user()->get_id()) &&  $this->dataAccess->getTrashDate($userID) < 0) {
				continue;
			}
      $html .= $this->getEmployeeRow($userID, $login);
		}

		$html .= "</table>\n";
		
		return $html;
	}
	
  public function getEmployeeRow($userID, $login) {
  	if (!$this->dataAccess->isTrashed($userID)) {
      $html = "  <tr class=\"filter_user\" id=\"row[" . $userID . "]\">\n";
      $userFullName = $this->dataAccess->getUserFirstName($userID) . " " . $this->dataAccess->getUserLastName($userID);
      $userStatus = ($this->dataAccess->getUserStatus($userID)!="Teilnehmer") ? " - " . $this->dataAccess->getUserStatus($userID):"";
      $iconId = $this->dataAccess->getUserIconId($userID);
      $icon_link = ( $iconId == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $iconId . "&type=usericon&width=30&height=40";
      $html .= "    <td style=\"vertical-align:top;width: 26px;\"><img src=\"$icon_link\" width=\"26\" height=\"35\" alt=\"$userFullName\" title=\"$userFullName\"/></td>\n";
      //$html .= "    <td><input id=\"firstname[" . $userID . "]\" type=\"text\" class=\"greyInputField\" name=\"firstname\" value=\"" . $this->dataAccess->getUserFirstName($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" />";
      //$html .= "        <input id=\"lastname[" . $userID . "]\" type=\"text\" class=\"greyInputField\" name=\"lastname\" value=\"" . $this->dataAccess->getUserLastName($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" /> (<small>" . $login . "</small>)";
      //$html .= "        <br><input id=\"email[" . $userID . "]\" type=\"text\" class=\"greyInputField\" style=\"width:120px;\" name=\"email\" value=\"" . $this->dataAccess->getUserEmail($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" /></td>\n";
      $html .= "    <td style=\"width:370px;vertical-align:top;\"><img id=\"toggleImg[$userID]\" onclick=\"toggleEditMode($userID)\" style=\"cursor:pointer;float:right;\" src=\"/styles/standard/images/edit_16.gif\">";
      $html .= "      <div class=\"readonly\" id=\"readonly_$userID\"><b style=\"cursor:pointer\" onclick=\"location.href = ('/user/$login/')\"><div id=\"readonly_firstname[$userID]\" style=\"color:#C36026;display:inline;margin-top:-16px;\">" . $this->dataAccess->getUserFirstName($userID) . "</div> <div id=\"readonly_lastname[$userID]\" style=\"color:#C36026;display:inline;\">" . $this->dataAccess->getUserLastName($userID) . "</div></b> (" . $login . ")" . $userStatus;
      $html .= "        <br><small id=\"readonly_email[$userID]\" >" . (($this->dataAccess->getUserEmail($userID) == "") ? "keine E-Mail-Adresse" : $this->dataAccess->getUserEmail($userID)) . "</small>";
      $html .= "      <br><small id=\"desc_$userID\">". usermanangement::get_instance()->get_user_status_html($login) ."</small>";
      //$courses = $this->dataAccess->getCoursesForUser($userID);
      //$keys = array_keys($courses);
      //$course = $courses[$keys[0]];
      //$html .= "      <br><b>Kurse:</b><br> " . $course["COURSE_NAME"];
      //$html .= "<br><small>Rolle Teilnehmer</small>";
      //$html .= "<br><a href=\"\">Kurse verwalten</a>";
      $html .= "</div>";
      $html .= "<div class=\"edit\" id=\"edit_$userID\" style=\"display:none\"><div style=\"display:block;margin-bottom:3px\"><b>Benutzerkennung:</b> " . $login . "</div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>Vorname:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"firstname[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"firstname\" value=\"" . $this->dataAccess->getUserFirstName($userID) ."\"></td></tr></table></div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>Nachname:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"lastname[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"lastname\" value=\"" . $this->dataAccess->getUserLastName($userID) ."\"></td></tr></table></div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>E-Mail:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"email[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"email\" value=\"" . $this->dataAccess->getUserEmail($userID) ."\"></td></tr></table></div>";
      //$html .= "<div style=\"display:block;white-space:nowrap;\"><b><a href=\"javascript:;\">Kurse verwalten</a></b></div>";
      $html .= "</div></td>\n";
      $html .= "    <td style=\"vertical-align:top;\"><div id=\"password_$userID\">".$this->dataAccess->getPasswordHTML($userID)."</div><a href=\"javascript:resetPW('$login', '$userID');\">zurücksetzen</a></td>";
      //$html .= "    <td align=\"center\"><input type=\"button\" name=\"resetPasswordButton\" value=\"reset Passwort\" userID=\"" . $userID . "\" />&nbsp;<input type=\"button\" name=\"deleteButton\" value=\"l&ouml;schen\" userID=\"" . $userID . "\" /></td>\n";
//      $html .= "    <td align=\"center\">\n";
//      $html .= "      <select id=\"role[" . $userID . "]\" style=\"font-family:Verdana; font-size:11px; height:20px;\" name=\"userRole\" userID=\"" . $userID . "\" onchange=\"saveRoleChange(this)\" class=\"greyInputField\">\n";
//      $html .= "        <option value=\"0\">Mitarbeiter</option>\n";
//      $html .= "        <option value=\"2\"" . ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($userID) ? " selected" : "") . ">Kunden-Admin</option>\n";
//      
//      if ($isAdmin) {
//        $html .= "        <option value=\"3\"" . ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($userID) ? " selected" : "") . ">System-Admin</option>\n";
//      }
//      
//      $html .= "      </select>\n";
//      $html .= "    </td>\n";
      $html .= "    <td style=\"vertical-align:top;\"> <br><a href=\"javascript:showCourseDialog('$login', '$userID');\">verwalten</a></td>\n";
      $html .= "    <td style=\"vertical-align:top;\" align=\"center\"><div class=\"whiteCheckbox\"><input id=\"lockState[" . $userID . "]\" style=\"margin:0px;\" type=\"checkbox\"" . (($this->dataAccess->isLocked($userID)) ? " CHECKED" : " ") . " name=\"lockState\" userID=\"" . $userID . "\" onclick=\"saveLockCheckboxValue(this)\" /></div></td>\n";
      //$html .= "    <td align=\"center\"><a login=\"".$login."\" id=\"action[" . $userID . "]\" userID=\"" . $userID . "\" action=\"trashbin\" onclick=\"confirmAction(this);return false;\" href=\"\">in Papierkorb legen</a><br><a login=\"".$login."\" id=\"action[" . $userID . "]\" userID=\"" . $userID . "\" action=\"resetPW\" onclick=\"confirmAction(this);return false;\" href=\"\">Passwort zurücksetzen</a>
      $html .= "    <td style=\"vertical-align:top;width:90px\" align=\"center\"><div class=\"whiteCheckbox\"><input id=\"trashState[" . $userID . "]\" style=\"margin:0px;\" type=\"checkbox\"" . (($this->dataAccess->isTrashed($userID)) ? " CHECKED" : " ") . " name=\"trashState\" userID=\"" . $userID . "\" onclick=\"saveTrashCheckboxValue(this)\" /></div><div id=\"trashdate_$userID\">" . (($this->dataAccess->isTrashed($userID)) ? "noch " . $this->dataAccess->getTrashDate($userID) . " Tage" : " ") ."</div></td>\n";
      $html .= "  </tr>\n"; 
      return $html;
  	} else {
  	  $html = "  <tr class=\"filter_user\" id=\"row[" . $userID . "]\">\n";
      $userFullName = $this->dataAccess->getUserFirstName($userID) . " " . $this->dataAccess->getUserLastName($userID);
      $userStatus = ($this->dataAccess->getUserStatus($userID)!="Teilnehmer") ? " - " . $this->dataAccess->getUserStatus($userID):"";
      $iconId = $this->dataAccess->getUserIconId($userID);
      $icon_link = ( $iconId == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $iconId . "&type=usericon&width=30&height=40";
      $html .= "    <td style=\"vertical-align:top;width: 26px;background-color:#f7f7f7;\"><img src=\"$icon_link\" width=\"26\" height=\"35\" alt=\"$userFullName\" title=\"$userFullName\"/></td>\n";
      //$html .= "    <td><input id=\"firstname[" . $userID . "]\" type=\"text\" class=\"greyInputField\" name=\"firstname\" value=\"" . $this->dataAccess->getUserFirstName($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" />";
      //$html .= "        <input id=\"lastname[" . $userID . "]\" type=\"text\" class=\"greyInputField\" name=\"lastname\" value=\"" . $this->dataAccess->getUserLastName($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" /> (<small>" . $login . "</small>)";
      //$html .= "        <br><input id=\"email[" . $userID . "]\" type=\"text\" class=\"greyInputField\" style=\"width:120px;\" name=\"email\" value=\"" . $this->dataAccess->getUserEmail($userID) . "\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" /></td>\n";
      $html .= "    <td style=\"width:370px;vertical-align:top;color:#aaa;background-color:#f7f7f7;\">";
      $html .= "      <div class=\"readonly\" id=\"readonly_$userID\"><b><div id=\"readonly_firstname[$userID]\" style=\"color:#aaa;display:inline;margin-top:-16px\">" . $this->dataAccess->getUserFirstName($userID) . "</div> <div id=\"readonly_lastname[$userID]\" style=\"color:#aaa;display:inline;\">" . $this->dataAccess->getUserLastName($userID) . "</div></b> (" . $login . ")" . $userStatus;
      $html .= "        <br><small id=\"readonly_email[$userID]\" >" . (($this->dataAccess->getUserEmail($userID) == "") ? "keine E-Mail-Adresse" : $this->dataAccess->getUserEmail($userID)) . "</small>";
      $html .= "      <br><small id=\"desc_$userID\">". usermanangement::get_instance()->get_user_status_html($login) ."</small>";
      //$courses = $this->dataAccess->getCoursesForUser($userID);
      //$keys = array_keys($courses);
      //$course = $courses[$keys[0]];
      //$html .= "      <br><b>Kurse:</b><br> " . $course["COURSE_NAME"];
      //$html .= "<br><small>Rolle Teilnehmer</small>";
      //$html .= "<br><a href=\"\">Kurse verwalten</a>";
      $html .= "</div>";
      $html .= "<div class=\"edit\" id=\"edit_$userID\" style=\"display:none\"><div style=\"display:block;margin-bottom:3px\"><b>Benutzerkennung:</b> " . $login . "</div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>Vorname:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"firstname[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"firstname\" value=\"" . $this->dataAccess->getUserFirstName($userID) ."\"></td></tr></table></div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>Nachname:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"lastname[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"lastname\" value=\"" . $this->dataAccess->getUserLastName($userID) ."\"></td></tr></table></div>";
      $html .= "<div style=\"display:block;white-space:nowrap;\"><table><tr><td style=\"padding:0px;margin:0px;background-color:#ffffff\"><b>E-Mail:</b></td><td style=\"padding:0px;margin:0px;background-color:#ffffff;width:100%\"><input id=\"email[" . $userID . "]\" onclick=\"enterField(this)\" onfocus=\"enterField(this)\" onblur=\"saveFieldValue(this)\" userID=\"" . $userID . "\" style=\"width:100%;background-color:#ffffff;border: 1px solid #ccc;margin:1px;\" name=\"email\" value=\"" . $this->dataAccess->getUserEmail($userID) ."\"></td></tr></table></div>";
      //$html .= "<div style=\"display:block;white-space:nowrap;\"><b><a href=\"javascript:;\">Kurse verwalten</a></b></div>";
      $html .= "</div></td>\n";
      $html .= "    <td style=\"vertical-align:top;color:#aaa;background-color:#f7f7f7;\"><div id=\"password_$userID\">".$this->dataAccess->getPasswordHTML($userID)."</div>zurücksetzen</td>";
      //$html .= "    <td align=\"center\"><input type=\"button\" name=\"resetPasswordButton\" value=\"reset Passwort\" userID=\"" . $userID . "\" />&nbsp;<input type=\"button\" name=\"deleteButton\" value=\"l&ouml;schen\" userID=\"" . $userID . "\" /></td>\n";
//      $html .= "    <td align=\"center\">\n";
//      $html .= "      <select id=\"role[" . $userID . "]\" style=\"font-family:Verdana; font-size:11px; height:20px;\" name=\"userRole\" userID=\"" . $userID . "\" onchange=\"saveRoleChange(this)\" class=\"greyInputField\">\n";
//      $html .= "        <option value=\"0\">Mitarbeiter</option>\n";
//      $html .= "        <option value=\"2\"" . ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($userID) ? " selected" : "") . ">Kunden-Admin</option>\n";
//      
//      if ($isAdmin) {
//        $html .= "        <option value=\"3\"" . ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($userID) ? " selected" : "") . ">System-Admin</option>\n";
//      }
//      
//      $html .= "      </select>\n";
//      $html .= "    </td>\n";
      $html .= "    <td style=\"vertical-align:top;color:#aaa;background-color:#f7f7f7;\">Teilnehmer<br>verwalten</td>\n";
      $html .= "    <td style=\"vertical-align:top;color:#aaa;background-color:#f7f7f7;\" align=\"center\"><div class=\"whiteCheckbox\"><input id=\"lockState[" . $userID . "]\" style=\"margin:0px;\" type=\"checkbox\"" . (($this->dataAccess->isLocked($userID)) ? " CHECKED" : " ") . " name=\"lockState\" userID=\"" . $userID . "\" onchange=\"saveLockCheckboxValue(this)\" disabled /></div></td>\n";
      //$html .= "    <td align=\"center\"><a login=\"".$login."\" id=\"action[" . $userID . "]\" userID=\"" . $userID . "\" action=\"trashbin\" onclick=\"confirmAction(this);return false;\" href=\"\">in Papierkorb legen</a><br><a login=\"".$login."\" id=\"action[" . $userID . "]\" userID=\"" . $userID . "\" action=\"resetPW\" onclick=\"confirmAction(this);return false;\" href=\"\">Passwort zurücksetzen</a>
      $html .= "    <td style=\"vertical-align:top;width:90px\" align=\"center\"><div class=\"whiteCheckbox\"><input id=\"trashState[" . $userID . "]\" style=\"margin:0px;\" type=\"checkbox\"" . (($this->dataAccess->isTrashed($userID)) ? " CHECKED" : " ") . " name=\"trashState\" userID=\"" . $userID . "\" onchange=\"saveTrashCheckboxValue(this)\" /></div><div id=\"trashdate_$userID\">" . (($this->dataAccess->isTrashed($userID)) ? "noch " . $this->dataAccess->getTrashDate($userID) . " Tage" : " ") ."</div></td>\n";
      $html .= "  </tr>\n"; 
      return $html;
  	}
  }
	
	
	public function getRolesAsOptions ($userRole = 0) {
		
		$options = "<option value=\"0\">Mitarbeiter</option>";
		
		if ($this->dataAccess->isAdmin($this->currentUserID) || $this->dataAccess->isCustomerAdmin($this->currentUserID)) {
			$options .= "<option" . (($userRole == 1) ? " SELECTED" : "") . " value=\"1\">Filial-Admin</option>";	
		}

		if ($this->dataAccess->isAdmin($this->currentUserID)) {
			$options .= "<option" . (($userRole == 2) ? " SELECTED" : "") . " value=\"2\">Kunden-Admin</option>";
			$options .= "<option" . (($userRole == 3) ? " SELECTED" : "") . " value=\"3\">Admin</option>";	
		}
		
		return $options;
	}
	
	public function getCustomersAsOptions ($selectedCustomerID = "", $blacklist = array()) {		
		
		if ($this->dataAccess->isAdmin($this->currentUserID)) {
			$customers = $this->dataAccess->getAllCustomers();
			asort($customers);
		}
		
		else {
			$customers = array();
			$customersData = $this->dataAccess->getEmployeeCustomer($this->currentUserID);
			$customers[$customersData["id"]] = $customersData["name"];
		}

		$options = "";
		
		foreach ($customers as $customerID => $customerName) {
			if (!in_array($customerID, $blacklist) && !in_array($customerName, $blacklist)) {
				$selected = ($selectedCustomerID == $customerID) ? " SELECTED" : "";
				$options .= "<option" . $selected . " value=\"" . $customerID . "\">" . $customerName . "</option>";
			}
		}
		
		return $options;	
	}
	
	public function getCustomersCombobox ($selectedCustomerID = "") {
		
		$html = "";
		$html .= "		<select size=\"1\" name=\"customerID\" style=\"" . $this->comboboxCSSStyle . "\">\n";
		$html .= $this->getCustomersAsOptions($selectedCustomerID);
		$html .= "		</select>\n";

		return $html;
	}
	
	public function getElearningCoursesCombobox() {
		$html = "";
		$html .= "		<select size=\"1\" name=\"courseID\" style=\"" . $this->comboboxCSSStyle . "\">\n";
		$html .= $this->getElearningCoursesAsOptions();
		$html .= "		</select>\n";

		return $html;
	}
	
	public function getElearningCoursesAsOptions () {		
		
		$elearning_courses = $this->dataAccess->getAllElearningCourses();
		$options = "";
		
		foreach ($elearning_courses as $courseID => $courseName) {
			$options .= "<option value=\"" . $courseID . "\">" . $courseName . "</option>";
		}
		
		return $options;	
	}
	
	public function getCustomersSelection ($templateToLoadOnSubmit, $selectedCustomerID = "") {
		$html =  "<form action=\"/usermanagement/\" name=\"formular\" method=\"post\">\n";
		$html .= "	<table class=\"grid\" width=\"100%\">\n";
		$html .= "		<tr><th colspan=\"2\" class=\"group\">Kunde ausw&auml;hlen</th></tr>\n";
		$html .= $this->getCustomersCombobox($selectedCustomerID);
		$html .= "	</table>\n";
		$html .= "	<table width=\"100%\">\n";
		$html .= "		<tr>\n";
		$html .= "			<td align=\"right\">\n";
		$html .= "				<input type=\"hidden\" name=\"template\" value=\"" . $templateToLoadOnSubmit . "\">\n";
		$html .= "				<input type=\"submit\" value=\"ausw&auml;hlen\">\n";
		$html .= "			</td>\n";
		$html .= "		</tr>\n";
		$html .= "	</table>\n";
		$html .= "</form>\n";
		
		return $html;
	}
	
	public function getBranchSelection ($templateToLoadOnSubmit, $customerID = "") {
		$html  = "	<table class=\"grid\" width=\"100%\">\n";
		$html .= "		<tr><th colspan=\"2\" class=\"group\">Zu exportierende Filiale(n) ausw&auml;hlen</th></tr>\n";
		
		$html .= "		<tr>\n";
		$html .= "			<td class=\"label\">\n";
		$html .= "				Filiale:\n";
		$html .= "			</td>\n";
		$html .= "			<td>\n";
		$html .= "				<select size=\"1\" name=\"branchID\" style=\"" . $this->comboboxCSSStyle . "\">\n";
		
		if ($customerID == "") {
			$html .= "				<option value=\"all\">Alle Mitarbeiter</option>";
			
			$customers = $this->dataAccess->getAllCustomers();
			asort($customers);
			
			foreach ($customers as $customerID => $customerName) {
				$html .= "			<optgroup label=\"" . $customerName . "\" style=\"font-style:normal;\">\n";
				$html .= "				<option value=\"all" . $customerID . "\" style=\"font-weight:bold;\">- Alle Filialen</option>\n";
				
				$branches = $this->dataAccess->getAllBranches($customerID);
				asort($branches);
				
				foreach ($branches as $branchID => $branchName) {
					$html .= "			<option value=\"" . $branchID . "\">" . $branchName . "</option>\n";
				}
				
				$html .= "			</optgroup>";
			}
		}
		
		else {
			$html .= "				<option value=\"all" . $customerID . "\" style=\"font-weight:bold;\">- Alle Filialen</option>\n";
			
			$branches = $this->dataAccess->getAllBranches($customerID);
			asort($branches);
			
			foreach ($branches as $branchID => $branchName) {
				$html .= "			<option value=\"" . $branchID . "\">" . $branchName . "</option>\n";
			}

		}
		
		$html .= "				</select>\n";
		$html .= "			</td>\n";
		$html .= "		</tr>\n";
		$html .= "	</table>\n";
		
		return $html;
	}
	
	public function getCourseComboBox ($customerID, $emptyEntry = false) {
		
		$html = "";
		$html .= "<select size=\"1\" name=\"courseID\" style=\"" . $this->comboboxCSSStyle . "\">\n";

		if ($emptyEntry) {
			$html .= "	<option value=\"0\">---</option>\n";
		}
		
		foreach ($this->dataAccess->getAllCourseIDs() as $courseID) {
			
			$courseData = $this->dataAccess->getCourseData($courseID);
			
			if ($courseData["customerID"] == $customerID) {
				$html .= "	<option value=\"" . $courseID . "\">" . $courseData["name"] . " (" . $courseData["course_id"] . ")</option>\n";
			}
			
		}
		
		$html .= "</select>\n";

		return $html;		
	}
	
	public function getCourseRow ($courseID) {
		
		$courseData 	  	= $this->dataAccess->getCourseData($courseID);
		$numberOfLicenses 	= (int) $this->dataAccess->getCountCourseLicenses($courseID);
		$participants 	  	= $this->dataAccess->getCourseParticipants($courseID);
		$remainingLicenses  = $numberOfLicenses - count($participants);

		$html = "<tr>\n";
		
		// The "name/info" column
		$html .= "	<td><b>" . $courseData["name"] . " (" . $courseData["course_id"] . ")</b><br>" . $courseData["shortDesc"] . "</td>\n";
		
		// The "activated for" column
		if ($courseData["customerID"] != "") {
			$customers = $this->dataAccess->getAllCustomers();
			$html .= "	<td align=\"center\">" . $customers[$courseData["customerID"]] . "</td>\n";
		}
		else {
			$html .= "	<td align=\"center\">---</td>\n";
		}
		
		// The "licenses" column
		if ($courseData["customerID"] != "") {
			$html .= "	<td align=\"center\">" . $remainingLicenses . " / " . $numberOfLicenses . "</td>\n";
		}
		else {
			$html .= "	<td align=\"center\"><input type=\"text\" name=\"numberOfLicenses[" . $courseID . "]\" style=\"width:50px;\"></td>\n";
		}	

		// The "action" column
		if ($courseData["customerID"] != "") {
			$html .= "	<td align=\"right\">\n";
			$html .= "		<input onclick=\"confirmDeactivation(); return false;\" type=\"submit\" name=\"delete[" . $courseID . "]\" value=\"l&ouml;schen\" style=\"width:90px;\">\n";
			$html .= "		<input type=\"hidden\" name=\"customerID[" . $courseID . "]\" value=\"" . $courseData["customerID"] . "\" \>\n";
			$html .= "	</td>\n";
		}
		else {
			$html .= "	<td align=\"right\">\n";
			$html .= "		<select name=\"customerID[" . $courseID . "]\" size=\"1\" style=\"font-family:Verdana;font-size:11px;width:150px;height:20px;margin-right:10px;\">\n";
			$html .= $this->getCustomersAsOptions();
			$html .= "		</select>\n";
			$html .= "		<input onclick=\"setCommandValue('activateCourse');\" type=\"submit\" name=\"activate[" . $courseID . "]\" value=\"freischalten\" style=\"width:90px;\">";
			$html .= "	</td>\n";
		}

		$html .= "</tr>\n";
		
		return $html;
	}
	
	public function getCourseRowOverview ($courseID) {
		
		$elearning_course_id = elearning_mediathek::get_elearning_unit_id($courseID);
		$customer_id = $this->dataAccess->getObjectName($_SESSION["CURRENT_CUSTOMER_ID"]);
		
		$courseData 	  	= $this->dataAccess->getCourseData($courseID);
		//$numberOfLicenses 	= (int) $this->dataAccess->getCountCourseLicenses($courseID);
		$numberOfLicenses	= licensemanager::get_instance()->get_registered_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_id)));
		$participants 	  	= $this->dataAccess->getCourseParticipants($courseID);
		//$remainingLicenses  = $numberOfLicenses - count($participants);
		$remainingLicenses  = licensemanager::get_instance()->get_available_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_id)));

		//$html = "<tr style=\"cursor:pointer;\" onclick=\"submitForm(this)\" courseID=\"" . $courseID . "\">\n";
		$html = "<tr courseID=\"" . $courseID . "\">\n";
		
		
		// The "name/info" column
		$html .= "	<td><b>»" . $courseData["name"] . "« (".$courseData["course_id"].")</b><br>" . $courseData["shortDesc"] . "</td>\n";
		
		// The "remaining licenses" column
		$html .= "	<td align=\"center\">" . $remainingLicenses . " / " . $numberOfLicenses . "</td>\n";

		$html .= "</tr>\n";
		
		return $html;	
	}

	public function getCourseRowAssignCSV ($courseID) {
		
		$courseData 	  	= $this->dataAccess->getCourseData($courseID);
		$numberOfLicenses 	= (int) $this->dataAccess->getCountCourseLicenses($courseID);
		$participants 	  	= $this->dataAccess->getCourseParticipants($courseID);
		$remainingLicenses  = $numberOfLicenses - count($participants);
		$employees			= $this->dataAccess->getAllEmployees($courseData["customerID"]);

		$html = "<tr>\n";
		
		// The "select" column
		$html .= "	<td align=\"center\"><input type=\"checkbox\" name=\"select[" . $courseID . "]\" \></td>\n";
		
		// The "name/info" column
		$html .= "	<td><b>" . $courseData["name"] . " (" . $courseData["course_id"] . ")</b><br>" . $courseData["shortDesc"] . "</td>\n";
		
		// The "licenses" column
		if ($courseData["customerID"] != "") {
			$html .= "	<td align=\"center\">" . $numberOfLicenses . " / " . $remainingLicenses . "</td>\n";
		}
		else {
			$html .= "	<td align=\"center\"><input type=\"text\" name=\"numberOfLicenses[" . $courseID . "]\" style=\"width:50px;\"></td>\n";
		}
		
		return $html;	
	}
	
	/*
	public function getCourseRowRemove ($courseID) {
		
		$courseData 	  	= $this->dataAccess->getCourseData($courseID);
		$numberOfLicenses 	= (int) $this->dataAccess->getCountCourseLicenses($courseID);
		$participants 	  	= $this->dataAccess->getCourseParticipants($courseID);
		$remainingLicenses  = $numberOfLicenses - count($participants);
		$employees			= $this->dataAccess->getAllEmployees($courseData["customerID"]);

		$html = "<tr>\n";
		
		// The "name/info" column
		$html .= "	<td><b>" . $courseData["name"] . " (" . $courseID . ")</b><br>" . $courseData["shortDesc"] . "</td>\n";
		
		// The "licenses" column
		if ($courseData["customerID"] != "") {
			$html .= "	<td align=\"center\">" . $numberOfLicenses . " / " . $remainingLicenses . "</td>\n";
		}
		else {
			$html .= "	<td align=\"center\"><input type=\"text\" name=\"numberOfLicenses[" . $courseID . "]\" style=\"width:50px;\"></td>\n";
		}
		
		// The "action" column
		$html .= "	<td align=\"right\">\n";
		$html .= "		<select name=\"employeeID[" . $courseID . "]\" size=\"1\" style=\"font-family:Verdana;font-size:11px;width:150px;height:20px;margin-right:10px;\">\n";
		
		foreach ($participants as $participantID => $participantName) {
			$html .= "			<option value=\"" . $participantID . "\">" . $participantName . "</option>\n";
		}
		
		$html .= "		</select>\n";
		$html .= "		<input type=\"submit\" name=\"remove[" . $courseID . "]\" value=\"entfernen\" style=\"width:90px;\">";
		$html .= "	</td>\n";
		$html .= "</tr>\n";
		
		return $html;
		
	}
	*/
	public function getCourseRowQuota ($courseID) {
		
		$courseData 	  	= $this->dataAccess->getCourseData($courseID);
		$numberOfLicenses 	= (int) $this->dataAccess->getCountCourseLicenses($courseID);
		$participants 	  	= $this->dataAccess->getCourseParticipants($courseID);
		$remainingLicenses  = $numberOfLicenses - count($participants);

		$html = "<tr>\n";
		
		// The "name/info" column
		$html .= "	<td><b>" . $courseData["name"] . " (" . $courseData["course_id"] . ")</b><br>" . $courseData["shortDesc"] . "</td>\n";
		
		// The "activated for" column
		if ($courseData["customerID"] != "") {
			$customers = $this->dataAccess->getAllCustomers();
			$html .= "	<td align=\"center\">" . $customers[$courseData["customerID"]] . "</td>\n";
		}
		else {
			$html .= "	<td align=\"center\">---</td>\n";
		}
		
		// The "licenses" column
		$html .= "	<td align=\"center\"><input disabled=\"true\" type=\"text\" name=\"numberOfLicenses[" . $courseID . "]\" id=\"numberOfLicenses[" . $courseID . "]\" value=\"" . $numberOfLicenses . "\" style=\"width:50px;text-align:center;\"></td>\n";

		// The "action" column
		$html .= "	<td align=\"right\">\n";
		$html .= "		<select onchange=\"handleComboboxChange('" . $courseID . "');\" name=\"newQuota[" . $courseID . "]\" id=\"newQuota[" . $courseID . "]\" size=\"1\" style=\"font-family:Verdana;font-size:11px;width:150px;height:20px;margin-right:10px;\">\n";
		$html .= "			<option value=\"25\">um 25 erh&ouml;hen</option>\n";
		$html .= "			<option value=\"50\">um 50 erh&ouml;hen</option>\n";
		$html .= "			<option value=\"100\">um 100 erh&ouml;hen</option>\n";
		$html .= "			<option value=\"manual\">manulle Eingabe</option>\n";
		$html .= "		</select>\n";
		$html .= "		<input type=\"submit\" name=\"changeQuota[" . $courseID . "]\" value=\"&uuml;bernehmen\" style=\"width:90px;\">";
		$html .= "	</td>\n";

		$html .= "</tr>\n";
		
		return $html;
	}	
	
	public function getCustomerRow ($customerID, $customerName, $customerDesc) {
		
		$html = "<tr>\n";
		$html .= "	<td align=\"center\"><b>" . $customerName . "</b></td>\n";
		$html .= "	<td>\n";
		$html .= "		<input type=\"text\" name=\"customerName[" . $customerID . "]\" value=\"" . $customerDesc . "\" style=\"width:200px;margin-right:10px;\">\n";
		$html .= "	</td>\n";
		$html .= "	<td align=\"center\"><input type=\"submit\" name=\"save[" . $customerID . "]\" value=\"&Auml;nderung speichern\" style=\"margin-right:20px;\" onclick=\"setCommandValue('modifyCustomer');\"><input type=\"submit\" name=\"delete[" . $customerID . "]\" value=\"l&ouml;schen\" onclick=\"setCommandValue('deleteCustomer');\"></td>\n";
		$html .= "</tr>\n";
		
		return $html;
	}	

	public function getBranchRow ($branchID, $branchName) {
		
		$html = "<tr>\n";
		$html .= "	<td align=\"center\"><b>" . $branchID . "</b></td>\n";
		$html .= "	<td>" . ($this->dataAccess->getBranchOwner($branchID)) ."</td>\n";
		$html .= "	<td>\n";
		$html .= "		<input type=\"text\" name=\"branchName[" . $branchID . "]\" value=\"" . $branchName . "\" style=\"width:200px;margin-right:10px;\">\n";
		$html .= "	</td>\n";
		$html .= "	<td align=\"center\"><input type=\"submit\" name=\"save[" . $branchID . "]\" value=\"&Auml;nderung speichern\" style=\"margin-right:20px;\" onclick=\"setCommandValue('modifyBranch');\"><input type=\"submit\" name=\"delete[" . $branchID . "]\" value=\"l&ouml;schen\" onclick=\"setCommandValue('deleteBranch');\"></td>\n";
		$html .= "</tr>\n";
		
		return $html;
	}
	
	public function getParticipantRow ($userID) {
		
		$attributes = $this->dataAccess->getAllAttributes($userID);
		
		$login = $attributes["name"];
		$firstname = $attributes["firstname"];
		$lastname = $attributes["lastname"]; 
		
		$html = "<tr>\n";
		
		// The "login" column
		$html .= "	<td align=\"left\">" . $login . "</td>\n";

		// The "name" column
		$html .= "	<td align=\"left\">" . $firstname . " " . $lastname . "</td>\n";
		
		// The "action" column
		$html .= "	<td align=\"center\"><input type=\"submit\" name=\"userID[" . $userID . "]\" value=\"entfernen\" onclick=\"\" userID=\"" . $userID . "\" /><br/><br/>
		<input type=\"submit\" name=\"userID[" . $userID . "]\" value=\"zum Ansprechpartner dieses Kurses ernennen\" onclick=\"\" userID=\"" . $userID . "\" />
		</td>\n";
		
		$html .= "</tr>\n";
		
		return $html;		
	}
	
	public function getParticipantSelection ($customerID, $courseID) {
		
		$participants = $this->dataAccess->getCourseParticipants($courseID);
		
		$html = "<select multiple id=\"participantSelection\" name=\"userIDs[]\" size=\"10\" style=\"font-family:Verdana; font-size:11px; width:300px; height:200px;\">\n";
		
		foreach ($this->dataAccess->getAllEmployees($customerID) as $userID => $login) {
			if (! array_key_exists($userID, $participants)) {
				$firstname = $this->dataAccess->getUserFirstName($userID);
				$lastname = $this->dataAccess->getUserLastName($userID); 
				$html .= "	<option value=\"" . $userID . "\">" . $login . " (" . $firstname . " " . $lastname . ")</option>\n";
			}
		}
		
		$html .= "</select>\n";
		
		return $html;
	}
	
	
	public function createHistoryEntries ($logFiles) {
		
		$html = "";
		
		
		foreach (array_reverse($logFiles) as $logFile) {
			$html .= $this->createHistoryEntry($logFile);
		}
		
//		$html .= "<form action=\"/usermanagement/\" name=\"formular\" method=\"post\">\n";
//		$html .= "	<table width=\"100%\" style=\"margin-top:10px;\">";
//		$html .= "		<tr>\n";
//		$html .= "			<td align=\"right\">\n";
//		$html .= "				<input type=\"submit\" value=\"History l&ouml;schen\">\n";
//		$html .= "				<input type=\"hidden\" name=\"command\" value=\"deleteHistory\">";
//		$html .= "				<input type=\"hidden\" name=\"template\" value=\"employees-history\">";
//		$html .= "			</td>\n";
//		$html .= "		</tr>\n";	
//		$html .= "	</table>\n";
//		$html .= "</form>\n";
		
		return $html;
		
	}
	
	public function createHistoryEntry ($logFile) {

		$xml = simplexml_load_string($logFile["content"]);

		$timestamp = str_replace(".xml", "", $logFile["name"]);
		$date = date("d.m.Y, H:i ", $timestamp);
		
		$html = "<img style=\"cursor:pointer\" onclick=\"animatedcollapse.toggle(['".str_replace(".","_", $logFile["name"])."']);toggleIcon(this);\" src=\"/styles/stahl-orange/images/plusIcon.gif\" /> <h3 style=\"display:inline\">Erstellte Benutzer vom " . $date . " Uhr</h3> <!--<a href=\"\">(als Excel Datei speichern)</a>--><br>\n";
		
		$html .= "<div id=\"".str_replace(".","_", $logFile["name"])."\"><form action=\"/usermanagement/\" name=\"formular" . $timestamp . "\" id=\"formular" . $timestamp . "\" method=\"post\">\n";
		
		$html .= "<table class=\"grid\" width=\"100%\">";
		$html .= "	<tr>\n";
		$html .= "		<th>Benutzername</th>\n";
		$html .= "		<th>Vor- und Nachname</th>\n";
		$html .= "		<th>initiales Passwort</th>\n";
		$html .= "		<th>Status</th>\n";
		//$html .= "		<th style=\"text-align:center;\"><a href=\"#\" onclick=\"checkAllCheckboxes('formular" . $timestamp . "');\">alle selektieren</a></th>\n";
		$html .= "	</tr>\n";			
		
		foreach ($xml as $user) {
			$html .= "	<tr>\n";
			$html .= "		<td align=\"center\">" . $user["login"] . "</td>\n";
			$html .= "		<td align=\"center\">" . $user["firstname"] . " " .$user["lastname"] . "</td>\n";
			$html .= "		<td align=\"center\">" . $user["password"] . "</td>\n";
			$html .= "		<td align=\"center\">" . $user["state"] . "</td>\n";
			//$html .= "		<td align=\"center\"><input type=\"checkbox\" name=\"user[" . $user["login"] . "]\" value=\"1\" /></td>\n";
			$html .= "	</tr>\n";
		}
			
		$html .= "</table>";
		
//		$html .= "<table width=\"100%\">";
//		$html .= "	<tr>\n";
//		$html .= "		<td align=\"right\">\n";
//		$html .= "			<input type=\"submit\" value=\"selektierte Benutzer l&ouml;schen\">\n";
//		$html .= "			<input type=\"hidden\" name=\"command\" value=\"deleteMultipleEmployees\">";
//		$html .= "			<input type=\"hidden\" name=\"template\" value=\"employees-history\">";
//		$html .= "			<input type=\"hidden\" name=\"logfile\" value=\"" . $logFile["name"] . "\">";
//		$html .= "		</td>\n";
//		$html .= "	</tr>\n";	
//		$html .= "</table>";
		
		$html .= "</form></div>\n";
		
		return $html;		
	}
	
	
	public function getFilterComboBox ($customerID) {
		
		$html = "";
		$html .= "<select onchange=\"filter_course(this)\" size=\"1\" name=\"courseID\" style=\"" . $this->comboboxCSSStyle . "\">\n";
		$html .= "	<option value=\"all\">+ alle Benutzer</option>\n";
		$html .= "	<option value=\"noCourses\">- ohne Kurszugeh&ouml;rigkeit</option>\n";

		foreach ($this->dataAccess->getAllCourseIDs() as $courseID) {
			
			$courseData = $this->dataAccess->getCourseData($courseID);
			
			if ($courseData["customerID"] == $customerID) {
				$html .= "	<option value=\"" . $courseID . "\">" . $courseData["name"] . " (" . $courseData["course_id"] . ")</option>\n";
			}
			
		}
		
		$html .= "</select>\n";

		return $html;		
	}
	
	public function getExportCourseComboBox ($customerID) {
		
		$html = "";
		$html .= "<select size=\"1\" name=\"courseID\" style=\"" . $this->comboboxCSSStyle . "\">\n";
		$html .= "	<option value=\"all\">+ alle Benutzer</option>\n";
		//$html .= "	<option value=\"noCourses\">- ohne Kurszugeh&ouml;rigkeit</option>\n";

		foreach ($this->dataAccess->getAllCourseIDs() as $courseID) {
			
			$courseData = $this->dataAccess->getCourseData($courseID);
			
			if ($courseData["customerID"] == $customerID) {
				$html .= "	<option value=\"" . $courseID . "\">" . $courseData["name"] . " (" . $courseData["course_id"] . ")</option>\n";
			}
			
		}
		
		$html .= "</select>\n";

		return $html;		
	}
	
	
	public function js_confirmation () {
		
		$javascript = "";
		
		$javascript .= 	"function confirmDeletion () {\n";
		$javascript .=	"	check = confirm (\"Soll diese Filiale wirklich geloescht werden?\");\n";
		$javascript .=	"	if (check) {\n";
		$javascript .=	"		document.getElementById('command').value = 'deleteBranch';\n";
		$javascript .=	"		document.getElementById('branchDataForm').submit()";
		$javascript .=	"}\n";
		$javascript .=	"}";
		
		return $javascript;
		
	}
	
	public function jsConfirmAction ($functionName, $commandName, $formName, $confirmMessage) {
		
		$javascript = "";
		
		$javascript .= 	"function " . $functionName . " () {\n";
		$javascript .=	"	check = confirm (\"" . $confirmMessage . "\");\n";
		$javascript .=	"	if (check) {\n";
		$javascript .=	"		document.getElementById('command').value = '" . $commandName . "';\n";
		$javascript .=	"		document.getElementById('" . $formName . "').submit()";
		$javascript .=	"}\n";
		$javascript .=	"}";
		
		return $javascript;
		
	}	
	
	public function jsHandleComboboxChange () {
		$javascript  = "function handleComboboxChange (courseID) {\n";
		$javascript .= "	comboboxID = \"newQuota[\" + courseID + \"]\";";
		$javascript .= "	textfieldID = \"numberOfLicenses[\" + courseID + \"]\";";
		$javascript .= "	combobox = document.getElementById(comboboxID);\n";
		$javascript .= "	textfield = document.getElementById(textfieldID);\n";
		$javascript .= "	if (combobox.value == \"manual\") {\n";
		$javascript .= "		textfield.disabled = false;\n";
		$javascript .= "	}\n";
		$javascript .= "	else {\n";
		$javascript .= "		textfield.disabled = true;\n";
		$javascript .= "	}\n";
		$javascript .= "";
		$javascript .= "}\n";
		
		return $javascript;
	}
	
	public function js_confirmation_delete_customer () {
		
		$javascript = "";
		
		$javascript .= 	"function confirmDeletion () {\n";
		$javascript .=	"	check = confirm (\"Soll dieser Kunde wirklich geloescht werden?\");\n";
		$javascript .=	"	if (check) {\n";
		$javascript .=	"		document.getElementById('command').value = 'deleteCustomer';\n";
		$javascript .=	"		document.getElementById('customerDataForm').submit()";
		$javascript .=	"}\n";
		$javascript .=	"}";
		
		return $javascript;
		
	}	
	
	public function jsSetCommandValue () {
		
		$javascript = "";
		
		$javascript .= 	"function setCommandValue (commandValue) {\n";
		$javascript .=	"	document.getElementById('command').value = commandValue;\n";
		$javascript .=	"}\n";
		
		return $javascript;
		
	}
	
	public function jsCheckAllCheckboxes () {
		
		$javascript = "";
		
		$javascript .= 	"function checkAllCheckboxes (formID) {\n";
		$javascript .=	"	var inputs = document.getElementById(formID).getElementsByTagName('input');\n";
		$javascript .=	"	for (var i = 0; i < inputs.length; i++) {\n";
		$javascript .=	"		var input = inputs[i];\n";
		$javascript .=	"		if (input.getAttribute('type') == 'checkbox') {\n";
		$javascript .=	"			input.checked = true;\n";
		$javascript .=	"		}\n";
		$javascript .=	"	}\n";
		$javascript .=	"}\n";
		
		return $javascript;
		
	}
	
	public function rootUserOk() {
		$root_id = $this->dataAccess->getRootUser();
		$root_firstname = $this->dataAccess->getUserFirstName($root_id);
		$root_lastname = $this->dataAccess->getUserLastName($root_id);
		$root_status = $this->dataAccess->getUserStatus($root_id);
		if ($root_firstname == "" && $root_lastname == "Systemverwalter" && $root_status == "coactum GmbH") {
			return true;
		} else {
			return false;
		}
	}
	
	
}

?>