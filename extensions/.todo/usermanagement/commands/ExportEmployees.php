<?php

class ExportEmployees implements Command {
	
	public function execute (Request $request, Response $response) {
		$courseID = $request->getParameter("courseID");
		$customerID = $request->getParameter("customerID");
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('Benutzer');
		
		
		$filename = "";
		$titel = "";
		//get users
		$currentRow = 4;
		if ($courseID === "all") {
			$filename = "Nutzerliste_" . $_SESSION["CURRENT_CUSTOMER_NAME"] . "_" . date("d-m-Y",time()) . ".xls";
			$titel = "E-Learning Benutzliste des Unternehmen " . $_SESSION["CURRENT_CUSTOMER_NAME"];
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllEmployees($customerID) as $employeeID => $employeeName) {
				$password = "****";
				
				if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isGeneratedPassword($employeeID)) {
					$password = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getGeneratedPassword($employeeID);
				}
				$email = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserEmail($employeeID);
				$date = date("d.m.Y", $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjCreationDate($employeeID));
				if ($email === false) {
					$email = "";
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue("A" . $currentRow, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserFirstName($employeeID));
				$objPHPExcel->getActiveSheet()->SetCellValue("B" . $currentRow, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLastName($employeeID));
				$objPHPExcel->getActiveSheet()->SetCellValue("C" . $currentRow, $email);
				$objPHPExcel->getActiveSheet()->SetCellValue("D" . $currentRow, $employeeName);
				$objPHPExcel->getActiveSheet()->SetCellValue("E" . $currentRow, $password);
				$objPHPExcel->getActiveSheet()->SetCellValue("F" . $currentRow, $date);
		
				$currentRow ++;
			}
		} else {
			$filename = "Nutzerliste_" . $_SESSION["CURRENT_CUSTOMER_NAME"] . "_" . $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($courseID) ."_" . date("d-m-Y",time()) . ".xls";
			$titel = "E-Learning Benutzliste des Unternehmen " . $_SESSION["CURRENT_CUSTOMER_NAME"] . " - " . $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectDesc($courseID) . " (" . $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($courseID) . ")";
			foreach($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID) as $employeeID => $employeeName) {
				$password = "****";
				
				if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isGeneratedPassword($employeeID)) {
					$password = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getGeneratedPassword($employeeID);
				}
				$email = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserEmail($employeeID);
				if ($email === false) {
					$email = "";
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue("A" . $currentRow, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserFirstName($employeeID));
				$objPHPExcel->getActiveSheet()->SetCellValue("B" . $currentRow, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLastName($employeeID));
				$objPHPExcel->getActiveSheet()->SetCellValue("C" . $currentRow, $email);
				$objPHPExcel->getActiveSheet()->SetCellValue("D" . $currentRow, $employeeName);
				$objPHPExcel->getActiveSheet()->SetCellValue("E" . $currentRow, $password);
		
				$currentRow ++;
			}
		}
		
		// Excel header
		$objPHPExcel->getActiveSheet()->SetCellValue("A1", $titel);
		$objPHPExcel->getActiveSheet()->SetCellValue("A2", "Stand ".date("d.m.Y",time()));
		$objPHPExcel->getActiveSheet()->SetCellValue("A3", "Vorname" );
		$objPHPExcel->getActiveSheet()->SetCellValue("B3", "Nachname" );
		$objPHPExcel->getActiveSheet()->SetCellValue("C3", "Email" );
		$objPHPExcel->getActiveSheet()->SetCellValue("D3", "Benutzerkennung" );
		$objPHPExcel->getActiveSheet()->SetCellValue("E3", "Passwort" );
		$objPHPExcel->getActiveSheet()->SetCellValue("F3", "Erstellungsdatum" );
		
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		
		$objWriter->setTempDir(PATH_TEMP);
		$objWriter->save(PATH_TEMP . $filename);
		
		$myFile = PATH_TEMP . $filename;
		$fh = fopen($myFile, 'r');
		$theData = fread($fh, filesize($myFile));
		fclose($fh);
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		
		echo $theData;
		exit;
	}
}

?>