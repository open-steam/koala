<?php
namespace Mplme\Commands;
class Upload extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	
	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$mplme = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$frameResponseObject->setTitle("Mplme");
		
		if (isset($_REQUEST["xmlData"])) {
			$xmlDoc = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $mplme->get_path() . "/data.xml");
			if ($xmlDoc === 0 || !($xmlDoc instanceof  \steam_document)) {
				$xml = simplexml_load_string("<datasets></datasets>"); 
				$xmlDoc = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "data.xml", $xml->asXML(), "text/xml");
				$xmlDoc->move($mplme);
			} else {
				$xmlRaw = $xmlDoc->get_content();
				$xml = simplexml_load_string($xmlRaw);
			}
			$xmlRequest = simplexml_load_string($_REQUEST["xmlData"]);
			$xmlRequestDom = new \domDocument;
			$xmlRequestDom->loadXML($xmlRequest->asXML());
			
			$xmlDom = new \domDocument;
			$xmlDom->loadXML($xml->asXML());
			$xmlDom->documentElement->appendChild($xmlDom->importNode($xmlRequestDom->documentElement, true));
			
			if ($_FILES && is_array($_FILES)) {
				foreach ($_FILES as $file) {
					$content = file_get_contents($file["tmp_name"]);
					$doc = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $file["name"], $content, $file["type"]);
					$doc->move($mplme);
				}
			}
			
			$xmlDoc->set_content($xmlDom->saveXML());
			//echo "<pre>" . htmlentities($xmlDom->saveXML()) . "</pre>";
			echo "ok";
		} else {
			echo "nix";
		}
		die;
		//echo "done";
		//$frameResponseObject->addWidget(new \Widgets\Blank());
		//return $frameResponseObject;
	}

}
?>