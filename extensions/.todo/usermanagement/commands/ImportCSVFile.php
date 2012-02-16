<?php
mb_internal_encoding("UTF-8"); 
class ImportCSVFile implements Command {

	private $commandHelper;
	
	public function execute (Request $request, Response $response) {
		
		$this->commandHelper = new CommandHelper();

		$filePath = $_FILES["csvFile"]["tmp_name"];
		
		// Create file handle
		$file = file($filePath);
		
		// Create log file handle
		$timestamp = time();
		$outputFilePath = "../usermanagement/tmp/" . $timestamp . ".txt";
		$outputFile = fopen($outputFilePath, "w");
		
		$errorOccurred = false;
		
		foreach ($file as $line) {
			
			$data = explode(",", str_replace("\"", "", $line));
			
			$firstname = isset($data[0]) ? $this->fixEncoding(trim($data[0])) : "";
			$lastname  = isset($data[1]) ? $this->fixEncoding(trim($data[1])) : "";
			$email     = isset($data[2]) ? $this->fixEncoding(trim($data[2])) : "";
			
			$login = $this->createValidLogin($firstname, $lastname);
						
			try {
				$password = $this->commandHelper->createRandomPassword(8);
				$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createEmployee($login, $password, $email, $firstname, $lastname);
				fwrite($outputFile, "" . $firstname . " " . $lastname . ", Login: " . $login . ", Passwort: " . $password . "\n");
			}
			catch (Exception $exception) {
				fwrite($outputFile, "" . $firstname . " " . $lastname . ", COULD NOT BE CREATED!!!\n");
				$errorOccurred = true;
			}
		}
		
		fclose($outputFile);
		
		if (!$errorOccurred) {
			return "Alle Benutzer aus der CSV-Datei wurden erfolgreich angelegt.<br>Klicke <a href=\"/usermanagement/tmp/" . $timestamp . ".txt\">hier</a> f&uuml;r Details.";
		}
		
		else {
			throw new UsermanagementException("Mindestens ein Benutzer aus der CSV-Datei konnte nicht angelegt werden", "Klicke <a href=\"/usermanagement/tmp/" . $timestamp . ".txt\">hier</a> f&uuml;r Details");
		}
		
	}
	
	function fixEncoding($x){ 
		if (mb_detect_encoding($x)=='UTF-8'){ 
			return $x; 
		} else { 
			return utf8_encode($x); 
		} 
	} 
	
	private function createValidLogin ($firstname, $lastname) {
		
		if ($firstname == "" && $lastname == "") {
			return "";
		} else if ($firstname == "") {
			$loginCandidate = strtolower($lastname);
		} else if ($lastname == "") {
			$loginCandidate = strtolower($firstname);
		} else {
			$loginCandidate = strtolower($firstname[0] . $lastname);
		}
		
		$t = $loginCandidate;

		$t = str_replace("\x80", "A", $t); //À
		$t = str_replace("\x81", "A", $t); //Á
		$t = str_replace("\x82", "A", $t); //Â
		$t = str_replace("\x83", "A", $t); //Ã
		$t = str_replace("\x84", "AE", $t); //Ä
		$t = str_replace("\x85", "AE", $t); //Å
		$t = str_replace("\x86", "AE", $t); //Æ
		$t = str_replace("\x87", "C", $t); //Ç
		$t = str_replace("\x88", "E", $t); //È
		$t = str_replace("\x89", "E", $t); //É
		$t = str_replace("\x8a", "E", $t); //Ê
		$t = str_replace("\x8b", "E", $t); //Ë
		$t = str_replace("\x8c", "I", $t); //Ì
		$t = str_replace("\x8d", "I", $t); //Í
		$t = str_replace("\x8e", "I", $t); //Î
		$t = str_replace("\x8f", "I", $t); //Ï
		$t = str_replace("\x90", "D", $t); //Ð
		$t = str_replace("\x91", "N", $t); //Ñ
		$t = str_replace("\x92", "O", $t); //Ò
		$t = str_replace("\x93", "O", $t); //Ó
		$t = str_replace("\x94", "O", $t); //Ô
		$t = str_replace("\x95", "OE", $t); //Õ
		$t = str_replace("\x96", "OE", $t); //Ö
		$t = str_replace("\x98", "OE", $t); //Ø
		$t = str_replace("\x99", "U", $t); //Ù
		$t = str_replace("\x9a", "U", $t); //Ú
		$t = str_replace("\x9b", "U", $t); //Û
		$t = str_replace("\x9c", "UE", $t); //Ü
		$t = str_replace("\x9d", "Y", $t); //Ý
		$t = str_replace("\x9f", "ss", $t); //ß
		$t = str_replace("\xa0", "a", $t); //à
		$t = str_replace("\xa1", "a", $t); //á
		$t = str_replace("\xa2", "a", $t); //â
		$t = str_replace("\xa3", "ae", $t); //ã
		$t = str_replace("\xa4", "ae", $t); //ä
		$t = str_replace("\xa5", "ae", $t); //å
		$t = str_replace("\xa6", "ae", $t); //æ
		$t = str_replace("\xa7", "c", $t); //ç
		$t = str_replace("\xa8", "e", $t); //è
		$t = str_replace("\xa9", "e", $t); //é
		$t = str_replace("\xaa", "e", $t); //ê
		$t = str_replace("\xab", "e", $t); //ë
		$t = str_replace("\xac", "i", $t); //ì
		$t = str_replace("\xad", "i", $t); //í
		$t = str_replace("\xae", "i", $t); //î
		$t = str_replace("\xaf", "i", $t); //ï
		$t = str_replace("\xb1", "n", $t); //ñ
		$t = str_replace("\xb2", "o", $t); //ò
		$t = str_replace("\xb3", "o", $t); //ó
		$t = str_replace("\xb4", "o", $t); //ô
		$t = str_replace("\xb5", "o", $t); //õ
		$t = str_replace("\xb6", "oe", $t); //ö
		$t = str_replace("\xb8", "oe", $t); //ø
		$t = str_replace("\xb9", "u", $t); //ù
		$t = str_replace("\xba", "u", $t); //ú
		$t = str_replace("\xbb", "u", $t); //û
		$t = str_replace("\xbc", "ue", $t); //ü
		$t = str_replace("\xbd", "y", $t); //ý
		$t = str_replace("\xbf", "y", $t); //ÿ
				
		for ($i = 0; $i < 48; $i++)
			$t = str_replace(chr ($i), "", $t);
		for ($i = 58; $i < 65; $i++)
			$t = str_replace(chr ($i), "", $t);
		for ($i = 91; $i < 97; $i++)
			$t = str_replace(chr ($i), "", $t);
		for ($i = 123; $i < 256; $i++)
			$t = str_replace(chr ($i), "", $t);

		$loginCandidate = strtolower($t);
	
		$userlist = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserList();
		
		if (array_search($loginCandidate, $userlist) != false) {
			for ($i=1; $i<=count($userlist)+1; $i++) {
				if (array_search($loginCandidate . $i, $userlist) == false) {
					$loginCandidate .= $i;
					break;
				}
			}
		}
		
		return $loginCandidate;
	}
}

?>