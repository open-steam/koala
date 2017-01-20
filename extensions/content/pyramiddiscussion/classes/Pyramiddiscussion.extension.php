<?php
class Pyramiddiscussion extends AbstractExtension implements IObjectExtension{

    public function getName(){
        return "Pyramiddiscussion";
    }

    public function getDesciption(){
        return "Extension for Pyramiddiscussions.";
    }

    public function getVersion(){
        return "v1.0.0";
    }

    public function getAuthors(){
        $result = array();
        $result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
        return $result;
    }

    public function getObjectReadableName(){
        return "Pyramidendiskussion";
    }

    public function getObjectReadableDescription(){
        return "Darstellung von Pyramidendiskussionen.";
    }

    public function getObjectIconUrl(){
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/pyramiddiscussion.svg";
    }

    public function getHelpUrl(){
      return "";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment){
        return new \Pyramiddiscussion\Commands\NewPyramiddiscussionForm();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject){
        $pyramidObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
        $pyramidType = $pyramidObject->get_attribute("OBJ_TYPE");
        if ($pyramidType != "0" && strStartsWith($pyramidType, "container_pyramiddiscussion")) {
            return new \Pyramiddiscussion\Commands\Index();
        }
        return null;
    }

    public function getPriority(){
        return 8;
    }

    public function copyPyramiddiscussion($object){
        $group = $object->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
        $user = \lms_steam::get_current_user();

        if ($group->check_access(SANCTION_WRITE, $user)) {
            $instances = $group->get_attribute("PYRAMIDDISCUSSION_INSTANCES");
            if (!is_array($instances)) {
                $instances = array($object->get_id());
            }

            $copy = $object->copy();
            $instances[] = $copy->get_id();
            $group->set_attribute("PYRAMIDDISCUSSION_INSTANCES", $instances);

            $copy->move($user);
        }
    }

    public function deletePyramiddiscussion($object){
        $group = $object->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
        $user = \lms_steam::get_current_user();

        if ($group->check_access(SANCTION_WRITE, $user)) {
            $id = $object->get_id();
            $instances = $group->get_attribute("PYRAMIDDISCUSSION_INSTANCES");
            if (!is_array($instances)) {
                $instances = array($id);
            }

            foreach ($instances as $key => $value) {
                if ($value == $id) {
                    unset($instances[$key]);
                }
            }
            $instances = array_values($instances);

            if (!empty($instances)) {
                $group->set_attribute("PYRAMIDDISCUSSION_INSTANCES", $instances);
            } else {
                // no other instances of this pyramiddiscussion exist, delete groups
                $group->delete();
            }
            $object->delete();
        }
    }
}
