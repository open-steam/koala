<?php
namespace Explorer\Commands;
class Unsubscribe extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
                
                $user = \lms_steam::get_current_user_no_guest();
                $portal = $user->get_attribute("HOME_PORTAL");
                $trashbin = $user->get_attribute("USER_TRASHBIN");
                
                if ($portal instanceof \steam_object) {
                    $columns = $portal->get_inventory();
                    foreach ($columns as $column) {
                        if ($column instanceof \steam_container) {
                            $portlets = $column->get_inventory();
                            foreach ($portlets as $portlet) {
                                if ($portlet->get_attribute("bid:portlet") === "subscription") {
                                    if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID") == $this->id) {
                                        $portlet->move($trashbin);
                                    }
                                }
                            }
                        }
                    }
                    \ExtensionMaster::getInstance()->getExtensionById("HomePortal")->updateSubscriptions($portal->get_id());
                }
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
                $widget = new \Widgets\RawHTML();
                $widget->setHtml("<img src=\"".PATH_URL."explorer/asset/icons/subscribe.png\" onclick=\"sendRequest('Subscribe', {'id':'{$this->id}', 'column':'2' }, 'subscribe" . $this->id . "', 'updater', '', '', 'Explorer');\">");
		
                $jsWrapper = new \Widgets\JSWrapper();
                $jsWrapper->setJS("jQuery('#" . $this->id . "').removeClass('listviewer-item-selected');");
                
                $ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($widget);
                $ajaxResponseObject->addWidget($jsWrapper);
		return $ajaxResponseObject;
	}
}
?>