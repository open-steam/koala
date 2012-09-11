<?php
namespace PortletMsg\Commands;
class Repair extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
                $objectId = $params[0];
                
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		
                $clipboard = $GLOBALS["STEAM"]->get_current_steam_user();
                $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		
                //check rights
                if(!$steamObject->check_access_write($currentUser)){
                    echo "Sie haben nicht die erforderlichen Rechte für eine Reparatur";die;
                }
                
                /*
                if($steamObject->get_attribute("bid:doctype")!=="portlet:msg"){
                    echo "Kein gültiges Meldungsobjekt";die;
                }
                */
                
                if($steamObject->get_attribute("OBJ_TYPE")!=="container_portlet_bid"){
                    echo "Kein gültiges Meldungsobjekt";die;
                }
                
                
                $msgIdArray = array();
                
                //remove the pics
                $portletInventory = $steamObject->get_inventory();
                foreach ($portletInventory as $msgObject) {
                    $pictrueId = $msgObject->get_attribute("bid:portlet:msg:picture_id");
                    
                    //case message
                    if($pictrueId==0){
                        $msgIdArray[]=$msgObject->get_id();
                    }
                    
                    //case picture
                    if($pictrueId!=0){
                        $msgObject->move($clipboard);
                    }
                }
                
                $steamObject->set_attribute("bid:portlet:content",$msgIdArray);
	}
        
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		//no response
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		// no response
                //echo "Reparatur abgeschlossen";die;
                echo "Reparatur der Meldungen durchgeführt";die;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>