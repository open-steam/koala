<?php

include_once( PATH_LIB . "wiki_handling.inc.php" );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile(PATH_TEMPLATES . "wiki_edit.template.html");

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

if (!isset($create))
    $create = FALSE;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $values = $_POST["values"];
    if (get_magic_quotes_gpc()) {
	if (!empty($values['title']))
	    $values['title'] = stripslashes($values['title']);
	if (!empty($values['body']))
	    $values['body'] = stripslashes($values['body']);
    }
    
    //Check if already exists
    $lw = new koala_wiki($wiki_container);
    if($lw->contains_item($values['title']) && $create){
		$problems = gettext('Enter an other name. This wiki already exists.');
    }

    if (empty($values["title"]))
	$problems = gettext("Please enter a subject for your message.");
    if (empty($values["body"]))
	$problems .= ( empty($problems)) ? gettext("Please enter your message.") : "<br>" . gettext("Please enter your message.");

    if (strpos($values["title"], "/")) {
	if (!isset($problems))
	    $problems = "";
	$problems .= gettext("Please don't use the \"/\"-char in the subject of your post.");
    }

    if (empty($problems)) {
	$wiki_content = str_replace("@", "&#64;", $values["body"]);

	if (!empty($values['save'])) {
	    if ($create) {
		$wiki_doc = steam_factory::create_document(
				$GLOBALS["STEAM"]->get_id(), $values["title"] . ".wiki", $wiki_content, "text/wiki", $wiki_container, ""
		);

		if (ENABLED_SEARCH & WIKI_SEARCH_ENABLED) {
		    $indexer = new wiki_indexer($wiki_container->get_id());
		    $indexer->add_new_document($wiki_doc->get_id(), $values["title"], "");
		}
	    } else {
		// PRUEFEN, OB ALLES OK, DANN NEUE WERTE SPEICHERN
		$wiki_doc->set_name($values['title'] . ".wiki");
		$wiki_doc->set_content($wiki_content);

		if (ENABLED_SEARCH & WIKI_SEARCH_ENABLED) {
		    $indexer = new wiki_indexer($wiki_container->get_id());
		    $indexer->update_changed_document($wiki_doc->get_id(), "", "", $wiki_doc);
		}
	    }
	    // Clean cache for wiki_entries
	    $cache = get_cache_function($wiki_container->get_id(), 600);
	    $cache->clean($wiki_container->get_id());

	    // clean rsscache
	    $rcache = get_cache_function("rss", 600);
	    $feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wiki_container->get_id();
	    $rcache->drop("lms_rss::get_items", $feedlink);

	    header("Location: " . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/");
	    exit;
	} else {
	    // PREVIEW
	    $content->setCurrentBlock("BLOCK_PREVIEW");
	    $content->setVariable("LABEL_PREVIEW_EDIT", gettext("Preview the description"));
	    $content->setVariable("PREVIEW_EDIT", get_formatted_output($values["desc"]));
	    $content->parse("BLOCK_PREVIEW");
	    $headline = gettext("Change it?");
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
    if (WIKI_WYSIWYG) {
	//TODO
	$content->setVariable("TEXT_DSC", h($wikicontent));
	//$content->setVariable( "TEXT_DSC", wikitext_to_html( h($wikicontent), $wiki_container->get_id() ) );
    } else {
	$content->setVariable("TEXT_DSC", h($wikicontent));
    }
    $content->setVariable("TITLE_COMMENT", str_replace(".wiki", "", h($wikiname)));
    $content->setVariable("BACK_LINK", $_SERVER["HTTP_REFERER"]);
} else {
    $content->setVariable("TITLE_COMMENT", h($values["title"]));
    if (isset($values["body"]))
	$content->setVariable("TEXT_DSC", h($values["body"]));
}

$content->setVariable("LABEL_HERE_IT_IS", "");
$content->setVariable("LABEL_TITLE", gettext("Title"));
$content->setVariable("LABEL_BODY", gettext("Body"));

//$content->setVariable( "LABEL_WIKI_H2", gettext( "H2" ) );
//$content->setVariable( "HINT_WIKI_H2", gettext( "heading (level 2)" ) );
//$content->setVariable( "LABEL_WIKI_H3", gettext( "H3" ) );
//$content->setVariable( "HINT_WIKI_H3", gettext( "heading (level 3)" ) );
//$content->setVariable( "LABEL_WIKI_BOLD", gettext( "'''B'''" ) );
//$content->setVariable( "HINT_WIKI_BOLD", gettext( "boldface" ) );
//$content->setVariable( "LABEL_WIKI_ITALIC", gettext( "''I''" ) );
//$content->setVariable( "HINT_WIKI_ITALIC", gettext( "italic" ) );
//$content->setVariable( "LABEL_WIKI_BULLET_LIST", gettext( "* list" ) );
//$content->setVariable( "HINT_WIKI_BULLET_LIST", gettext( "bullet list" ) );
//$content->setVariable( "LABEL_WIKI_NUMBERED_LIST", gettext( "# list" ) );
//$content->setVariable( "HINT_WIKI_NUMBERED_LIST", gettext( "numbered list" ) );
//$content->setVariable( "LABEL_WIKI_LINE", gettext( "-----" ) );
//$content->setVariable( "HINT_WIKI_LINE", gettext( "horizontal line" ) );
//$content->setVariable( "LABEL_WIKI_LINK", gettext( "[[wiki]]" ) );
//$content->setVariable( "HINT_WIKI_LINK", gettext( "wiki link" ) );
//$content->setVariable( "LABEL_WIKI_URL", gettext( "[URL]" ) );
//$content->setVariable( "HINT_WIKI_URL", gettext( "web link" ) );
//$content->setVariable( "LABEL_WIKI_IMAGE", gettext( "IMG" ) );
//$content->setVariable( "HINT_WIKI_IMAGE", gettext( "image" ) );

$content->setVariable("ANNOTATION_IMAGE_URL", PATH_SERVER . "/styles/standard/images/wiki/comment_small.gif");

$content->setVariable("LABEL_PREVIEW", gettext("Preview"));
$content->setVariable("LABEL_SAVE_CHANGES", gettext("Save changes"));
$content->setVariable("LABEL_RETURN", gettext("back"));
$content->setVariable("JS_NOTICE", '"' . gettext("Warning!\\nYou have edited your entry!\\nIf you proceed, all changes will be lost!\\nDo you really want to proceed?") . '"');

// widget: Images
$widget = new HTML_TEMPLATE_IT();
$widget->loadTemplateFile(PATH_TEMPLATES . "widget_wiki_images.template.html");
$inventory = $wiki_container->get_inventory();
if (!is_array($inventory))
    $inventory = array();
if (sizeof($inventory) > 0) {
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $inventory, array(OBJ_NAME, OBJ_DESC, DOC_MIME_TYPE));
    $images = array();
    foreach ($inventory as $object) {
	$mime = strtolower($object->get_attribute(DOC_MIME_TYPE));
	if ($mime === "image/jpg" || $mime === "image/jpeg" || $mime === "image/gif" || $mime === "image/png")
	    $images[] = $object;
    }
    if (empty($images)) {
	$content->setCurrentBlock("BLOCK_WIKI_ENTRY_NOIMAGE");
	$content->setVariable("WIKI_ENTRY_NOIMAGE", "Es befinden sich keine Bilder in der Mediathek.");
	$content->parse("BLOCK_WIKI_ENTRY_NOIMAGE");
    } else {
	$i = 0;
	foreach ($images as $image) {
	    $path = PATH_URL . "get_document.php?id=" . $image->get_id() . "&width=40&height=80";
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
	    //$widget->setCurrentBlock("BLOCK_IMAGE");
	    //$widget->setVariable("WIKI_IMAGE_NAME", $image->get_name());
	    //$widget->setVariable("WIKI_IMAGE_ADD_LINK", "javascript:insert('[[Image:" . $image->get_identifier() . "]]', '', 'formular', 'values[body]')");
	    //$widget->setVariable("WIKI_IMAGE_LINK", PATH_URL . "get_document.php?id=" . $image->get_id() . "&width=40&height=80");
	    //$widget->setVariable("WIKI_IMAGE_VIEW_LINK", PATH_URL . "doc/" . $image->get_id() . "/");
	    //$widget->setVariable("WIKI_IMAGE_TITLE", $image->get_name());
	    //$widget->setVariable("WIKI_IMAGE_ADD", gettext("Insert"));
	    //$widget->setVariable("WIKI_IMAGE_VIEW", gettext("View"));
	    //$widget->parse("BLOCK_IMAGE");
	}
    }
}
//$widget->setVariable("UPLOAD_TEXT", gettext("Upload an image"));
//$widget->setVariable("UPLOAD_LINK", PATH_URL . "upload/?env=" . $wiki_container->get_id());
//$widget->setVariable("WIKI_IMAGE_EXTERNAL", gettext("External image"));
//$widget->setVariable("WIKI_IMAGE_EXTERNAL_LINK", "javascript:insert('[[Image:http://', ']]', 'formular', 'values[body]')");
//$content->setCurrentBlock("BLOCK_WIDGET");
//$content->setVariable("WIDGET_TITLE", gettext("Images"));
//$content->setVariable("WIDGET_HTML_CODE", $widget->get());
//$content->parse("BLOCK_WIDGET");

if ($create) {
    $pagetitle = gettext("New Article");
} else {
    $pagetitle = str_replace("%NAME", h(substr($wiki_doc->get_name(), 0, -5)), gettext("Edit '%NAME'?"));
}

$rootlink = lms_steam::get_link_to_root($wiki_container);
(WIKI_FULL_HEADLINE) ?
		$headline = array(
	    $rootlink[0],
	    $rootlink[1],
	    array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
	    array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
	    array("link" => "", "name" => $pagetitle)
		) :
		$headline = array(
	    array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
	    array("link" => "", "name" => $pagetitle));

$portal->set_page_main(
	$headline, $content->get()
);
$portal->show_html();
?>