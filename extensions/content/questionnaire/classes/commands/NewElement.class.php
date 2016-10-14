<?php

namespace Questionnaire\Commands;

class NewElement extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $idRequestObject = new \IdRequestObject();
        $idRequestObject->setId($this->id);

        $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Erstelle ein neues Element in »" . getCleanName($questionnaire) . "«");
        //disable the save button (not used here)
        $dialog->setSaveAndCloseButtonLabel(null);
        //and rename the cancel Button
        $dialog->setCancelButtonLabel("Abbrechen");

        $dialog->setWidth(500);
        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        $questionTypes = array(
          "ShortTextQuestion" => "Kurzer Text",
          "LongTextQuestion" => "Langer Text",
          "SingleChoiceQuestion" => "Single Choice",
          "MultipleChoiceQuestion" => "Multiple Choice",
          "MatrixQuestion" => "Matrix",
          "GradingQuestion" => "Benotung",
          "TendencyQuestion" => "Tendenz"
        );

        $layoutTypes = array(
          "Description" => "Beschreibung",
          "Headline" => "Überschrift",
          "PageBreak" => "Seitenumbruch",
          "JumpLabel" => "Sprungmarke"
        );

        $html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px; margin-top: 20px;\">";
        $html .= "<h3>Antwort-Typen</h3>";
        foreach ($questionTypes as $key => $value){
          $html .= "<div style=\"clear:both;\" class=\"questionnaireNewElement\">";
          $html .= "<a href=\"\" onclick=\"sendRequest('NewElementForm', {'id':{$this->id}, 'key':'{$key}', 'value':'{$value}'}, 'wizard', 'wizard', null, null, 'questionnaire');return false;\" title=\"{$value}\"><svg style='float:left; width:18px; height:18px;'><use xlink:href='" . PATH_URL . "questionnaire/asset/icons/" . $key . ".svg#" . $key . "'/></svg><p style=\"float:left; margin-top: 2px; margin-left: 5px; font-size:12px;\">" . $value . "</p></a>";
          $helpurl = " ";
			    if($helpurl != "") $html .= "<a href=\"\" onclick=\"window.open('" . $helpurl . "', '_blank');\" title=\"mehr Informationen\"><svg style='float:right; width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/help.svg#help' /></svg></a>";
          $html .= "</div>";
        }
        $html .= "<h3 style='margin-top:30px;'>Layout</h3>";
        foreach ($layoutTypes as $key => $value){
          $html .= "<div style=\"clear:both;\" class=\"questionnaireNewElement\">";
          $html .= "<a href=\"\" onclick=\"sendRequest('NewElementForm', {'id':{$this->id}, 'key':'{$key}', 'value':'{$value}'}, 'wizard', 'wizard', null, null, 'questionnaire');return false;\" title=\"{$value}\"><svg style='float:left; width:18px; height:18px;'><use xlink:href='" . PATH_URL . "questionnaire/asset/icons/" . $key . ".svg#" . $key . "'/></svg><p style=\"float:left; margin-top: 2px; margin-left: 5px; font-size:12px;\">" . $value . "</p></a>";
          $helpurl = " ";
          if($helpurl != "") $html .= "<a href=\"\" onclick=\"window.open('" . $helpurl . "', '_blank');\" title=\"mehr Informationen\"><svg style='float:right; width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/help.svg#help' /></svg></a>";
          $html .= "</div>";
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($html);
        $dialog->addWidget($rawHtml);

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        //this case is not used
    }

}

?>
