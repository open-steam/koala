<?php

namespace Explorer\Commands;

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

        $extensions = \ExtensionMaster::getInstance()->getExtensionByType("IObjectExtension");
        
        $commands = array();

        //sort order of new dialog in explorer
        //the order is defined in the platform whitelist constant
        usort($extensions, "sortExplorerNewDialog");

        //skip list
        foreach ($extensions as $key => $extension) {
            if (strstr(strtolower(get_class($extension)), "portlet")) {
                unset($extensions[$key]);
            }

            if (strstr($extension->getName(), "deprecated") ||
                    strstr($extension->getObjectReadableName(), "deprecated")) {
                unset($extensions[$key]);
            }
        }


        //create new object dialog
        foreach ($extensions as $extension) {
            /* if (!strstr(strtolower(get_class($extension)), "portlet") ) */ {
                $command = $extension->getCreateNewCommand($idRequestObject);
                if ($command) {
                    $commands[] = $command;
                }
            }
        }

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Erstelle ein neues Objekt in »" . getCleanName($object) . "«");
        $dialog->setCloseButtonLabel(null);

        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        $html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px\">";
       
        
        foreach ($commands as $command) {
            $namespaces = $command->getExtension()->getUrlNamespaces();            
            $html .= "<a class=\"explorernewentry\" href=\"\" onclick=\"sendRequest('{$command->getCommandName()}', {'id':{$this->id}}, 'wizard', 'wizard', null, null, '{$namespaces[0]}');return false;\" title=\"{$command->getExtension()->getObjectReadableDescription()}\"><img src=\"{$command->getExtension()->getObjectIconUrl()}\"> {$command->getExtension()->getObjectReadableName()}</a><br>";
        }
        $html .= "<div style=\"float:right\"><a class=\"button pill negative\" onclick=\"closeDialog();return false;\" href=\"#\">Abbrechen</a></div></div><div id=\"wizard_wrapper\"></div>";


        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($html);

        $dialog->addWidget($rawHtml);

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $object = $currentUser->get_workroom();

        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Eigenschaften von " . $object->get_name());

        $dialog->setContent("Nulla dui purus, eleifend vel, consequat non, <br>
							dictum porta, nulla. Duis ante mi, laoreet ut,  <br>
							commodo eleifend, cursus nec, lorem. Aenean eu est.  <br>
							Etiam imperdiet turpis. Praesent nec augue. Curabitur  <br>
							ligula quam, rutrum id, tempor sed, consequat ac, dui. <br>
							Vestibulum accumsan eros nec magna. Vestibulum vitae dui. <br>
							Vestibulum nec ligula et lorem consequat ullamcorper.  <br>
							Class aptent taciti sociosqu ad litora torquent per  <br>
							conubia nostra, per inceptos hymenaeos. Phasellus  <br>
							eget nisl ut elit porta ullamcorper. Maecenas  <br>
							tincidunt velit quis orci. Sed in dui. Nullam ut  <br>
							mauris eu mi mollis luctus. Class aptent taciti  <br>
							sociosqu ad litora torquent per conubia nostra, per  <br>
							inceptos hymenaeos. Sed cursus cursus velit. Sed a  <br>
							massa. Duis dignissim euismod quam. Nullam euismod  <br>
							metus ut orci. Vestibulum erat libero, scelerisque et,  <br>
							porttitor et, varius a, leo.");
        $dialog->setButtons(array(array("name" => "speichern", "href" => "save")));
        return $dialog->getHtml();
    }

}

?>