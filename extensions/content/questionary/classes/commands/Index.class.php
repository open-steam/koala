<?php
namespace Questionary\Commands;

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
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("Dieser Fragebogen wurde mit einer alten Version erstellt. Das alte Format wird leider nicht mehr unterstützt.");
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }
}
?>