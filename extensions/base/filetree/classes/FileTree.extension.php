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
        if (isset($path[1])) {
            switch ($path[1]) {
                case "forum":
                    if ($path[2] === "showTopic") {
                        $currentID = $path[3];
                        break;
                    }
                case "pyramiddiscussion";
                    if ($path[2] === "view" || $path[2] === "Index") {
                        $currentID = $path[3];
                        break;
                    }
                case "rapidfeedback":
                    if ($path[2] === "view" || $path[2] === "edit") {
                        $currentID = $path[3];
                        break;
                    }
                case "wiki":
                    if ($path[2] === "compare" || $path[2] === "recover" || $path[2] === "entry") {
                        $currentID = $path[3];
                        break;
                    }
                case "worksheet":
                    if ($path[2] === "EditBlock" || $path[2] === "DeleteBlock") {
                        $currentID = $path[3];
                        break;
                    }
                case "404":
                    break;
                case "403":
                    break;
                default:
                    for ($count = count($path)-1; $count >= 0; $count--) {
                        if (intval($path[$count]) !== 0) {
                            $currentID = $path[$count];
                            break;
                        }
                    }
                    break;
            }
        }
        
        $isExplorer = false;
        if (strpos($_SERVER['REQUEST_URI'], "/explorer/") === 0) {
            /*$currentID = substr($_SERVER['REQUEST_URI'], 16);
            if (strpos($currentID, "/") !== -1) {
                $currentID = substr($currentID, 0, strlen($currentID) - 1);
            }*/
            $isExplorer = true;
            if ($currentID === "") {
                $currentID = $GLOBALS["STEAM"]->get_current_steam_user()->get_workroom()->get_id();
            }
        }
        
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
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
        
        if (!$isExplorer) {
            $iconHtml = "<img name=\"false\" title=\"Navigationsbaum\" src=\"" . $this->getAssetUrl() . "icons/tree_white.png\">";
            $result[] = array("name" => $iconHtml, "onclick" => "openFileTree()");
        }
        return $result;
    }
}
?>