<?php
namespace Explorer\Commands;

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
		if (isset($this->id)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($object instanceof \steam_exit) {
				$object = $object->get_exit();
				$this->id = $object->get_id();
			}
		} else {
			$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
			$object = $currentUser->get_workroom();
			$this->id = $object->get_id();
		}
		
		if (!$object instanceof \steam_object) {
			\ExtensionMaster::getInstance()->send404Error();
			die;
		}
		
		$objectModel = \AbstractObjectModel::getObjectModel($object);
		
		if ($object && $object instanceof \steam_container) {
			$count = $object->count_inventory();
			if ($count > 150) {
				die ("Es befinden sich $count Objekte im diesem Ordner. Das Laden ist nicht möglich.");
			}
			$objects = $object->get_inventory();
		} else {
			$objects = array();
		}
		
		$objectType = getObjectType($object);
		switch ($objectType) {
			case "document":
				header("location: " . PATH_URL . "explorer/ViewDocument/" . $this->id . "/");
				die;
				break;
			
			case "forum":
				header("location: " . PATH_URL . "forum/Index/" . $this->id . "/");
				die;
				break;
			
			case "referenceFolder":
				$exitObject = $object->get_exit();
				header("location: " . PATH_URL . "explorer/Index/" . $exitObject->get_id() . "/");
				die;
				break;
			
			case "referenceFile":
				$linkObject = $object->get_link_object();
				header("location: " . PATH_URL . "explorer/Index/" . $linkObject->get_id() . "/");
				die;
				break;
			
			case "user":
				header("location: " . PATH_URL . "user/Index/" . $object->get_name() . "/");
				die;
				break;
			
			case "group":
				\ExtensionMaster::getInstance()->send404Error();
				die;
				break;
			
			case "trashbin":
				\ExtensionMaster::getInstance()->send404Error();
				die;
				break;
				
			case "portal_old":
				$rawHtml = new \Widgets\RawHtml();
				//$rawHtml->setHtml("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden. Bitte umwandeln.");
				$frameResponseObject->addWidget($rawHtml);
				$frameResponseObject->setProblemDescription("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden.");
				$frameResponseObject->setProblemSolution("Bitte umwandeln.");
				return $frameResponseObject;
				break;
				
			case "gallery":
				header("location: " . PATH_URL . "gallery/Index/" . $this->id . "/");
				die;
				break;
				
			case "portal":
				header("location: " . PATH_URL . "portal/Index/" . $this->id . "/");
				die;
				break;
				
			case "portalColumn":
				\ExtensionMaster::getInstance()->send404Error();
				die;
				break;
				
			case "portalPortlet":
				\ExtensionMaster::getInstance()->send404Error();
				die;
				break;
				
			case "userHome":
				//ok
				break;
			
			case "groupWorkroom":
				//ok
				break;
				
			case "room":
				//ok
				break;
				
			case "container":
				//ok
				break;
                        
                        case "map":
                            header("location: " . PATH_URL . "map/Index/" . $this->id . "/");
                            die;
                            break;
				
			case "unknown":
				\ExtensionMaster::getInstance()->send404Error();
				die;
				break;
		}
		
		$title = getCleanName($object);
		
		$parent = $object->get_environment();
		if ($parent instanceof \steam_container) {
			//$parentLink = array("name"=>"nach oben", "link"=>PATH_URL . "explorer/Index/" . $parent->get_id() . "/");
			$parentLink = "";
		} else {
			$parentLink = "";
		}
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array($parentLink, array("name" => "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($object)."\"></img> " . $title . " " . \Explorer\Model\Sanction::getMarkerHtml($object, false))));
		
		$this->getExtension()->addJS();
		$this->getExtension()->addCSS();
		
                
                //check sanctions
                $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
	        
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(!$envWriteable ?  : array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
                                                                    array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
                                                                    array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
                                            ));
                
                
		//$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));
		
		$presentation = $object->get_attribute("bid:presentation");
		$preHtml = "";
		if ($presentation === "head") {
			$objects = $object->get_inventory();
			if (count($objects) > 0) {
				$first = $objects[0];
				$mimetype = $first->get_attribute(DOC_MIME_TYPE);
				if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif") {
					// Image
					$preHtml = "<div style=\"text-align:center\"><img style=\"max-width:100%\" src=\"" . PATH_URL . "Download/Document/" . $first->get_id() . "/\"></div>";
				} else if ($mimetype == "text/html") {
					$rawContent = $first->get_content();
                                        //$preHtml = strip_tags($rawContent,"<h1><h2><h3><h4><h5><p><a><div><style><b><i><strong><img><hr><table><tr><th><td><ul><ol><li>");
                                        //$preHtml = $rawContent;
                                        $htmlDocument = new \HtmlDocument();
                                        $preHtml = $htmlDocument->makeViewModifications($rawContent);
                                        $preHtml = cleanHTML($preHtml);
                                } else if (strstr($mimetype, "text")) {
					$bidDokument = new \BidDocument($first);
                                        $preHtml = $bidDokument->get_content();
				}				
			}
		} else if ($presentation === "index" && !(isset($_GET["view"]) && ($_GET["view"] === "list"))) {
			$objects = $object->get_inventory();
			if (count($objects) > 0) {
				$first = $objects[0];
				$url = \ExtensionMaster::getInstance()->getUrlForObjectId($first->get_id(), "view");
				header("location: {$url}");
				exit;
			}
		}
		
                /*
                //make html output modifications
                $htmlDocument = new \HtmlDocument();
                $preHtml = $htmlDocument->makeViewModifications($preHtml);
                $preHtml = cleanHTML($preHtml);
                */
                
                if ($preHtml !== "") {
			$preHtml = "<div style=\"border-bottom: 1px solid #ccc; padding-bottom:10px; margin-bottom:10px\">{$preHtml}</div>";
		}
		
                
		$environment = new \Widgets\RawHtml();
		$environment->setHtml("{$preHtml}<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");
		
		$loader = new \Widgets\Loader();
		$loader->setWrapperId("explorerWrapper");
		$loader->setMessage("Lade Dokumente ...");
		$loader->setCommand("loadContent");
		$loader->setParams(array("id"=>$this->id));
		$loader->setElementId("explorerWrapper");
		$loader->setType("updater");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("<div id=\"explorerContent\">".$breadcrumb->getHtml().$environment->getHtml().$loader->getHtml()."</div>");
		
		$rawHtml->addWidget($breadcrumb);
		$rawHtml->addWidget($environment);
		$rawHtml->addWidget($loader);
	
		$frameResponseObject->setTitle($title);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>