<?php

class Group extends AbstractExtension {

    public function getName() {
        return "Group";
    }

    public function getDesciption() {
        return "Extension for group view.";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Christoph", "Sens", "csens@mail.upb.de");
        return $result;
    }

    public function getObjectReadableName() {
        return "Gruppen";
    }

    public function getUrlNamespaces() {
        return array(strtolower($this->getName()), "group");
    }

}

?>