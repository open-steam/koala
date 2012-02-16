<?php
namespace Worksheet\Commands;
class CopyList extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$tpl = new \Worksheet\Template($this->id);



		
		$worksheet = new \Worksheet\Worksheet($this->id);

		$worksheet->validateRole("view");
		
		
		$tplData = Array();
		

		$copies = $worksheet->getEditCopiesList();
		
		if ($copies) {
		
			foreach ($copies as $copy) {

				$usrObj = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $copy['user']);

				$maxScore = $copy['worksheet']->getMaxScore();

				if ($maxScore === false) {
					$score = false;
				} else {
					
					if ($copy['worksheet']->getMaxScore() == 0) {
						$p = false;
					} else {
						$p = round($copy['worksheet']->getScore()*100/$copy['worksheet']->getMaxScore(), 1);
						if ($p < 0) {
							$p = 0;
						}
					}
					
					$score = Array(
						"score" => $copy['worksheet']->getScore(),
						"percent" => $p,
						"max" => $maxScore
					);
				}
				
				$picId = $usrObj->get_attribute("OBJ_ICON")->get_id();
				$img = PATH_URL."download/image/".$picId."/40/60/";

				$tplData[] = Array(
					"user" => Array(
						"name" => $usrObj->get_name(),
						"fullname" => $usrObj->get_attribute(USER_FULLNAME),
						"firstname" => $usrObj->get_attribute(USER_FIRSTNAME),
						"id" => $copy['user'],
						"img" => $img
					),
					"worksheet" => Array(
						"id" => $copy['worksheet']->getId(),
						"name" => $copy['worksheet']->getName(),
						"status" => $copy['worksheet']->getStatus()
					),
					"score" => $score
				);

			}
		
		} else {
			$tplData = false;
		}

		$tpl->assign("data", $tplData);
		
		$tpl->display("CopyList.template.html");
				
				


		
		/* template output */
		$tpl->parse($frameResponseObject);

		/* page title */
		$frameResponseObject->setTitle($worksheet->getName());
		
		$frameResponseObject->setHeadline(array(
			array("name" => $worksheet->getName(), "link" => PATH_URL."worksheet/View/".$this->id),
			array("name" => "Arbeitsblätter korrigieren")
		));
		
		
		return $frameResponseObject;
		
	}
}
?>