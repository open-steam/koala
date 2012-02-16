<?php
class GetEncryptKey implements Command {
	public function execute(Request $request, Response $response) {
		$key = licensemanager::get_instance()->get_encrypt_key();
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "getEncryptKey");
		$result["state"] = "ok";
		$result["encryptKey"] = $key;
		return $result;
	}
}
?>