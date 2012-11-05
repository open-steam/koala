<?php
namespace Spreadsheets\Commands;
class Debug extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand  {
	
	private $params;
	private $document;
	private $NodeServer = SPREADSHEETS_RT_SERVER;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0])) {
			$this->id = $this->params[0];
			$this->document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			//$docContents = file_get_contents("http://$this->NodeServer/doc/$docId");
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		//$debug = file_get_contents("http://$this->NodeServer/doc/exists/$this->id");
		//$debug = $this->document->get_name();
		//$debug = $this->document->get_attribute("RT_EDIT");
		$debug = $this->document->get_content();
		//$debug = $this->document->check_access_read($user);
		//$debug = $GLOBALS["STEAM"]->get_login_user_name();
		/*$content = '{"x":"1"}';
		$opts = array('http' =>
		    array(
		        'method'  => 'PUT',
		        'header'  => 'Content-type: application/json',
		        'content' => $content
		    )
		);
		$context = stream_context_create($opts);
		$debug = file_get_contents("http://$this->NodeServer/doc/set/$this->id", false, $context);*/
		/*$debug = sizeof($this->document->get_attributes());
		foreach ($this->document->get_attributes() as $m) {
			$debug .= "<br>" . $m ;
		}*/

		//$debug = file_get_contents("http://$this->NodeServer/doc/$this->id");
		//$debug = "Courses." . $this->params[0] . "." . $this->params[1];
		/*$group_course = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->params[0] . "." . $this->params[1]);
		$group = new \koala_group_course( $group_course );
		$debug = $group_course instanceof \steam_group;
		$members = $group->get_learners();

		foreach ($members as $m) {
			//foreach ($m->get_members() as $u) {
			//$user_name = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $m->get_id());
			//$debug .= "<br>" . $user_name ;
			$debug .= "<br>" . $m->get_attribute("OBJ_NAME");
			$debug .= "   " . $m instanceof \steam_user;
		//}
		}*/

		/*if ($response) {
			$this->document->set_content($response);
		}*/

		//$user = $GLOBALS["STEAM"]->get_current_steam_user();
		//$debug = $this->document->check_access_write($user);

		echo $debug;
		die;

		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($debug);
		$frameResponseObject->setTitle("Tabellen");
		$frameResponseObject->setHeadline("Tabellen");
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml("<b>hallo</b>");	
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
	
	
}
?>