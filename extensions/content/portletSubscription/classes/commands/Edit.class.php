<?php
namespace PortletSubscription\Commands;

class Edit extends \AbstractCommand implements \IAjaxCommand {
	
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));
		
                $titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Titel");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());
                
		/*$folderInput = new \Widgets\TextInput();
		$folderInput->setLabel("Objekt ID");
		$folderInput->setData($object);
		$folderInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_SUBSCRIPTION_OBJECTID"));
		$dialog->addWidget($folderInput);
		$dialog->addWidget(new \Widgets\Clearer());*/
                
                $dropDownWidget = new \Widgets\ComboBox();
                $dropDownWidget->setLabel("Typ");
                $dropDownWidget->setData($object);
                $dropDownWidget->setOptions(array(
                    array("name" => "Privat", "value" => "0"),
                    array("name" => "Zeitraum: 1 Woche", "value" => "604800"),
                    array("name" => "Zeitraum: 2 Wochen", "value" => "1209600"),
                    array("name" => "Zeitraum: 3 Wochen", "value" => "1814400"),
                    array("name" => "Zeitraum: 4 Wochen", "value" => "2419200")));
                $dropDownWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_SUBSCRIPTION_TYPE"));
                $dialog->addWidget($dropDownWidget);
                $dialog->addWidget(new \Widgets\Clearer());
                
                $dropDownWidget = new \Widgets\ComboBox();
                $dropDownWidget->setLabel("Sortierung");
                $dropDownWidget->setData($object);
                $dropDownWidget->setOptions(array(
                    array("name" => "Frühe Neuigkeiten zuerst anzeigen", "value" => "0"),
                    array("name" => "Späte Neuigkeiten zuerst anzeigen", "value" => "1")));
                $dropDownWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_SUBSCRIPTION_ORDER"));
                $dialog->addWidget($dropDownWidget);
                $dialog->addWidget(new \Widgets\Clearer());
			

		$this->dialog = $dialog;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>