<?php
namespace Explorer\Commands;
class Copy extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $user;
        private $success = true;
        private $name;
	
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
                } else if (getObjectType($object) === "pyramiddiscussion") {
                    \ExtensionMaster::getInstance()->getExtensionById("Pyramiddiscussion")->copyPyramiddiscussion($object);
                } else {
                    if ($object instanceof \steam_link){ 
                        $copy = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
                        $copy->set_name($object->get_name());
                        $copy->move($this->user);
                    } else if ($object instanceof \steam_container) {
                        list($countObjects, $countSize) = $this->countInventoryRecursive($object);
                        if ($countObjects <= 500 && $countSize <= 524288000) {
                            $copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $object);
                            $copy->move($this->user);
                        } else {
                            $this->success = false;
                            $this->name = $object->get_name();
                        }
                    } else {
                        $copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $object);
                        $copy->move($this->user);
                    }
		}
	}
	
        private function countInventoryRecursive($object, $countObjects = 0, $countSize = 0) {
            $inventory = $object->get_inventory();
            $countObjects = $countObjects + count($inventory);
            foreach ($inventory as $element) {
                if ($countObjects > 500 || $countSize > 524288000) {
                    break;
                }
                if ($element instanceof \steam_document) {
                    $countSize = $countSize + $element->get_content_size();
                } else if ($element instanceof \steam_container) {
                    list($countObjects, $countSize) = $this->countInventoryRecursive($element, $countObjects, $countSize);
                }
            }
            return array($countObjects, $countSize);
        }
        
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
            if ($this->success === true) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$clipboardModel = new \Explorer\Model\Clipboard($this->user);
		$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';" ;
                $jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
            } else {
                $ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
                $js = "
                    if ($('#error').length == 0) {
                        $('#content').prepend('<p id=\"error\" style=\"\">Es ist nicht möglich Ordner zu kopieren, die mehr als 200 Objekte enthalten oder deren Dateigröße 500MB übersteigt.<br>Folgende Ordner wurden nicht kopiert: " . $this->name . "</p>');
                    } else {
                        var html = $('#error').html();
                        html = html.substring(156, html.length);
                        if (html.indexOf(' " . $this->name . "') == -1) {
                            $('#error').append(', ' + '" . $this->name . "');
                        }
                    }
                    $('.popupmenuwapper').hide();
                    $('.popupmenuanker').removeClass('open')";
                $jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
            }
	}
}
?>