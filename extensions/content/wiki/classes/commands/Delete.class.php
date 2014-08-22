<?php

namespace Wiki\Commands;

class Delete extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $portal = \lms_portal::get_instance();
        $portal->initialize(GUEST_NOT_ALLOWED);

        // Disable caching
        // TODO: Work on cache handling. An enabled cache leads to bugs
        // if used with the wiki.
        \CacheSettings::disable_caching();

        $WikiExtension = \Wiki::getInstance();

        if (isset($this->params[1])) {
            if ($this->params[0] == "version" && isset($this->params[1])) {
                // delete previous version of a doc
                $id = $this->params[1];
                if ($id != null) {
                    $doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                    $parent_wiki = $doc->get_attribute("OBJ_VERSIONOF");
                    $all_versions = $parent_wiki->get_attribute("DOC_VERSIONS");

                    //user authorized ?
                    $current_user = \lms_steam::get_current_user();
                    $author = $doc->get_attribute("DOC_USER_MODIFIED");

                    if ($current_user->get_name() !== $author->get_attribute("OBJ_NAME")) {
                        //TODO: Error Message
                        header("Location: " . PATH_URL . "wiki/versions/" . $parent_wiki->get_id());
                        die;
                    }

                    $keys = array_keys($all_versions);
                    sort($keys);
                    $new_array = array();
                    $new_key = 1;

                    foreach ($keys as $key) {
                        $version = $all_versions[$key];
                        if (!( $version instanceof \steam_document ) || $version->get_id() == $doc->get_id())
                            continue;
                        $version->set_attribute("DOC_VERSION", $new_key);
                        $new_array[$new_key] = $version;
                        $new_key++;
                    }

                    if (empty($new_array)) {
                        $parent_wiki->set_attribute("DOC_VERSIONS", 0);
                        $parent_wiki->set_attribute("DOC_VERSION", 1);
                    } else {
                        $parent_wiki->set_attribute("DOC_VERSIONS", $new_array);
                        $parent_wiki->set_attribute("DOC_VERSION", count($new_array) + 1);
                    }

                    \lms_steam::delete($doc);

                    // clean wiki cache (not used by wiki)
                    $cache = get_cache_function($doc->get_id(), 600);
                    $cache->clean("koala_wiki::get_items", $doc->get_id());
                    $_SESSION["confirmation"] = gettext("Wiki entry deleted sucessfully");
                    header("Location: " . PATH_URL . "wiki/versions/" . $parent_wiki->get_id());

                    // TODO
                    die();
                }
            } else {
                $wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
            }
        }

        $wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $user = \lms_steam::get_current_user();

        if ($wiki_container->get_attribute("UNIT_TYPE")) {
            $place = "units";
        } else {
            $place = "communication";
        }

        // delete a current wiki document
        if (isset($wiki_doc) && $wiki_doc != null) {
            if (@$_REQUEST["force_delete"]) {
                // is deleted entry wiki startpage ?
                $entryName = $wiki_doc->get_name();
                $startpage = $wiki_container->get_attribute("WIKI_STARTPAGE") . ".wiki";

                if ($entryName == $startpage)
                    $wiki_container->set_attribute("WIKI_STARTPAGE", "glossary");

                \lms_steam::delete($wiki_doc);
                // clean wiki cache (not used by wiki)
                $cache = get_cache_function($wiki_container->get_id(), 600);
                $cache->clean("koala_wiki::get_items", $wiki_container->get_id());
                $_SESSION["confirmation"] = gettext("Wiki entry deleted sucessfully");

                //Remove from search index
                if (ENABLED_SEARCH & WIKI_SEARCH_ENABLED) {
                    $indexer = new wiki_indexer($wiki_container->get_id());
                    $indexer->remove_document($wiki_doc->get_id());
                }

                // clean rsscache
                $rcache = get_cache_function("rss", 600);
                $feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wiki_container->get_id();
                $rcache->drop("lms_rss::get_items", $feedlink);

                header("Location: " . PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/");
            } else {
                $wiki_name = h(substr($wiki_doc->get_name(), 0, -5));
                $content = $WikiExtension->loadTemplate("wiki_delete.template.html");
                $content->setVariable("LABEL_ARE_YOU_SURE", str_replace("%NAME", h($wiki_name), gettext("Are you sure you want to delete the wiki page '%NAME' ?")));
                $content->setVariable("LABEL_DELETE", gettext('Delete'));
                $content->setVariable("LABEL_OR", gettext('or'));
                $content->setVariable("LABEL_CANCEL", gettext('Cancel'));
                $content->setVariable("FORM_ACTION", $_SERVER["REQUEST_URI"]);
                $content->setVariable("BACK_LINK", PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/");

                //Breadcrumbs
             //   $rootlink = \lms_steam::get_link_to_root($wiki_container);
                (WIKI_FULL_HEADLINE) ?
                                $headline = array(
                            $rootlink[0],
                            $rootlink[1],
                            array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
                            array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
                            array("link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace(".wiki", "", h($wiki_doc->get_name()))),
                            array("link" => "", "name" => gettext("Delete"))
                                ) :
                                $headline = array(
                            array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
                            array("link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace(".wiki", "", h($wiki_doc->get_name()))),
                            array("link" => "", "name" => gettext("Delete"))
                );

                $rawHtml = new \Widgets\RawHtml();
                $rawHtml->setHtml($content->get());
                $frameResponseObject->addWidget($rawHtml);
                $frameResponseObject->setHeadline($headline);
            }
        } else {
            // delete wiki
            $object = $wiki_container;
            $user = \lms_steam::get_current_user();

            if ($_SERVER["REQUEST_METHOD"] == "POST" && $object->check_access_write($user)) {
                $values = $_POST["values"];
                if ($values["delete"]) {
                    $_SESSION["confirmation"] = str_replace("%NAME", h($object->get_name()), gettext("The wiki '%NAME' has been deleted."));
                    $workroom = $object->get_environment();
                    \lms_steam::delete($object);

                    $wid = $object->get_id();
                    // Clean Cache for the deleted wiki
                    require_once( "Cache/Lite.php" );
                    $cache = new \Cache_Lite(array("cacheDir" => PATH_CACHE));
                    $cache = get_cache_function($wid, 600);
                    $cache->drop("lms_steam::get_items", $wid);
                    // Handle Related Cache-Data
                    require_once( "Cache/Lite.php" );
                    $cache = new \Cache_Lite(array("cacheDir" => PATH_CACHE));
                    $cache->clean($wid);
                    // clean wiki cache (not used by wiki)
                    $fcache = get_cache_function($object->get_id(), 600);
                    $fcache->drop("koala_wiki::get_items", $object->get_id());

                    // Clean communication summary cache für the group/course
                    if (is_object($workroom)) {
                        $cache = get_cache_function(\lms_steam::get_current_user()->get_name(), 600);
                        $cache->drop("lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_CONTAINER | CLASS_ROOM, array("OBJ_TYPE", "WIKI_LANGUAGE"));
                        $cache->drop("lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM);

                        // clean rsscache
                        $rcache = get_cache_function("rss", 600);
                        $feedlink = PATH_URL . "services/feeds/wiki_public.php?id=" . $wid;
                        $rcache->drop("lms_rss::get_items", $feedlink);
                    }

                    header("Location: " . PATH_URL . "explorer/");
                    die;
                }
            }

            $content = $WikiExtension->loadTemplate("object_delete.template.html");
            if ($object->check_access_write($user)) {
                $content->setVariable("LABEL_ARE_YOU_SURE", str_replace("%NAME", h($object->get_name()), gettext("Are you sure you want to delete the wiki '%NAME' ?")));

                //$rootlink = \lms_steam::get_link_to_root($object);
                $content->setVariable("DELETE_BACK_LINK", PATH_URL . "explorer/Index/" . $wiki_container->get_environment()->get_id() . "/");

                $content->setCurrentBlock("BLOCK_DELETE");
                $content->setVariable("FORM_ACTION", $_SERVER["REQUEST_URI"]);
                $content->setVariable("LABEL_DELETE_IT", gettext("yes, delete it"));
                $content->setVariable("BACK_LINK", $_SERVER["HTTP_REFERER"]);
                $content->setVariable("LABEL_RETURN", gettext("back"));
                $content->parse("BLOCK_DELETE");
            } else {
                $content->setVariable("LABEL_ARE_YOU_SURE", gettext("You have no rights to delete this wiki!"));
            }
            $content->setVariable("TEXT_INFORMATION", gettext("The Wiki and all its entries will be deleted."));
            $creator = $object->get_creator();
            $creator_data = $creator->get_attributes(array("USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON"));
            $content->setVariable("LABEL_FROM_AND_AGO", str_replace("%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data["USER_FIRSTNAME"]) . " " . h($creator_data["USER_FULLNAME"]) . "</a>", gettext("by %N")) . ", " . how_long_ago($object->get_attribute("OBJ_CREATION_TIME")));

            $icon = $creator_data["OBJ_ICON"];
            if ($icon instanceof steam_object) {
                $icon_id = $icon->get_id();
            } else {
                $icon_id = 0;
            }

            $content->setVariable("ICON_SRC", PATH_URL . "get_document.php?id=" . $icon_id);

           // $rootlink = \lms_steam::get_link_to_root($wiki_container);
            (WIKI_FULL_HEADLINE) ?
                            $headline = array(
                        $rootlink[0],
                        $rootlink[1],
                        array("link" => $rootlink[1]["link"] . "{$place}/", "name" => gettext("{$place}")),
                        array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
                        array("name" => gettext("Delete wiki"), "link" => "")
                            ) :
                            $headline = array(
                        array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
                        array("name" => gettext("Delete wiki"), "link" => ""));


            $rawHtml = new \Widgets\RawHtml();
            $rawHtml->setHtml($content->get());
            $frameResponseObject->addWidget($rawHtml);
            $frameResponseObject->setHeadline($headline);
        }

        return $frameResponseObject;
    }

}

?>