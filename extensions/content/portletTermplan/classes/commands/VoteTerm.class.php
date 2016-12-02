<?php
namespace PortletTermplan\Commands;
class VoteTerm extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		
		//get basic stuff
		$portletObjectId = $params["portletObjectId"];
		$termId = $params["termId"];
		
		$userName = \lms_steam::get_current_user()->get_name();
		
		//check diffrent types of parameter
		if(is_string($portletObjectId)){
			$portletObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$portletObjectId);
		}else{
			$portletObject = $parentObjectId;
		}

		//get content of steam object
		$content = $portletObject->get_attribute("bid:portlet:content");
		$optionsDescriptonArray = $content["options"];
		
		//inialize count of title
		if (!isset($content["voteUserMapping"])){
			$content["voteUserMapping"] = array();
		}
		
		//intialize mapping if not initialized
		if(!isset($content["voteUserMapping"])) {
			$content["voteUserMapping"] = json_encode(array());
		} 
	
		//decode mapping
		$encodedVoteUserMapping = $portletObject->get_attribute("termChoices");
		if($encodedVoteUserMapping=="0"){
			$mapping = array();
		} else {
			$mapping = json_decode($encodedVoteUserMapping,true);
		}
		
		if (!isset($mapping[$userName])) $mapping[$userName]=0;
		$mapping[$userName]=$this->changeVote($mapping[$userName], $termId);
		
		//write result
		$portletObject->set_attribute("bid:portlet:content", $content);
		$portletObject->set_attribute("termChoices", json_encode($mapping));
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
	
	}

	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}

	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
	
	
	/*
	 * edit encoding inside the mapping
	 * example termchoices:0:1:2:3:5
	 */
	private function changeVote($mappingEntry, $choice){
		$mappingEntryArray = explode(":",$mappingEntry);
		$choice = intval($choice);
		
		//create new string
		$newTermchoices="termchoices";
		for($i=0;$i<=5;$i++){
			if(array_search((String)$i,$mappingEntryArray)){
				if($i==$choice){
					$newTermchoices.="";
				}else{
					$newTermchoices.=":".$i;
				}
			}else{
				if($i==$choice){
					$newTermchoices.=":".$i;
				}else{
					$newTermchoices.="";
				}
			}
		}
		return $newTermchoices;
	}
	
}
?>