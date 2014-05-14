<?php
class Webarena extends AbstractExtension implements IObjectExtension
{
    public function getName()
    {
        return "Webarena";
    }

    public function getDesciption()
    {
        return "Extension for opening webarena files.";
    }

    public function getVersion()
    {
        return "v1.0.0";
    }

    public function getAuthors()
    {
        $result = array();
        $result[] = new Person("Tobias", "Kempkensteffen", "tobias.kempkensteffen@gmail.com");

        return $result;
    }

    public function getObjectReadableName()
    {
        return "Virtuelle Tafel";
    }

    public function getObjectReadableDescription()
    {
        return "Virtuelle Tafel";
    }

    public function getObjectIconUrl()
    {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/webarena.png";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment)
    {
        return new \Webarena\Commands\NewWebarenaForm();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject)
    {
        $obj = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
        $type = $obj->get_attribute("OBJ_TYPE");
        if ($type === "container_webarena") {
            return new \Webarena\Commands\Index();
        }

        return null;
    }

    public function getPriority()
    {
        return 8;
    }
}
