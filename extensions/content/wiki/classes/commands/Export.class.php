<?php
namespace Wiki\Commands;
class Export extends \AbstractCommand implements \IFrameCommand
{
    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject)
    {
        return true;
    }

    public function processData(\IRequestObject $requestObject)
    {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0]: "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject)
    {
        $portal = \lms_portal::get_instance();
        $portal->initialize( GUEST_NOT_ALLOWED );

        // Disable caching
        // TODO: Work on cache handling. An enabled cache leads to bugs
        // if used with the wiki.
        \CacheSettings::disable_caching();

        $WikiExtension = \Wiki::getInstance();
        $wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        $content = $WikiExtension->loadTemplate("wiki_export.template.html");

        $problems = "";

        $wiki_id = ($wiki_container->get_id());

        $backlink = PATH_SERVER . "/wiki/glossary/" . $wiki_id;

        //Handle request
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $action = $_POST["action"];
            $group = $_POST["group"];
            $msg = "";

            if ($action === "move") {
                $object_to_move = $wiki_container;
                $msg = "Wiki erfolgreich verschoben!";
            } elseif ($action === "copy") {
                $wiki_copy = $wiki_container->copy();
                $object_to_move = $wiki_copy;
                $msg = "Wiki erfolgreich kopiert!";
            } else {
                die;
            }

            $target_group_id = substr($group, 1);
            //echo $target_group_id;
            //echo $group{0};

            if ($group{0} == 'c') {
                $koalaGroup = new \koala_group_course(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $target_group_id, CLASS_GROUP));
            } elseif ($group{0} == 'g') {
                $koalaGroup = new \koala_group_default(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $target_group_id, CLASS_GROUP));
            }

            $targetEnv = $koalaGroup->get_workroom();
            $object_to_move->move($targetEnv);

            $_SESSION["confirmation"] = $msg;
            header("Location: " . $backlink);
            die;
        }

        //Load courses and groups
        $scg = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP);
        $current_semester = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER);
        //$cache->call( "lms_steam::semester_get_courses", $current_semester->get_id(), $user->get_name() );

        $course_memberships = \lms_steam::semester_get_user_coursememberships($current_semester->get_id(), \lms_steam::get_current_user(), "ALL");
        $group_memberships = \lms_steam::user_get_groups(\lms_steam::get_current_user()->get_name(), false);

        $user = \lms_steam::get_current_user();
        if (!empty($group_memberships)) {
            $content->setCurrentBlock("BLOCK_GROUPS");
            $content->setVariable("LABEL_GROUPS", gettext("Groups"));
            foreach ($group_memberships as $group) {
                $sg = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $group['OBJ_ID'], CLASS_GROUP);
                $kg = new \koala_group_default($sg);
                if ($kg->is_admin($user)) {
                    $content->setCurrentBlock("BLOCK_GROUP_ENTRIES");
                    $content->setVariable("VALUE_GROUP_NAME", $group["OBJ_NAME"]);
                    $content->setVariable("VALUE_ID", "g" . $group["OBJ_ID"]);
                    $content->parse("BLOCK_GROUP_ENTRIES");
                }
            }
            $content->parse("BLOCK_GROUPS");
        }

        if (!empty($course_memberships)) {
            $content->setCurrentBlock("BLOCK_COURSES");
            $content->setVariable("LABEL_COURSES", gettext("Courses"));
            foreach ($course_memberships as $course) {
                $sg = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $course['OBJ_ID'], CLASS_GROUP);
                $kg = new \koala_group_course($sg);
                if ($kg->is_admin($user)) {
                    $content->setCurrentBlock("BLOCK_COURSE_ENTRIES");
                    $content->setVariable("VALUE_GROUP_NAME", $course["COURSE_NAME"]);
                    $content->setVariable("VALUE_ID", "c" . $course["OBJ_ID"]);
                    $content->parse("BLOCK_COURSE_ENTRIES");
                }
            }
            $content->parse("BLOCK_COURSES");
        }

        $move_wiki_help_text = <<<HELP
Beim Verschieben eines Wikis werden weiterhin die ursprünglichen Autoren verwendet.
Das Wiki ist in dem Ursprungsbereich nicht mehr verfügbar.
HELP;

        $copy_wiki_help_text = <<<HELP
Beim Kopieren eines Wikis werden Sie zum Autor aller Wikieinträge des neu angelegten Wikis.
Das Wiki bleibt beim Kopieren im Ursprungsbereich erhalten.
HELP;

        $content->setVariable('DESTINATION', "Zielort");
        $content->setVariable('ACTION', "Aktion");

        $content->setVariable('COPY_HELP_TEXT', $copy_wiki_help_text);
        $content->setVariable('MOVE_HELP_TEXT', $move_wiki_help_text);

        $content->setVariable("LABEL_MOVE", "Wiki verschieben");
        $content->setVariable("LABEL_COPY", "Wiki kopieren");
        $content->setVariable("LABEL_CHOOSE", gettext("Choose a group or course"));

        $headline = array();
        //$rootlink = \lms_steam::get_link_to_root($wiki_container);

        (WIKI_FULL_HEADLINE) ?
        $headline = array(
        $rootlink[0],
        $rootlink[1],
        array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
        array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/")
        ) :
        $headline = array(
        array("name" => h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"));

          $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $frameResponseObject->addWidget($rawHtml);
        $frameResponseObject->setHeadline($headline);

        return $frameResponseObject;
    }
}
