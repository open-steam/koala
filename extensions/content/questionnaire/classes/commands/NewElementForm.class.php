<?php

namespace Questionnaire\Commands;

class NewElementForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;
    private $key;
    private $value;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        isset($this->params["key"]) ? $this->key = $this->params["key"] : "";
        isset($this->params["value"]) ? $this->value = $this->params["value"] : "";
    }

  	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
      $ajaxResponseObject->setStatus("ok");

      $ajaxForm = new \Widgets\AjaxForm();
      $ajaxForm->setSubmitCommand("Create" . $this->key);
      $ajaxForm->setSubmitNamespace("Questionnaire");

      $html = '<style type="text/css">
              .attribute {
              clear: left;
              padding: 5px 2px 5px 2px;
              }

              .attributeSameRowFront {
              padding: 5px 2px 5px 2px;
              width: 255px;
              float: left;
              }

              .attributeSameRow {
              padding: 5px 2px 5px 2px;
              width: 191px;
              float: left;
              }

              .attributeName {
              float: left;
              width: 180px;
              }

              .attributeNameSameRow {
              float: left;
              width: 70px;
              }

              .attributeNameSameRowFront {
              float: left;
              width: 60px;
              }

              .attributeNameRequired {
              float: left;
              padding-right: 20px;
              text-align: right;
              font-weight: bold;
              width: 80px;
              }

              .attributeValue {
              float: left;
              width: 300px;
              }

              .attributeValue .text, .attributeValue textarea {
              width: 150px;
              }

              .text{
              width:267px;
              }

              .attributeValueColumn {
              float: left;
              position: relative;
              text-align: center;
              }
              </style>
              <input type="hidden" name="id" value="' . $this->id . '">

              <h3>' . $this->value . '</h3>';

      if($this->key != "Description" && $this->key != "PageBreak" && $this->key != "Headline" && $this->key != "JumpLabel"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Frage:</div>
                  <div><input type="text" class="text" value="" name="question"></div>
                  </div>

                  <div class="attribute">
                  <div class="attributeName">Hilfetext:</div>
                  <div><input type="text" class="text" value="" name="help"></div>
                  </div>

                  <div class="attribute">
                  <div class="attributeName">Pflichtfrage:</div>
                  <div><input type="checkbox" value="" name="obligatory"></div>
                  </div>';
      }

      if($this->key == "ShortTextQuestion"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Maximale Zeichenanzahl:</div>
                  <div><input type="number" class="text" value="" name="length"></div>
                  </div>';
      }
      if($this->key == "LongTextQuestion"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Zeilen:</div>
                  <div><input type="number" class="text" value="" name="rows"></div>
                  </div>';
      }
      if($this->key == "SingleChoiceQuestion" || $this->key == "MultipleChoiceQuestion"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Ausrichtung:</div>
                  <div>
                  <select size="1">
                  <option selected="" name="horizontal" value="horizontal">horizontal</option>
                  <option name="vertical" value="vertical">vertikal</option>
                  </select>
                  </div>
                  </div>';
        for($i = 1; $i < 11; $i++) {
          $html .= '<div class="attribute">
                    <div class="attributeName">Option ' . $i . ':</div>
                    <div><input type="text" class="text" value="" name="option' . $i . '"></div>
                    </div>';
        }
      }
      if($this->key == "MatrixQuestion"){
        for($i = 1; $i < 11; $i++) {
          $html .= '<div class="attributeSameRowFront">
                    <div class="attributeNameSameRowFront">Zeile ' . $i . ':</div>
                    <div><input style="width:117px;" type="text" value="" name="row' . $i . '"></div>
                    </div>
                    <div class="attributeSameRow">
                    <div class="attributeNameSameRow">Spalte ' . $i . ':</div>
                    <div><input style="width:117px;" type="text" value="" name="column' . $i . '"></div>
                    </div>';
        }
      }
      if($this->key == "GradingQuestion"){
        for($i = 1; $i < 11; $i++) {
          $html .= '<div class="attribute">
                    <div class="attributeName">Element ' . $i . ':</div>
                    <div><input type="text" class="text" value="" name="element' . $i . '"></div>
                    </div>';
        }
      }
      if($this->key == "TendencyQuestion"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Skala:</div>
                  <div>
                  <select size="1">
                  <option selected="" name="2" value="2">2</option>
                  <option name="3" value="3">3</option>
                  <option name="4" value="4">4</option>
                  <option name="5" value="5">5</option>
                  <option name="6" value="6">6</option>
                  <option name="7" value="7">7</option>
                  <option name="8" value="8">8</option>
                  </select> Schritte
                  </div>
                  </div>';
        for($i = 1; $i < 11; $i++) {
          $html .= '<div class="attributeSameRowFront">
                    <div class="attributeNameSameRowFront">Von:</div>
                    <div><input style="width:117px;" type="text" value="" name="from' . $i . '"></div>
                    </div>
                    <div class="attributeSameRow">
                    <div class="attributeNameSameRow">bis:</div>
                    <div><input style="width:117px;" type="text" value="" name="to' . $i . '"></div>
                    </div>';
        }
      }
      if($this->key == "Description"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Beschreibung:</div>
                  <div><textarea class="text" rows="3"></textarea></div>
                  </div>';
      }
      if($this->key == "Headline"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Überschrift:</div>
                  <div><input type="text" class="text" value=""></div>
                  </div>';
      }
      if($this->key == "JumpLabel"){
        $html .= '<div class="attribute">
                  <div class="attributeName">Text:</div>
                  <div><textarea class="text" rows="3"></textarea></div>
                  </div>
                  <div class="attribute">
                  <div class="attributeName">Sprungziel (Fragenummer):</div>
                  <div><input type="number" class="text" value="" name="questionnumber"></div>
                  </div>';
      }

      $html .= '<div class="attribute">
                <div><input type="hidden" name="parent" value="{$this->id}"></div>
                </div>';

      $ajaxForm->setHtml($html);
      $ajaxResponseObject->addWidget($ajaxForm);
      return $ajaxResponseObject;
  	}

  	public function frameResponse(\FrameResponseObject $frameResponseObject) {

  	}
}

?>
