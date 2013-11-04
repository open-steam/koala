<?php

namespace Explorer\Commands;

class CodeEditDocument extends \AbstractCommand implements \IFrameCommand {

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
                \lms_portal::get_instance()->add_javascript_src("CodeMirror", PATH_URL . "styles/standard/javascript/CodeMirror/lib/codemirror.js?version=" . KOALA_VERSION);
               
                \lms_portal::get_instance()->add_javascript_src("CodeMirror", PATH_URL . "styles/standard/javascript/CodeMirror/mode/xml/xml.js?version=" . KOALA_VERSION);
                \lms_portal::get_instance()->add_javascript_src("CodeMirror", PATH_URL . "styles/standard/javascript/CodeMirror/mode/javascript/javascript.js?version=" . KOALA_VERSION);
                \lms_portal::get_instance()->add_javascript_src("CodeMirror", PATH_URL . "styles/standard/javascript/CodeMirror/mode/css/css.js?version=" . KOALA_VERSION);
                \lms_portal::get_instance()->add_javascript_src("CodeMirror", PATH_URL . "styles/standard/javascript/CodeMirror/mode/htmlmixed/htmlmixed.js?version=" . KOALA_VERSION);
                

                $mimetype = $object->get_attribute(DOC_MIME_TYPE);
                $objName = $object->get_name();
                $objDesc = trim($object->get_attribute(OBJ_DESC));
                if (($objDesc === 0) || ($objDesc === "")) {
                    $name = $objName;
                } else {
                    $name = $objDesc . " (" . $objName . ")";
                }

                $actionBar = new \Widgets\ActionBar();
                $actionBar->setActions(array(
                    array("name" => "Anzeigen", "link" => PATH_URL . "Explorer/ViewDocument/" . $this->id . "/"),
                    array("name" => "Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/"),
                    //array("name"=>"Quelltext", "link"=> PATH_URL . "Explorer/CodeEditDocument/" . $this->id . "/"),
                    //array("name"=>"Herunterladen", "link"=> PATH_URL . "Download/Document/" . $this->id . "/"),
                    array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                    array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
                ));

                $contentText = new \Widgets\TextareaCode();
                $contentText->setWidth(945);
                $contentText->setRows(50);
                $contentText->setData($object);
                $contentText->setContentProvider(\Widgets\DataProvider::contentProvider());
                $clearer = new \Widgets\Clearer();
// 				$html = "";
// 				if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif") {  // Image
// 					$html = "<div style=\"text-align:center\"><img style=\"max-width:100%\" title=\"{$name}\" alt=\"Bild: {$name}\" src=\"" . PATH_URL . "Download/Document/" . $this->id . "/\"></div>";
// 				} else if ($mimetype == "text/html") {
// 					$html = strip_tags($object->get_content(),"<h1><h2><h3><h4><h5><p><a><div><style><b><i><strong><img>");
// 				} else if (strstr($mimetype, "text")) {
// 					$html = "<pre>{$object->get_content()}</pre>";
// 				} else {
// 					header("location: " . PATH_URL . "Download/Document/" . $this->id . "/");
// 				}
// 				$rawHtml = new \Widgets\RawHtml();
// 				$rawHtml->setHtml($html);
                //$rawHtml->addWidget($breadcrumb);
                //$rawHtml->addWidget($environment);
                //$rawHtml->addWidget($loader);

                $frameResponseObject->setTitle($name);
                $frameResponseObject->addWidget($actionBar);
                //$frameResponseObject->addWidget($rawHtml);
                $frameResponseObject->addWidget($contentText);
                $frameResponseObject->addWidget($clearer);

                return $frameResponseObject;
            }
        } else {
            ExtensionMaster::getInstance()->send404Error();
        }
    }

}

?>