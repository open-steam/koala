<?php

namespace Ellenberg\Commands;
//this class is responisble to display the content (links to the external ressource) of the the ellenberg-object
class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    
    //the basepath to all links with the ellenberg-tool
    private $ellenbergUrl = 'http://amole.cs.upb.de/webapp/#';

    public function validateData(\IRequestObject $requestObject) {
        //nothing to validate here
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        //get the loginname of the current user to forward it to the ellenberg tool
        $userLoginName = $_SESSION[ "LMS_USER" ]->get_login();
        
        //generate the output
        //this is the header
        $headlineHtml = new \Widgets\Breadcrumb();
        $headlineHtml->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/old/annotation.gif\" /> " . $object->get_name() . " - Ellenbergauswertung ")));
        $frameResponseObject->addWidget($headlineHtml);

        //set some css code
        $cssStyles = new \Widgets\RawHtml();
        $cssStyles->setCss('
            .attribute{width:150px;float:left;padding-left:50px;padding-top:5px;} 
            .value{margin-left:200px;padding-top:5px;} 

            .breadcrumb {
                padding-left: 50px;
                padding-right: 50px;
            }
        ');
        $frameResponseObject->addWidget($cssStyles);

        $hintRemotePlatform = new \Widgets\RawHtml();
        $hintRemotePlatform->setHtml('<p class ="breadcrumb">Mit den folgenden Links können Sie die Ellenberg-Auswertungsplattform aufrufen.'
                .'<br />Diese ist kein direkter Bestandteil von bid, sondern auf einem externen Server untergebracht.'
                .'<br />Für die Benutzung dieser Ellenberganwendung ist eine erneute Anmeldung mit den bid-Zugangsdaten erforderlich.</p>');
                
        $frameResponseObject->addWidget($hintRemotePlatform);
        
        $generatorPlatform = new \Widgets\RawHtml();
        $generatorPlatform->setHtml('Unter folgendem Link können Sie Ellenbergerhebungsbögen erstellen:'
                . '<div class="attribute">Auswertungsbogen erstellen:</div><div class="value"><a target="_blank" href="'.$this->ellenbergUrl.'scenario/'.$object->get_Attribute("ELLENBERG_ID").'/'.$userLoginName.'" > '.$this->ellenbergUrl.'scenario/'.$object->get_Attribute("ELLENBERG_ID").'/'.$userLoginName.'</a></div>');
        $frameResponseObject->addWidget($generatorPlatform);
        
        $summaryPlatform = new \Widgets\RawHtml();
        $summaryPlatform->setHtml('<br />Unter folgendem Link können Sie Ellenbergerhebungsbögen auswerten:'
                . '<div class="attribute">Erhebung auswerten:</div><div class="value"><a target="_blank" href="'.$this->ellenbergUrl.'summary/'.$object->get_Attribute("ELLENBERG_ID").'/'.$userLoginName.'" > '.$this->ellenbergUrl.'summary/'.$object->get_Attribute("ELLENBERG_ID").'/'.$userLoginName.'</a></div>');
        $frameResponseObject->addWidget($summaryPlatform);
        
        $hintLocal = new \Widgets\RawHtml();
        $hintLocal->setHtml('<p class ="breadcrumb">Wenn Sie die Daten aus der Ellenbergauswertungsplattform exportieren,</br>'
                . 'werden sie unter folgendem Link gespeichert:</p>');
        $frameResponseObject->addWidget($hintLocal);
        
        $explorer = new \Widgets\RawHtml();
        $explorer->setHtml('<div class="attribute">Exportierte Dateien:</div><div class="value"><a href="'.PATH_SERVER.'/explorer/index/'.$this->id.'" > '.PATH_SERVER.'/explorer/index/'.$this->id.'</a></div>');
        $frameResponseObject->addWidget($explorer);
       
        return $frameResponseObject;
    }


}

?>