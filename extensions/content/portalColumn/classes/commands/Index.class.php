<?php
namespace PortalColumn\Commands;
class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$objectId=$requestObject->getId();
		$steam = $GLOBALS["STEAM"];
		$extensionMaster=\ExtensionMaster::getInstance();
		$portalColumnExtension = $extensionMaster->getExtensionById("PortalColumn");
		
		$this->getExtension()->addCSS();
		
		$htmlBody="";
		$portalColumnObject = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $objectId );
		
		$portlets = $portalColumnObject->get_inventory();
		
		//handle column size
		$columnWidthPx = trim($portalColumnObject->get_attribute("bid:portal:column:width"));
		if (strEndsWith($columnWidthPx, "px")) {
			$columnWidth = str_replace("px", "", $columnWidthPx);
			$columnWidthExt = "px";
		} else if (strEndsWith($columnWidthPx, "%")) {
			$columnWidth = str_replace("%", "", $columnWidthPx);
			$columnWidthExt = "%";
		} else {
			$columnWidth = $columnWidthPx;
			$columnWidthExt = "px";
		}
		if ((int)$columnWidth > 0) {
			$columnWidthPx = $columnWidth . $columnWidthExt;
		} else {
			$columnWidthPx = "200px";
		}
		
		
		$this->rawHtmlWidget = new \Widgets\RawHtml();
		$htmlBody.='<div class="column" style="width:'.$columnWidthPx.';">';
		
		//popupmenu
		$popupmenu = new \Widgets\PopupMenu();
		$popupmenu->setCommand("GetPopupMenu");
		$popupmenu->setData($portalColumnObject);
		$popupmenu->setNamespace("PortalColumn");
		$popupmenu->setElementId("portal-overlay");
		$htmlBody.= '<h2 class="editbutton columnheadline"><div class="editbutton">'.$popupmenu->getHtml().'</div><div style="margin-left:3px;">Spalte</div></h2>';
		
		foreach ($portlets as $portlet) {
			//handle link objects as portlets
			$params = array();
			if($portlet instanceof \steam_link) {
				$params["referenced"] = true;
				$params["referenceId"] = $portlet->get_id();
				$portlet = $portlet->get_link_object();
				if($portlet==NULL) continue;
			}
			$widgets = $extensionMaster->getWidgetsByObjectId($portlet->get_id(), "view", $params);
			$this->rawHtmlWidget->addWidgets($widgets);
			$data = \Widgets\Widget::getData($widgets);
			$htmlBody.= $data["html"];
		}
		
		$htmlBody.="</div>";
		$this->rawHtmlWidget->setHtml($htmlBody);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->addWidget($this->rawHtmlWidget);
		return $idResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->addWidget($this->rawHtmlWidget);
		return $frameResponseObject;
	}
	
	

}
?>