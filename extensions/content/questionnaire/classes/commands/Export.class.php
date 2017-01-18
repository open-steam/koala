<?php
namespace Questionnaire\Commands;
class Export extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
  private $survey;
  private $questionnaire;
  private $result_container;
  private $survey_object;
  private $user;
  private $staff;
  //wether the current user is allowed to export the questionary
  private $admin = 0;
  private $objPHPExcel;
  private $startRowTitle = 9;
  private $startTitleColumn = 1;
  private $startRowAnswers = 12;

	public function validateData(\IRequestObject $requestObject) {
		//nothing to validate here
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
    $this->params = $requestObject->getParams();
    isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
    $this->survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
    $this->questionnaire = $this->survey->get_environment();
    $this->result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->survey->get_path() . "/results");
    $this->survey_object = new \Questionnaire\Model\Survey($this->survey);

    //load the xml file with the structure
    $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $this->survey->get_path() . "/survey.xml");
    $this->survey_object->parseXML($xml);

    $this->user = \lms_steam::get_current_user();
		$creator = $this->questionnaire->get_creator();

		// check if current user is admin
		$staff = $this->questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
		$this->admin = 0;
		if ($creator->get_id() == $this->user->get_id() || \lms_steam::is_steam_admin($this->user)) {
			$this->admin = 1;
		}
		else{
			if(in_array($this->user, $staff)){
				$this->admin = 1;
			}
			else{
				foreach ($staff as $object) {
					if ($object instanceof \steam_group && $object->is_member($this->user)) {
						$this->admin = 1;
						break;
					}
				}
			}
		}

    if ($this->admin == 1) {
        $this->objPHPExcel = new \PHPExcel();
        $this->objPHPExcel->getProperties()->setCreator("koaLA/bidOWL")
                                           ->setLastModifiedBy("koaLA/bidOWL")
                                           ->setTitle("Fragebogen Auswertung")
                                           ->setSubject("Fragebogen Auswertung")
                                           ->setDescription("Fragebogen Auswertung")
                                           ->setKeywords("Fragebogen Auswertung")
                                           ->setCategory("Fragebogen Auswertung");

			  //set the alignment to left, because numbers are aligned on the right in excel
			  $this->objPHPExcel->getDefaultStyle()
			                    ->getAlignment()
			                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        //set the title of the sheet
        $this->objPHPExcel->setActiveSheetIndex(0)->setTitle(gettext('Fragebogenauswertung'));

        //set the general information for the survey
        $this->buildheaderInCodument();

        $this->buildQuestionTitleRow($this->startRowTitle, $this->startTitleColumn);

        //let this function generate the answers
        $this->buildAnswerRows($this->startRowAnswers);

        $filename = "fragebogen" . $this->id . ".xls";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        die();
    } else {
        die();
    }
	}

  private function buildheaderInCodument(){
      //build the generell information
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', "Fragebogen");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Allgemein Beschreibung");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A3', "Object-ID");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', "Exportdatum");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', "Alle Antworten von Administratoren editierbar");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A6', "Eigene Antworten editierbar");
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A7', "Ausfüllen");
      //$this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A9', "Frage");

      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', $this->questionnaire->get_attribute("OBJ_NAME"));
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', $this->questionnaire->get_attribute("OBJ_DESC"));
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', $this->survey->get_id());
      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', date("d.m.Y H:i:s", time()));

      if ($this->questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1) {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', "Ja");
      } else {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', "Nein");
      }
      if ($this->questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1) {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', "Ja");
      } else {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', "Nein");
      }
      if ($this->questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES") == 0) {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B7', "mehrfach");
      } else {
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('B7', "einfach");
      }
  }

  private function buildQuestionTitleRow($row, $column){
      $questions = $this->survey_object->getQuestions();
			$questionCount = 1;
			foreach ($questions as $question) {
          if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
              if ($question instanceof \Questionnaire\Model\TextQuestion) {
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage ".$questionCount . " (kurzer Text)");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());
                  $column++;
              } else if ($question instanceof \Questionnaire\Model\TextareaQuestion) {
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage ".$questionCount . " (langer Text)");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());
                  $column++;
              } else if ($question instanceof \Questionnaire\Model\SingleChoiceQuestion) {
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage ".$questionCount . " (Single Choice)");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());
                  $column++;
              } else if ($question instanceof \Questionnaire\Model\MultipleChoiceQuestion) {
                  $options = $question->getOptions();
                  $optionsCount = count($options)-1;

                  //merge the cells of the title and question
                  $this->mergeCellsInRow($column, $row, $optionsCount);
                  $this->mergeCellsInRow($column, $row+1, $optionsCount);

                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage ".$questionCount . " (Multiple Choice) ");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());
                  foreach ($options as $option){
                      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+2, $option);
                      $column++;
                  }
              } else if ($question instanceof \Questionnaire\Model\MatrixQuestion) {
									//the Gradingquestion extends the Matrixquestion with given columnnames
									if($question instanceof \Questionnaire\Model\GradingQuestion){
										$label = "Benotung";
										$options = $question->getRows();
									}
									else{
										$label = "Matrix";
										$options = $question->getColumns();
									}

                  $optionsCount = count($options)-1;

                  //merge the cells of the title and question
                  $this->mergeCellsInRow($column, $row, $optionsCount);
                  $this->mergeCellsInRow($column, $row+1, $optionsCount);

                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage ".$questionCount . " (".$label.")");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());

                  foreach ($question->getRows() as $questionRow) {
                      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+2, $questionRow);
                      $column++;
                  }
              } else if ($question instanceof \Questionnaire\Model\TendencyQuestion) {

                  $options = $question->getOptions();
                  $optionsCount = count($options)-1;

                  //merge the cells of the title and question
                  $this->mergeCellsInRow($column, $row, $optionsCount);
                  $this->mergeCellsInRow($column, $row+1, $optionsCount);

                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Frage " . $questionCount . " (Tendenz)");
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+1, $question->getQuestionText());

                  foreach ($question->getOptions() as $questionRow) {
                      $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row+2,$questionRow[0] . " - " . $questionRow[1]);
                      $column++;
                  }
              }
              $questionCount++;
          }
			}
			if ($this->questionnaire->get_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS") == 1) {
				$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Teilnehmer");
				$column++;
			}
			if ($this->questionnaire->get_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME") == 1) {
				$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Erstellungszeit");
				$column++;
			}
  }

  private function buildAnswerRows($row){
      $resultsCount = 1;
      //go into the directory and get all results
      $results = $this->result_container->get_inventory(); //all results objects of all users
			foreach ($results as $result) { //result object of one user
      		$column = 1;
          if ($result instanceof \steam_object && $result->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {

              //set the backgroundcolor of each second row if there are more then three results
              if($row % 2 ==1 AND count($results) > 3){
                  $fill = array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'EDEDED') );
                  $this->objPHPExcel->getActiveSheet()->getStyle($row)->getFill()->applyFromArray($fill);
              }

              $resultArray = $this->survey_object->getIndividualResult($result); //result array of one user

              $questions = $this->survey_object->getQuestions();
              $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column-1, $row, "Teilnehmer ".$resultsCount);

							$resultCount = 0;
							foreach($questions as $question){
								if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
									if ($question instanceof \Questionnaire\Model\MultipleChoiceQuestion) {
										$options = $question->getOptions();
										foreach ($options as $option) {
												if(in_array($option, $resultArray[$resultCount])){
														$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "x");
												}
												$column++;
										}
									} else {
										$single = $resultArray[$resultCount];
											foreach ($single as $part) {
												$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $part);
												$column++;
											}
										}
									$resultCount++;
								}
							}

              if ($this->questionnaire->get_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS") == 1) {
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $result->get_creator()->get_full_name());
                  $column++;
              }
              if ($this->questionnaire->get_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME") == 1) {
                  $this->objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, date("d.m.Y H:i:s", $result->get_attribute("OBJ_CREATION_TIME")));
                  $column++;
              }
              $resultsCount++;
              $row++;
          }
			}

      foreach (range(0, $column) as $col) {
          //$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);
					$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth(25);
      }
  }

  //merge the cells in one row
  //used for questions with more than one answer to merge the title row
  private function mergeCellsInRow($startColumn, $row, $count){
      $beginCell = $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($startColumn, $row);
      $endCell   = $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($startColumn+$count, $row);
      $this->objPHPExcel->getActiveSheet()->mergeCells($beginCell->getColumn().$beginCell->getRow().':'.$endCell->getColumn().$endCell->getRow());
  }
}
?>
