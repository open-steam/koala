<?php
class FileTree extends AbstractExtension implements IMenuExtension {

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
    
    public function getMenuEntries() {
        $this->addJS('jqueryFileTree.js');
        $this->addCSS('jqueryFileTree.css');
        
        $result = array();
        
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $workRoom = $currentUser->get_workroom();
        $js = "if ($('#fileTree').html() == '') {
                    $('#fileTree').fileTree({
                        root: '" . $workRoom->get_id() . "',
                        script: 'FileTree',
                    }, function(file) {
                        alert(file);
                    });
               }
               $('#treeDialog').dialog({ height: 500, width: 250, title: 'Navigationsbaum' });";
        $result[] = array("name" => "Navigationsbaum", "onclick" => $js);
        return $result;
    }
}
?>