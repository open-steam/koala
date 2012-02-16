<?php
namespace Portfolio\Commands;
class CreatePortfolio extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$description = strip_tags($this->params["desc"]);
		$name = strip_tags($this->params["name"]);
		$newPortfolio = \PortfolioModel::create($name, $description, "");

		$jswrapper->setJs(<<<END
		closeDialog();
END
		);
			
		$jswrapper = new \Widgets\JSWrapper();
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>