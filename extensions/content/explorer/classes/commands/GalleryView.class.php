<?php

namespace Explorer\Commands;

class GalleryView extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	private $filter;
	private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
			$this->params = $requestObject->getParams();
			if (isset($this->params[0])) {
					$intVal = intval($this->params[0]);
					if ($intVal !== 0) {
							$this->id = $intVal;
					} else {
							$this->id = "";
							if (strpos($this->params[0], "filter=") !== false) {
									$this->filter = substr($this->params[0], 7);
							} else {
									$this->filter = "";
							}
					}
			}
			if (isset($this->params[1])) {
					if (strpos($this->params[1], "filter=") !== false) {
							$this->filter = substr($this->params[1], 7);
					} else {
							$this->filter = "";
					}
			}
	}

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

			if (isset($this->id)) {

					$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
					if ($object instanceof \steam_exit) {
							$object = $object->get_exit();
							$this->id = $object->get_id();
					}
			} else {
					$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
					$object = $currentUser->get_workroom();
					$this->id = $object->get_id();

					if (defined("DELETE_GROUP_HOME_EXITS") && DELETE_GROUP_HOME_EXITS && $object->get_attribute("DELETED_GROUP_HOME_EXITS") == "0") {
							$inventory = $object->get_inventory_filtered(array(
									array('+', 'class', CLASS_EXIT),
							));
							foreach ($inventory as $element) {
									$exitElement = $element->get_exit();
									if ($exitElement instanceof \steam_room && $exitElement->get_creator() instanceof \steam_group) {
											$element->delete();
									}
							}
							$object->set_attribute("DELETED_GROUP_HOME_EXITS", 1);
					}
			}

			if (!$object instanceof \steam_object) {
					\ExtensionMaster::getInstance()->send404Error();
			}

			$objectModel = \AbstractObjectModel::getObjectModel($object);

			if ($object && $object instanceof \steam_container) {

					$count = $object->count_inventory();
					if ($count > 500) {
							throw new \Exception("Es befinden sich $count Objekte in diesem Ordner. Das Laden ist nicht möglich.");
					}
					try{
					$objects = $object->get_inventory();
					}catch(\NotFoundException $e) {\ExtensionMaster::getInstance()->send404Error();}
					catch (\AccessDeniedException $e) {throw new \Exception("", E_USER_ACCESS_DENIED);}

			} else {
					$objects = array();
			}

			$objectType = getObjectType($object);
			switch ($objectType) {
					case "document":
							header("location: " . PATH_URL . "explorer/ViewDocument/" . $this->id . "/");
							die;
							break;

					case "forum":
							header("location: " . PATH_URL . "forum/Index/" . $this->id . "/");
							die;
							break;

					case "referenceFolder":
							$exitObject = $object->get_exit();
							header("location: " . PATH_URL . "explorer/Index/" . $exitObject->get_id() . "/");
							die;
							break;

					case "referenceFile":
							$linkObject = $object->get_link_object();

							if (($linkObject === NULL) || !($linkObject instanceof \steam_object)) {

									\ExtensionMaster::getInstance()->send404Error();
							}
							header("location: " . PATH_URL . "explorer/Index/" . $linkObject->get_id() . "/");
							die;
							break;

					case "user":
							header("location: " . PATH_URL . "user/Index/" . $object->get_name() . "/");
							die;
							break;

					case "group":
							\ExtensionMaster::getInstance()->send404Error();
							break;

					case "trashbin":
							\ExtensionMaster::getInstance()->send404Error();
							break;

					case "portal_old":
							$rawHtml = new \Widgets\RawHtml();
							$frameResponseObject->addWidget($rawHtml);
							$frameResponseObject->setProblemDescription("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden.");
							$frameResponseObject->setProblemSolution("Bitte umwandeln.");
							return $frameResponseObject;
							break;

					case "gallery":
							header("location: " . PATH_URL . "gallery/Index/" . $this->id . "/");
							die;
							break;

					case "portal":
							header("location: " . PATH_URL . "portal/Index/" . $this->id . "/");
							die;
							break;

					case "portalColumn":
							\ExtensionMaster::getInstance()->send404Error();
							break;

					case "portalPortlet":
							\ExtensionMaster::getInstance()->send404Error();
							break;

					case "userHome":
							//ok
							break;

					case "groupWorkroom":
							//ok
							break;

					case "room":
							//ok
							break;

					case "container":
							//ok
							break;

					case "map":
							header("location: " . PATH_URL . "map/Index/" . $this->id . "/");
							die;
							break;

					case "unknown":
							\ExtensionMaster::getInstance()->send404Error();
							break;
			}

			//build breadcrumb
			$title = getCleanName($object, 65);
			$breadcrumbArray = array(array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($object) . "\" style=\"float: left;\"></img><p style=\"float:left; margin-top:0px; margin-left:5px; margin-right:5px;\">" . $title . "</p>"));
			$parent = $object->get_environment();
			while($parent instanceof \steam_container){
				$title = getCleanName($parent, 65);
				array_unshift($breadcrumbArray, array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($parent) . "\" style=\"float: left;\"></img><p style=\"float:left; margin-top:0px; margin-left:5px; margin-right:5px;\">" . $title . "</p>", "link" => PATH_URL . "explorer/index/" . $parent->get_id() . "/"));
				$parent = $parent->get_environment();
			}
			array_unshift($breadcrumbArray, "");
			$breadcrumb = new \Widgets\Breadcrumb();
			$breadcrumb->setData($breadcrumbArray);

			$this->getExtension()->addJS();
			$this->getExtension()->addCSS();

			//check sanctions
			$envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
			$envSanction = $object->check_access(SANCTION_SANCTION);

			$presentation = $object->get_attribute("bid:presentation");
			$preHtml = "";
			if ($presentation === "head") {
					$objects = $object->get_inventory();
					if (count($objects) > 0) {
							$first = $objects[0];
							$mimetype = $first->get_attribute(DOC_MIME_TYPE);
							if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif" || $mimetype == "image/svg+xml") {
									// Image
									$preHtml = "<div style=\"text-align:center\"><img style=\"max-width:100%\" src=\"" . PATH_URL . "Download/Document/" . $first->get_id() . "/\"></div>";
							} elseif ($mimetype == "text/html") {
									$rawContent = $first->get_content();
									$htmlDocument = new \HtmlDocument();
									$preHtml = $htmlDocument->makeViewModifications($rawContent, $object, true);
									$preHtml = cleanHTML($preHtml);
							} elseif (strstr($mimetype, "text")) {
									$bidDokument = new \BidDocument($first);
									$preHtml = $bidDokument->get_content();
							}
					}
			} elseif ($presentation === "index" && !(isset($_GET["view"]) && ($_GET["view"] === "list"))) {
					$objects = $object->get_inventory();
					if (count($objects) > 0) {
							$first = $objects[0];
							$url = \ExtensionMaster::getInstance()->getUrlForObjectId($first->get_id(), "view");
							header("location: {$url}");
							die;
					}
			}

			if ($preHtml !== "") {
					$preHtml = "<div style=\"border-bottom: 1px solid #ccc; padding-bottom:10px; margin-bottom:10px\">{$preHtml}</div>";
			}

			$description = new \Widgets\RawHtml();
			if(isUserHome($object)){
				$desc = "";
			}
			else{
				$desc = $object->get_attribute("OBJ_DESC");
			}
			$description->setHtml("<p style='margin-top:0px; color:#AAAAAA; clear:both; float:left;'>" . $desc . "</p>");

			$environment = new \Widgets\RawHtml();
			$environment->setHtml("{$preHtml}<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");

			$selectAll = new \Widgets\RawHtml();
			$selectAll->setHtml("<div id='selectAll' style='float:right; margin-right:22px;'><p style='float:left; margin-top:1px;'>Alle auswählen: </p><input onchange='elements = jQuery(\".galleryEntry.show > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'></div>");

			$loader = new \Widgets\Loader();
			$loader->setWrapperId("explorerWrapper");
			$loader->setMessage("Lade Objekte...");
			$loader->setCommand("loadGalleryContent");
			$loader->setParams(array("id" => $this->id));
			$loader->setElementId("explorerWrapper");
			$loader->setType("updater");

			$slider = new \Widgets\RawHtml();
			$slider->setHtml('<script>$(function() {$("#slider").slider({
				value:5,
				min: 1,
				max: 12,
				step: 1,
				slide: function(event, ui) {
					$("#objectSlider").val(ui.value);
					$(".galleryEntry").removeClass("Row1 Row2 Row3 Row4 Row5 Row6 Row7 Row8 Row9 Row10 Row11 Row12").addClass("Row" + ui.value);
				}
			});
			$("#objectSlider").val($("#slider").slider("value"))});</script><p id="objectSliderLabel" style="margin-top: 20px;float: right;margin-right: -130px;"><label for="objectSlider">Objekte pro Zeile:</label><input type="text" id="objectSlider" readonly style="border:0; color:#f6931f; font-weight:bold; width:30px;"></p><div id="slider"></div>');

			$rawHtml = new \Widgets\RawHtml();
			$rawHtml->setHtml("<div id=\"explorerContent\">" . $breadcrumb->getHtml() . $description->getHtml() . $environment->getHtml() . $selectAll->getHtml() . $slider->getHtml() . $loader->getHtml() . "</div>");

			$script = "function initSort(){";
			foreach ($objects as $o) {
					if (getObjectType($o) !== "trashbin") {
							$script .= "$('#" . $o->get_id() . "').attr('onclick', '');
							$('#" . $o->get_id() . "').attr('onmouseover', '');
							$('#" . $o->get_id() . "').attr('onmouseout', '');
							$('#" . $o->get_id() . "_1').unbind('mouseenter mouseleave');    ";
					}
			}
			$assetUrl = \Explorer::getInstance()->getAssetUrl() . "images/sort_horizontal.png";
			$script .= '
					$("#sort-icon").attr("name", "true");
					$("#sort-icon").parent().bind("click", function(){$(this).css("background-color", "#CCCCCC");
});
					var newIds = "";
					$("#explorerGallery").sortable();
					$("#explorerGallery").disableSelection();
					$("#explorerGallery").bind("sortupdate", function(event, ui){
							var changedElement = $(ui.item).attr("id");
							$("#explorerGallery").children().each(function(index, value){
									if(index == $("#explorerGallery").children().length-1) newIds += value.id;
									else newIds += value.id + ", ";
								});
							sendRequest("Sort", {"changedElement": changedElement, "id": $("#environment").attr("value"), "newIds":newIds }, "", "data", function(response){ }, function(response){ }, "explorer");
							newIds = "";
					});
					$("#content").prepend("<div style=\"margin-left:380px;position:absolute;height:35px;width:180px;background-image:url(' . $assetUrl . ');\"></div>");

	}';

			$rawHtml->setJs($script);
			$rawHtml->setPostJsCode('$($(".popupmenuanker")[0]).css("margin-top", "3px");');

			$inventory = $object->get_inventory();
			$keywordmatrix = array();
			foreach ($inventory as $inv) {
					$keywordmatrix[] = $inv->get_attribute("OBJ_KEYWORDS");
			}
			$kwList = array();
			foreach ($keywordmatrix as $kwRow) {
					foreach ($kwRow as $element) {
							$kwList[] = $element;
					}
			}

			$popupMenuSearch = new \Widgets\PopupMenu();
			$popupMenuSearch->setCommand("GetPopupMenuSearch");
			$popupMenuSearch->setNamespace("Explorer");
			$popupMenuSearch->setData($object);
			$popupMenuSearch->setElementId("search-area-popupmenu");

			if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {
					$searchField = new \Widgets\Search();
					$searchField->setId("searchfield");
					$searchField->setAutocomplete($kwList);
					$searchField->setPopupMenu($popupMenuSearch);
					$searchField->setValue($this->filter);
					$searchField->setGalleryView();
			}

			$frameResponseObject->setTitle($title);
			if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {
					$frameResponseObject->addWidget($searchField);
			}

			//$frameResponseObject->addWidget($actionBar);
			$frameResponseObject->addWidget($rawHtml);

			return $frameResponseObject;
    }

}

?>
