<?php
namespace Portal\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$current_room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        $portal = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $this->params["name"], $current_room);
        $portal->set_attribute( "OBJ_TYPE", "container_portal_bid" );

        $columnWidth = array("1" => "900px", "2" => "200px;700px", "3" =>"200px;500px;200px");

        $columnCount = $this->params["columns"];
        $columnWidth = explode( ';', $columnWidth[$columnCount] );
        $columns = array();

        for($i = 1; $i <= $columnCount ; $i++) {
          $columns[$i] = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), ''.$i, $portal, '' . $i );
          $columns[$i]->set_attributes(array ("OBJ_TYPE" =>
                      "container_portalColumn_bid", "bid:portal:column:width" =>
                      $columnWidth[$i-1] ));
        }

        // populate columns with default portlets
        switch (count($columns)) {
            case 1:
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletHeadline", array("parent" => $columns[1], "title" => $this->params["name"], "version"=>"3.0"));
							\ExtensionMaster::getInstance()->callCommand("Create", "PortletMsg", array("parent" => $columns[1], "title" => "Meldungen", "version"=>"3.0"));
                break;
            case 2:
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletTopic", array("parent" => $columns[1], "title" => "Linkliste", "version"=>"3.0"));
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletHeadline", array("parent" => $columns[2], "title" => $this->params["name"], "version"=>"3.0"));
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletMsg", array("parent" => $columns[2], "title" => "Meldungen", "version"=>"3.0"));
                break;
            case 3:
              \ExtensionMaster::getInstance()->callCommand("Create", "PortletTopic", array("parent" => $columns[1], "title" => "Linkliste", "version"=>"3.0"));
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletHeadline", array("parent" => $columns[2], "title" => $this->params["name"], "version"=>"3.0"));
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletMsg", array("parent" => $columns[2], "title" => "Meldungen", "version"=>"3.0"));
            	\ExtensionMaster::getInstance()->callCommand("Create", "PortletAppointment", array("parent" => $columns[3], "title" => "Termine", "version"=>"3.0"));
                break;
        }

		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs('closeDialog(); location.reload();');
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
