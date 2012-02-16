<?php
namespace PortletRss\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		//create portlet
		$params = $requestObject->getParams();
		
		$name = $params["title"];
		$column = $params["parent"];
		
		$version = "3.0";
		
		//check diffrent types of parameter
		if(is_string($column)){
			$columnObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$column);
		}else{
			$columnObject = $column;
		}
		
		
		//default values
		$address = "http://api.flickr.com/services/feeds/photoset.gne?set=72157603709124069&nsid=12597119@N03&lang=de-de&format=rss_200";
		$address = "http://www.lehrer-online.de/rss-materialien.xml";
		$num_items = "5";
		$desc_length = "50";
		$style = "message"; //Breit
		$style = "rss_feed"; //Schmal
		$allow_html = "checked";
		
		//create object
		$portletObject = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $columnObject);
		
	    $portletContent = array(
			"address" => $address,
			"num_items" => $num_items,
			"desc_length" => $desc_length,
			"style" => $style,
			"allow_html" => $allow_html
		);
	    
	    $portletObject->set_attributes(array(
	        OBJ_DESC => $name,
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "rss",
	        "bid:portlet:version" => $version,
	        "bid:portlet:content" => $portletContent,
	    ));
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
}
?>