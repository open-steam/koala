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

        //root test for restricted creation
        $steamUser = $GLOBALS['STEAM']->get_current_steam_user();
        if ($steamUser->get_name()=="root") $isRoot=true; else $isRoot=false;

        //skip list
        foreach ($extensions as $key => $extension) {
            //case portlet
            if (strstr(strtolower(get_class($extension)), "portlet")) {
                unset($extensions[$key]);
            }

            //case create restricted to root
            if (defined("CREATE_RESTRICTED_TO_ROOT")){
                if (!$isRoot){
                    $extensionClass = strtolower(get_class($extension));
                    if (strstr(strtolower(CREATE_RESTRICTED_TO_ROOT), strtolower($extensionClass))){
                        unset($extensions[$key]);
                    }
                }
            }

            //case depricated
            if (strstr($extension->getName(), "deprecated") ||
                    strstr($extension->getObjectReadableName(), "deprecated")) {
                unset($extensions[$key]);
            }
        }

        //create new object dialog
        foreach ($extensions as $extension) {
            {
                $command = $extension->getCreateNewCommand($idRequestObject);
                if ($command) {
                    $commands[] = $command;
                }
            }
        }

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Erstelle ein neues Objekt in »" . getCleanName($object) . "«");
        //disable the save button (not used here)
        $dialog->setSaveAndCloseButtonLabel(null);
        //and rename the cancel Button
        $dialog->setCancelButtonLabel("Abbrechen");

        $dialog->setWidth(500);
        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        $html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px; margin-top: 20px;\">";

        foreach ($commands as $command) {
            $namespaces = $command->getExtension()->getUrlNamespaces();

            $url = $command->getExtension()->getObjectIconUrl();
            $name = str_replace(".svg", "", array_pop(explode("/", $url)));

            $html .= "<div style=\"clear:both;\" class=\"explorernewentry\">";
            $html .= "<a href=\"\" onclick=\"sendRequest('{$command->getCommandName()}', {'id':{$this->id}}, 'wizard', 'wizard', null, null, '{$namespaces[0]}');return false;\" title=\"{$command->getExtension()->getObjectReadableDescription()}\"><svg style='float:left; width:18px; height:18px;'><use xlink:href='" . $url . "#" . $name . "' /></svg><p style=\"float:left; margin-top: 2px; margin-left: 5px; font-size:12px;\">{$command->getExtension()->getObjectReadableName()}</p></a>";
            $helpurl = $command->getExtension()->getHelpUrl();
      			if($helpurl != "")
            $html .= "<a href=\"\" onclick=\"window.open('" . $helpurl . "', '_blank');\" title=\"mehr Informationen\"><svg style='float:right; width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/help.svg#help' /></svg></a>";
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
