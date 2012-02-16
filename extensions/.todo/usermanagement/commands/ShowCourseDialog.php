<?php

class ShowCourseDialog implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
		// The user to dialog for
		$userID = $request->getParameter("userID");
		$result = array("id" => $request->getParameter("senderID"), "command" => "showCourseDialog");
		
		$firstname = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserFirstName($userID);
		$lastname = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLastName($userID);
		$login = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID);
		
		$course_rows = "";
		$counter = 0;
		foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseDataForCustomer($_SESSION["CURRENT_CUSTOMER_ID"]) as $courseID) {
			$data = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseData($courseID);
			$is_teilnehmer = teilnehmer_role::is_role($userID, new kurs_context($courseID));
			$is_betreuer = betreuer_role::is_role($userID, new kurs_context($courseID));
			$is_anspechpartner = ansprechpartner_role::is_role($userID, new kurs_context($courseID));
			$is_mitglied = ($is_teilnehmer || $is_betreuer);
			$is_unternehmens_verwalter = unternehmensverwalter_role::is_role($userID, new unternehmens_context($_SESSION["CURRENT_CUSTOMER_ID"]));
			$is_system_verwalter = systemverwalter_role::is_role($userID, new system_context(""));
			$current_user_is_systemverwalter = systemverwalter_role::is_role($GLOBALS["STEAM"]->get_current_steam_user()->get_id(), "");
			$mitglied_checked = ($is_mitglied) ? "checked" : "";
			$unternehmens_verwalter_checked = ($is_unternehmens_verwalter) ? "checked" : "";
			$system_verwalter_checked = ($is_system_verwalter) ? "checked" : "";
			$mitglied_disabled = "";
			$teilnehmer_checked = "";
			$betreuer_checked = "";
			$ansprechpartner_checked = "";
			$role_disabled = "";
			if ($is_mitglied) {
				if ($is_anspechpartner) {
					$ansprechpartner_checked = "checked";
				} else if ($is_betreuer) {
					$betreuer_checked = "checked";
				} else if ($is_teilnehmer) {
					$teilnehmer_checked = "checked";
				}
				if (!$current_user_is_systemverwalter) {
					$mitglied_disabled = "disabled";
				}
			} else {
				$role_disabled = "disabled";
			}
			$counter++;
			$course_rows .= "<tr>";
			$course_rows .= "<td>".$data["course_id"]."</td>";
			$course_rows .= "<td><b>»".$data["name"]."«</b><br><small>(".$data["shortDesc"].")</small></td>";
			$course_rows .= "<td><form name=\"member\"><input type=\"checkbox\" $mitglied_checked $mitglied_disabled /></form>";
			$course_rows .= "";
			$course_rows .= "</td>";
			$course_rows .= "<td><form name=\"role\" $role_disabled><input type=\"radio\" name=\"role\" value=\"Teilnehmer\" $teilnehmer_checked onchange=\"changeRole('Teilnehmer',$courseID,$userID)\">Teilnehmer</input><br>
									   <input type=\"radio\" name=\"role\" value=\"Betreuer\" $betreuer_checked onchange=\"changeRole('Betreuer',$courseID,$userID)\">Betreuer</input><br>
									   <input type=\"radio\" name=\"role\" value=\"Ansprechpartener\" $ansprechpartner_checked onchange=\"changeRole('Ansprechpartner',$courseID,$userID)\">Ansprechpartner</input></form></td>";
			$course_rows .= "</tr>";
		}
		
		if ($counter == 0) {
			$course_rows = "Es sind keine Kurse für ihr Unternehmer freigeschaltet.";
		}
		
		if (USERMANAGEMENT_SYSTEMADMIN) {
			$system_admin_text = <<< END
						<tr>
							<td>Systemverwalter</td>
							<td><form name="system_admin"><input name="system_admin_checkbox" type="checkbox" onchange="toggle_system_admin()" $system_verwalter_checked/><input name="user_id" type="hidden" value="$userID" /></form></td>
						</tr>
END;
		} else {
			$system_admin_text = "";
		}
		
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID)) {
		$root_staff = <<< END
					<h3>Verwalterrolle</h3>
					<table class="grid">
						<tr>
							<th style="width:400px">Rolle</th>
							<th>Status</th>
						</tr>
						<tr>
							<td>Unternehmensverwalter</td>
							<td><form name="customer_admin"><input name="customer_admin_checkbox" type="checkbox" onchange="toggle_customer_admin()" $unternehmens_verwalter_checked/><input name="user_id" type="hidden" value="$userID" /></form></td>
						</tr>
						$system_admin_text
					</table>
END;
	} else {
		$root_staff = "";
	}

		$html = <<< END
		<style type='text/css'>
			.dialog {
				width: 100%
			}
			
			.dialog tbody {
				width: 100%
			}
			
			.dialog tr {
				width: 100%
			}
			
			.dialog_th {
				background-color:#C36026;
				width: 745px;
				height: 25px;
				color: white;
			}
			
			.dialog td {
				vertical-align:top;
				
			}
		</style>
		
		<table class="dialog">
			<tr>
				<th class="dialog_th"><div style="float:right;display:inline;cursor:pointer" onclick="closeDialog()">[schliessen]</div><h3 style="display:inline">Rolle verwalten</h3></th>
			</tr>
			<tr>
				<td>
					<div style="margin:20px"><div class="infoBar">Hier können Sie die Mitgliedschaft in Kursen und die Rolle im Kurs ändern.<br>
					Ausgewählter Benutzer: <b>$firstname $lastname <small>($login)</small></b></div>
					<h3>Kurse verwalten</h3>
					<table class="grid">
						<tr>
							<th>Kurs ID</th>
							<th style="width:400px">Kursname</th>
							<th>Mitglied</th>
							<th>Rolle</th>
						</tr>
						$course_rows
					</table>
					$root_staff
					</div>
				</td>
			</tr>
		</table>
END;
		
		$result["html"] = $html;
		
		return $result;
		
	}
	
}

?>