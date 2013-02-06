<?php
namespace PortletFolderList\Commands;

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
                
		$folderInput = new \Widgets\TextInput();
		$folderInput->setLabel("Ordner ID");
		$folderInput->setData($object);
		$folderInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_FOLDERLIST_FOLDERID"));
		$dialog->addWidget($folderInput);
		$dialog->addWidget(new \Widgets\Clearer());
                
		$elementsInput = new \Widgets\TextInput();
		$elementsInput->setLabel("Anzahl an Objekten");
		$elementsInput->setData($object);
		$elementsInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_FOLDERLIST_ITEMCOUNT"));
		$dialog->addWidget($elementsInput);
		$dialog->addWidget(new \Widgets\Clearer());
                
                $dateInput = new \Widgets\Checkbox();
		$dateInput->setLabel("Änderungsdatum anzeigen");
		$dateInput->setData($object);
                $dateInput->setCheckedValue("true");
                $dateInput->setUncheckedValue("false");
		$dateInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_FOLDERLIST_CHANGEDATE"));
		$dialog->addWidget($dateInput);
		$dialog->addWidget(new \Widgets\Clearer());
			
		$dialog->setForceReload(true);
		$this->dialog = $dialog;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>