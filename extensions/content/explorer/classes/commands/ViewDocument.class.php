<?php

namespace Explorer\Commands;

class ViewDocument extends \AbstractCommand implements \IFrameCommand {

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

            if (!($object instanceof \steam_object)) {
                \ExtensionMaster::getInstance()->send404Error();
            }
            $noSanctionDialog = false;
            if (intval($object->check_access(SANCTION_SANCTION)) === 0) {
                $noSanctionDialog = true;
            }
            if (intval($object->check_access(SANCTION_WRITE)) === 0) {
                $noActionbar = true;
            }

            if (!$object->check_access_read()) {
                $labelDenied = new \Widgets\RawHtml();
                $labelDenied->setHtml("Das Dokument kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
                $frameResponseObject->addWidget($labelDenied);
                return $frameResponseObject;
                //throw new \Exception("no access", E_USER_ACCESS_DENIED);
            }

            //chronic
            \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($object);

            $objName = $object->get_name();

            //document type: link
            if ($object instanceof \steam_docextern) {

                header('Location: ' . $object->get_attribute("DOC_EXTERN_URL"));
                die;

                /*
                  if (isset($this->params[1]) && $this->params[1] === "new") {
                  header('Location: ' . $object->get_attribute("DOC_EXTERN_URL") . '');
                  die;
                  }

                  $actionBar = new \Widgets\ActionBar();
                  $actionBar->setActions(array(
                  array("name" => "URL in neuem Fenster öffnen", "onclick" => "javascript:window.open('{$object->get_attribute("DOC_EXTERN_URL")}');return false;")
                  ));
                  $rawHtml = new \Widgets\RawHtml();
                  $rawHtml->setHtml("<iframe height=\"800px\" width=\"100%\" src=\"{$object->get_attribute("DOC_EXTERN_URL")}\" scrolling=\"yes\"></iframe>");
                  $frameResponseObject->setTitle($objName);
                  $frameResponseObject->addWidget($actionBar);
                  $frameResponseObject->addWidget($rawHtml);
                  return $frameResponseObject;
                 */
            }

            //document type: steam document
            else if ($object instanceof \steam_document) {

                //document type: map
                if ((strpos($objName, ".kml") !== false) || (strpos($objName, ".kmz") !== false)) {
                    header("location: " . PATH_URL . "map/Index/" . $this->id . "/");
                    die;
                }

                $mimetype = $object->get_attribute(DOC_MIME_TYPE);
                $objDesc = trim($object->get_attribute(OBJ_DESC));
                $actionBar = new \Widgets\ActionBar();
                if ($noSanctionDialog) {
                    $actionBar->setActions(array(
                            //array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $objName),
                            //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))
                    ));
                } else {
                    $actionBar->setActions(array(
                            //array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $objName),
                            //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                            //array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                    ));
                }

                if (($objDesc === 0) || ($objDesc === "")) {
                    $name = $objName;
                } else {
                    $name = $objName . " (" . $objDesc . ")";
                }

                //document type: image
                $html = "";
                if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/jpg" || $mimetype == "image/gif" || $mimetype == "image/svg+xml" || $mimetype == "image/bmp") {  // Image
                    $dummyContent = $object->get_content(); //to check sanction
                    $html = "<h2><svg style='width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/image.svg#image'></use></svg> " . $name . "</h2><div style=\"text-align:center\"><img style=\"max-width:100%\" title=\"{$name}\" alt=\"Bild: {$name}\" src=\"" . PATH_URL . "Download/Document/" . $this->id . "/\"></div>";
                }

                //document type: pdf
                else if ($mimetype === "application/pdf") {
                    $PDFUrlDownload = PATH_URL . 'Download/Document/' . $this->id . '/' . $objName;
                    $PDFUrlEmbed = PATH_URL . 'Download/Document/' . $this->id . '/';
                    $html = '<h2><svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/application_pdf.svg#application_pdf"></use></svg> ' . $name . '</h2><object data=' . $PDFUrlEmbed . ' type="application/pdf" width="100%"><p>Ihr Browser verfügt nicht über die Fähigkeit, PDF-Dateien direkt anzeigen zu können. Sie können die Datei allerdings <a href="' . $PDFUrlDownload . '">herunterladen</a>, um sie mit einer entsprechenden Software zu betrachten.</p></object>';
                    $html = $html . '<script type="text/javascript">$("object").height($(window).height()-200);</script>';
                }

                //document type: html-text
                else if ($mimetype == "text/html") {
                    if ($noSanctionDialog) {
                        $actionBar->setActions(array(
                                //array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                                //array("name" => "Quelltext", "link" => PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
                                //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))
                        ));
                    } else {
                        $actionBar->setActions(array(
                                //array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                                //array("name" => "Quelltext", "link" => PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
                                //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                                //array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                        ));
                    }

                    $htmlDocument = new \HtmlDocument($object);
                    $html = '<h2><svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/text_html.svg#text_html"></use></svg> ' . $name . '</h2>';
                    $html .= $htmlDocument->getHtmlContent(); //this return cleand html, do not clean again
                    if ($html == "") {
                        $html = "Es ist noch kein Text vorhanden. <a href=" . PATH_URL . "Explorer/EditDocument/" . $this->id . "/" . ">Jetzt einen Text erstellen</a>";
                    }
                }

                //document type: simple text
                else if (strstr($mimetype, "text")) {
                    $bidDokument = new \BidDocument($object);
                    if ($noSanctionDialog) {
                        $actionBar->setActions(array(
                                //array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                                //array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/"),
                                //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))
                        ));
                    } else {
                        $actionBar->setActions(array(
                                //array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                                //array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/"),
                                //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                                //array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                        ));
                    }

                    $html = $bidDokument->get_content();

                    //make html modifications
                    $htmlDocument = new \HtmlDocument();
                    $html = $htmlDocument->makeViewModifications($html);
                    $html = cleanHTML($html);
                    $html = '<h2><svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/text.svg#text"></use></svg> ' . $name . '</h2>' . $html;
                }

                //document type: audio
                else if ((strpos($mimetype, "audio") !== false)) {
                    $mediaplayerHtml = new \Widgets\RawHtml();
                    $mediaPlayerUrl = getDownloadUrlForObjectId($this->id);
                    if ((strpos($mimetype, "mpeg") !== false)) { //mp3 format, use html 5 audio tag
                        $mediaplayerHtml->setHtml('<h2><svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/audio.svg#audio"></use></svg> ' . $name . '</h2><div class="CSSLoader"></div><audio controls style="display:none;" oncanplay="$(this).prev().remove();$(this).show();"><source src="' . $mediaPlayerUrl . '" type="audio/mpeg">Ihr Browser unterstützt das Audio-Element nicht.</audio>');
                    } else {
                        $mediaplayerPath = \PortletMedia::getInstance()->getAssetUrl() . 'emff_lila_info.swf';
                        $mediaplayerWidth = "200";
                        $mediaplayerHeight = round(200 * 11 / 40) . "";
                        $mediaplayerHtml->setHtml('<h2><svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/audio.svg#audio"></use></svg> ' . $name . '</h2><object style="width:' . $mediaplayerWidth . 'px; height:' . $mediaplayerHeight . 'px" type="application/x-shockwave-flash" data="' . $mediaplayerPath . '"><param name="movie" value="' . $mediaplayerPath . '" /><param name="FlashVars" value="src=' . $mediaPlayerUrl . '" /><param name="bgcolor" value="#cccccc"></object>');
                    }
                }

                //document type: video
                else if ((strpos($mimetype, "video/x-flv") !== false) || (strpos($mimetype, "video/x-m4v") !== false) || (strpos($mimetype, "video/mpeg") !== false) || (strpos($mimetype, "video/mp4") !== false) || (strpos($mimetype, "video/3gpp") !== false) || (strpos($mimetype, "video/quicktime") !== false)) {
                    $html = "<h2><svg style='width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;'><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/video.svg#video'></use></svg> " . $name . "</h2>";
                    $mediaPlayerUrl = getDownloadUrlForObjectId($this->id);
                    if ((strpos($mimetype, "mp4") !== false)) { //mp4 format, use html 5 video tag
                        $mediaplayerHtml = new \Widgets\RawHtml();
                        $mediaplayerHtml->setHtml('<div class="CSSLoader"></div><video controls width="950" style="display:none;" oncanplay="$(this).prev().remove();$(this).show();"><source src="' . $mediaPlayerUrl . '" type="video/mp4">Ihr Browser unterstützt das Video-Element nicht.</video>');
                    } else {
                        $mediaplayerHtml = new \Widgets\Videoplayer();
                        $mediaplayerHtml->setTarget($mediaPlayerUrl);
                    }
                }

                //document type: download
                else {
                    //header("location: " . PATH_URL . "Download/Document/" . $this->id . "/");
                    $icon = deriveIcon($object);
                    $iconSVG = str_replace("png", "svg", $icon);
                    $idSVG = str_replace(".svg", "", $iconSVG);
                    $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
                    $html = "<h2><svg style='width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg> " . $name . "</h2>Derzeit ist es noch nicht möglich, diesen Dateityp direkt anzuzeigen. Sie können die Datei <a href=" . PATH_URL . "Download/Document/" . $this->id . "/" . $objName . ">herunterladen</a> um sie zu betrachten.";
                }

                //default
                $rawHtml = new \Widgets\RawHtml();
                $rawHtml->setHtml($html);

                $frameResponseObject->setTitle($name);
                if (!isset($noActionbar)) {
                    $frameResponseObject->addWidget($actionBar);
                }
                $frameResponseObject->addWidget($rawHtml);
                if (isset($mediaplayerHtml)) {
                    $frameResponseObject->addWidget($mediaplayerHtml);
                }
                $cssStyle = new \Widgets\RawHtml();
                $cssStyle->setCss('#content {overflow-x:auto;}');
                $frameResponseObject->addWidget($cssStyle);
                return $frameResponseObject;
            } else {
                $errorHtml = new \Widgets\RawHtml();
                $errorHtml->setHtml("Das Objekt \"" . $object->get_name() . "\" kann nicht mit dieser Komponente betrachtet werden.");
                $frameResponseObject->addWidget($errorHtml);
                return $frameResponseObject;
            }
        } else {
            ExtensionMaster::getInstance()->send404Error();
        }
    }

}