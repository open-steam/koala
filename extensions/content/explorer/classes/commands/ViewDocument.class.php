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
        /* 	if (isset($this->params[1])) {
          $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
          $parent = $object->get_environment();
          if ($parent instanceof \steam_container) {
          $doc = $parent->get_object_by_name($this->params[1]);
          if ($doc instanceof \steam_document) {
          header("location: " . PATH_URL . "Download/Document/" . $doc->get_id());
          exit;
          }
          }
          \ExtensionMaster::getInstance()->send404Error();
          exit;
          } */
        if (isset($this->id)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            
            //chronic
            \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($object);
            
            $objName = $object->get_name();
            if ($object instanceof \steam_docextern) {
                if(isset($this->params[1]) && $this->params[1] === "new"){
                  header('Location: '.$object->get_attribute("DOC_EXTERN_URL").'');
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
            } else if ($object instanceof \steam_document) {
                if ((strpos($objName, ".kml") !== false) || (strpos($objName, ".kmz") !== false)) {
                    $actionBar = new \Widgets\ActionBar();
                    $downloadUrl = getDownloadUrlForObjectId($this->id);
                    $actionBar->setActions(array(
                        array("name" => "URL in neuem Fenster öffnen", "onclick" => "javascript:window.open('http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "');return false;")
                    ));

                    $rawHtml = new \Widgets\RawHtml();
                    $rawHtml->setHtml("<iframe height=\"800px\" width=\"100%\" src=\"http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "\" scrolling=\"yes\"></iframe>");
                    $frameResponseObject->setTitle($objName);
                    $frameResponseObject->addWidget($actionBar);
                    $frameResponseObject->addWidget($rawHtml);
                    return $frameResponseObject;
                }
                $mimetype = $object->get_attribute(DOC_MIME_TYPE);
                $objDesc = trim($object->get_attribute(OBJ_DESC));
                $actionBar = new \Widgets\ActionBar();
                $actionBar->setActions(array(
                    array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $objName),
                    array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                    array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                ));

                if (($objDesc === 0) || ($objDesc === "")) {
                    $name = $objName;
                } else {
                    $name = $objDesc . " (" . $objName . ")";
                }
                $html = "";
                if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/jpg" || $mimetype == "image/gif") {  // Image
                    $html = "<div style=\"text-align:center\"><img style=\"max-width:100%\" title=\"{$name}\" alt=\"Bild: {$name}\" src=\"" . PATH_URL . "Download/Document/" . $this->id . "/\"></div>";
                } else if ($mimetype == "text/html") {
                    $actionBar->setActions(array(
                        //array("name"=>"Anzeigen", "link"=> PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
                        array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                        array("name" => "Quelltext", "link" => PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
                        //array("name"=>"Herunterladen", "link"=> PATH_URL . "Download/Document/" . $this->id . "/"), 
                        array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                        array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                    ));
                    //$html = "<B>Hello</I> How are <U> you?</B>";
                    $html = cleanHTML($object->get_content());

                    $dirname = dirname($object->get_path()) . "/";

                    preg_match_all('/href="([%a-z0-9.-_\/]*)"/iU', $html, $matches);
                    $orig_matches = $matches[0];
                    $path_matches = $matches[1];
                    foreach ($path_matches as $key => $path) {
                        $path = urldecode($path);
                        if (parse_url($path, PHP_URL_SCHEME) != null) {
                            continue;
                        }
                        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
                        if ($ref_object instanceof \steam_object) {
                            $new_path = PATH_URL . "explorer/index/" . $ref_object->get_id();
                        } else {
                            $new_path = PATH_URL . "404/";
                        }
                        $html = str_replace($orig_matches[$key], "href=\"" . $new_path . "\"", $html);
                    }

                    preg_match_all('/src="([%a-z0-9.\-_\/]*)"/iU', $html, $matches);
                    $orig_matches = $matches[0];
                    $path_matches = $matches[1];
                    foreach ($path_matches as $key => $path) {
                        $path = urldecode($path);
                        if (parse_url($path, PHP_URL_SCHEME) != null) {
                            continue;
                        }
                        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
                        if ($ref_object instanceof \steam_object) {
                            $new_path = PATH_URL . "Download/Document/" . $ref_object->get_id();
                        } else {
                            $new_path = PATH_URL . "styles/standard/images/404.jpg";
                        }
                        $html = str_replace($orig_matches[$key], "src=\"" . $new_path . "\"", $html);
                    }
                    //	die;
                    //	$html = preg_replace('/href="([a-z0-9.-_\/]*)"/iU', 'href="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $html);
                    //	$html = preg_replace('/src="([a-z0-9.\-_\/]*)"/iU', 'src="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $html);
                } else if (strstr($mimetype, "text")) {
                    $bidDokument = new \BidDocument($object);
                    $actionBar->setActions(array(array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"), array("name" => "Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/"), array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))), array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))));
                    //$html = "<pre>{$object->get_content()}</pre>";
                    $html = $bidDokument->get_content();
                } else if ((strpos($mimetype, "audio") !== false)) {
                    $mediaplayerHtml = new \Widgets\RawHtml();
                    $mediaplayerPath = \PortletMedia::getInstance()->getAssetUrl() . 'emff_lila_info.swf';
                    $mediaplayerWidth = "200";
                    $mediaplayerHeight = round(200 * 11 / 40) . "";
                    $mediaPlayerUrl = getDownloadUrlForObjectId($this->id);
                    $mediaplayerHtml->setHtml(<<<END
                        
			<object style="width: {$mediaplayerWidth}px; height:{$mediaplayerHeight}px" type="application/x-shockwave-flash" data="{$mediaplayerPath}"><param name="movie" value="{$mediaplayerPath}" /><param name="FlashVars" value="src={$mediaPlayerUrl}" /><param name="bgcolor" value="#cccccc"></object>
			
                            
END
                    );
                    $noActionbar = true;
                }else if ((strpos($mimetype, "video") !== false)) {
                    $mediaplayerHtml = new \Widgets\RawHtml();
                    $mediaplayerPath = \PortletMedia::getInstance()->getAssetUrl() . 'mediaplayer.swf';
                    $mediaplayerWidth = "500";
                    $mediaplayerHeight = round(500 * 10 / 16) . "";
                    $mediaPlayerUrl = getDownloadUrlForObjectId($this->id);
                    $mediaplayerHtml->setHtml(<<<END
                        
			<embed src="{$mediaplayerPath}" width="{$mediaplayerWidth}" height="{$mediaplayerHeight}" allowscriptaccess="always" allowfullscreen="true" flashvars="width={$mediaplayerWidth}&height={$mediaplayerHeight}&file={$mediaPlayerUrl}&autostart=false" />
			
                            
END
                    );
                    $noActionbar = true;
                } else {
                    header("location: " . PATH_URL . "Download/Document/" . $this->id . "/");
                }
                $rawHtml = new \Widgets\RawHtml();
                $rawHtml->setHtml($html);

                //$rawHtml->addWidget($breadcrumb);
                //$rawHtml->addWidget($environment);
                //$rawHtml->addWidget($loader);

                $frameResponseObject->setTitle($name);
                if (!isset($noActionbar)) {
                    $frameResponseObject->addWidget($actionBar);
                }
                if (isset($mediaplayerHtml)) {
                    $frameResponseObject->addWidget($mediaplayerHtml);
                }
                $frameResponseObject->addWidget($rawHtml);
                return $frameResponseObject;
            }
        } else {
            header("location: " . PATH_URL . "404/");
        }
    }

}

?>