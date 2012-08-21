<?php
namespace PortletMedia\Commands;
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
		$portletInstance = \PortletMedia::getInstance();
		$portletPath = $portletInstance->getExtensionPath();
		$portlet = $portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		
		//icon
		$referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";
		
		//reference handling
		$params = $requestObject->getParams();
		if(isset($params["referenced"]) && $params["referenced"]==true){
			$portletIsReference = true;
			$referenceId = $params["referenceId"];
		}else{
			$portletIsReference = false;
		}
		
		//hack
		include_once(PATH_BASE."core/lib/bid/slashes.php");
		
		
		//get content of portlet
		$content = $portlet->get_attribute("bid:portlet:content");
		if(is_array($content) && count($content) > 0){
			array_walk($content, "_stripslashes");
		} else {
			$content = array();
		}
		
		if(sizeof($content) > 0){
			$portletFileName=$portletPath."/ui/html/index.html";
			$tmpl = new \HTML_TEMPLATE_IT();
			$tmpl->loadTemplateFile($portletFileName);
			
			//popupmenu
			if(!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
				$popupmenu = new \Widgets\PopupMenu();
				$popupmenu->setData($portlet);
				$popupmenu->setNamespace("PortletMedia");
				$popupmenu->setElementId("portal-overlay");
				$tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
			}
			
			if($portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
				$popupmenu = new \Widgets\PopupMenu();
				$popupmenu->setData($portlet);
				$popupmenu->setNamespace("Portal");
				$popupmenu->setElementId("portal-overlay");
				$popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
											array("key" => "linkObjectId", "value" => $referenceId)
											));
				$popupmenu->setCommand("PortletGetPopupMenuReference");
				$tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
			}
			
			$tmpl->setVariable("EDIT_BUTTON","");
			$tmpl->setVariable("PORTLET_ID",$portlet->get_id());
			$tmpl->setVariable("HEADLINE",$content["headline"]);
			
			//refernce icon
			if($portletIsReference){
                                $envId = $portlet->get_environment()->get_environment()->get_id();
                                $envUrl = PATH_URL . "portal/index/" . $envId;
				$tmpl->setVariable("REFERENCE_ICON","<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
			}
			
			
                        //description
                        if($content["description"]===0){
                            $tmpl->setVariable("DESCRIPTION","");
			}else{
                            $tmpl->setVariable("DESCRIPTION",$content["description"]);
			}
                        	
		
			$media_type = $content["media_type"];
                        
                        
                        //determine youtube video
                        $isYoutubeVideo = false;
                        $mediaArray = $portlet->get_attribute("bid:portlet:content");
                        if( strpos($mediaArray["url"], "youtube")){
                            $isYoutubeVideo = true;
                        }
                        
			if ($media_type == "image"){
				$tmpl->setCurrentBlock("image");
                                $tmpl->setVariable("URL",$content["url"]);
				$tmpl->parse("image");
			}
			else if ($media_type == "movie" && !$isYoutubeVideo) {
                                $tmpl->setCurrentBlock("movie");
                                $mediaplayerHtml = new \Widgets\Videoplayer();
                                
                                $column = $portlet->get_environment();
                                $columnWidth = intval($column->get_attribute("bid:portal:column:width"));
                                
                                $mediaplayerHtml->setHeight(intval(($columnWidth-10)/4*3));
                                $mediaplayerHtml->setWidth($columnWidth-10);
                                
                                $mediaArray = $portlet->get_attribute("bid:portlet:content");
                                $mediaplayerHtml->setTarget($mediaArray["url"]);
                                $tmpl->setVariable("MEDIA_PLAYER", $mediaplayerHtml->getHtml());
                                $tmpl->parse("movie");
			}
                        
                        else if ($media_type == "movie" && $isYoutubeVideo) {
                                $tmpl->setCurrentBlock("movieYoutube");
                                $mediaArray = $portlet->get_attribute("bid:portlet:content");
                                //$tmpl->setVariable("YOUTUBE_URL", $mediaArray["url"]);
                                
                                $url = $mediaArray["url"];
                                $youTubeUrlCode = "";
                                
                                $column = $portlet->get_environment();
                                $columnWidth = intval($column->get_attribute("bid:portal:column:width"));
                                
                                $tmpl->setVariable("MEDIA_PLAYER_WIDTH", $columnWidth-10);
				$tmpl->setVariable("MEDIA_PLAYER_HEIGHT", intval(($columnWidth-10)/4*3));
				
                                //case watch
                                if (strpos($url, "watch")){
                                    $begin = strpos($url, "watch?v=")+8;
                                    $lenght = strpos(substr($url, $begin),"&");
                                    if($lenght){
                                        $youTubeUrlCode = substr($url, $begin, $lenght);
                                    }
                                    else{
                                        $youTubeUrlCode = substr($url, $begin);
                                    }
                                }
                                
                                //case embed
                                else if(strpos($url, "embed")){
                                    $begin = strpos($url, "/embed/")+7;
                                    $lenght = strpos(substr($url, $begin),'"');
                                    $youTubeUrlCode = substr($url, $begin, $lenght);
                                }
                                
                                $tmpl->setVariable("YOUTUBE_URL_CODE", $youTubeUrlCode);
                                $tmpl->parse("movieYoutube");
			}
                        
			else if ($media_type == "audio") {
				$tmpl->setCurrentBlock("audio");
				$width = str_replace(array("px", "%"), "", $portlet->get_environment()->get_attribute("bid:portal:column:width")) - 10;
				$media_player = $portletInstance->getAssetUrl() . 'emff_lila_info.swf';
				$tmpl->setVariable("MEDIA_PLAYER", $media_player);
				$tmpl->setVariable("MEDIA_PLAYER_WIDTH", $width);
				$tmpl->setVariable("MEDIA_PLAYER_HEIGHT", round($width * 11/40));
				$tmpl->parse("audio");
			}
			if ($portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
				$tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON");
				$tmpl->setVariable("PORTLET_ID_EDIT",$portlet->get_id());
				$tmpl->parse("BLOCK_EDIT_BUTTON");
			}
		
			//output
			$htmlBody=$tmpl->get();
		}
		else{
			//output for no content
			$htmlBody="";
		}
		$this->content=$htmlBody;
		
		//widgets
		$outputWidget = new \Widgets\RawHtml();
		$outputWidget->setHtml($htmlBody);
		
		//popummenu
		$popupmenu = new \Widgets\PopupMenu();
		$popupmenu->setData($portlet);
		$popupmenu->setNamespace("PortletMedia");
		$popupmenu->setElementId("portal-overlay");
		$outputWidget->addWidget($popupmenu);
		
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
	
}
?>