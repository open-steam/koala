<?php

namespace Portfolio\Commands;

class CommentDialog extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $entry;

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
        if (!isset($this->id) || $this->id === "") {
            throw new \Exception("no valid id");
        } else {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_room) {
                $this->entry = \Portfolio\Model\Entry::getEntryByRoom($object);
            } else if ($object instanceof \steam_document) {
                $this->entry = $object;
            }
        }
        $this->id = $this->entry->get_id();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $portal = \lms_portal::get_instance();
        $currentUserName = $currentUser->get_name();
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Eintrag kommentieren");
        $dialog->setDescription("Hier können Sie der ausgewählten Eintrag kommentieren.");
        $dialog->setWidth("600");

        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        // get discussion thread between portfolio owner and current user or create empty thread
        $threads = $this->entry->get_annotations();
        if (is_array($threads) && isset($threads[0])) {
            $discussion = $threads[0];
        }
        //foreach ($threads as $thread) {
        //	if ($thread->get_name() === $currentUserName) {
        //		$discussion = $thread;
        //	}
        //} 
        if (!isset($discussion)) {
            $discussion = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Comments", "Portfolio Comments.", "text/plain");
            $discussion->set_sanction_all(\steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "steam"));
            $this->entry->add_annotation($discussion);
        }

        $chat = new \Widgets\Chat();
        $chat->setMaxHeight("350");
        $chat->setData($discussion);
        $dialog->addWidget($chat);

        $dialog->addWidget(new \Widgets\Clearer());

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("<hr>");
        $dialog->addWidget($rawHtml);

        if ($this->entry instanceof \steam_document) {
            $comboBox = new \Widgets\ComboBox();
            $comboBox->setLabel("Kompetenzbezogene Strukturhilfe");
            $comboBox->setLabelWidth("200");
            $comboBox->setOnChange("jQuery('#dialog').find('input').attr('pre', '<b>'+value+': </b>');");
            $comboBox->setOptions(array(
                array("name" => "", "value" => ""),
                array("name" => "Kompetenzerweiterung (z.B.  weitere Arbeitsmethoden)", "value" => "Kompetenzerweiterung"),
                array("name" => "Kompetenzanpassung (z.B. betriebliche Spezialisierungen)", "value" => "Kompetenzanpassung"),
                array("name" => "Kompetenzvervollständigung (z.B. bei fehlenden Teilaspekten)", "value" => "Kompetenzvervollständigung"),
            ));
            $dialog->addWidget($comboBox);

            $dialog->addWidget(new \Widgets\Clearer());

            $comboBox = new \Widgets\ComboBox();
            $comboBox->setLabel("Bildungsempfehlung");
            $comboBox->setLabelWidth("200");
            $comboBox->setOnChange("jQuery('#dialog').find('input').attr('post', ' (<em>Bildungsempfehlung: '+value+')</em>');");
            $comboBox->setOptions(array(
                array("name" => "", "value" => ""),
                array("name" => "DAWINCI Modul", "value" => "DAWINCI Modul"),
                //array("name" => "Elchmodul", "value" => ""),
                //array("name" => "...", "value" => ""),
            ));
            $dialog->addWidget($comboBox);
            $dialog->addWidget(new \Widgets\Clearer());
        }

        $textinput = new \Widgets\TextInput();
        $textinput->setData($discussion);
        $textinput->setContentProvider(\Widgets\DataProvider::annotationDataProvider());
        $textinput->setLabel("Kommentar schreiben");
        $textinput->setInputWidth("400");
        $textinput->setCustomSaveCode("jQuery('.ichat').append('<div class=\'outgoing_row\'><img src=\'".\lms_user::get_user_image_url(32, 32)."\' title=\'".$portal->get_user()->get_forename() . " " . $portal->get_user()->get_surname()."\'><div class=\'message\'>'+value+'</div></div>'); jQuery('.ichat').animate({ scrollTop: jQuery('.ichat').prop('scrollHeight') }, 3000);");
        $dialog->addWidget($textinput);

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

}

?>