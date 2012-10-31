<?php
namespace Spreadsheets\Commands;

require_once PATH_DEPENDING  . 'classes/phpexcel/Classes/PHPExcel.php';
require_once PATH_DEPENDING . 'classes/phpexcel/Classes/PHPExcel/IOFactory.php';

/**
 * This Command exports the spreadsheet document with the given ID into a file.
 * The first parameter is the ID of the document.
 * The second parameter is the requested format for the file (currently "csv" or "xls")
 */
class Export extends \AbstractCommand implements \IFrameCommand {

	private $params, $id, $format, $document, $content;
	private $NodeServer = SPREADSHEETS_RT_SERVER;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
		else {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
			isset($this->params[1]) ? $this->format = $this->params[1]: "xls";
		}

		$this->document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$response = file_get_contents("http://$this->NodeServer/doc/get/$this->id");
		if ($response) {
			$this->content = $response;
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$this->export($this->format);
		return $frameResponseObject;
	}

	private function export($format) {
		$spreadsheet = json_decode($this->content);

		// Create new PHPExcel object
		$objPHPExcel = new \PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("koaLA Spreadsheets")
									 ->setLastModifiedBy("koaLA Spreadsheets")
									 ->setTitle($this->document->get_name());

		// Parse the spreadsheet document
		for ($row=0; $row < count($spreadsheet->sheets[0]->rows); $row++) {
			$cells = $spreadsheet->sheets[0]->rows[$row]->cells;
			for ($col=0; $col < count($cells); $col++) {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row+1, $cells[$col]->value);

				//alignment
				if(isset($cells[$col]->style->flags->styleRight) && $cells[$col]->style->flags->styleRight == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				elseif(isset($cells[$col]->style->flags->styleCenter) && $cells[$col]->style->flags->styleCenter == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				//font-style
				if(isset($cells[$col]->style->flags->styleBold) && $cells[$col]->style->flags->styleBold == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->setBold(true);
				if(isset($cells[$col]->style->flags->styleItalics) && $cells[$col]->style->flags->styleItalics == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->setItalic(true);
				if(isset($cells[$col]->style->flags->styleUnderline) && $cells[$col]->style->flags->styleUnderline == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->setUnderline(\PHPExcel_Style_Font::UNDERLINE_SINGLE);
				if(isset($cells[$col]->style->flags->styleLineThrough) && $cells[$col]->style->flags->styleLineThrough == "true")
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->setStrikethrough(true);

				//font-size
				$fontSize = "font-size";
				if(isset($cells[$col]->style->$fontSize))
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->setSize($cells[$col]->style->$fontSize);

				if(isset($cells[$col]->style->color))
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFont()->getColor()->setRGB(trim($cells[$col]->style->color, '#'));

				$backgroundColor = "background-color";
				if(isset($cells[$col]->style->$backgroundColor)) {
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row+1)->getFill()->getStartColor()->setRGB(trim($cells[$col]->style->$backgroundColor, '#'));
				}

				//set column width
				if ($row == 1) {
					$colString = \PHPExcel_Cell::stringFromColumnIndex($col);
					$size = $spreadsheet->sheets[0]->columns[$col]->size / 10;
					$objPHPExcel->getActiveSheet()->getColumnDimension($colString)->setWidth($size);	
				}
			}

			//set row height
			if (isset($spreadsheet->sheets[0]->rows[$row]->size)) {
				$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($spreadsheet->sheets[0]->rows[$row]->size);
			}
			else
			{	
				//auto height
				$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
			}
		}

		//write the file
	 	$filename = $this->document->get_name() . "." . $format;
	 	header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$filename");
		header('Cache-Control: max-age=0');
		if ($format == "xls") {
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		}
		elseif($format == "csv") {
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'csv');
		}
		$objWriter->setPreCalculateFormulas(FALSE);
		$objWriter->save('php://output');
		die;
	}
}
?>
