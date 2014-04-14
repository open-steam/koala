<?php

namespace Rapidfeedback\Commands;

class Export extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
        $rapidfeedback = $survey->get_environment();
        $result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
        $survey_object = new \Rapidfeedback\Model\Survey($survey);
        $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
        $survey_object->parseXML($xml);

        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
        $admin = 0;
        foreach ($staff as $group) {
            if ($group->is_member($user)) {
                $admin = 1;
                break;
            }
        }
        if ($rapidfeedback->get_creator()->get_id() == $user->get_id()) {
            $admin = 1;
        }

        if ($admin == 1) {
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("koaLA/bidOWL")
                    ->setLastModifiedBy("koaLA/bidOWL")
                    ->setTitle("Fragebogen Auswertung")
                    ->setSubject("Fragebogen Auswertung")
                    ->setDescription("Fragebogen Auswertung")
                    ->setKeywords("Fragebogen Auswertung")
                    ->setCategory("Fragebogen Auswertung");

            $styleArray = array(
                'font' => array(
                    'bold' => true
                )
            );

            $objPHPExcel->setActiveSheetIndex(0)->setTitle(gettext('overview'));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', "Fragebogen");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Allgemeine Beschreibung");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A3', "Object-ID");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', "Exportdatum");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', "Alle Antworten von Administratoren editierbar");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A6', "Eigene Antworten editierbar");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A7', "Ausfüllen");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A10', "Frage")->getStyle('A10')->applyFromArray($styleArray);







            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', $survey->get_attribute("OBJ_DESC"));
            $desc = $rapidfeedback->get_attribute("OBJ_DESC");
            if ($desc !== 0) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', $rapidfeedback->get_attribute("OBJ_DESC"));
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', $survey->get_id());
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', date("d.m.Y H:i:s", time()));
            if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_EDIT") == 1) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', "Ja");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', "Nein");
            }
            if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', "Ja");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', "Nein");
            }
            if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_PARTICIPATION_TIMES") == 0) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B7', "mehrfach");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B7', "einfach");
            }

            $questions = $survey_object->getQuestions();
            $row = 10;
            $column = 1;
            $questionCount = 1;
            foreach ($questions as $question) {
                if ($question instanceof \Rapidfeedback\Model\AbstractQuestion) {
                    if ($question instanceof \Rapidfeedback\Model\TextQuestion) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . " " . $question->getQuestionText());
                        $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                        $column++;
                    } else if ($question instanceof \Rapidfeedback\Model\TextareaQuestion) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . " " . $question->getQuestionText());
                        $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                        $column++;
                    } else if ($question instanceof \Rapidfeedback\Model\SingleChoiceQuestion) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . " " . $question->getQuestionText());
                        $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                        $column++;
                    } else if ($question instanceof \Rapidfeedback\Model\MultipleChoiceQuestion) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . " " . $question->getQuestionText());
                        $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                        $column++;
                        $array = $question->getOptions();
                        foreach ($array as $a) {
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row + 1, $a);
                            $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                            $column++;
                        }
                    } else if ($question instanceof \Rapidfeedback\Model\MatrixQuestion) {
                        $rowCount = 1;
                        $columnArray = $question->getColumns();
                        foreach ($question->getRows() as $questionRow) {
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . "." . $rowCount . " " . $questionRow);
                            $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                            foreach ($columnArray as $c) {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row + 1, $c);
                                $column++;
                            }

                            $rowCount++;
                        }
                    } else if ($question instanceof \Rapidfeedback\Model\TendencyQuestion) {
                        $optionCount = 1;
                        foreach ($question->getOptions() as $option) {
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $questionCount . "." . $optionCount . " " . $option[0] . " - " . $option[1]);
                            $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                            $column++;
                            $optionCount++;
                        }
                    }
                    $questionCount++;
                }
            }
            $participantColumn = 0;
            $creationTimeColumn = 0;
            if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_PARTICIPANTS") == 1) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Teilnehmer");
                $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                $participantColumn = $column;
                $column++;
            }
            if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_CREATIONTIME") == 1) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, "Erstellungszeit");
                $objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $row)->applyFromArray($styleArray);
                $creationTimeColumn = $column;
                $column++;
            }

            $results = $result_container->get_inventory();
            $resultCount = 1;
            $row = 12;

            foreach ($results as $result) {
                $column = 0;
                if ($result instanceof \steam_object && $result->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) {
                    $resultArray = $survey_object->getIndividualResult($result);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $resultCount);
                    $column++;
                    $questionCount = 0;
                    foreach ($resultArray as $oneResult) {
                        if (is_array($oneResult)) {
                            if ($questions[$questionCount] instanceof \Rapidfeedback\Model\MultipleChoiceQuestion) {
                                $questionArray = $questions[$questionCount]->getOptions();
                                foreach ($oneResult as $partResult) {
                                    foreach ($questionArray as $j => $ele) {
                                        if ($ele === $partResult) {
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column + $j + 1, $row, "x");
                                        }
                                    }
                                }
                                $column+= count($questionArray);

                                /* else {
                                  foreach ($oneResult as $partResult) {
                                  $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $partResult);
                                  } */
                            } else if ($questions[$questionCount] instanceof \Rapidfeedback\Model\MatrixQuestion) {

                                //0 -> array , 1 --> array
                                $cArray = json_decode(json_encode($questions[$questionCount]->getColumns()), TRUE);
                                foreach ($oneResult as $res) {
                                    $array = json_decode(json_encode($res), TRUE);
                                    foreach ($array as $r) {
                                        for ($j = 0; $j < count($cArray); $j++) {
                                            if ($r === $cArray[$j][0]) {
                                                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column + $j, $row, "x");
                                            }
                                        }
                                        $column+=count($cArray);
                                    }
                                }
                            } else {
                                $cellContent = "";
                                foreach ($oneResult as $partResult) {
                                    $cellContent = $cellContent . $partResult . "|";
                                }
                                $cellContent = substr($cellContent, 0, strlen($cellContent) - 1);
                                if (!$cellContent)
                                    $cellContent = "";
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $cellContent);
                                $column++;
                            }
                        } else {
                            if (!$oneResult)
                                $oneResult = "";
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $oneResult);
                            $column++;
                        }
                        $questionCount++;
                    }
                    if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_PARTICIPANTS") == 1) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($participantColumn, $row, $result->get_creator()->get_full_name());
                        $column++;
                    }
                    if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_CREATIONTIME") == 1) {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($creationTimeColumn, $row, date("d.m.Y H:i:s", $result->get_attribute("OBJ_CREATION_TIME")));
                        $column++;
                    }
                    $resultCount++;
                    $row++;
                }
            }

            $filename = "fragebogen" . $this->id . ".xls";
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit();
        } else {
            exit();
        }
    }

}

?>