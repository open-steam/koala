<?php
class SetEncryptKey implements Command {
	public function execute (Request $request, Response $response) {
		
		$key = $request->getParameter("key");
		licensemanager::get_instance()->set_encrypt_key($key);
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "setEncryptKey");
		$result["state"] = "ok";
			
		return $result;
	}
}
?>