<?php
class koala_wiki extends koala_object {

	private $template;
	private $steam_wiki;
  private $version;

	public function __construct($steam_container) {
		$this->template = Wiki::getInstance()->loadTemplate("wiki_index.template.html");

		if (!$steam_container instanceof steam_container) {
			throw new Exception($steam_container->get_id() . " is not a steam_container", E_PARAMETER);
		}
		$this->steam_wiki = $steam_container;
		$this->steam_object = $steam_container;
	}

  public function set_version($version) {
    $this->version = $version;
  }

	public function contains_item($itemname){
		$items = $this->get_items($this->steam_wiki->get_id());
		foreach($items as $item){
			if($item[OBJ_NAME] === $itemname.'.wiki'){
				return true;
			}
		}
		return false;
	}

	public function set_admin_menu($context, $wiki_obj) {
		$user = lms_steam::get_current_user();
		$index_menu = array();
		$entry_menu = array();
		$mediathek_menu = array();
		$version_menu = array();
		if($wiki_obj->get_attribute("UNIT_TYPE")){
		    $place = "units";
		}
		else{
		    $place = "communication";
		}

    $is_admin = false;
		if ($wiki_obj->get_creator()->get_id() == $user->get_id()) {
      $is_admin = true;
		}

		if ($context == "index") {

			//if($wiki_obj->check_access_write( $user )) $index_menu[] = array("link" => PATH_URL . "wiki/edit/" . $wiki_obj->get_id(), "name" => "Neuer Eintrag");
			//if (WIKI_MEDIATHEK && $wiki_obj->check_access_write($user)) $index_menu[] = array("link" => PATH_URL . "wiki/mediathek/" . $wiki_obj->get_id(), "name" => gettext("Mediathek"));

			// TODO for groups
			//$grp = lms_steam::get_koala_group_for_object_id($wiki_obj->get_id());
			//$grp = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $wiki_obj->get_id());
			//$is_admin = $grp->is_admin($user);

			/*
      if ($is_admin) {
				(WIKI_EDIT) ? $index_menu[] = array("link" => PATH_URL . "wiki/configuration/" . $wiki_obj->get_id(), "name" => gettext("Preferences")) : "";
      }
			if ($is_admin) {
				(WIKI_DELETE) ? $index_menu[] = array("link" => PATH_URL . "wiki/delete/" . $wiki_obj->get_id(), "name" => gettext("Delete")) : "";
				(WIKI_EXPORT && ($place !== "units")) ? $index_menu[] = array("link" => PATH_URL . "wiki/export/" . $wiki_obj->get_id(), "name" => "Wiki-Export") : "";
			}
			*/
		}

		/*
		if ($context == "entry") {
			if ($wiki_obj->check_access_write($user)) {
        $entry_menu[] = array("link" => PATH_URL . "wiki/edit/" . $wiki_obj->get_id(), "name" => "Bearbeiten");
        if (WIKI_MEDIATHEK) $entry_menu[] = array("link" => PATH_URL . "wiki/mediathek/" . $wiki_obj->get_environment()->get_id(), "name" => gettext("Mediathek"));
				$entry_menu[] = array("link" => PATH_URL . "wiki/glossary/" . $wiki_obj->get_environment()->get_id() . "/", "name" => "Glossar");
				if ($wiki_obj->check_access_move($user)) {
					$entry_menu[] = array("link" => PATH_URL . "wiki/delete/" . $wiki_obj->get_environment()->get_id() . "/" . $wiki_obj->get_id(), "name" => gettext("Delete"));
				}
			} else {
				$entry_menu[] = array("link" => PATH_URL . "wiki/glossary/" . $wiki_obj->get_environment()->get_id() . "/", "name" => "Glossar");
			}
		}
		*/

		/*
		if ($context == "mediathek") {
			if ($wiki_obj->check_access_write($user)) {
				$mediathek_menu[] = array("onclick" => "sendRequest('Upload', { id : " . $wiki_obj->get_id() . "}, '', 'popup');", "name" => "Bilder hinzufügen");
			}
		}
		*/

		/*
		if ($context == "version") {
			$wiki_orig = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $wiki_obj->get_id());
			if ($wiki_orig->check_access_write($user)) $version_menu[] = array("link" => PATH_URL . "wiki/recover/" . $wiki_orig->get_id() . "/" . $this->version, "name" => gettext("Recover version"));
		}
		*/

		$menue = array("index" => $index_menu, "entry" => $entry_menu, "mediathek" => $mediathek_menu, "version" => $version_menu);

		(isset($menue[$context])) ? $fctns = $menue[$context] : $fctns = "";
		if (is_array($fctns)) {
			$actionBar = new Widgets\ActionBar();
			$actionBar->setActions($fctns);
			$this->template->setCurrentBlock("BLOCK_ADMIN_NEW");
			$this->template->setVariable("ACTIONBAR", $actionBar->getHtml());
			$this->template->parse("BLOCK_ADMIN_NEW");
		}
	}

	public function get_items($id) {
		$wiki = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_CONTAINER);
		$items = $wiki->get_inventory(CLASS_DOCUMENT, array(), SORT_NAME);
		$result = array();
		$i = 0;

		steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $items, array(OBJ_NAME, OBJ_CREATION_TIME, DOC_USER_MODIFIED, DOC_MIME_TYPE, DOC_LAST_MODIFIED));

		$authors = array();

		foreach ($items as $item) {
			if (!strstr($item->get_name(), ".wiki")) {
				continue;
			}

			$result[$i] = $item->get_attributes(array(OBJ_CREATION_TIME, DOC_USER_MODIFIED, DOC_MIME_TYPE, DOC_LAST_MODIFIED, OBJ_NAME));

			$author_obj = $item->get_attribute(DOC_USER_MODIFIED);
			if (is_object($author_obj)) {
				$authors[$author_obj->get_id()] = $item->get_attribute(DOC_USER_MODIFIED);
			}
			$result[$i]["OBJ_ID"] = $item->get_id();
			$i++;
		}

		steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), array_values($authors), array(OBJ_NAME, USER_FULLNAME, USER_FIRSTNAME));

		$i = 0;
		foreach ($items as $item) {
			if (!strstr($item->get_name(), ".wiki")) {
				continue;
			}
			$author = $item->get_attribute(DOC_USER_MODIFIED);
			if (is_object($author)) {
				$name = $authors[$author->get_id()]->get_attribute(USER_FIRSTNAME) . " " . $authors[$author->get_id()]->get_attribute(USER_FULLNAME);
				$result[$i][DOC_USER_MODIFIED] = $name;
			}
			$i++;
		}
		return $result;
	}

	public function set_main_html($html_code) {
        $this->template->setCurrentBlock();
		$this->template->setVariable("WIKI_MAIN_HTML", $html_code);
	}

	public function set_widget_html($widget_title, $widget_html) {
		$this->template->setVariable("WIDGETS_PREFIX", "<td class=\"sidebar\" width=\"30%\">");
		$this->template->setCurrentBlock("BLOCK_WIDGET");
		$this->template->setVariable("WIDGET_TITLE", "<h3>&raquo; " . $widget_title . "</h3>");
		$this->template->setVariable("WIDGET_HTML_CODE", $widget_html);
		$this->template->parse("BLOCK_WIDGET");
		$this->template->setVariable("WIDGETS_POSTFIX", "</td>");
	}

	public function get_html() {
		return $this->template->get();
	}

	public function get_name() {
		return $this->steam_wiki->get_name();
	}

	public function set_widget_most_discussed($linked_list) {
		if ($linked_list->get_current_size() == 0) {
			return NULL;
		}
		$t = Wiki::getInstance()->loadTemplate("widget_wiki_comments.template.html");
		$forward = FALSE;
		$linked_list->reset($forward);
		while ($list_element = $linked_list->get_element($forward)) {
			$wiki_doc = $list_element->get_data();
			$t->setCurrentBlock("BLOCK_LINK");
			$t->setVariable("WIKI_DOC_NAME", str_replace(".wiki", "", h($wiki_doc["OBJ_NAME"])));
			$t->setVariable("WIKI_DOC_LINK", PATH_URL . "wiki/" . $wiki_doc["OBJ_ID"] . "/");
			$t->setVariable("WIKI_DOC_INFO", $wiki_doc["COMMENTS_NO"] . " " . gettext("comments"));
			$t->parse("BLOCK_LINK");
		}
		$this->set_widget_html(gettext("Most Discussed"), $t->get());
	}

	public function set_widget_latest_comments($linked_list) {
		if ($linked_list->get_current_size() == 0) {
			return NULL;
		}
		$t = Wiki::getInstance()->loadTemplate("widget_wiki_comments.template.html");
		$forward = FALSE;
		$linked_list->reset($forward);
		while ($list_element = $linked_list->get_element($forward)) {
			$wiki_doc = $list_element->get_data();
			$t->setCurrentBlock("BLOCK_LINK");
			$t->setVariable("WIKI_DOC_NAME", str_replace(".wiki", "", h($wiki_doc["OBJ_NAME"])));
			$t->setVariable("WIKI_DOC_LINK", PATH_URL . "wiki/" . $wiki_doc["OBJ_ID"] . "/");
			$t->setVariable("WIKI_DOC_INFO", str_replace("%NAME", h($wiki_doc["COMMENTS_LAST_AUTHOR"]), gettext("by %NAME")) . ", " . how_long_ago($wiki_doc["COMMENTS_LAST"]));
			$t->parse("BLOCK_LINK");
		}
		$this->set_widget_html(gettext("Latest Comments"), $t->get());
	}

	public function set_widget_last_changed($linked_list) {
		if ($linked_list->get_current_size() == 0) {
			return NULL;
		}
		$t = Wiki::getInstance()->loadTemplate("widget_wiki_changed.template.html");
		$forward = FALSE;
		$linked_list->reset($forward);
		while ($list_element = $linked_list->get_element($forward)) {
			$t->setCurrentBlock("BLOCK_LINK");
			$wiki_doc = $list_element->get_data();
			$t->setVariable("WIKI_DOC_NAME", str_replace(".wiki", "", h($wiki_doc["OBJ_NAME"])));
			$t->setVariable("LINK_TITLE", str_replace("%NAME", h($wiki_doc["DOC_USER_MODIFIED"]), gettext("by %NAME")) . ", " . how_long_ago($wiki_doc["DOC_LAST_MODIFIED"]));
			$t->setVariable("WIKI_DOC_LINK", PATH_URL . "wiki/" . $wiki_doc["OBJ_ID"] . "/");
			$t->parse("BLOCK_LINK");
		}
		$this->set_widget_html(gettext("Last Modified"), $t->get());
	}

	public function set_widget_categories() {
		$t = Wiki::getInstance()->loadTemplate("widget_weblog_categories.template.html");
		$t->setVariable("IMAGE_FEED", getStyleResourceUrl("images/feedsmall.gif"));
		$t->setVariable("LINK_ALL_ENTRIES", PATH_URL . "weblog/" . $this->steam_weblog->get_id() . "/?entries=all");
		$t->setVariable("LABEL_ALL_ENTRIES", gettext("All entries") . " (" . count($this->steam_weblog->get_date_objects()) . ")");
		$categories = $this->steam_weblog->get_categories();
		foreach ($categories as $category) {
			$items = $category->get_inventory();
			$t->setCurrentBlock("BLOCK_CATEGORY");
			$t->setVariable("BLOCK_IMAGE_FEED", getStyleResourceUrl("images/feedsmall.gif"));
			$t->setVariable("LINK_CATEGORY", PATH_URL . "weblog/" . $category->get_id() . "/");
			$t->setVariable("LABEL_CATEGORY", h($category->get_name()) . "(" . count($items) . ")");
			$t->parse("BLOCK_CATEGORY");
		}
		$this->set_widget_html(gettext("Categories"), $t->get());
	}

	public function set_widget_links($wiki_doc) {
		$t = Wiki::getInstance()->loadTemplate("widget_wiki_links.template.html");
		$links = $wiki_doc->get_attribute("OBJ_WIKILINKS");
		if (!is_array($links)) {
			$links = array();
		}
		foreach ($links as $doc) {
			if ($doc instanceof steam_document) {
				$t->setCurrentBlock("BLOCK_LINK");
				$t->setVariable("WIKI_DOC_LINK", PATH_URL . "wiki/entry/" . $doc->get_identifier());
				$t->setVariable("WIKI_DOC_NAME", str_replace(".wiki", "", h($doc->get_name())));
				$t->parse("BLOCK_LINK");
			}
		}
		$this->set_widget_html(gettext("Links here"), $t->get());
	}

	public function set_widget_previous_versions($wiki_doc) {
		$requests_start = $GLOBALS["STEAM"]->get_request_count();
		$t = Wiki::getInstance()->loadTemplate("widget_wiki_previous_versions.template.html");
		$prev_versions = $wiki_doc->get_previous_versions();
		if (!is_array($prev_versions)) {
			$prev_versions = array();
		}
		$no_versions = count($prev_versions);
		if ($no_versions > 0) {
			$from = 0;
			$to = $no_versions >= 5 ? 5 : $no_versions;
			// Use buffer for document attributes
			$attributes_tnr = array();
			for ($i = $from; $i < $to; $i++) {
				$attributes_tnr[$prev_versions[$i]->get_id()] = $prev_versions[$i]->get_attributes(array(DOC_USER_MODIFIED, DOC_LAST_MODIFIED, DOC_VERSION), TRUE);
			}
			$attributes_result = $GLOBALS["STEAM"]->buffer_flush();
			// use buffer for author attributes
			$author_tnr = array();
			for ($i = $from; $i < $to; $i++) {
				$author_tnr[$prev_versions[$i]->get_id()] = $attributes_result[$attributes_tnr[$prev_versions[$i]->get_id()]][DOC_USER_MODIFIED]->get_attributes(array(USER_FIRSTNAME, USER_FULLNAME, OBJ_NAME), TRUE);
			}
			$author_result = $GLOBALS["STEAM"]->buffer_flush();

			for ($i = $from; $i < $to; $i++) {
				$attributes = $attributes_result[$attributes_tnr[$prev_versions[$i]->get_id()]];
				$last_author = $author_result[$author_tnr[$prev_versions[$i]->get_id()]];
				$t->setCurrentBlock("BLOCK_VERSIONS");
				if ($prev_versions[$i] instanceof steam_document) {
					$t->setVariable("WIKI_DOC_LINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/" . $prev_versions[$i]->get_id() . "/");
					$t->setVariable("WIKI_DOC_VERSION", "Version " . h($attributes_result[$attributes_tnr[$prev_versions[$i]->get_id()]][DOC_VERSION]));
					$t->setVariable("AUTHOR_LINK", PATH_URL . "user/" . $author_result[$author_tnr[$prev_versions[$i]->get_id()]][OBJ_NAME] . "/");
					$t->setVariable("VALUE_POSTED_BY", h($last_author[USER_FIRSTNAME]) . " " . h($last_author[USER_FULLNAME]));
					$t->setVariable("VALUE_DATE_TIME", strftime("%x %X", $attributes["DOC_LAST_MODIFIED"]));
					$t->setVariable("LABEL_BY", gettext("created by"));
					$t->setVariable("VALUE_CREATED", gettext("by") . " " . h($last_author[USER_FIRSTNAME]) . " " . h($last_author[USER_FULLNAME]));
					$t->parse("BLOCK_VERSIONS");
				}
			}
			if ($no_versions > 5) {
				$t->setVariable("MORE_VERSIONS", "<p>" . gettext("and") . " " . h(count($prev_versions) - 5) . " " . gettext("more versions") . ".</p>");
			}
			$t->setVariable("VERSION_MANAGEMENT", "<p><li>&raquo; <small><a href=\"" . PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/\">" . gettext("enter version management") . "</a></small></li>");
		}
		$this->set_widget_html(gettext("Previous versions"), $t->get());
	}

	public function set_widget_archive() {
		$months = $this->steam_weblog->get_archives($this->steam_weblog->get_date_objects());
		$t = Wiki::getInstance()->loadTemplate("widget_weblog_archive.template.html");
		while (list( $year_month, $no_entries ) = each($months)) {
			$year_month_str = explode("-", $year_month);
			$timestamp = mktime(0, 0, 0, $year_month_str[1], 1, $year_month_str[0]);
			$t->setCurrentBlock("BLOCK_ARCHIVE");
			$t->setVariable("LINK_TO_ARCHIVE", PATH_URL . "weblog/" . $this->steam_weblog->get_id() . "/archive/" . $timestamp . "/");
			$t->setVariable("LABEL_ARCHIVE", strftime("%B %g", $timestamp));
			$t->parse("BLOCK_ARCHIVE");
		}
		$t->setVariable("LABEL_CURRENT_POSTS", gettext("Current posts"));
		$this->set_widget_html(gettext("Archive"), $t->get());
	}

	public function set_widget_access($grp) {
		$access_descriptions = koala_wiki::get_access_descriptions($grp);
		$act_access = $this->steam_wiki->get_attribute(KOALA_ACCESS);
		$access_descriptions = $access_descriptions[$act_access];
		$access = $access_descriptions["summary_short"] . ": " . $access_descriptions["label"];
		if ($act_access == PERMISSION_PRIVATE_READONLY) {
			$creator = $this->steam_wiki->get_creator();
			if ($creator->get_id() != lms_steam::get_current_user()->get_id()) {
				$access = str_replace("%NAME", $creator->get_name(), $access);
			} else {
				$access = str_replace("%NAME", "you", $access);
			}
		}
		$t = Wiki::getInstance()->loadTemplate("widget_weblog_access.template.html");
		$t->setCurrentBlock("BLOCK_ACCESS");
		$t->setVariable("LABEL_ACCESS", $access);
		$t->parse("BLOCK_ACCESS");
		$this->set_widget_html(gettext("Access"), $t->get());
	}

	public function set_podcast_link() {
		$this->template->setCurrentBlock("BLOCK_PODCAST");
		$this->template->setVariable("PODCAST_LINK", "pcast://" . str_replace(URL_SCHEMA, "", PATH_URL) . "/services/feeds/podcast.php?id=" . $this->steam_weblog->get_id());
		$this->template->setVariable("PODCAST_LOGO", getStyleResourceUrl("images/podcaster_full_small.jpg"));
		$this->template->parse("BLOCK_PODCAST");
	}

	static public function get_access_descriptions($grp) {
		$private = gettext("Private");
		$public = gettext("Public");
		$staff_only = gettext("Staff only");
		$ret = array(
		PERMISSION_UNDEFINED => array(
		"label" => gettext("Not defined."),
		"summary_short" => gettext("-"))
		);
		if ((string) $grp->get_attribute("OBJ_TYPE") == "course") {
			$ret += array(
			PERMISSION_PRIVATE_READONLY => array(
		    "label" => gettext("Only members can read and comment. Only staff members can edit and post new entries."),
		    "summary_short" => $private,
		    "members" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_MOVE,
		    "steam" => 0,
			),
			PERMISSION_PRIVATE => array(
		    "label" => gettext("Only members can read, edit and post new entries."),
		    "summary_short" => $private,
		    "members" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
		    "steam" => 0,
			),
			PERMISSION_PRIVATE_STAFF => array(
		    "label" => gettext("Only staff members can read, edit and post new entries."),
		    "summary_short" => $staff_only,
		    "members" => 0,
		    "steam" => 0,
			),
			PERMISSION_PUBLIC => array(
		    "label" => gettext("All users can read, edit and post new entries."),
		    "summary_short" => $public,
		    "members" => 0,
		    "steam" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
			),
			PERMISSION_PUBLIC_READONLY => array(
		    "label" => gettext("All users can read and make comments. Only members can edit and post new entries."),
		    "summary_short" => $public,
		    "members" => SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
		    "steam" => SANCTION_READ | SANCTION_ANNOTATE,
			)
			);
		} else if ((string) $grp->get_attribute("OBJ_TYPE") == "group") {
			$ret += array(
			PERMISSION_PRIVATE_READONLY => array(
		    "label" => gettext("Only members can read and make comments. Only %NAME can edit and post new entries."),
		    "summary_short" => $private,
		    "members" => SANCTION_READ | SANCTION_ANNOTATE,
		    "steam" => 0,
			),
			PERMISSION_PRIVATE => array(
		    "label" => gettext("Only members can read, edit and post new entries."),
		    "summary_short" => $private,
		    "members" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
		    "steam" => 0,
			),
			PERMISSION_PUBLIC_READONLY => array(
		    "label" => gettext("All users can read, only members can comment, edit and post new entries."),
		    "summary_short" => $public,
		    "members" => SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
		    "steam" => SANCTION_READ,
			),
			PERMISSION_PUBLIC => array(
		    "label" => gettext("All users can read, edit and post new entries."),
		    "summary_short" => $public,
		    "members" => 0,
		    "steam" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
			)
			);
		} else {
			// wiki is not in a group or course
			$ret += array(
			PERMISSION_PRIVATE_READONLY => array(
		    "label" => gettext("Nur Sie dürfen lesen, Einträge bearbeiten und neue Einträge erstellen."),
		    "summary_short" => $private,
		    "members" => 0,
		    "steam" => 0,
			),
			PERMISSION_PUBLIC_READONLY => array(
		    "label" => gettext("Alle Benutzer dürfen lesen, nur Sie können Einträge bearbeiten und neue Einträge erstellen."),
		    "summary_short" => $public,
		    "members" => 0,
		    "steam" => SANCTION_READ,
			),
			PERMISSION_PUBLIC => array(
		    "label" => gettext("All users can read, edit and post new entries."),
		    "summary_short" => $public,
		    "members" => 0,
		    "steam" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT | SANCTION_WRITE | SANCTION_MOVE,
			)
			);
		}
		return $ret;
	}

	public function get_url() {
		return PATH_URL . "wiki/Index/" . $this->get_id() . "/";
	}

}

?>
