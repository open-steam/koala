<?php
namespace TCR\Commands;
class PrivateDocuments extends \AbstractCommand implements \IFrameCommand {

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
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		// determine kind of documents of the current user to display (0 = theses, 1 = reviews, 2 = responses)
		$kindOfDocument = 0;
		if (isset($this->params[1])) {
			$kindOfDocument = $this->params[1];
		}
		
		// release document dialog was submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["release_element"])) {
			$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["element_id"]);
			if ($_POST["kind"] == 0) {
				$critics = array();
				$critics[$_POST["critic"]] = 0;
				$element->set_attribute("TCR_REVIEWS", $critics);
				$element->set_attribute("TCR_RELEASED", time());
			} else {
				$element->set_attribute("TCR_RELEASED", time());
			}
		}
		
		// edit document dialog was submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_element"])) {
			$old_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["old_id"]);
			// old element was plain text
			if ($old_element->get_attribute("DOC_MIME_TYPE") == "text/plain") {
				// new element is plain text
				if ($_POST["new_upload_text"] == 0) {
					$old_element->set_name($_POST["title"]);
					$old_element->set_attribute("OBJ_DESC", $_POST["desc"]);
					$old_element->set_content($_POST["content"]);
				// new element is an upload
				} else {
					$old_element->delete();
					$radio = 1;
				}
			// old element was an upload
			} else {
				// new element is the same
				if ($_POST["new_upload"] == 0) {
					$old_element->set_name($_POST["title"]);
					$old_element->set_attribute("OBJ_DESC", $_POST["desc"]);
				// new element is a new upload
				} else if ($_POST["new_upload"] == 1) {
					$old_element->delete();
					$radio = 1;
				// new element is plain text
				} else {
					$old_element->set_attribute("DOC_MIME_TYPE", "text/plain");
					$old_element->set_name($_POST["title"]);
					$old_element->set_attribute("OBJ_DESC", $_POST["desc"]);
					$old_element->set_content($_POST["new_content"]);
				}
			}
		}
		
		// if a new element got created or already existing element gets a new upload
		if (($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["create_element"]))) || isset($radio)) {
			$problems = "";
			$hints    = "";
			if (!isset($radio)) {
				$radio = $_POST["radio"];
			}
			if ($radio == 1) {
				// handle upload
				require_once( PATH_LIB . "format_handling.inc.php" );
				$max_file_size = parse_filesize(ini_get('upload_max_filesize'));
				$max_post_size = parse_filesize(ini_get('post_max_size'));
				if ($max_post_size > 0 && $max_post_size < $max_file_size) {
					$max_file_size = $max_post_size;
				}
				if (empty($_FILES) || (!empty( $_FILES["file"]["error"]) && $_FILES["file"]["error"] > 0)) {
		        	if (!empty($_FILES) && empty($_FILES["file"]["name"])) {
			            $problems = gettext( "No file chosen." ) . " ";
			            $hints = gettext( "Please choose a local file to upload." ) . " ";
		       		} else {
		            	$problems = gettext( "Could not upload document." ) . " ";
		            	$hints = str_replace(
		              		array("%SIZE", "%TIME"),
		              		array(readable_filesize($max_file_size), (string)ini_get('max_execution_time')),
		              			gettext("Maybe your document exceeded the allowed file size (max. %SIZE) or the upload might have taken too long (max. %TIME seconds).")
		            		) . " ";
		          	}
				}
				if (empty($problems)) {
					$content = file_get_contents($_FILES["file"]["tmp_name"]);
					$type = $_FILES["file"]["type"];
				}
			} else {
				$content = $_POST["content"];
				$type = "text/plain";
			}
			if ($_POST["kind"] == 0) {
				$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
			} else if ($_POST["kind"] == 1) {
				$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/reviews");
			} else {
				$container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/responses");
			}
			$title = $_POST["title"];
			$desc = $_POST["desc"];
			if (empty($problems)) {
				$new_element = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $title, $content, $type, $container, $desc);
				if ($_POST["kind"] == 0) {
					$new_element->set_attribute("TCR_ROUND", $_POST["round"]);
					$new_element->set_attribute("TCR_REVIEWS", array());
					$new_element->set_attribute("TCR_RELEASED", 0);
				} else if ($_POST["kind"] == 1) {
					$new_element->set_attribute("TCR_RELEASED", 0);
					$correspondingThesis = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["elementID"]);
					$critics_thesis = $correspondingThesis->get_attribute("TCR_REVIEWS");
					$critics_thesis[$user->get_id()] = $new_element->get_id();
					$correspondingThesis->set_attribute("TCR_REVIEWS", $critics_thesis);
				} else {
					$new_element->set_attribute("TCR_RELEASED", 0);
					$correspondingReview = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["elementID"]);
					$correspondingReview->set_attribute("TCR_RESPONSE", $new_element->get_id());
				}
			} else {
				$frameResponseObject->setProblemDescription($problems);
				$frameResponseObject->setProblemSolution($hints);
			}
		}
		
		// display actionbar
		$actionbar = new \Widgets\Actionbar();
		$admins = $TCR->get_attribute("TCR_ADMINS");
		if (in_array($user->get_id(), $admins)) {
			$actions = array(
				array("name" => "Konfiguration" , "link" => $TCRExtension->getExtensionUrl() . "configuration/" . $this->id),
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		} else {
			$actions = array(
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		}
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);
		
		$group = $TCR->get_attribute("TCR_GROUP");
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		$content = $TCRExtension->loadTemplate("tcr_privatedocuments.template.html");
		// display a message if current user is not a user of this tcr
		$members = $TCR->get_attribute("TCR_USERS");
		if (!in_array($user->get_id(), $members)) {
			$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE");
			$content->setVariable("DISPLAY_TABLE", "none");
			$content->setVariable("NOT_USER", "Sie sind nicht als Teilnehmer dieses Thesen-Kritik-Replik-Verfahrens eingetragen. Wenden Sie sich an einen Administrator.");
			$content->parse("BLOCK_DOCUMENTS_TABLE");
			
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml($content->get());
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array(
				array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Private Dokumente")
			));
			return $frameResponseObject;
		}
		
		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"Thesen", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id), 
			array("name"=>"Kritiken", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id . "/1"), 
			array("name"=>"Repliken", "link"=>$TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id . "/2")));
		$tabBar->setActiveTab($kindOfDocument);
		$frameResponseObject->addWidget($tabBar);
		
		// create array structure and add theses for their round
		$rounds = $TCR->get_attribute("TCR_ROUNDS");
		$theses_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $TCR->get_path() . "/theses");
		$theses_inventory = $theses_container->get_inventory();
		$theses = array();
		$theses_response = array();
		foreach ($theses_inventory as $thesis) {
			$current_round = $thesis->get_attribute("TCR_ROUND");
			if ($thesis->get_creator()->get_id() == $user->get_id()) {
				$theses[$current_round] = $thesis;
			}
			$critics = $thesis->get_attribute("TCR_REVIEWS");
			if (is_array($critics)) {
				if (array_key_exists($user->get_id(), $critics)) {
					$theses_response[$current_round] = $thesis;
				}
			}
		}
		
		// display private documents table
		$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE");
		if ($kindOfDocument == 0 || $kindOfDocument == 2) {
			$content->setVariable("THESES_LABEL", "Erstellte Thesen");
			$content->setVariable("REVIEWS_LABEL", "Erhaltene Kritiken");
			$content->setVariable("RESPONSES_LABEL", "Erstellte Repliken");
			if ($kindOfDocument == 0) {
				// thesis view
				for ($count = 1; $count <= $rounds; $count++) {
					$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
					$content->setVariable("ROUND_VALUE", "Runde " . $count);
					if (!array_key_exists($count, $theses)) {
						$content->setVariable("CREATE_THESIS", "These erstellen");
						$content->setVariable("THESIS_ICON", "create_32");
						$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "create/" . $this->id . "/" . $count . "/" . $kindOfDocument);
						$content->setVariable("DISPLAY_THESIS_SECOND", "none");
						$content->setVariable("DISPLAY_REVIEW", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
					} else {
						$current_critics = $theses[$count]->get_attribute("TCR_REVIEWS");
						if (count($current_critics) > 0) {
							$content->setVariable("CREATE_THESIS", "Anzeigen");
							$content->setVariable("THESIS_ICON", "view_32");
							$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "release/" . $theses[$count]->get_id());
							$content->setVariable("DISPLAY_THESIS_SECOND", "none");
							foreach ($current_critics as $critic => $review) {
								if ($review == 0) {
									$content->setVariable("DISPLAY_REVIEW", "none");
									$content->setVariable("DISPLAY_RESPONSE", "none");
									$review_released = 0;
								} else {
									$current_review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $review);
									$review_released = $current_review->get_attribute("TCR_RELEASED");
								}
								if ($review_released != 0) {
									$content->setVariable("CREATE_REVIEW", "Anzeigen");
									$content->setVariable("REVIEW_ICON", "view_32");
									$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "release/" . $current_review->get_id());
									$content->setVariable("DISPLAY_REVIEW_SECOND", "none");
									$responseID = $current_review->get_attribute("TCR_RESPONSE");
									if ($responseID == 0) {
										$response_released = 0;
									} else {
										$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
										$response_released = $response_element->get_attribute("TCR_RELEASED");
									}
									if ($response_released == 0) {
										$content->setVariable("DISPLAY_RESPONSE", "none");
									} else {
										$content->setVariable("CREATE_RESPONSE", "Anzeigen");
										$content->setVariable("RESPONSE_ICON", "view_32");
										$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "release/" . $response_element->get_id());
										$content->setVariable("DISPLAY_RESPONSE_SECOND", "none");
									}
								} else {
									$content->setVariable("DISPLAY_REVIEW", "none");
									$content->setVariable("DISPLAY_RESPONSE", "none");
								}
							}
						} else {
							$content->setVariable("CREATE_THESIS", "Anzeigen / Bearbeiten");
							$content->setVariable("THESIS_ICON", "view_32");
							$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "edit/" . $theses[$count]->get_id());
							$content->setVariable("THESIS_ICON2", "release_32");
							$content->setVariable("THESIS_URL2", $TCRExtension->getExtensionUrl() . "release/" . $theses[$count]->get_id());
							$content->setVariable("RELEASE_THESIS", "Veröffentlichen");
							$content->setVariable("DISPLAY_REVIEW", "none");
							$content->setVariable("DISPLAY_RESPONSE", "none");
						}
					}
					$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
					$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				}
			// response view
			} else {
				for ($count = 1; $count <= $rounds; $count++) {
					$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
					$content->setVariable("ROUND_VALUE", "Runde " . $count);
					if (!array_key_exists($count, $theses)) {
						$content->setVariable("DISPLAY_THESIS", "none");
						$content->setVariable("DISPLAY_REVIEW", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
					} else {
						$current_critics = $theses[$count]->get_attribute("TCR_REVIEWS");
						if (count($current_critics) > 0) {
							$content->setVariable("CREATE_THESIS", "Anzeigen");
							$content->setVariable("THESIS_ICON", "view_32");
							$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "release/" . $theses[$count]->get_id());
							$content->setVariable("DISPLAY_THESIS_SECOND", "none");
							foreach ($current_critics as $critic => $review) {
								if ($review == 0) {
									$review_released = 0;
								} else {
									$current_review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $review);
									$review_released = $current_review->get_attribute("TCR_RELEASED");
								}
								if ($review_released != 0) {
									$content->setVariable("CREATE_REVIEW", "Anzeigen");
									$content->setVariable("REVIEW_ICON", "view_32");
									$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "release/" . $current_review->get_id());
									$content->setVariable("DISPLAY_REVIEW_SECOND", "none");
									$responseID = $current_review->get_attribute("TCR_RESPONSE");
									if ($responseID == 0) {
										$content->setVariable("CREATE_RESPONSE", "Replik erstellen");
										$content->setVariable("RESPONSE_ICON", "create_32");
										$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "create/" . $this->id . "/" . $count . "/" . $kindOfDocument . "/" . $current_review->get_id());
										$content->setVariable("DISPLAY_RESPONSE_SECOND", "none");
									} else {
										$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
										$response_released = $response_element->get_attribute("TCR_RELEASED");
										if ($response_released == 0) {
											$content->setVariable("CREATE_RESPONSE", "Anzeigen / Bearbeiten");
											$content->setVariable("RESPONSE_ICON", "view_32");
											$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "edit/" . $response_element->get_id());
											$content->setVariable("RESPONSE_ICON2", "release_32");
											$content->setVariable("RESPONSE_URL2", $TCRExtension->getExtensionUrl() . "release/" . $response_element->get_id());
											$content->setVariable("RELEASE_RESPONSE", "Veröffentlichen");
										} else {
											$content->setVariable("CREATE_RESPONSE", "Anzeigen");
											$content->setVariable("RESPONSE_ICON", "view_32");
											$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "release/" . $response_element->get_id());
											$content->setVariable("DISPLAY_RESPONSE_SECOND", "none");
										}
									}
								} else {
									$content->setVariable("DISPLAY_REVIEW", "none");
									$content->setVariable("DISPLAY_RESPONSE", "none");
								}
							}
						} else {
							$content->setVariable("DISPLAY_THESIS", "none");
							$content->setVariable("DISPLAY_REVIEW", "none");
							$content->setVariable("DISPLAY_RESPONSE", "none");
						}
					}
					$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
					$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				}
			}
		// review view
		} else {
			$content->setVariable("THESES_LABEL", "Erhaltene Thesen");
			$content->setVariable("REVIEWS_LABEL", "Erstellte Kritiken");
			$content->setVariable("RESPONSES_LABEL", "Erhaltene Repliken");
			for ($count = 1; $count <= $rounds; $count++) {
				$content->setCurrentBlock("BLOCK_DOCUMENTS_TABLE_ELEMENT");
				$content->setVariable("ROUND_VALUE", "Runde " . $count);
				if (!array_key_exists($count, $theses_response)) {
					$content->setVariable("DISPLAY_THESIS", "none");
					$content->setVariable("DISPLAY_REVIEW", "none");
					$content->setVariable("DISPLAY_RESPONSE", "none");
				} else {
					$content->setVariable("CREATE_THESIS", "Anzeigen");
					$content->setVariable("THESIS_ICON", "view_32");
					$content->setVariable("THESIS_URL", $TCRExtension->getExtensionUrl() . "release/" . $theses_response[$count]->get_id());
					$content->setVariable("DISPLAY_THESIS_SECOND", "none");
					$critics_array = $theses_response[$count]->get_attribute("TCR_REVIEWS");
					if ($critics_array[$user->get_id()] == 0) {
						$content->setVariable("CREATE_REVIEW", "Kritik erstellen");
						$content->setVariable("REVIEW_ICON", "create_32");
						$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "create/" . $this->id . "/" . $count . "/" . $kindOfDocument . "/" . $theses_response[$count]->get_id());
						$content->setVariable("DISPLAY_REVIEW_SECOND", "none");
						$content->setVariable("DISPLAY_RESPONSE", "none");
						$content->setVariable("DISPLAY_RESPONSE_SECOND", "none");
					} else {
						$review = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $critics_array[$user->get_id()]);
						$released = $review->get_attribute("TCR_RELEASED");
						if ($released == 0) {
							$content->setVariable("CREATE_REVIEW", "Anzeigen / Bearbeiten");
							$content->setVariable("REVIEW_ICON", "view_32");
							$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "edit/" . $review->get_id());
							$content->setVariable("REVIEW_ICON2", "release_32");
							$content->setVariable("REVIEW_URL2", $TCRExtension->getExtensionUrl() . "release/" . $review->get_id());
							$content->setVariable("RELEASE_REVIEW", "Veröffentlichen");
							$content->setVariable("DISPLAY_RESPONSE", "none");
						} else {
							$content->setVariable("CREATE_REVIEW", "Anzeigen");
							$content->setVariable("REVIEW_ICON", "view_32");
							$content->setVariable("REVIEW_URL", $TCRExtension->getExtensionUrl() . "release/" . $review->get_id());
							$content->setVariable("DISPLAY_REVIEW_SECOND", "none");
							$responseID = $review->get_attribute("TCR_RESPONSE");
							if ($responseID == 0) {
								$response_released = 0;
							} else {
								$response_element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $responseID);
								$response_released = $response_element->get_attribute("TCR_RELEASED");
							}
							if ($response_released == 0) {
								$content->setVariable("DISPLAY_RESPONSE", "none");
							} else {
								$content->setVariable("CREATE_RESPONSE", "Anzeigen");
								$content->setVariable("RESPONSE_ICON", "view_32");
								$content->setVariable("RESPONSE_URL", $TCRExtension->getExtensionUrl() . "release/" . $response_element->get_id());
								$content->setVariable("DISPLAY_RESPONSE_SECOND", "none");
							}
						}
					}
				}
				$content->setVariable("ASSETURL", $TCRExtension->getAssetUrl());
				$content->parse("BLOCK_DOCUMENTS_TABLE_ELEMENT");
			}
		}
		$content->parse("BLOCK_DOCUMENTS_TABLE");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Private Dokumente")
		));
		return $frameResponseObject;
	}
}
?>