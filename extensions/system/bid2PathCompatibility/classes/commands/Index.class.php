<?php
namespace bid2PathCompatibility\Commands;
class Index extends \AbstractCommand implements \IFrameCommand{
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
        
        }
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
                $requestUrl = urldecode($_SERVER['REQUEST_URI']);
                
                //not implemented
                if(strpos($requestUrl, "index.php?object?=/")){
                    $this->redirectToSteamPath("/");
                }
                
                //not tested
                if(strpos($requestUrl, "index.php?object?=")){
                    $searchString = "index.php?object?=";
                    $begin = strpos($requestUrl, $searchString) + strlen($searchString);
                    $this->redirectToObjectId(substr($requestUrl,$begin));
                }
                
                //not implemented
                if(strpos($requestUrl, "home")){
                    $this->redirectToSteamPath("/");
                }
            
                //not implemented
                if(strpos($requestUrl, "hilfe")){
                    $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "schulen")){
                        $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "lernstatt_intern")){
                        $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "/externe_partner/")){
                        $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "/schulen/")){
                        $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "/dialog/")){
                        $this->redirectToSteamPath("/");
                }
            
                //not implemented
                if(strpos($requestUrl, "/partner/")){
                        $this->redirectToSteamPath("/");
                }
                
                //not implemented
                if(strpos($requestUrl, "/projekte/")){
                        $this->redirectToSteamPath("/");
                }
            
            
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Test bid2PathCompatibility ".$requestUrl);
                $frameResponseObject->addWidget($rawWidget);
                return $frameResponseObject;
	}
        
        
        /*
         * redirects to the extension/object with a steam path
         */
        private function redirectToSteamPath($steamPath){
                $objectId = $this->getObjectId($steamPath);
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
		header("Location: ".$url); //error
                die;
        }
        
        
        /*
         * redirects to the extension/object with a object id
         */
        private function redirectToObjectId($objectId){
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
		header("Location: ".$url); //error
                die;
        }
        
        
        
        /*
         * gets a objectId to a steam path
         */
        private function getObjectId($path){
            $object = \steam_factory::path_to_object($GLOBALS["STEAM"], $path);
            return $object->get_id();
        }
}
?>