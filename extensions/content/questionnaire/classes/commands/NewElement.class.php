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
          array("ShortTextQuestion", "Kurzer Text", "https://bid.lspb.de/explorer/ViewDocument/1223346/"),
          array("LongTextQuestion", "Langer Text", "https://bid.lspb.de/explorer/ViewDocument/1223347/"),
          array("SingleChoiceQuestion", "Single Choice", "https://bid.lspb.de/explorer/ViewDocument/1223343/"),
          array("MultipleChoiceQuestion", "Multiple Choice", "https://bid.lspb.de/explorer/ViewDocument/1223345/"),
          array("MatrixQuestion", "Matrix", "https://bid.lspb.de/explorer/ViewDocument/1223348/"),
          array("GradingQuestion", "Benotung", "https://bid.lspb.de/explorer/ViewDocument/1223349/"),
          array("TendencyQuestion", "Tendenz", "https://bid.lspb.de/explorer/ViewDocument/1223350/")
        );

        $layoutTypes = array(
          array("Description", "Beschreibung", "https://bid.lspb.de/explorer/ViewDocument/1223351/"),
          array("Headline", "Überschrift", "https://bid.lspb.de/explorer/ViewDocument/1223352/"),
          array("PageBreak", "Seitenumbruch", "https://bid.lspb.de/explorer/ViewDocument/1223359/"),
          array("JumpLabel", "Sprungmarke", "https://bid.lspb.de/explorer/ViewDocument/1223360/")
        );

        $html = "<h3>Fragen</h3>";
        foreach ($questionTypes as $key => $value){
          $html .= "<div style=\"clear:both;\" class=\"questionnaireNewElement\">";
          $html .= "<a href=\"\" onclick=\"showCreateDialog(" . $key . ");closeDialog();window.scrollTo(0,document.body.scrollHeight);return false;\" title=\"{$value[1]}\"><svg style='float:left; width:18px; height:18px;'><use xlink:href='" . PATH_URL . "questionnaire/asset/icons/" . $value[0] . ".svg#" . $value[0] . "'/></svg><p style=\"float:left; margin-top: 2px; margin-left: 5px; font-size:12px;\">" . $value[1] . "</p></a>";
			    $html .= "<a href=\"\" onclick=\"window.open('" . $value[2] . "', '_blank');\" title=\"mehr Informationen\"><svg style='float:right; width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/help.svg#help' /></svg></a>";
          $html .= "</div>";
        }
        $html .= "<h3 style='margin-top:30px;'>Layout-Elemente</h3>";
        foreach ($layoutTypes as $key => $value){
          $html .= "<div style=\"clear:both;\" class=\"questionnaireNewElement\">";
          $html .= "<a href=\"\" onclick=\"showLayoutDialog(" . ($key+7) . ");closeDialog();window.scrollTo(0,document.body.scrollHeight);return false;\" title=\"{$value[1]}\"><svg style='float:left; width:18px; height:18px;'><use xlink:href='" . PATH_URL . "questionnaire/asset/icons/" . $value[0] . ".svg#" . $value[0] . "'/></svg><p style=\"float:left; margin-top: 2px; margin-left: 5px; font-size:12px;\">" . $value[1] . "</p></a>";
          $html .= "<a href=\"\" onclick=\"window.open('" . $value[2] . "', '_blank');\" title=\"mehr Informationen\"><svg style='float:right; width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/help.svg#help' /></svg></a>";
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
