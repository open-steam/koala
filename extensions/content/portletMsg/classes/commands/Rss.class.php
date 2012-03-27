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
                //var_dump($requestObject);
                
                //$objectId=$requestObject->getId();
		//$portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		$params = $requestObject->getParams();
		$objectId=$params[0];
		$portlet = $portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		
                $steam = $GLOBALS["STEAM"];
                
                $steamUser = $steam->get_current_steam_user();
                $config_webserver_ip = "";
                
                //var_dump($objectId);
                //$object = $portletObject;
                
                
                
		//icon
		$referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";
		
		//reference handling
		if(isset($params["referenced"]) && $params["referenced"]==true){
			$portletIsReference = true;
			$referenceId = $params["referenceId"];
		}else{
			$portletIsReference = false;
		}
		
		
		$this->getExtension()->addCSS();
		$this->getExtension()->addJS();
		
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
		
		$portletFileName=$portletPath."/ui/html/index.html";
		$tmpl = new \HTML_TEMPLATE_IT();
		$tmpl->loadTemplateFile($portletFileName);
		$tmpl->setVariable("PORTLET_ID",$portlet->get_id());
		
                
                
                //other stuff
                
                
                $htmlBody=$tmpl->get();
		$this->content=$htmlBody;
                
                
                // ------old stuff ---------


                //$object = (int) (isset($_GET["object"]))?trim($_GET["object"]):0;
                //$object = $portletObject->get_id();
                
                //login und $steam def. in "./includes/login.php"
                //$steam = new steam_connector(	$config_server_ip, $config_server_port, $login_name, $login_pwd);

                if( !$steam || !$steam->get_login_status() ) {
                    echo("*** Login fehlgeschlagen! ***<br>");
                    exit();
                }

                //$UBB = new UBBCode();

                // if ID has been properly specified => download rss feed by calling /scripts/rss.pike and deliver output
                
                
                /*
                if( (int) $object != 0 )
                    $current_room = steam_factory::get_object( $steam, $object );
                else {
                    echo "This script can only render a valid RSS feed if you provide a valid ObjectID.<br/>";
                    echo "Example: " . $config_webserver_ip . "/modules/portal2/portlets/msg/rss.php?object=" . $steamUser->get_workroom()->get_id();
                    exit;
                }
                */
                
                
                
                
                if (true || $current_room->check_access_read($steam->get_login_user())) {
                    
                    
                    /*
                    // Get room's attributes and store them
                    if ($current_room->get_attribute("OBJ_DESC")){
                        $feed_title = $current_room->get_attribute("OBJ_DESC");   
                    }else{
                        $feed_title = $current_room->get_name();
                    }
                    */
                    
                    //$feed_description = $feed_title;
                    $feed_description = "feed_title"; //TODO
                    
                    
                    
                    //$feed_link = $config_webserver_ip . $current_room->get_environment()->get_environment()->get_path();
                    $feed_link = "localhost/PortletMsg/Rss/1520";
                    
                    

                    // Get inventory and store all relevant attributes in array entries
                    $inventory = $portletObject->get_inventory();
                    $rss_items = array();
                    
                    //collect data for feed
                    foreach ($inventory as $item) {
                        if ($item->get_attribute("DOC_MIME_TYPE") == "text/plain") {
                            $item_title = '<title>' . $item->get_name() . '</title>';

                            $item_content = $item->get_content();
                            $item_image = $item->get_attribute("bid:portlet:msg:picture_id");
                            
                            if ($item_image){
                                $item_content = $item_content . '<div><img src="' . $config_webserver_ip . '/tools/get.php?object=' . $item_image . '" /></div>';
                            }

                            $item_description = '<description><![CDATA[' . $item_content . ']]></description>';

                            $item_link = $item->get_attribute("bid:portlet:msg:link_url");
                            if ($item_link == ' '){
                                $item_link = $feed_link;

                            }
                            $item_link = '<link>' . $feed_link . '</link>';

                            $lastchanged = $item->get_attribute(DOC_LAST_MODIFIED);
                            if ($lastchanged === 0) {
                                $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
                            }
                            $item_pubDate = '<pubDate>' . strftime("%a, %d %b %Y %H:%M:%S GMT", $lastchanged) . '</pubDate>';

                            // $author = '<author>' . $item->get_attribute("OBJ_OWNER") . '</author>';
                            $item_guid = '<guid>' . $config_webserver_ip . '/index.php?object=' . $item->get_id() . '</guid>';

                            array_push ($rss_items, $item_title . $item_description . $item_link . $item_pubDate . $item_guid);
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
                    echo "<title>" . $feed_title . "</title>\n";
                    echo "<description>" . $feed_description . "</description>\n";
                    echo "<link>" . $feed_link . "</link>\n";
                    echo "<generator>PHPsTeam/bid-owl 2.0</generator>\n";
                    echo "<ttl>60</ttl>\n";

                    echo "<image><url>" . $config_webserver_ip . "/icons/bid_Logo_neu.gif</url>\n";
                    echo "<title>" . $feed_title . "</title>\n";
                    echo "<description>" . $feed_description . "</description>\n";
                    echo "<link>" . $feed_link . "</link>\n";
                    echo "</image>\n";

                    foreach ($rss_items as $item){
                        echo "<item>" . $item . "</item>\n";
                    }
                    
                    echo "</channel>\n";
                    echo "</rss>\n";

                }
                else {
                    echo "The access rights of the requested object do not allow you to read it.";
                    exit;
                }

                //Logout & Disconnect
                //$steam->disconnect();

                // end old stuff
                
                
		//widgets
		$outputWidget = new \Widgets\RawHtml();
		//$outputWidget->addWidget(new \Widgets\PopupMenu());
		$outputWidget->setHtml("<div> Ein Rss feed <div>");
		$this->rawHtmlWidget = $outputWidget;
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