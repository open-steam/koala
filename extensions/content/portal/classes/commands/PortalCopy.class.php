<?php
namespace Portal\Commands;
class PortalCopy extends \AbstractCommand implements \IAjaxCommand, \IFrameCommand
{
    private $params;
    private $id;
    private $user;

    public function validateData(\IRequestObject $requestObject)
    {
        return true;
    }

    public function processData(\IRequestObject $requestObject)
    {
        $this->params = $requestObject->getParams();
        $objectId = $this->params["id"];
        $portalObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $objectId );
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();

        //copy portal
        $portalCopy = $portalObject->copy();

        //remove broken messages: works!
        foreach ($portalCopy->get_inventory() as $columnObject) {
            foreach ($columnObject->get_inventory() as $portletObject) {
                if ($portletObject->get_attribute("bid:portlet")==="msg") {
                    //get ids in attrbute

                    $oldIds = $portletObject->get_attribute("bid:portlet:content");
                    $newIds = array();

                    //delete wrong references messages
                    foreach ($portletObject->get_inventory() as $oldMessageObject) {
                        $oldMessageObject->delete();
                    }

                    foreach ($oldIds as $messageId) {
                        //copy to here
                        //make new id list
                        $msgObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $messageId );
                        $msgCopy = $msgObject->copy();
                        $msgCopy->move($portletObject);
                        $newIds[]=$msgCopy->get_id();
                        //handle included pics
                        $pictrueId = $msgObject->get_attribute("bid:portlet:msg:picture_id");
                        if ($pictrueId!="") {
                            $pictureObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $pictrueId );
                            $pictuteCopy = $pictureObject->copy();
                            $pictuteCopy->move($portletObject); //test
                            $msgCopy->set_attribute("bid:portlet:msg:picture_id",$pictuteCopy->get_id());
                            $msgCopy->move($portletObject);
                        }

                    }
                    //save in attrubute
                    $portletObject->set_attribute("bid:portlet:content",$newIds);
                }
            }
        }

        $portalCopy->move($currentUser);

        return;
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject)
    {
        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
        window.location.reload();
END
        );
        $ajaxResponseObject->addWidget($jswrapper);

        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject)
    {
        // no response
    }

    //not used
    //works in case of no references
    private function getMessage($portalObject, $columnIndex, $portletIndex, $messageIndex)
    {
        $columnCount=0;
        foreach ($portalObject->get_inventory() as $columnObject) {
            //iterate over column
            $portletCount=0;
            if ($columnCount==$columnIndex) foreach ($columnObject->get_inventory() as $portletObject) {
                //iterate over portlet
                if ($portletObject->get_attribute("bid:portlet")==="msg") {
                    $messageCount=0;
                    $messageIndexArray = $portletObject->get_attribute("bid:portlet:content");
                    if ($portletCount==$portletIndex) foreach ($messageIndexArray as $messageId) {
                        //iterate over messages
                        if ($messageCount==$messageIndex) {
                            $message = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $messageId);

                            return $message;
                        }
                        $messageCount++;
                    }
                    $portletObject->delete();
                }
                $portletCount++;
            }
            $columnCount++;
        }
    }

}
