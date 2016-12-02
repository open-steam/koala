<?php
class FileTree extends AbstractExtension implements IIconBarExtension {

    public function getName() {
        return "FileTree";
    }

    public function getDesciption() {
        return "Extension for file tree view.";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
        return $result;
    }

    public function getIconBarEntries() {
        $this->addJS();
        $this->addCSS();

        $currentID = "";
        $path = explode("/", $_SERVER['REQUEST_URI']);
        for ($count = 0; $count < count($path); $count++) {
            if (intval($path[$count]) !== 0) {
                $currentID = $path[$count];
                break;
            }
        }
        if ($currentID === "403" || $currentID === "404") {
            $currentID = "";
        }
        if (strpos($_SERVER['REQUEST_URI'], "/favorite/") === 0) {
            $currentID = "";
        }

        if ($currentID !== "") {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentID);
            if ($object instanceof \steam_object && !(getObjectType($object) === "room") && !(getObjectType($object) === "userHome")) {
                if ($object->get_environment() instanceof \steam_object) {
                    $currentID = $object->get_environment()->get_id();
                }
            }
        }

        $isExplorerOrBookmark = false;
        if (strpos(strtolower($_SERVER['REQUEST_URI']), "/explorer/") === 0 || strpos(strtolower($_SERVER['REQUEST_URI']), "/bookmarks/") === 0) {
            $isExplorerOrBookmark = true;
            if ($currentID === "") {
                $currentID = \lms_steam::get_current_user()->get_workroom()->get_id();
            }
        }

        $user = \lms_steam::get_current_user();
        $filetree = $user->get_attribute("FILETREE");
        if (!is_array($filetree)) {
            // default options
            $filetree = array("visible" => 0,
                              "position" => "'center'",
                              "width" => 250,
                              "height" => 500);
        }

        \lms_portal::get_instance()->add_javascript_code($this->getName(),
                "var filetreeVisible = " . $filetree["visible"] . ";
                 var filetreePosition = " . $filetree["position"] . ";
                 var filetreeWidth = " . $filetree["width"] . ";
                 var filetreeHeight = " . $filetree["height"] . ";
                 var filetreeCurrentID = '" . $currentID . "';

                 function openFileTree() {
                    if ($('#fileTree').html() == '') {
                            $('#fileTree').fileTree({
                                root: 'root" . $currentID . "',
                                script: 'FileTree',
                            }, function(file) {
                                alert(file);
                            });
                    };
                    if ($('#treeDialog').dialog('isOpen')) {
                        $('#treeDialog').dialog('close');
                    } else {
                        $('#treeDialog').dialog('open');
                    }
               }");

        $result = array();

        if (!$isExplorerOrBookmark) {
            $iconHtml = "<div title='Navigationsbaum'><svg><use xlink:href='" . $this->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>";
            $result[] = array("name" => $iconHtml, "onclick" => "openFileTree()");
        }
        return $result;
    }
}
?>
