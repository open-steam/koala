<?php
namespace Wiki\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if(isset($this->params[0])){
			if(strpos($this->params[0], '?') !== false) {
					$temp = explode('?', $this->params[0]);
					if(sizeof($temp) > 0){
						$this->id = intval($temp[0]);
					}
			}
			else{
				$this->id = $this->params[0];
			}
		}
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$objectType = $wiki_container->get_attribute("OBJ_TYPE");
		if($objectType != "0" && $objectType == "container_wiki_koala"){
			$create = TRUE;
		} else {
			$wiki_doc = $wiki_container;
			$wiki_container = $wiki_doc->get_environment();
			$create = FALSE;
		}

		if (isset($_GET["title"])) {
			$values[ "title" ] = $_GET["title"];
		}

		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addJS();
		$WikiExtension->addCSS();
		$content = $WikiExtension->loadTemplate("wiki_edit.template.html");

		if (!($wiki_container->check_access_write())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("Das Wiki kann nicht angezeigt werden, da Sie nicht über die erforderlichen Schreibrechte verfügen.");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		$wiki_entries = $wiki_container->get_inventory(CLASS_DOCUMENT);
		foreach ($wiki_entries as $wiki_entry) {
			if ($wiki_entry->get_attribute(DOC_MIME_TYPE) === "text/wiki") {
				$name = $wiki_entry->get_name();
				$content->setCurrentBlock("BLOCK_WIKI_ENTRY_OPTION");
				$content->setVariable("WIKI_ENTRY_OPTION", "<option value=\"$name\">$name</option>");
				$content->parse("BLOCK_WIKI_ENTRY_OPTION");
		  }
		}

		$problems = "";

		if (!isset($create)) $create = FALSE;

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
		    $values = $_POST["values"];
		    if (get_magic_quotes_gpc()) {
					if (!empty($values['title'])) $values['title'] = stripslashes($values['title']);
					if (!empty($values['body'])) $values['body'] = stripslashes($values['body']);
		    }

		    //Check if already exists
		    $lw = new \koala_wiki($wiki_container);
		    if($lw->contains_item($values['title']) && $create){
					$problems = "Es existiert bereits ein Eintrag mit diesem Namen. Bitte wählen Sie einen anderen.";
		    }

		    if (empty($values["title"])) $problems = gettext("Please enter a subject for your message.");
		    if (empty($values["body"])) $problems .= ( empty($problems)) ? gettext("Please enter your message.") : "<br>" . gettext("Please enter your message.");

		    if (strpos($values["title"], "/")) {
					if (!isset($problems)) $problems = "";
					$problems .= gettext("Please don't use the \"/\"-char in the subject of your post.");
		    }

		    if (empty($problems)) {
					$wiki_content = str_replace("@", "&#64;", $values["body"]);

					if (!empty($values['save'])) {
				    if ($create) {
							$wiki_doc = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $values["title"] . ".wiki", $wiki_content, "text/wiki", $wiki_container, "");

							if (ENABLED_SEARCH & WIKI_SEARCH_ENABLED) {
						    $indexer = new \wiki_indexer($wiki_container->get_id());
						    $indexer->add_new_document($wiki_doc->get_id(), $values["title"], "");
							}
						} else {
							// PRUEFEN, OB ALLES OK, DANN NEUE WERTE SPEICHERN
							$wiki_doc->set_name($values['title'] . ".wiki");
							$wiki_doc->set_content($wiki_content);

							if (ENABLED_SEARCH & WIKI_SEARCH_ENABLED) {
						    $indexer = new \wiki_indexer($wiki_container->get_id());
						    $indexer->update_changed_document($wiki_doc->get_id(), "", "", $wiki_doc);
							}
						}
				    // Clean cache for wiki_entries
				    $cache = get_cache_function($wiki_container->get_id(), 600);
				    $cache->clean($wiki_container->get_id());

				    // clean rsscache
				    $rcache = get_cache_function("rss", 600);
				    $feedlink = PATH_URL . "wiki/RSS/" . $wiki_container->get_id();
				    $rcache->drop("lms_rss::get_items", $feedlink);

				    header("Location: " . PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/");
				    die;
				} else {
				    // PREVIEW
				    $content->setCurrentBlock("BLOCK_PREVIEW");
				    $content->setVariable("LABEL_PREVIEW_EDIT", gettext("Preview the description"));
				    $content->setVariable("PREVIEW_EDIT", get_formatted_output($values["desc"]));
				    $content->parse("BLOCK_PREVIEW");
				    $headline = gettext("Change it?");
				    $content->setCurrentBlock();
				    $content->setVariable("TEXT_DSC", h($values["desc"]));
				    $content->setVariable("TITLE_COMMENT", h($values["title"]));
				}
		  } else {
				$portal->set_problem_description($problems);
		  }
		}

		if (empty($values)) {
		    $wikicontent = "";
		    $wikiname = "";
		    if (!$create) {
					$wikicontent = $wiki_doc->get_content();
					$wikicontent = str_replace("&#64;", "@", $wikicontent);
					$wikiname = $wiki_doc->get_name();
		    }
            $content->setCurrentBlock();
		    if (WIKI_WYSIWYG) {
					//TODO
					$content->setVariable("TEXT_DSC", h($wikicontent));
		    } else {
					$content->setVariable("TEXT_DSC", h($wikicontent));
		    }
		    	$content->setVariable("TITLE_COMMENT", str_replace(".wiki", "", h($wikiname)));
		} else {
		    $content->setVariable("TITLE_COMMENT", h($values["title"]));
		    if (isset($values["body"])) $content->setVariable("TEXT_DSC", h($values["body"]));
		}

		$content->setVariable("LABEL_TITLE", gettext("Title"));
		$content->setVariable("LABEL_BODY", gettext("Body"));
		$content->setVariable("ANNOTATION_IMAGE_URL", PATH_URL . "/wiki/asset/icons/comment_small.gif");
		$content->setVariable("LABEL_SAVE_CHANGES", gettext("Save changes"));

		$content->setVariable("WIKI_CON_ID", $wiki_container->get_id());

		// widget: Images
		$widget = $WikiExtension->loadTemplate("widget_wiki_images.template.html");
		$inventory = $wiki_container->get_inventory();
		if (!is_array($inventory)) $inventory = array();
		if (sizeof($inventory) > 0) {
		    \steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $inventory, array(OBJ_NAME, OBJ_DESC, DOC_MIME_TYPE));
		    $images = array();
		    foreach ($inventory as $object) {
					$mime = strtolower($object->get_attribute(DOC_MIME_TYPE));
					if ($mime === "image/jpg" || $mime === "image/jpeg" || $mime === "image/gif" || $mime === "image/png") $images[] = $object;
		    }
		    if (empty($images)) {
					$content->setCurrentBlock("BLOCK_WIKI_ENTRY_NOIMAGE");
					$content->setVariable("WIKI_ENTRY_NOIMAGE", "Es befinden sich keine Bilder in der Mediathek.");
					$content->parse("BLOCK_WIKI_ENTRY_NOIMAGE");
		    } else {
					$i = 0;
					foreach ($images as $image) {
				    $path = PATH_URL . "download/image/" . $image->get_id() . "/40/80";
				    $content->setCurrentBlock("BLOCK_WIKI_ENTRY_IMAGE");
				    $content->setVariable("WIKI_ENTRY_IMAGE", <<< END
<table style="float:left">
	<tr>
		<td>
			<input id="image$i" type="radio" name="images" value="{$image->get_name()}"/>
		</td>
		<td>
			<img src="$path" title="{$image->get_name()}">
		</td>
	</tr>
</table>
END
				    );
				    $content->parse("BLOCK_WIKI_ENTRY_IMAGE");
				    $i++;
					}
		    }
		}

		if ($create) {
		    $pagetitle = gettext("New Article");
		} else {
		    $pagetitle = str_replace("%NAME", h(substr($wiki_doc->get_name(), 0, -5)), gettext("Edit '%NAME'"));
		}

		(WIKI_FULL_HEADLINE) ?
				$headline = array(
			    $rootlink[0],
			    $rootlink[1],
			    array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
			    array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
			    array("link" => "", "name" => $pagetitle)
				) :
				$headline = array(
			    array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
			    array("link" => "", "name" => $pagetitle));

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		$pollingDummy = new \Widgets\PollingDummy();
		$frameResponseObject->addWidget($pollingDummy);
		return $frameResponseObject;
	}
}
?>
