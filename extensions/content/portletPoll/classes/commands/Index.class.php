<?php
namespace PortletPoll\Commands;
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
		
		$portletInstance = \PortletPoll::getInstance();
		$portletPath = $portletInstance->getExtensionPath();
		
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
		
		$portlet= $portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
		$portletName = $portlet->get_attribute(OBJ_DESC);
		
		$this->getExtension()->addCSS();
		$this->getExtension()->addJS();
		
		//hack
		include_once(PATH_BASE."/core/lib/bid/slashes.php");
		
		
		//get content of portlet
		$content = $portlet->get_attribute("bid:portlet:content");
		if(is_array($content) && count($content) > 0){
			array_walk($content, "_stripslashes");
		} else {
			$content = array();
		}
		
		$UBB = new \UBBCode();
		include_once(PATH_BASE."core/lib/bid/derive_url.php");
		  	  	
		$portletFileName=$portletPath."/ui/html/index.html";
		$tmpl = new \HTML_TEMPLATE_IT();
		$tmpl->loadTemplateFile($portletFileName);
		
		if(sizeof($content) > 0){
			//popupmenu
			if (!$portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
				$popupmenu = new \Widgets\PopupMenu();
				$popupmenu->setData($portlet);
				$popupmenu->setNamespace("PortletPoll");
				$popupmenu->setElementId("portal-overlay");
				$tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
			}
			
			if ($portletIsReference && $portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
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
			
			
			
			$startDate = $content["start_date"];
			$end_date = $content["end_date"];
		
			if (time() > mktime(0, 0, 0, $startDate["month"], $startDate["day"], $startDate["year"]) && time() < mktime(24, 0, 0, $end_date["month"], $end_date["day"], $end_date["year"])){
				$pollActive = true;	
			}else{
		  		$pollActive = false;
			}
		
			$options = $content["options"];
			$options_votecount = $content["options_votecount"];
		
			$max_votecount = 1;
			foreach($options_votecount as $option_votecount){
				if ($option_votecount > $max_votecount) $max_votecount = $option_votecount;
			}
		
			$tmpl->setVariable("PORTLET_ID",$portlet->get_id());
			$tmpl->setVariable("POLL_NAME",$portletName);
			
			//refernce icon
			//refernce icon
			if($portletIsReference){
                                $envId = $portlet->get_environment()->get_environment()->get_id();
                                $envUrl = PATH_URL . "portal/index/" . $envId;
				$tmpl->setVariable("REFERENCE_ICON","<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
			}
				
			$tmpl->setVariable("POLL_TOPIC",$content["poll_topic"]);
		
			if($pollActive){
				$i=0;
				foreach($options as $option){
					if($option != ""){
						$tmpl->setCurrentBlock("choice");
						$tmpl->setVariable("OPTION",$option);
						$tmpl->setVariable("OPTION_NUMBER",$i);
						//create command params
						$tmpl->setVariable("portletObjectId",$portlet->get_id());
						$tmpl->setVariable("voteItemId",$i);
						$tmpl->parse("choice");
					}
					$i++;
				}
			}else{
				$i=0;
				foreach($options as $option){
					$tmpl->setCurrentBlock("BLOCK_VOTE_RESULT");
					if ($option != "") {
						$tmpl->setVariable("OPTION",$option);
						$tmpl->setVariable("OPTION_VOTECOUNT",$options_votecount[$i]);
						$tmpl->setVariable("OPTION_NUMBER",$i);
						$tmpl->setVariable("PATH_COLOR",PATH_URL);
						$percentage = $options_votecount[$i] / $max_votecount * 100;
						$percentage = round($percentage);
						if ($percentage<1) {$percentage=1;}
						$tmpl->setVariable("WIDTH",$percentage);
						$tmpl->parse("BLOCK_VOTE_RESULT");
					}
					$i++;
				}
			}
		
			// we show the edit button only if the user has write access to the portal
			// because all portal readers need write access in order to vote
			$portalCol = $portlet->get_environment();
			$portal = $portalCol->get_environment();
			
			if ($portal->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())){
				$tmpl->setCurrentBlock("BLOCK_EDIT_BUTTON");
				$tmpl->setVariable("PORTLET_ID_EDIT",$portlet->get_id());
				$tmpl->parse("BLOCK_EDIT_BUTTON");
			}
			
		}
		
		$htmlBody = $tmpl->get();
		$this->content=$htmlBody;
		
		//widgets
		$outputWidget = new \Widgets\RawHtml();
		
		//popummenu
		$outputWidget->addWidget(new \Widgets\PopupMenu());
		
		$outputWidget->setHtml($htmlBody);
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