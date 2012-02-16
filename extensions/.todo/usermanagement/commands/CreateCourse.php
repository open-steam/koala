<?php
class CreateCourse implements Command {
	public function execute (Request $request, Response $response) {
		$jsonParams = $request->getParameter("jsonParams");
		$params = json_decode($jsonParams);
		$success = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createCourse($params->id, $params->courseID, $params->customerID);
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "createCourse");
		if ($success) {
			$result["state"] = "ok";
		} else {
			$result["state"] = "fail";
		}
		return $result;
	}
}
?>