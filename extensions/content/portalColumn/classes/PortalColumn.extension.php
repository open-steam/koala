<?php
class PortalColumn extends AbstractExtension implements IObjectExtension{
	
	public function getName() {
		return "PortalColumn";
	}
	
	public function getDesciption() {
		return "Extension for portal column.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return null;
	}
	
	public function getObjectReadableDescription() {
		return null;
	}
	
	public function getObjectIconUrl() {
		return null;
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		//can handle
		$usableObject = false;
		
		$portalColumnObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$portalType = $portalColumnObject->get_attribute("OBJ_TYPE");
		if ($portalType==="container_portalColumn_bid") {
			//go on;
		} else {
			return false;
		}
		
		//is this a portlet? then abort TEST
		$portletType = $portalColumnObject->get_attribute("bid:portlet");
		if ($portletType==="msg") {
			return false;
		} else {
			//return false;
		}
		
		
		if ($idRequestObject->getMethod() == "view") {
			return new \PortalColumn\Commands\Index();
		}
	}
	
	
	/*
	 * get the size of all columns this column is in
	 */
	public function getColumnSizeSum($portalColumnObject){
		$portalObject = $portalColumnObject->get_environment();
		$portalInventory = $portalObject->get_inventory();
		
		$portalSize = 0;
		foreach ($portalInventory as $portalColumnObject) {
			$columnWidthPx = substr($portalColumnObject->get_attribute("bid:portal:column:width"),0,strlen($portalColumnObject->get_attribute("bid:portal:column:width"))-2);
			$correctionFactor = 1;
			$columnWidthPx = round($columnWidthPx * $correctionFactor);
			$portalSize+= $columnWidthPx;
		}

		return $portalSize;
	}
	
	/*
	 * get correction factor for bid2 to bid3 portals
	 */
	public function getPortalSizeCorrectionFactor($portalColumnObject){
		//$sizeKoala = 600;
		$sizeKoala = 1010-20-20-10;
		$sizeBidPortal = $this->getColumnSizeSum($portalColumnObject);
		
		if (0==$sizeBidPortal){
			return 0.83;
		}
		
		$factor = $sizeKoala / $sizeBidPortal;
		return $factor;
	}
	
	/*
	 * get scaled down value for new portal column
	 */
	public function getCorrectedColumnSize($portalColumnObject){
		$correctionFactor = $this->getPortalSizeCorrectionFactor($portalColumnObject);
		
		$columnWidthPx = substr($portalColumnObject->get_attribute("bid:portal:column:width"),0,strlen($portalColumnObject->get_attribute("bid:portal:column:width"))-2);
		$columnWidthPx = round($columnWidthPx * $correctionFactor);
		return $columnWidthPx;
	}
	
}
?>