<?php
namespace Mplme\Commands;
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
		$frameResponseObject->setTitle("Mplme");
		$frameResponseObject = $this->getHtmlForObjectId($frameResponseObject);
		return $frameResponseObject;
	}
	/*
	 * returns html content - gallery view
	 *
	 * @objectId gallery id
	 * @from
	 */
	public function getHtmlForObjectId(\FrameResponseObject $frameResponseObject){
		$mplme = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		if (isset($this->params[1])) {
			$doc = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $mplme->get_path() . "/" . $this->params[1]);
			if (isset($doc) && $doc !== 0 && $doc instanceof \steam_document) {
				echo $doc->download();
				//echo $doc->get_content();
			} else {
				echo "404";
			}
			die;
		}

		
		
		$elements = $mplme->get_inventory();
		$html = "";
		foreach ($elements as $element) {
			if ($element->get_name() == "data.xml") {
				$xmlContent = $element->get_content();
				//$html .= "<b>".$element->get_name()."</b><br><pre>". htmlentities($element->get_content()) . "</pre><br>";
			} else if ($element->get_name() == "web_table.xsl") {
				$xslContent = $element->get_content();
				//$html .= "<b>".$element->get_name()."</b><br><pre>". htmlentities($element->get_content()) . "</pre><br>";
			}else {
				//$html .= "<b>".$element->get_name()."</b><br>";
			}
		}
		
		if (isset($xmlContent) && isset($xslContent)) {
			$xml = new \DOMDocument();
			$xml->loadXML($xmlContent);
			
			$searchstring = '/<includexslt[ 0-9a-zA-Z=\/\\"]*url="([~\-0-9a-zA-Z=\:\?\&\.\/]+)"[ 0-9a-zA-Z=\/"]*[ \-0-9a-zA-Z=\/"]*\/>/i';
			$xslContent = preg_replace_callback($searchstring, array(&$this, 'cb_replace'), $xslContent);
			
			$xsl = new \DOMDocument();
			$xsl->loadXML($xslContent);
			
			$proc = new \XSLTProcessor();
			$proc->importStylesheet($xsl);
			
			$html .= $proc->transformToXml($xml);
		}

		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline(array(array("name" => "zurück", "link" => "javascript:history.back()"), array("name" => "Mplme")));
		return $frameResponseObject;

	}
	
	private function cb_replace($matches){
	   return file_get_contents($matches[1]);
	}

}
?>