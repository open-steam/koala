<?php

namespace Explorer\Commands;

class Properties extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        $type = "";
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        $isWriteable = $object->check_access_write();


        $type = getObjectType($object);

        switch ($type) {
            case "document":
                $labelName = "Dateiname";
                $typeName = "Dokument";
                break;

            case "forum":
                $labelName = "Forumname";
                $typeName = "Forum";
                break;

            case "referenceFolder":
                $labelName = "Linkname";
                $typeName = "Referenz";
                break;

            case "referenceFile":
                $labelName = "Linkname";
                $typeName = "Referenz";
                break;

            case "user":
                $labelName = "Benutzername";
                $typeName = "Benutzer";
                break;

            case "group":
                $labelName = "Gruppenname";
                $typeName = "Gruppe";
                break;

            case "trashbin":
                $labelName = "Papierkorb";
                $typeName = "Papierkorb";
                break;

            case "gallery":
                $labelName = "Name";
                $typeName = "Fotoalbum";
                break;

            case "portal":
                $labelName = "Portalname";
                $typeName = "Portal";
                break;

            case "portalColumn":
                $labelName = "Spaltenname";
                $typeName = "Portal-Spalte";
                break;

            case "portalPortlet":
                $labelName = "Portletname";
                $typeName = "Portal-Portlet";
                break;

            case "userHome":
                $labelName = "Ordnername";
                $typeName = "Benutzerordner";
                break;

            case "groupWorkroom":
                $labelName = "Ordnername";
                $typeName = "Gruppen-Arbeitsraum";
                break;

            case "room":
                $labelName = "Ordnername";
                $typeName = "Ordner";
                break;

            case "container":
                $labelName = "Ordnername";
                $typeName = "Ordner";
                break;

            case "docextern":
                $labelName = "Internet-Link-Name";
                $typeName = "Internet-Referenz";
                break;

            case "unknown":
                $labelName = "Name";
                $typeName = "unbekannt";
                break;

            case "wiki":
                $labelName = "Name";
                $typeName = "Wiki";


            default:
                $labelName = "Name";
                $typeName = "unbekannt";
                break;
        }


        //pic tests
        $documentIsPicture = false;
        if ($type == "document") {
            $docType = $object->get_attribute("DOC_MIME_TYPE");
            $isJpg = strpos($docType, "jpg") !== false;
            $isJpeg = strpos($docType, "jpeg") !== false;
            $isGif = strpos($docType, "gif") !== false;
            $isPng = strpos($docType, "png") !== false;
            if ($isGif || $isJpeg || $isJpg || $isPng) {
                $documentIsPicture = true;
            }
        }


        //media tests
        $documentIsMedia = false;
        if ($type == "document") {
            $docType = $object->get_attribute("DOC_MIME_TYPE");
            if (strpos($docType, "video") !== false)
                $documentIsMedia = true;
            if (strpos($docType, "audio") !== false)
                $documentIsMedia = true;
        }

        $typeNameReadable = "";
        if ($typeName!="unbekannt") $typeNameReadable = "(".$typeName.")";

        $dialog = new \Widgets\Dialog();
        //$dialog->setTitle("Eigenschaften von »" . getCleanName($object) . "«<br>{$typeNameReadable}");
        $dialog->setTitle("Eigenschaften");
        $dialog->setWidth(400);

        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);
        //force the closeoperation of the dialog to reload the page to display the changed settings (tags enabled / disabled)
        //$dialog->setForceReload(true);

        if ($type == "userHome" || $type == "groupWorkroom") {
            $dataNameInput = new \Widgets\TextInput();
            $dataNameInput->setLabel("{$labelName}");
            $dataNameInput->setData($object);
            $dataNameInput->setReadOnly(true);
            $dataNameInput->setContentProvider(\Widgets\DataProvider::staticProvider(getCleanName($object, -1)));
        } else {
            $dataNameInput = new \Widgets\TextInput();
            $dataNameInput->setLabel("{$labelName}");
            $dataNameInput->setData($object);
            if (!$isWriteable) {
                $dataNameInput->setReadOnly(true);
            }
            $dataNameInput->setContentProvider(new \Widgets\NameAttributeDataProvider("OBJ_NAME", $object->get_name()));


            //create description text area
            $textAreaDescription = new \Widgets\Textarea();
            $textAreaDescription->setLabel("Beschreibung");
            $textAreaDescription->setData($object);
            if (!$isWriteable) {
                //Fehlt Methode
            }
            $textAreaDescription->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
            $textAreaDescription->setHeight(100);
            $desc = $object->get_attribute("OBJ_DESC");


            if ($desc !== 0) {
                $jsWrapperPicture = new \Widgets\JSWrapper();
                $desc = trim($desc);
            }


        }

        $ownerField = new \Widgets\TextField();
        $ownerField->setLabel("Besitzer");
        $creator = $object->get_creator();
        $creatorName = getCleanName($creator);
        $ownerField->setValue($creatorName);

        $embedField = new \Widgets\TextField();
        $embedField->setLabel("Einbettungs-Link");
        $embedLink2 = PATH_SERVER . "/download/document/" . $object->get_id();
        $embedField->setValue(trim($embedLink2));

        $changedField = new \Widgets\TextField();
        $changedField->setLabel("zuletzt geändert");
        $changedDate = $object->get_attribute(OBJ_LAST_CHANGED);
        $changedDate = getFormatedDate($changedDate);
        $changedField->setValue($changedDate . " Uhr");

        $createdField = new \Widgets\TextField();
        $createdField->setLabel("erstellt");
        $createDate = $object->get_attribute(OBJ_CREATION_TIME);
        $createDate = getFormatedDate($createDate);
        $createdField->setValue($createDate . " Uhr");

        $containerViewRadio = new \Widgets\RadioButton();
        $containerViewRadio->setLabel("Erstes Dokument");
        $containerViewRadio->setData($object);
        $containerViewRadio->setDefaultChecked("normal");
        $containerViewRadio->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:presentation"));
        if (!$isWriteable) {
            $containerViewRadio->setReadOnly(true);
        }

        //TODO: value is array
        $keywordArea = new \Widgets\TextInput();
        $keywordArea->setLabel("Tags");
        $keywordArea->setData($object);
        $keywordArea->setContentProvider(\Widgets\DataProvider::arrayToStringProvider("OBJ_KEYWORDS"));
        if (!$isWriteable) {
            $keywordArea->setReadOnly(true);
        }

        if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {


            $parent = $object->get_environment();
            if ($parent !== 0) {
                $inventory = $parent->get_inventory();
            } else {
                $inventory = array();
            }
            $keywordmatrix = array();
            foreach ($inventory as $inv) {


                if (!($inv->get_id() == $this->id)) {
                    $keywordmatrix[] = $inv->get_attribute("OBJ_KEYWORDS");
                }
            }
            $kwList = array();
            foreach ($keywordmatrix as $kwRow) {
                foreach ($kwRow as $element) {
                    if (trim($element) !== "") {
                        $kwList[] = trim($element);
                    }
                }
            }
            $kwList = array_unique($kwList);
            //sort the keywordarray in an ascending order
            sort($kwList);
            $taglist = array();
            foreach ($kwList as $kw) {
                $tagWidget = new \Widgets\Tag();
                $tagWidget->setKeyword($kw);
                $taglist[] = $tagWidget;
            }

            $tagrawHtml = new \Widgets\RawHtml();
            $html = '<script>function copyToTextInput(name){
            var valOld = $("input[type=text]")[1].value.trim();
            var tagfield = $("input[type=text]")[1];
            tagfield.value = valOld + " " + name;

            $(\'#'.$keywordArea->getId().'\').addClass(\'changed\');


            '.$keywordArea->getId().' = tagfield.value;
            //sendRequest("SendArrayToStringRequest", {"id": ' . $this->id . ', "attribute": "OBJ_KEYWORDS", "value": tagfield.value}, "", "data", function(response){widgets_textinput_save_success(tagfield.id, response);}, null, "Explorer");

            }</script><div class="tag-overview-row">';

            $breakCounter = 3;
            foreach ($taglist as $i => $tagWidget) {
                if ($i % $breakCounter !== 0) {
                    $html .= $tagWidget->getHtml();
                } else {
                    $html .= "</div>" . '<div class="tag-overview-row">' . $tagWidget->getHtml();
                }
            }
            $html .= "</div>";
            $tagrawHtml->setHtml($html);
            $tagrawHtml->setCss('.tag{overflow:hidden;float:left;cursor:pointer;width:55px;margin-right:8px;} .tag-overview-row{display:block;margin-left:137px;clear:both;width:200px;}');
        }

        //TODO: bid-attribute
        $descriptionInput = new \Widgets\TextInput();
        $descriptionInput->setLabel("Beschreibung");
        $descriptionInput->setData($object);
        $descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:description"));
        if (!$isWriteable) {
            $descriptionInput->setReadOnly(true);
        }

        $checkboxInput = new \Widgets\Checkbox();
        $checkboxInput->setLabel("Benutzer dürfen editieren:");
        $checkboxInput->setCheckedValue("1");
        $checkboxInput->setUncheckedValue(0);
        $checkboxInput->setData($object);
        $checkboxInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:forum_is_editable"));
        if (!$isWriteable) {
            $checkboxInput->setReadOnly(true);
        }

        $checkboxWWW = new \Widgets\Checkbox();
        $checkboxWWW->setLabel("Neues Fenster öffnen:");
        $checkboxWWW->setCheckedValue("1");
        $checkboxWWW->setUncheckedValue(0);
        $checkboxWWW->setData($object);
        $checkboxWWW->setContentProvider(\Widgets\DataProvider::attributeProvider("DOC_BLANK"));
        if (!$isWriteable) {
            $checkboxWWW->setReadOnly(true);
        }


        $checkboxHiddenObject = new \Widgets\Checkbox();
        $checkboxHiddenObject->setLabel("Verstecktes Objekt:");
        $checkboxHiddenObject->setCheckedValue("1");
        $checkboxHiddenObject->setUncheckedValue(0);
        $checkboxHiddenObject->setData($object);
        $checkboxHiddenObject->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:hidden"));

        if (!$isWriteable) {
            $checkboxHiddenObject->setReadOnly(true);
        }

        //checkbox for the option to enable or disable the tag-column
        $checkboxShowTags = new \Widgets\Checkbox();

        $checkboxShowTags->setLabel("Tags anzeigen:");
        $checkboxShowTags->setCheckedValue("1");
        $checkboxShowTags->setUncheckedValue(0);
        $checkboxShowTags->setData($object);
        $checkboxShowTags->setContentProvider(\Widgets\DataProvider::attributeProvider("SHOW_TAGS"));


        $seperator = new \Widgets\RawHtml();
        $seperator->setHtml("<br style=\"clear:both\"/>");
        $headlineAlg = new \Widgets\RawHtml();
        $headlineAlg->setHtml("<h3>Allgemein</h3>");
        $headlineMeta = new \Widgets\RawHtml();
        $headlineMeta->setHtml("<h3>Meta-Informationen</h3>");
        $headlineView = new \Widgets\RawHtml();
        $headlineView->setHtml("<h3>Darstellung</h3>");

        $dialog->addWidget($headlineAlg);

        if (($type == "document") && $documentIsPicture) {
            $fileName = new \Widgets\TextInput();
            $fileName->setLabel("Dateiname");
            if (!$isWriteable) {
                $fileName->setReadOnly(true);
            }
            $fileName->setData($object);
            $fileName->setContentProvider(new \Widgets\NameAttributeDataProvider("OBJ_NAME", $object->get_name() ));
            $dialog->addWidget($fileName);
        }

        if (($type == "document") && !$documentIsPicture) {
            $dialog->addWidget($dataNameInput);
        }

        if (($type !== "document")) {
            $dialog->addWidget($dataNameInput);
        }

        //embed link
        if ($documentIsPicture || $documentIsMedia) {
            $dialog->addWidget($seperator);
            $dialog->addWidget($embedField);
        }

        $dialog->addWidget($seperator);
        $dialog->addWidget($ownerField);
        $dialog->addWidget($seperator);
        $dialog->addWidget($changedField);
        $dialog->addWidget($seperator);
        $dialog->addWidget($createdField);
        $dialog->addWidget($seperator);
        $dialog->addWidget($checkboxHiddenObject);




        if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {
            //check if the attribute exists, if not: set it to false
            if($object->get_attribute("SHOW_TAGS") == 0){
                $object->set_attribute("SHOW_TAGS", 'false');
            }

            //only show the option to disable the tagcolumn if tags are enabled on the system
            $dialog->addWidget($checkboxShowTags);

            $dialog->addWidget($keywordArea);
            $dialog->addWidget($tagrawHtml);
        }

        if ($type != "portal" && $type != "docextern") {
            $dialog->addWidget($seperator);
        }


        if ($type == "container" || $type == "room") {
            $dialog->addWidget($textAreaDescription);
            $dialog->addWidget($seperator);

            //case head document possible
            $inventory = array();
            $inventory = $object->get_inventory();
            if (isset($inventory[0]) && $inventory[0] instanceof \steam_object) {
                $mime = $inventory[0]->get_attribute("DOC_MIME_TYPE");

                if (strpos($mime, "html") !== false) {
                    $dialog->addWidget($headlineView);
                    $containerViewRadio->setOptions(array(array("name" => "Normal (Ordneransicht)", "value" => "normal"), array("name" => "Deckblatt (statt der Ordneransicht)", "value" => "index"), array("name" => "Kopfdokument (über der Ordneransicht)", "value" => "head")));
                    $dialog->addWidget($containerViewRadio);
                } else if (getObjectType($inventory[0]) === "portal") {
                    $dialog->addWidget($headlineView);
                    $containerViewRadio->setOptions(array(array("name" => "Normal (Ordneransicht)", "value" => "normal"), array("name" => "Deckblatt (statt der Ordneransicht)", "value" => "index")));
                    $dialog->addWidget($containerViewRadio);
                }
            }

            $dialog->addWidget($seperator);
        } else if ($type == "document") {
            if (true) { //former documentIsPicture
                $dialog->addWidget($textAreaDescription);
            }
        } else if ($type == "forum") {
            $creatorId = $creator->get_id();
            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
            $currentUserId = $currentUser->get_id();
            if ($currentUserId == $creatorId) {
                $dialog->addWidget($checkboxInput);
                $dialog->addWidget($seperator);
            }
            $dialog->addWidget($textAreaDescription);
            $dialog->addWidget($seperator);
        }


        //www-link
        if ($type == "docextern") {
            $urlInput = new \Widgets\TextInput();
            $urlInput->setLabel("URL");
            $urlInput->setData($object);
            $urlInput->setContentProvider(\Widgets\DataProvider::attributeProvider("DOC_EXTERN_URL"));
            if(!$isWriteable){
                $urlInput->setReadOnly(true);
            }
            $dialog->addWidget($seperator);
            $dialog->addWidget($urlInput);
            $dialog->addWidget($seperator);
            $dialog->addWidget($textAreaDescription);
            $dialog->setSaveAndCloseButtonForceReload(true);
        }


        if ($type == "portal") {
            $statusbarCheckbox = new \Widgets\Checkbox();
            $statusbarCheckbox->setLabel("Statusleiste deaktiviert:");
            $statusbarCheckbox->setCheckedValue("1");
            $statusbarCheckbox->setUncheckedValue(0);
            $statusbarCheckbox->setData($object);
            $statusbarCheckbox->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portal_status_deactivate"));
            if(!$isWriteable){
                $statusbarCheckbox->setReadOnly(true);
            }
            $dialog->addWidget($seperator);
            $dialog->addWidget($statusbarCheckbox);
            $dialog->addWidget($seperator);
            $dialog->addWidget($textAreaDescription);
        }

        //wiki
        if (($type == "wiki") && ($typeName != "unbekannt")){
            $dialog->addWidget($seperator);
            $dialog->addWidget($textAreaDescription);
        }

        //gallery
        if ($type == "gallery"){
            $dialog->addWidget($seperator);
            $dialog->addWidget($textAreaDescription);
        }

        //all other objects
        if ($typeName == "unbekannt"){
            $dialog->addWidget($seperator);
            $dialog->addWidget($textAreaDescription);
        }

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $object = $currentUser->get_workroom();

        $dialog = new \Widgets\Dialog();
        //$dialog->setTitle("Eigenschaften von " . $object->get_name());
        $dialog->setTitle("Eigenschaften");

        $dialog->setButtons(array(array("name" => "speichern", "href" => "save")));
        return $dialog->getHtml();
    }

}
?>
