<?php

namespace Explorer\Commands;

class EditDocument extends \AbstractCommand implements \IFrameCommand {

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
        if (isset($this->id)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_document) {
                $mimetype = $object->get_attribute(DOC_MIME_TYPE);
                $objName = $object->get_name();
                $objDesc = trim($object->get_attribute(OBJ_DESC));
                if (($objDesc === 0) || ($objDesc === "")) {
                    $name = $objName;
                } else {
                    $name = $objName . " (" . $objDesc . ")";
                }

                $actionBar = new \Widgets\ActionBar();

                //document type: html text
                if ($mimetype == "text/html") {
                    $actionBar->setActions(array(
                            //array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
                            //array("name"=>"Quelltext", "link"=> PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
                            //array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
                            //array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
                    ));
                }
                //document type: simple text
                else {
                    $actionBar->setActions(array(
                            //array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
                            //array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
                            //array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
                    ));
                }

                $contentText = new \Widgets\Textarea();
                $contentText->setWidth(945);
                $contentText->setheight(400);
                $contentText->setData($object);
                $contentText->setTextareaClass("mce-full");

                //convert
                if (strstr($mimetype, "text/plain")) {
                    $bidDokument = new \BidDocument($object);
                    $html = $bidDokument->get_content();
                } else {
                    $html = cleanHTML($object->get_content());

                    //make html modifications
                    $htmlDocument = new \HtmlDocument();
                    $html = $htmlDocument->makeEditorModifications($html, $object);
                }

                $contentText->setContentProvider(new \Widgets\TextContentDataProvider($html));
                $clearer = new \Widgets\Clearer();

                $saveButton = new \Widgets\SaveButton();
                $saveButton->setLabel("Speichern & Anzeigen");
                $saveButton->setBeforeSaveJS("createOverlay('white', null, 'show'); $('#saveWrapper').show(); $('#".$saveButton->getId()."').addClass('disabled'); $('#cancel_button_texteditor').addClass('disabled'); if(j == 0){window.location.href = '" . PATH_URL . "explorer/ViewDocument/" . $this->id . "/';}");
                $saveButton->setSaveReload("window.location.href = '" . PATH_URL . "explorer/ViewDocument/" . $this->id . "/';");

                $loader = new \Widgets\Loader();
                $loader->setWrapperId("saveWrapper");
                $loader->setMessage("Speichere Dokument...");
                $jswrapper = new \Widgets\JSWrapper();
                $jswrapper->setJs('jQuery(document).ready(function(){$("#saveWrapper").hide();});');


                $cancelButton = new \Widgets\RawHtml();
                $cancelButton->setHtml('<div style="float:right; margin-top:5px;"><a id="cancel_button_texteditor" class="bidButton negative" href="' . PATH_URL . 'explorer/ViewDocument/' . $this->id . '/">Abbrechen</a>&nbsp;</div>');

                $frameResponseObject->addWidget($loader);
                $frameResponseObject->addWidget($jswrapper);
                $frameResponseObject->setTitle($name);
                $frameResponseObject->addWidget($actionBar);
                $frameResponseObject->addWidget($contentText);
                $frameResponseObject->addWidget($saveButton);
                $frameResponseObject->addWidget($cancelButton);
                $frameResponseObject->addWidget($clearer);
                return $frameResponseObject;
            }
        } else {
            ExtensionMaster::getInstance()->send404Error();
        }
    }

}

?>
