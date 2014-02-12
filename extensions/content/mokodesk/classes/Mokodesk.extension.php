<?php
class Mokodesk extends AbstractExtension implements IObjectExtension
{
    public function getName()
    {
        return "Mokodesk";
    }

    public function getDesciption()
    {
        return "Extension for opening the Mokodesk Desktop.";
    }

    public function getVersion()
    {
        return "v1.0.0";
    }

    public function getAuthors()
    {
        $result = array();
        $result[] = new Person("Dominik", "Niehus", "dominik.niehus@coactum.de");

        return $result;
    }

    public function getObjectReadableName()
    {
        return "Mokodesk";
    }

    public function getObjectReadableDescription()
    {
        return "Mokodesk";
    }

    public function getObjectIconUrl()
    {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/mokodesk.png";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment)
    {
        return null;
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject)
    {
        $obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
        $type = $obj->get_attribute("OBJ_TYPE");
        if ($type === "LARS_DESKTOP") {
            return new \Mokodesk\Commands\Index();
        }

        return null;
    }

    public function getPriority()
    {
        return 8;
    }
}
