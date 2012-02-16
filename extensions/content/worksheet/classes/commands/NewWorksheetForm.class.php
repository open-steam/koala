<?php
namespace Worksheet\Commands;
class NewWorksheetForm extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
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
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("NewWorksheet");
		$ajaxForm->setSubmitNamespace("Worksheet");
		$html='<table width="100%">';
		$html.='<tr><td>Name des neuen Arbeitblattes:</td> <td><input name="name"></td></tr>';
		$html.='<tr><input type="hidden" name="id" value="'.$this->id. '"></tr>';
		$html.='</table>';
		
		$ajaxForm->setHtml(<<<END
		{$html}
				
END
	);
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;

	
	}

}
?>