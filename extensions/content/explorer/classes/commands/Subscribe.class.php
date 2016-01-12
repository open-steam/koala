<?php
namespace Explorer\Commands;
class Subscribe extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
                
                $user = $GLOBALS["STEAM"]->get_current_steam_user();
                $portal = $user->get_attribute("HOME_PORTAL");
                
                if ($portal instanceof \steam_object) {
                    $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                    $column = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $portal->get_path() . "/" . $this->params["column"]);
                    \ExtensionMaster::getInstance()->callCommand("Create", "PortletSubscription", array("parent" => $column, "objectid" => $this->id, "title" => "" . getCleanName($object), "type" => "0", "sort" => "0", "version"=>"3.0"));
                }
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
                $widget = new \Widgets\RawHTML();
                $widget->setHtml("<img src=\"".PATH_URL."explorer/asset/icons/unsubscribe.png\" onclick=\"sendRequest('Unsubscribe', {'id':'{$this->id}' }, 'subscribe" . $this->id . "', 'updater', '', '', 'Explorer');\">");
		
                $jsWrapper = new \Widgets\JSWrapper();
                $jsWrapper->setJS("jQuery('#" . $this->id . "').removeClass('listviewer-item-selected');");
                
                $ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($widget);
                $ajaxResponseObject->addWidget($jsWrapper);
		return $ajaxResponseObject;
	}
}
?>