<?php
namespace HomePortal\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	  $user = \lms_steam::get_current_user();

	  //fallback, steam user not available or loggt out
	  if(!($user instanceof \steam_user)){
	      header("location: " . PATH_URL . "explorer/");
	      die;
	  }

	  $portal = $user->get_attribute("HOME_PORTAL");

	  if (!($portal instanceof \steam_object)) {
	      $current_room = $user->get_workroom();

	      $portal = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Home Portal", $current_room);
	      //hide the hopemortal by default
	      $portal->set_attribute("bid:hidden", "1");
	      $portal->set_attribute( "OBJ_TYPE", "container_portal_bid" );

	      $columnWidth = array("170px", "530px", "200px");
	      $columns = array();

	      for($i = 1; $i <= 3 ; $i++) {
	          $columns[$i] = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), ''.$i, $portal, '' . $i );
	          $columns[$i]->set_attributes(array ("OBJ_TYPE" =>
	                  "container_portalColumn_bid", "bid:portal:column:width" =>
	                  $columnWidth[$i-1] ));
	      }

	      // populate columns with default portlets
	      \ExtensionMaster::getInstance()->callCommand("Create", "PortletUserPicture", array("parent" => $columns[1], "version"=>"3.0"));
	      \ExtensionMaster::getInstance()->callCommand("Create", "PortletBookmarks", array("parent" => $columns[2], "number" => "5", "version"=>"3.0"));
	      \ExtensionMaster::getInstance()->callCommand("Create", "PortletChronic", array("parent" => $columns[2], "elements" => "15", "version"=>"3.0"));

	      $user->set_attribute("HOME_PORTAL", $portal);
	  }

	  header("location: " . PATH_URL . "portal/Index/" . $portal->get_id() . "/");
	  die;
	}
}
?>
