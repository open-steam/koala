<?php
class GetMenu implements ICommand {
	function execute ($request, $response) {
		$content = new HTML_TEMPLATE_IT();
		$content->loadTemplateFile(Explorer::getInstance()->getExtensionPath() . "ui/html/menu.template.html" );
		
		$response->setStatus("200 OK");
		$response->write("Test");
		return $response;
	}
}
?>