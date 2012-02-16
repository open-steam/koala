<?php
namespace Portal\Commands;
class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {
	
	private $params;
	private $id;
	private $rawHtmlWidget;
	private $portalObject;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \IdRequestObject) {
			$this->id = $requestObject->getId();
		}
		
		$steam = $GLOBALS["STEAM"];
		
		//get singleton and portlet path
		$portalInstance = \Portal::getInstance();
		$portalPath = $portalInstance->getExtensionPath();
		
		//template
		$templateFileName=$portalPath."/ui/html/index.html";
		$tmpl = new \HTML_TEMPLATE_IT();
		$tmpl->loadTemplateFile($templateFileName);
		
		$this->getExtension()->addCSS();
		$this->getExtension()->addJS();
		
		
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = $currentUser->get_workroom();
		
		$objectId = $this->id;
		
		//get the portal object
		$this->portalObject = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $objectId );
		
		$type = getObjectType($this->portalObject);
		if (!($type === "portal")) {
			\ExtensionMaster::getInstance()->send404Error();
			die;
		}
		
		\Portal::getInstance()->setPortalObject($this->portalObject);
		 
		//get the content of the portal object
		$portalColumns = $this->portalObject->get_inventory();
		
		$htmlBody="";
		$extensionMaster=\ExtensionMaster::getInstance();

		$count=0;
		
		$htmlCollectorColRow[][]=array();
		$col=0;
		$row=0;
		
		$this->rawHtmlWidget = new \Widgets\RawHtml();
		foreach ($portalColumns as $columnObject) {
			$columnObjectId = $columnObject->get_id();
			$widgets = $extensionMaster->getWidgetsByObjectId($columnObjectId, "view");
			$this->rawHtmlWidget->addWidgets($widgets);
			$data = \Widgets\Widget::getData($widgets);
			$htmlBody.= $data["html"];
			$count++;
		}
		
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		if (isset($this->portalObject) && $this->portalObject->check_access_write($currentUser)) {
			$htmlBody .= "<script>if (readCookie(\"portalEditMode\") === \"{$objectId}\") {portalLockButton({$objectId})}</script>";
		}
		
		$tmpl->setVariable("BODY", $htmlBody);
		
		$htmlBodyTemplated=$tmpl->get();
		
		$this->rawHtmlWidget->setHtml($htmlBodyTemplated);
	}
	
	public function idResponse(IdResponseObject $idResponseObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle(getCleanName($this->portalObject));
		$frameResponseObject->addWidget($this->rawHtmlWidget);
		return $frameResponseObject;
	}
}
?>