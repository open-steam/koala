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
        
        if (strpos($_SERVER['REQUEST_URI'], "/explorer/") === 0) {
            $currentID = substr($_SERVER['REQUEST_URI'], 16);
            if (strpos($currentID, "/") !== -1) {
                $currentID = substr($currentID, 0, strlen($currentID) - 1);
            }
        } else {
            $currentID = "";
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
                 var filetreeCurrentID = '" . $currentID . "';");
        
        $js = "if ($('#fileTree').html() == '') {
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
               };";
        $iconHtml = "<img name=\"false\" title=\"Navigationsbaum\" 
                        src=\"" . $this->getAssetUrl() . "icons/tree_white.png\">";
        
        $result = array();
        $result[] = array("name" => $iconHtml, "onclick" => $js);
        return $result;
    }
}
?>