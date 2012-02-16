<?php

class ChangeCourseQuota implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$changeQuotaKeys  		= array_keys($request->getParameter("changeQuota"));
		$newQuotas 				= $request->getParameter("newQuota");
		$newNumbersOfLicenses	= $request->getParameter("numberOfLicenses");
		
		$courseID 			= $changeQuotaKeys[0];
		$newQuota 			= $newQuotas[$courseID];
		$newNumberOfLicenses = $newNumbersOfLicenses[$courseID];
		
		$participants = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);

		if ($newQuota == "manual") {
			
			if ($newNumberOfLicenses == "") {
				throw new UsermanagementException("Keinen Wert f&uuml;r Lizenzen eingegeben", "");
			}
			
			else if (!is_numeric($newNumberOfLicenses)) {
				throw new UsermanagementException("Ung&uuml;ltige Eingabe.", "Nur numerische Werte sind erlaubt!");
			}

			else if ((int)$newNumberOfLicenses < 0) {
				throw new UsermanagementException("Ung&uuml;ltige Eingabe.", "Werte kleiner 0 sind nicht erlaubt!");
			}
			
			else if ((int)$newNumberOfLicenses < count($participants)) {
				$diff = count($participants) - (int)$newNumberOfLicenses;
				throw new UsermanagementException("Anzahl von Lizenzen zu gering f&uuml;r aktuelle Kursteilnehmerzahl.", "Entferne zun&auml;chst mindestens " . $diff . " Mitarbeiter aus dem Kurs");
			}
			
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setCourseLicenses($courseID, (int) $newNumberOfLicenses);
			
		}
		
		else {
			
			$currentLicenses = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCountCourseLicenses($courseID);
			$newNumberOfLicenses = $currentLicenses + (int) $newQuota;
			
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setCourseLicenses($courseID, (int) $newNumberOfLicenses);
			
		}
		
		return "&Auml;nderungen &uuml;bernommen";

	}
	
}

?>