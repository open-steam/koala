<?php
class InstallLicense implements Command {
	public function execute (Request $request, Response $response) {
		$license = $request->getParameter("license");
		
		$valid = licensemanager::get_instance()->add_license($license);
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "installLicense");
		if ($valid) {
			$result["state"] = "ok";
		} else {
			$result["state"] = "fail";
		}
		return $result;
	}
}
?>