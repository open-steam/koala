<?php
//new dummy file for system config
namespace Profile\Commands;

include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

class SystemConfig extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $GENERAL_displayed = false;
    private $CONTACTS_AND_GROUPS_displayed = false;
    private $CONTACT_DATA_displayed = false;
    private $profileUser;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        //chronic
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentOther("profile");
        
        $userObject = $GLOBALS["STEAM"]->get_current_steam_user();
        
        $head = new \Widgets\Label();
        $head->setLabel("Einstellungen");
        
        $checkboxObjectsHidden = new \Widgets\Checkbox();
        $checkboxObjectsHidden->setLabel("Verstecke Objekte im Explorer anzeigen");
        $checkboxObjectsHidden->setCheckedValue("TRUE");
        $checkboxObjectsHidden->setUncheckedValue("FALSE");
        $checkboxObjectsHidden->setData($userObject);
        $checkboxObjectsHidden->setContentProvider(\Widgets\DataProvider::attributeProvider("EXPLORER_SHOW_HIDDEN_DOCUMENTS"));

        $frameResponseObject->addWidget($head);
        $frameResponseObject->addWidget($checkboxObjectsHidden);
        return $frameResponseObject;
    }
}

?>