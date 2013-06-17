<?php
namespace Wiki\Commands;
class Configuration extends \AbstractCommand implements \IFrameCommand {

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
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );
		
		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();
		
		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addJS();
		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$env = $wiki_container->get_environment();
		
		$grp = $env->get_creator();
		if ($grp->get_name() == "learners" && $grp->get_attribute(OBJ_TYPE) == "course_learners") {
		  	$grp = $grp->get_parent_group();
		}
		  
		if (!isset($wiki_container) || !is_object($wiki_container)) {
		    if (empty($_GET["env"]))
			throw new Exception("Environment not set.");
		
		    if (empty($_GET["group"]))
			throw new Exception("Group not set.");
		
		    if (!$env = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_GET["env"]))
			throw new Exception("Environment unknown.");
		
		    if (!$grp = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_GET["group"]))
			throw new Exception("Group unknown");
		}
		
		$accessmergel = FALSE;
		if (isset($wiki_container) && is_object($wiki_container)) {
		    $creator = $wiki_container->get_creator();
		    if ($wiki_container->get_attribute(KOALA_ACCESS) == PERMISSION_UNDEFINED && \lms_steam::get_current_user()->get_id() != $creator->get_id() && !\lms_steam::is_koala_admin(\lms_steam::get_current_user())) {
				$accessmergel = TRUE;
		    }
		}
		
		$backlink = ( empty($_POST["values"]["backlink"]) ) ? $_SERVER["HTTP_REFERER"] : $_POST["values"]["backlink"];
		
		if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["values"])) {
		    $values = $_POST["values"];
		    if (get_magic_quotes_gpc()) {
			if (!empty($values['name']))
			    $values['name'] = stripslashes($values['name']);
		    }
		
		    if (empty($values["name"]) && !empty($_POST['new'])) {
				$problems = "Der Name des Wikis fehlt.";
				$hints = gettext("Please type in a name.");
		    }
		    if (strpos($values["name"], "/")) {
				if (!isset($problems))
			    	$problems = "";
				$problems .= gettext("Please don't use the \"/\"-char in the name of the wiki.");
		    }
		
		    if (empty($problems) && array_key_exists("new", $_POST)) {
                                /*
				$group_members = $grp;
				$group_admins = 0;
				$group_staff = 0;
			
				// check if group is a course
				$grouptype = (string) $grp->get_attribute("OBJ_TYPE");
				if ($grouptype == "course") {
				    $group_staff = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $grp->get_groupname() . ".staff");
				    $group_admins = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $grp->get_groupname() . ".admins");
				    $group_members = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $grp->get_groupname() . ".learners");
				    $workroom = $group_members->get_workroom();
				} else {
				    $workroom = $grp->get_workroom();
				}
			
				if (!isset($wiki_container) || !is_object($wiki_container)) {
				    $new_wiki = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $values["name"], $env, $values["dsc"]);
				    $new_wiki->set_attribute("OBJ_TYPE", "container_wiki_koala");
				    if (isset($is_glossary)) {
					$new_wiki->set_attribute("UNIT_TYPE", "units_glossary");
					$new_wiki->set_attribute("UNIT_DISPLAY_TYPE", gettext("Glossary"));
				    }
				    $_SESSION["confirmation"] = str_replace("%NAME", $values["name"], gettext("New wiki '%NAME' created."));
				} else {
				    $wiki_container->set_attribute(OBJ_NAME, $values["name"]);
                                    $wiki_container->set_attribute(OBJ_DESC, $values["name"]);
				    if ($values["wiki_startpage"] == gettext("Glossary")) $values["wiki_startpage"] = "glossary";
				    $wiki_container->set_attribute("WIKI_STARTPAGE", $values["wiki_startpage"]);
				    $portal->set_confirmation(gettext("The changes have been saved."));
				    $new_wiki = $wiki_container;
				}
			
				$koala_wiki = new \koala_wiki($new_wiki);
				$access = (int) $values["access"];
				$access_descriptions = \koala_wiki::get_access_descriptions($grp);
				if (!$accessmergel)
				    $koala_wiki->set_access($access, $access_descriptions[$access]["members"], $access_descriptions[$access]["steam"], $group_members, $group_staff, $group_admins);
			
				$GLOBALS["STEAM"]->buffer_flush();
			
				$cache = get_cache_function(\lms_steam::get_current_user()->get_name());
				$cache->drop("lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CONTAINER, array("OBJ_TYPE", "WIKI_LANGUAGE"));
			
				$cache->drop("lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM);
			
				if (!isset($wiki_container) || !is_object($wiki_container)) {
					if (isset($owner)) {
			    		header("Location: " . $owner->get_url() . "units/");
					} else {
						header("Location: " . $backlink);
					}
				    exit;
				}*/
                        
                                $wiki_container->set_attribute(OBJ_NAME, $values["name"]);
                                $wiki_container->set_attribute(OBJ_DESC, $values["name"]);
				if ($values["wiki_startpage"] == gettext("Glossary")) $values["wiki_startpage"] = "glossary";
				$wiki_container->set_attribute("WIKI_STARTPAGE", $values["wiki_startpage"]);
				$portal->set_confirmation(gettext("The changes have been saved."));
		    } else {
				$portal->set_problem_description(isset($problems) ? $problems : "", isset($hints) ? $hints : "" );
		    }
		}

		$content = $WikiExtension->loadTemplate("object_new.template.html");
		
		if (isset($wiki_container) && is_object($wiki_container)) {
		    $content->setVariable("INFO_TEXT", str_replace("%NAME", h($wiki_container->get_name()), gettext("You are going to edit the wiki '<b>%NAME</b>'.")));
		    $content->setVariable("LABEL_CREATE", gettext("Save changes"));
		    $pagetitle = gettext("Preferences");
		    if (empty($values)) {
				$values = array();
				$values["name"] = $wiki_container->get_name();
				$values["dsc"] = $wiki_container->get_attribute(OBJ_DESC);
				$startpage = $wiki_container->get_attribute("WIKI_STARTPAGE");
				$values["wiki_startpage"] = ( $startpage == "glossary" ) ? gettext("Glossary") : $startpage;
				$values["access"] = $wiki_container->get_attribute(KOALA_ACCESS);
		    }
		    $breadcrumbheader = gettext("Preferences");
		    $content->setVariable("OPTION_WIKI_GLOSSARY", "Glossar");
		    $wiki_entries = $wiki_container->get_inventory(CLASS_DOCUMENT);
		    $wiki_entries_sorted = array();
		    foreach ($wiki_entries as $wiki_entry) {
			if ($wiki_entry->get_attribute(DOC_MIME_TYPE) === "text/wiki")
			    $wiki_entries_sorted[] = str_replace(".wiki", "", $wiki_entry->get_name());
		    }
		    sort($wiki_entries_sorted);
		    $startpageFound = false;
		    foreach ($wiki_entries_sorted as $wiki_entry) {
				$content->setCurrentBlock("BLOCK_WIKI_STARTPAGE_OPTION");
				$content->setVariable("OPTION_WIKI_STARTPAGE", $wiki_entry);
				if ($values["wiki_startpage"] == $wiki_entry) {
				    $content->setVariable("WIKI_STARTPAGE_SELECTED", "selected");
				    $startpageFound = true;
				}
				$content->parse("BLOCK_WIKI_STARTPAGE_OPTION");
		    }
		
		    if (!$startpageFound)
			$content->setVariable("OPTION_WIKI_GLOSSARY_SELECTED", "selected");
		}
		else {
		    $grpname = $grp->get_attribute(OBJ_NAME);
		    if ($grp->get_attribute(OBJ_TYPE) == "course") {
				$grpname = $grp->get_attribute(OBJ_DESC);
		    }
		    $content->setVariable("OPTION_WIKI_GLOSSARY", "Glossar");
		    $content->setVariable("OPTION_WIKI_GLOSSARY_SELECTED", "selected");
		    $content->setVariable("INFO_TEXT", str_replace("%ENV", h($grpname), gettext("You are going to create a new wiki in '<b>%ENV</b>'.")));
		    $content->setVariable("LABEL_CREATE", gettext("Create wiki"));
		    $pagetitle = gettext("Create wiki");
		    $breadcrumbheader = gettext("Add new wiki");
		}
		
		if (!empty($values)) {
		    if (!empty($values["name"]))
				$content->setVariable("VALUE_NAME", h($values["name"]));
		    if (!empty($values["dsc"]))
				$content->setVariable("VALUE_DSC", h($values["dsc"]));
		    if (!empty($values["wiki_startpage"]))
				$content->setVariable("VALUE_WIKI_STARTPAGE", h($values["wiki_startpage"]));
		}
		$content->setVariable("VALUE_BACKLINK", $backlink);
		$content->setVariable("LABEL_NAME", gettext("Name"));
		$content->setVariable("LABEL_DSC", gettext("Description"));
		$content->setVariable("LABEL_WIKI_STARTPAGE", "Startseite");
		$content->setVariable("LABEL_ACCESS", gettext("Access"));
		
		$content->setVariable("LABEL_BB_BOLD", gettext("B"));
		$content->setVariable("HINT_BB_BOLD", gettext("boldface"));
		$content->setVariable("LABEL_BB_ITALIC", gettext("I"));
		$content->setVariable("HINT_BB_ITALIC", gettext("italic"));
		$content->setVariable("LABEL_BB_UNDERLINE", gettext("U"));
		$content->setVariable("HINT_BB_UNDERLINE", gettext("underline"));
		$content->setVariable("LABEL_BB_STRIKETHROUGH", gettext("S"));
		$content->setVariable("HINT_BB_STRIKETHROUGH", gettext("strikethrough"));
		$content->setVariable("LABEL_BB_IMAGE", gettext("IMG"));
		$content->setVariable("HINT_BB_IMAGE", gettext("image"));
		$content->setVariable("LABEL_BB_URL", gettext("URL"));
		$content->setVariable("HINT_BB_URL", gettext("web link"));
		$content->setVariable("LABEL_BB_MAIL", gettext("MAIL"));
		$content->setVariable("HINT_BB_MAIL", gettext("email link"));
		
		/*if ($accessmergel) {
		    $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode("\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n");
		    $content->setCurrentBlock("BLOCK_ACCESSMERGEL");
		    $content->setVariable("LABEL_ACCESSMERGEL", str_replace("%MAILTO", $mailto, gettext("There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again.")));
		    $content->parse("BLOCK_ACCESSMERGEL");
		} else {
		    $access = \koala_wiki::get_access_descriptions($grp);
		    if ((string) $grp->get_attribute("OBJ_TYPE") == "course") {
				$access_default = PERMISSION_PRIVATE_READONLY;
		    } else {
				$access_default = PERMISSION_PRIVATE_READONLY;
				if (isset($wiki_container) && is_object($wiki_container) && $creator->get_id() != \lms_steam::get_current_user()->get_id()) {
			    	$access[PERMISSION_PRIVATE_READONLY] = str_replace("%NAME", $creator->get_name(), $access[PERMISSION_PRIVATE_READONLY]);
				} else {
			    	$access[PERMISSION_PRIVATE_READONLY] = str_replace("%NAME", "you", $access[PERMISSION_PRIVATE_READONLY]);
				}
		    }
		    if (is_array($access)) {
				$content->setCurrentBlock("BLOCK_ACCESS");
				foreach ($access as $key => $array) {
				    if (($key != PERMISSION_UNDEFINED) || ((isset($values) && (int) $values["access"] == PERMISSION_UNDEFINED ))) {
					$content->setCurrentBlock("ACCESS");
					$content->setVariable("LABEL", $array["summary_short"] . ": " . $array["label"]);
					$content->setVariable("VALUE", $key);
					if ((isset($values) && $key == (int) $values["access"]) || (empty($values) && $key == $access_default)) {
					    $content->setVariable("CHECK", "checked=\"checked\"");
					}
					$content->parse("ACCESS");
				    }
				}
				$content->parse("BLOCK_ACCESS");
		    }
		}*/
		
		$content->setVariable("BACKLINK", "<a class='button' href=\"$backlink\">" . gettext("back") . "</a>");
		if (isset($is_glossary)) {
		    $content->setVariable("NAME_SAVE_BUTTON", "name='unit_new[units_glossary]'");
		} else {
		    $content->setVariable("NAME_SAVE_BUTTON", "name='values[save]'");
		}
		//$rootlink = \lms_steam::get_link_to_root($grp);
		(WIKI_FULL_HEADLINE) ?
				$headline = array(
			    $rootlink[0],
			    $rootlink[1],
			    array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				) : "";
		
		
		if (isset($wiki_container) && is_object($wiki_container)) {
		    $headline[] = array("link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/", "name" => h($wiki_container->get_name()));
		}
		$headline[] = array("link" => "", "name" => $breadcrumbheader);
		
		if (isset($is_glossary)) {
		    $con = $content->get();
		    return;
		}

  		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>