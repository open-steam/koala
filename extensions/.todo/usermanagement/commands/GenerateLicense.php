<?php
class GenerateLicense implements Command {
	public function execute (Request $request, Response $response) {
		$jsonParams = $request->getParameter("jsonParams");
		$params = json_decode($jsonParams);
		$license = licensemanager::get_instance()->generate_license($params->customerID, $params->courseID, $params->seats, $params->expiredate);
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "generateLicense");
		$result["state"] = "ok";
		$result["license"] = $license;
		return $result;
	}
}
?>