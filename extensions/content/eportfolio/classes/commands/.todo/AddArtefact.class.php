<?php
/*
 * wizard like setting competences for artefacts
 */
namespace Portfolio\Commands;
class AddArtefact extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $portfolioId;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->portfolioId = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		if (!$this->portfolioId){
			print "no object id given!";
			exit;
		}
		$portfolio = \PortfolioModel::getById($this->portfolioId);

		$loader = new \Widgets\Loader();
		$loader->setWrapperId("artefactsWrapper");
		$loader->setMessage("loading artefacts ...");
		$loader->setCommand("loadArtefacts");
		$loader->setParams(array($this->portfolioId));
		$loader->setElementId("artefactsWrapper");
		$loader->setType("updater");
		
		$html .= <<<END
<script type="text/javascript">
$(':checkbox').change(function() {
   sendRequest("UpdateSelectedArtefacts", {"artefactId": "{$this->artefactId}", "portfolio": "{$this->portfolioId}", "checked": $(this).prop("checked")}, "", "data");
});
</script>
END
		;
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->addWidget($loader);
		return $frameResponseObject;
	}
}

?>
