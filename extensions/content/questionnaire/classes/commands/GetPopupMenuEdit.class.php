<?php

namespace Questionnaire\Commands;

class GetPopupMenuEdit extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;
    private $logged_in;
    private $questionId;
    private $layoutElement;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
        $this->questionId = $this->params["questionId"];
        $this->layoutElement = $this->params["layoutElement"];
        $portal = \lms_portal::get_instance();
        $lms_user = $portal->get_user();
        $this->logged_in = $lms_user->is_logged_in();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
        $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
        $copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";
        $sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
        $topIcon = $explorerUrl . "icons/menu/svg/top.svg";
        $upIcon = $explorerUrl . "icons/menu/svg/up.svg";
        $downIcon = $explorerUrl . "icons/menu/svg/down.svg";
        $bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";

        $editMethod = "editQuestion";
        if($this->layoutElement) $editMethod = "editLayoutElement";

        $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $surveys = $questionnaire->get_inventory();
        $survey = $surveys[0];
        $survey_object = new \Questionnaire\Model\Survey($questionnaire);
        $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
        $survey_object->parseXML($xml);
        $questions = $survey_object->getQuestions();

				$items = array(
          array("raw" => "<a href=\"#\" onclick=\"{$editMethod}({$this->questionId});return false;\"><svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten</a>"),
					array("raw" => "<a href=\"#\" onclick=\"copyElement({$this->id}, {$this->questionId});return false;\"><svg><use xlink:href='{$copyIcon}#copy'/></svg> Duplizieren</a>"),
          (count($questions) >= 2) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
              array("name" => "<a class='menuItemUp' href=\"#\" onclick=\"moveElement({$this->questionId}, 'top');jQuery('.popupmenuwrapper').remove();jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben</a>"),
              array("name" => "<a class='menuItemUp' href=\"#\" onclick=\"moveElement({$this->questionId}, 'up');jQuery('.popupmenuwrapper').remove();jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben</a>"),
              array("name" => "<a class='menuItemDown' href=\"#\" onclick=\"moveElement({$this->questionId}, 'down');jQuery('.popupmenuwrapper').remove();jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten</a>"),
              array("name" => "<a class='menuItemDown' href=\"#\" onclick=\"moveElement({$this->questionId}, 'bottom');jQuery('.popupmenuwrapper').remove();jQuery('.open').removeClass('open');jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten</a>")
          )) : "",
          array("raw" => "<a href=\"#\" onclick=\"deleteElement({$this->questionId});return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen</a>")
				);

        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        //$popupMenu->setPosition(round($this->x + $this->width-85)  . "px", round($this->y + $this->height+5) . "px");
        $popupMenu->setPostJsCode('
          var first = $("#sortable_rf").children().not("input").not("[id=\"newlayout\"]").not("[id=\"newquestion\"]").first();
          var last = $("#sortable_rf").children().not("input").not("[id=\"newlayout\"]").not("[id=\"newquestion\"]").last();
          if(("rfelement"+' . $this->questionId . ') == $(first).attr("id")){
            $(".menuItemUp").parent().hide();
          }
          if(("rfelement"+' . $this->questionId . ') == $(last).attr("id")){
            $(".menuItemDown").parent().hide();
          }');
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>
