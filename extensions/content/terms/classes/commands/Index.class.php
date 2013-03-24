<?php
namespace Terms\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
            $TermsExtension = \Terms::getInstance();
            $content = $TermsExtension->loadTemplate("index.template.html");
            $content->setCurrentBlock("BLOCK_TERMS_OF_USE");
            $content->setVariable("TERMS_URL_CHANGE", PLATFORM_USERMANAGEMENT_URL);
            $content->setVariable("TERMS_SYSTEM", PLATFORM_TITLE);
            $content->setVariable("TERMS_URL_DOWNLOAD", $TermsExtension->getAssetUrl() . "Nutzungsordnung.pdf");
            $content->parse("BLOCK_TERMS_OF_USE");
            
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml($content->get());
            $frameResponseObject->addWidget($rawWidget);
            return $frameResponseObject;
	}
}
?>