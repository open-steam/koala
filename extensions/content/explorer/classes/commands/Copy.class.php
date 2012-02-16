<?php
namespace Explorer\Commands;
class Copy extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $user;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		if(getObjectType($object) === "portal" ){
            $portalInstance = \PortletTopic::getInstance();
            $portalObjectId = $object->get_id();
            \ExtensionMaster::getInstance()->callCommand("PortalCopy", "Portal", array("id" => $portalObjectId));
        } else {
        	if ($object instanceof \steam_link) { 
	        	$copy = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
			} else {
	            $copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $object);
			}
        	$copy->move($this->user);
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$clipboardModel = new \Explorer\Model\Clipboard($this->user);
		$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>