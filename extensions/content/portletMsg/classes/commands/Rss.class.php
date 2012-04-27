<?php
namespace PortletMsg\Commands;
class Rss extends \AbstractCommand implements \IFrameCommand, \IIdCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
        
        public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
                $params = $requestObject->getParams();
		$objectId=$params[0];
		$portlet = $portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		
                $steam = $GLOBALS["STEAM"];
                
                $steamUser = $steam->get_current_steam_user();
                $configWebserverIp = "";
                
                
		//icon
		$referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";
		
		//reference handling
		if(isset($params["referenced"]) && $params["referenced"]==true){
                    $portletIsReference = true;
                    $referenceId = $params["referenceId"];
		}else{
                    $portletIsReference = false;
		}
		
		
		$portletName = $portlet->get_attribute(OBJ_DESC);
		
		//hack
		include_once(PATH_BASE."core/lib/bid/slashes.php");
		
		//get content of portlet
		$content = $portlet->get_attribute("bid:portlet:content");
		if(is_array($content) && count($content) > 0){
                    array_walk($content, "_stripslashes");
		} else {
                    $content = array();
		}
		
		$portletInstance = \PortletMsg::getInstance();
		$portletPath = $portletInstance->getExtensionPath();
		
		$UBB = new \UBBCode();
		include_once(PATH_BASE."core/lib/bid/derive_url.php");
		
		

                if( !$steam || !$steam->get_login_status() ) {
                    echo("*** Login fehlgeschlagen! ***<br>");
                    exit();
                }
 
                
                if ($portletObject->check_access_read($steamUser)) {
                    $feedDescription = $portletObject->get_name();
                    $feedLink = getDownloadUrlForObjectId($portletObject->get_id());
                    
                    // Get inventory and store all relevant attributes in array entries
                    $inventory = $portletObject->get_inventory();
                    $rssItems = array();
                    
                    //collect data for feed
                    foreach ($inventory as $item) {
                        if ($item->get_attribute("DOC_MIME_TYPE") == "text/plain") {
                            $itemTitle = '<title>' . $item->get_name() . '</title>';

                            $itemContent = $item->get_content();
                            $itemImage = $item->get_attribute("bid:portlet:msg:picture_id");
                            
                            if ($itemImage){
                                $itemContent = $itemContent . '<div><img src="' . getDownloadUrlForObjectId($itemImage) . '" /></div>';
                            }

                            $itemDescription = '<description><![CDATA[' . $itemContent . ']]></description>';

                            $itemLink = $item->get_attribute("bid:portlet:msg:link_url");
                            if ($itemLink == ' '){
                                $itemLink = $feedLink;
                            }
                            $itemLink = '<link>' . $feedLink . '</link>';

                            $lastchanged = $item->get_attribute(DOC_LAST_MODIFIED);
                            if ($lastchanged === 0) {
                                $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
                            }
                            $itemPubDate = '<pubDate>' . strftime("%a, %d %b %Y %H:%M:%S GMT", $lastchanged) . '</pubDate>';

                            // $author = '<author>' . $item->get_attribute("OBJ_OWNER") . '</author>';
                            $itemGuid = '<guid>' . $configWebserverIp . '/index.php?object=' . $item->get_id() . '</guid>';

                            array_push ($rssItems, $itemTitle . $itemDescription . $itemLink . $itemPubDate . $itemGuid);
                        }
                    }

                    
                    //create feed
                    header('Content-Type: text/xml');
                    header('Cache-Control: private');
                    header('Cache-Control: must-revalidate');
                    header("Pragma: public");
                    header('Connection: close');
                    header("Content-Disposition: inline; filename=rss_feed.rss");
                    
                    echo "<?xml version='1.0' encoding='utf-8'?>\n";
                    echo "<rss version='2.0'>\n";
                    echo "<channel>\n";
                    echo "<title>" . $feedTitle . "</title>\n";
                    echo "<description>" . $feedDescription . "</description>\n";
                    echo "<link>" . $feedLink . "</link>\n";
                    echo "<generator>PHPsTeam/bid-owl 2.0</generator>\n";
                    echo "<ttl>60</ttl>\n";

                    echo "<image><url>" . $configWebserverIp . "/icons/bid_Logo_neu.gif</url>\n";
                    echo "<title>" . $feedTitle . "</title>\n";
                    echo "<description>" . $feedDescription . "</description>\n";
                    echo "<link>" . $feedLink . "</link>\n";
                    echo "</image>\n";

                    foreach ($rssItems as $item){
                        echo "<item>" . $item . "</item>\n";
                    }
                    
                    echo "</channel>\n";
                    echo "</rss>\n";
                    exit;
                }
                else {
                    echo "The access rights of the requested object do not allow you to read it.";
                    exit;
                }

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
	

	//get message ids in container order
	//not used
	private function getMessageIds($messageContainer){
		$realMessageIds = array();
		$inventory = $messageContainer->get_inventory();
		
		foreach ($inventory as $steamObject) {
			$docType = $steamObject->get_attribute("DOC_MIME_TYPE");
			if($docType=="text/plain"){
				$realMessageIds[]=$steamObject->get_id();
			}else{
				//continue;
			}
			
		}
		
		//repair old portals
		$messageContainer->set_attribute("content", $inventory);
		return $realMessageIds;
	}
	
	//not used
	private function getImagePath($id, $portlet=""){
		if($portlet!=""){
			$inventory = $portlet->get_inventory();
			foreach ($inventory as $object) {
				//TODO: return url by name
			}
		}
		return getDownloadUrlForObjectId($id);
	}
}
?>