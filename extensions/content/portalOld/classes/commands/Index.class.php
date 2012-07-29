<?php
namespace PortalOld\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portal = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$portalType = $portal->get_attribute("bid:doctype");

		if ($_SERVER["REQUEST_METHOD"] == "POST" && $portal->check_access(SANCTION_WRITE) && $portal->check_access(SANCTION_INSERT) && $portal->check_access(SANCTION_MOVE)) {
			if ($portalType==="portal") {
				$geo = new \oldportal_geometry($portal->get_attribute("bid:geometry"));
				$depth = 1;
				$columnCount = 0;
				$portlets = array();
				$columns = array(0, array(), array(), array());
				
				// check first row vertically
				$firstElement = "";
				$segments = $geo->get_segment_by_depth(1, VERTICAL);
				if (count($segments) == 1) {
					foreach ($segments as $key => $segment) {
						if ($segment["id"] instanceof \steam_object && !in_array($segment["id"], $portlets) && $segment["id"]->get_attribute("bid:doctype") == "portlet") {
							$firstElement = $segment["id"];
						}
					}
				}
				
				// only put $firstElement in second column if there are 2 or 3 columns (converting old layouts)
				while ($segments = $geo->get_segment_by_depth($depth++, HORIZONTAL)) {
					$columnCount++;
				}
				if ($columnCount > 1 && $firstElement != "") {
					array_push($columns[2], $firstElement);
					array_push($portlets, $firstElement);
				}
				
				// traverse old geometry by portal columns (horizontal) to determine layout
				$depth = 1;
				$columnCount = 0;
				while ($segments = $geo->get_segment_by_depth($depth++, HORIZONTAL)) {
					foreach ($segments as $key => $segment) {
						if ($segment["id"] instanceof \steam_object && !in_array($segment["id"], $portlets) && $segment["id"]->get_attribute("bid:doctype") == "portlet") {
							array_push($columns[$columnCount+1], $segment["id"]);
							array_push($portlets, $segment["id"]);
						}
					}
					$columnCount++;
				}
				
				// delete not visible portlets
		        $inventory = $portal->get_inventory();
		        foreach ($inventory as $item) {
		        	if ($item instanceof \steam_object && !in_array($item, $portlets) && $item->get_attribute("bid:doctype") == "portlet") {
		        		$item->delete();
		        	}
		        }
		        
		        // convert portlets
				foreach ($portlets as $portlet) {
					if ($portlet->get_attribute("bid:doctype") == "portlet" && !($portlet instanceof \steam_link)) {
						if ($portlet->get_attribute("bid:portlet") == "headline") {
							$headline = $portlet;
							$headline->set_attribute("OBJ_DESC", $headline->get_name());
							$headline->set_attribute("OBJ_TYPE", "container_portlet_bid");
							$headline->set_attribute("bid:portlet:version", "3.0");
						} else if ($portlet->get_attribute("bid:portlet") == "topic") {
							$topic = $portlet;
							$topic->set_attribute("OBJ_DESC", $topic->get_name());
							$topic->set_attribute("OBJ_TYPE", "container_portlet_bid");
							$topic->set_attribute("bid:portlet:version", "3.0");
						} else if ($portlet->get_attribute("bid:portlet") == "msg") {
							$msg = $portlet;
							// convert messages saved in BBCode to HTML
							$messages = $msg->get_inventory();
							$UBB = new \UBBCode();
							foreach ($messages as $message) {
								if ($message instanceof \steam_document && !strStartsWith($message->get_attribute("DOC_MIME_TYPE"), "image/")) {
									$message->set_content($UBB->encode($message->get_content()));
								}
							}
							$msg->set_attribute("OBJ_DESC", $msg->get_name());
							$msg->set_attribute("OBJ_TYPE", "container_portlet_bid");
							$msg->set_attribute("bid:portlet:version", "3.0");
						} else if ($portlet->get_attribute("bid:portlet") == "appointment") {
							$appointment = $portlet;
							$appointment->set_attribute("OBJ_DESC", $appointment->get_name());
							$appointment->set_attribute("OBJ_TYPE", "container_portlet_bid");
							$appointment->set_attribute("bid:portlet:version", "3.0");
						} else if ($portlet->get_attribute("bid:portlet") == "rss") {
							$rss = $portlet;
							$rss->set_attribute("OBJ_DESC", $rss->get_name());
							$rss->set_attribute("OBJ_TYPE", "container_portlet_bid");
							$rss->set_attribute("bid:portlet:version", "3.0");
						}
					}
				}
		        
				// create column containers
				$columnWidth = array("1" => "900px", "2" => "200px;700px", "3" =>"200px;500px;200px");
		        $columnWidth = explode(';', $columnWidth[$columnCount]);
		        $columnContainers = array();
		        for($i = 1; $i <= $columnCount ; $i++) {
			          $columnContainers[$i] = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), ''.$i, $portal, '' . $i);
			          $columnContainers[$i]->set_attributes(array ("OBJ_TYPE" => "container_portalColumn_bid", "bid:portal:column:width" => $columnWidth[$i-1]));
		        }
		        
		        // move portlets in corresponding column containers
				foreach ($columns[1] as $column) {
		        	$column->move($columnContainers[1]);
		        }
				foreach ($columns[2] as $column) {
		        	$column->move($columnContainers[2]);
		        }
				foreach ($columns[3] as $column) {
		        	$column->move($columnContainers[3]);
		        }

		        // set portal type parameter and redirect to converted portal
				$portal->set_attribute("OBJ_TYPE", "container_portal_bid");
				$portal->delete_attribute("bid:doctype");
				$_SESSION["confirmation"] = "\"Altes\" Portal wurde erfolgreich umgewandelt.";
				header("Location: " . PATH_URL . "portal/Index/" . $portal->get_id() . "/");
				die;
			} else {
				header("Location: " . PATH_URL . "portal/Index/" . $portal->get_id() . "/");
				die;
			}
		}
		
		if ($portalType==="portal") {
			$rawWidget = new \Widgets\RawHtml();
			if ($portal->check_access(SANCTION_WRITE) && $portal->check_access(SANCTION_INSERT) && $portal->check_access(SANCTION_MOVE)) {
				$PortalOldExtension = \PortalOld::getInstance();
				$content = $PortalOldExtension->loadTemplate("portalold_index.template.html");
				$content->setCurrentBlock("BLOCK_INDEX");
				$content->setVariable("CONVERT_LABEL", "Jetzt umwandeln");
				$content->setVariable("WARNING_TEXT", "Achtung: Es werden nur aktuell angezeigte Komponenten des Portals konvertiert. Nicht angezeigte Komponenten gehen bei der Konvertierung verloren.");
				$content->parse("BLOCK_INDEX");
				$rawWidget->setHtml($content->get());
			}
			
			$frameResponseObject->setProblemDescription("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden.");
			$frameResponseObject->setProblemSolution("Bitte umwandeln.");
			$frameResponseObject->addWidget($rawWidget);
		}
		return $frameResponseObject;
	}
}
?>