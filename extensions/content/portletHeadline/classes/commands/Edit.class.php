<?php
namespace PortletHeadline\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
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
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		//$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([headline])"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
		
		$align =  new \Widgets\ComboBox();
		$align->setLabel("Ausrichtung");
		$align->setOptions(array(	 array("name"=>"Linksbündig", "value"=>"left"), 
									 array("name"=>"Rechtsbündig", "value"=>"right"),
									 array("name"=>"Zentriert", "value"=>"center")
									 ));
		$align->setData($object);
		$align->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([alignment])"));
		$dialog->addWidget($align);
		$dialog->addWidget(new \Widgets\Clearer());
		
		
		$size = new \Widgets\ComboBox();
		$size->setLabel("Größe");
		$size->setOptions(array(
							array("name"=>"15", "value"=>"15"),
							array("name"=>"20", "value"=>"20"),
							array("name"=>"25", "value"=>"25"),
							array("name"=>"30", "value"=>"30"),
							array("name"=>"35", "value"=>"35"),
							array("name"=>"40", "value"=>"40"),
							array("name"=>"50", "value"=>"50"),
							array("name"=>"60", "value"=>"60")
								));
		$size->setData($object);
		$size->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([size])"));
		$dialog->addWidget($size);
		$dialog->setForceReload(true);
		
		$this->dialog = $dialog;
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>