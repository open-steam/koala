<?php

namespace Portal\Commands;

class ColorGeneratorPortal {

    private $id;

    public function setId($id) {
        $this->id = $id;
    }

    public function generateCss() {
        if (!isset($this->id)) {
            throw new \Exception("Id isn't set!");
        }
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if(!($obj instanceof \steam_object)){
            throw new \Exception("current steam object isn't valid!");
        }
        $component_fontcolor = $obj->get_attribute("bid:portal:component_fontcolor");
        $component_bgcolor = $obj->get_attribute("bid:portal:component_bgcolor");
        $headline_bgcolor = $obj->get_attribute("bid:portal:headline_bgcolor");
        $headline_fontcolor = $obj->get_attribute("bid:portal:headline_fontcolor");
        $content_fontcolor = $obj->get_attribute("bid:portal:content_fontcolor");
        $content_bgcolor = $obj->get_attribute("bid:portal:content_bgcolor");
        $link_fontcolor = $obj->get_attribute("bid:portal:link_fontcolor");
        $bgcolor = $obj->get_attribute("bid:portal:bgcolor");
    }

}

?>
