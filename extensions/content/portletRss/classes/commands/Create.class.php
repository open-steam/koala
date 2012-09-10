<?php
namespace PortletRss\Commands;
class Create extends \AbstractCommand implements \IAjaxCommand {
	
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
		$column = $params["id"];
                
                if(isset($params["html"])){
                    $html = $params["html"];
                }else{
                    $html = "false";
                }
		
		$version = "3.0";
		
		//check diffrent types of parameter
		if(is_string($column)){
			$columnObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$column);
		}else{
			$columnObject = $column;
		}
		
		
		//default values
		$address = $params["rss"];               
		$num_items = "5";
		$desc_length = "50";
		$style = "message"; //Breit
		$style = "rss_feed"; //Schmal
		if($html){
                    $allow_html = "checked";		
                }else{
                    $allow_html = "";
                }
                
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