<?php
/*
 * class for importing and exporting exam lists for the exam office
 * 
 * @author Marcel Jakoblew
 */

class exam_office_file_handling{
	
	private static $instance = NULL; 
	 
	private function __construct() {
		
	} 
	 
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private function __clone() {}
	
	
	/*
	 * this function parses a given table file and extracts all matriculation numbers
	 * 
	 * @tableFilePath the path to a table file, allowed are .xls .xlsx .csv
	 * @unsafeMode check wheather the numbers are all in the same column and without discontinuities
	 * 
	 * @return a list of matriculation numbers, FALSE if safeMode condition is violated
	 */
	public function tableFile2matriculationNumbersList($tableFilePath, $unsafeMode=FALSE){
		$objPHPExcel = PHPExcel_IOFactory::load($tableFilePath);
		$checkAllInSameColumn = TRUE;
		$checkAllIterating = TRUE;
		$numberOfMatriculationNumbers=0;
		$lastCellObj = FALSE;
		$validMatriculationNumbers = array();
		
		foreach ($objPHPExcel->getAllSheets() as $sheet){
			$cellCollection = $sheet->getCellCollection();
			foreach ($cellCollection as $cellObj){
				$cellValue = $cellObj->getValue();
				$cellValueInt = intval($cellValue);
				if ($this->isMatriculationNumber($cellValueInt)){
					//a valid matriculation number
					$numberOfMatriculationNumbers++;
					if($lastCellObj!=FALSE && ($lastCellObj->getColumn())!=($cellObj->getColumn())) {$checkAllInSameColumn=FALSE;}
					if($lastCellObj!=FALSE && (int)($lastCellObj->getRow())!=((int)($cellObj->getRow()))-1) {$checkAllIterating=FALSE;};
					$validMatriculationNumbers[]=$cellValueInt;
					$lastCellObj = $cellObj;
				}
			}
		}
		
		//return cases
		if($checkAllInSameColumn && $checkAllIterating) {
			return $validMatriculationNumbers;
		} else {
			if ($unsafeMode) return $validMatriculationNumbers;
			return FALSE;
		}
	}
	
	/*
	 * add a participant to the database
	 * participant is added to the course and the exam table
	 * 
	 * @term the exam term
	 * @matNr matriculationNumber
	 * 
	 * @return true if data saved to database
	 */
	public function addParticipantToDatabaseFromMatriculationNumber($matNr,$term=0){
		$ldapManager = exam_organization_ldap_manager::getInstance();
		$imtLogin = $ldapManager->matriculationNumber2imtLogin($matNr);
		$lastName = $ldapManager->matriculationNumber2lastName($matNr);
		$firstName = $ldapManager->matriculationNumber2firstName($matNr);
		$eoDatabase = exam_organization_database::getInstance();
		$result1 = $eoDatabase->addParticipantToCourse($imtLogin,$lastName,$firstName,$matNr); //add participant to course
		$result2 = $eoDatabase->addParticipantToExam($imtLogin,$term); //add participant to exam
		
		//HIS LSF import
		if(isset($_SESSION["exam_organization_excelRowArray"])){
			$rowStringArray = $_SESSION["exam_organization_excelRowArray"];
			if(isset($rowStringArray[$matNr])){
				$result3 = $eoDatabase->saveExcelRow($term, $matNr, $rowStringArray[$matNr]); //add participant to exam
			}
		}
		
		//return result state
		if ( !($result1==FALSE) && !($result2==FALSE) ) return TRUE;
		return FALSE;
	}
	
	/*
	 * returns true if a matriculation number is valid
	 * 
	 * @$mnr a potential matriculation number
	 * 
	 * @return true if a number is a matriculation number, else false
	 */
	public function isMatriculationNumber( $mnr ) {
		$first = substr($mnr, 0, 1);
		$prf   = substr($mnr, strlen($mnr)-1, 1);
	    $mod   = $mnr % 11;
		return (($first==3 || $first==6) && ($mod==0 ? TRUE : ($mod==1 && $prf==0)));
	}
	
	
	
	//HIS-LSF import/export
	
	/*
	 * first call
	 *
	 */
	public function getExcelRowString($tableFilePath, $matNr){
		$objPHPExcel = PHPExcel_IOFactory::load($tableFilePath); //get a excel object
		$objWorksheet = $objPHPExcel->getActiveSheet();
		//search the row number for a matnr
		$rowNumber = $this->getRowNumber($objPHPExcel, $matNr);
		//now i have a row number
		//get the row obj to the row number
		$rowContentArray = array();
		$hicol = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn()); //Spalten .... O
		for($i=1;$i<=$hicol;$i++){
			$cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i, $rowNumber)->getValue();
			if($cellValue!="" || $cellValue!=NULL){
				$rowContentArray[$i] = $cellValue;
			}
		}
		
		//convert the row obj to a row string
		$rowString = $this->rowArray2String($rowContentArray);
		//return the row string
		return $rowString;
	}
	
	
	/*
	 * 
	 * 
	 */
	private function getRowNumber($objPHPExcel, $matNr){
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
		$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
		
		//do this only once
		foreach ($objPHPExcel->getAllSheets() as $sheet){//sheets
			for($row=1;$row<=$highestRow;$row++){//rows
				for($cell=0;$cell<=$highestColumnIndex;$cell++){//cells
					//get the content
					$content  = $objWorksheet->getCellByColumnAndRow($cell, $row)->getValue();
					if ($content==$matNr){
						return $row;
					}
				}
			}
		}
	}

	public function rowArray2String($rowArray){
		return serialize($rowArray);
	}
	
	public function string2RowArray($rowString){
		return unserialize($rowString);
	}
	
	
	/*
	 * @tableFilePath path of the downloaded excel file
	 * 
	 * @return array[matriculationnumber] = rowString 
	 */
	public function parseFullExcel2rowStringArray($tableFilePath){
		$objPHPExcel = PHPExcel_IOFactory::load($tableFilePath); //get a excel object
		$objWorksheet = $objPHPExcel->getActiveSheet();
		
		//get highest row and column
		$hicol = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn()); //Spalten .... O
		$hiRow = $objWorksheet->getHighestRow(); //reihen
		
		$rowsArray = array(); //contains ALL rows with matriculation number as index
		
		for($row=0;$row<=$hiRow;$row++){ //iterate over all rows
			
			$rowContentArray = array(); //contains the content of ONE row
			for($column=0;$column<=$hicol;$column++){ //iterate over all columns
				$cellValue = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($column, $row)->getValue();
				if($cellValue!="" || $cellValue!=NULL){
					$rowContentArray[$column] = $cellValue;
				}
			}
			//convert the row array to a row string
			$matriculationNumber = $this->getMatriculationNumberFromArray($rowContentArray);
			
			if ($matriculationNumber===FALSE) {
				//matriclation number not found
			} else {
				//matriclation number found
				$rowString = $this->rowArray2String($rowContentArray);
				$rowsArray[$matriculationNumber]=$rowString;
			}
			
		}
		return $rowsArray;
	}
	
	
	/*
	 * helper function for parseFullExcel2rowStringArray
	 */
	private function getMatriculationNumberFromArray($array){
		foreach ($array as $element){
			if($this->isMatriculationNumber($element)) {
				return $element;
			}
		}
		return FALSE;
	}

}
?>