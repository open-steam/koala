<?php
namespace Profile\Commands;

class Privacy extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject = $this->execute($frameResponseObject);
        return $frameResponseObject;
    }

    public function execute(\FrameResponseObject $frameResponseObject) {
        $user = \lms_steam::get_current_user();
        
        // display actionbar
        $profileUtils = new \ProfileActionBar($user, $user);
        $actions = $profileUtils->getActions();
        if (count($actions) > 1) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions($actions);
            $frameResponseObject->addWidget($actionBar);
        }
        
        // display infobar
        $infoBar = new \Widgets\InfoBar();
        $infoBar->addParagraph(gettext("Here you can set which persons can see what information on your profile page."));
        $frameResponseObject->addWidget($infoBar);
        
        // get privacy object (create it if it does not exist)
        $privacy_object = $user->get_attribute("KOALA_PRIVACY");
        if (!($privacy_object instanceof \steam_object)) {
            $privacy_object = \steam_factory::create_object($GLOBALS["STEAM"]->get_id(), "privacy profile", CLASS_OBJECT);
            if (!($privacy_object instanceof \steam_object)) {
                throw new \Exception("Error creating Privacy-Proxy-Object", E_USER_NO_PRIVACYPROFILE);
            }
            $user->set_attribute("KOALA_PRIVACY", $privacy_object);
            $privacy_object->set_acquire($user);
            
            $deny_all = PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS;
            $privacy_object->set_attributes(array( 
                "PRIVACY_STATUS" => $deny_all, 
                "PRIVACY_GENDER" => $deny_all, 
                "PRIVACY_FACULTY" => $deny_all,  
                "PRIVACY_MAIN_FOCUS" => $deny_all, 
                "PRIVACY_WANTS" => $deny_all, 
                "PRIVACY_HAVES" => $deny_all, 
                "PRIVACY_ORGANIZATIONS" => $deny_all, 
                "PRIVACY_HOMETOWN" => $deny_all, 
                "PRIVACY_OTHER_INTERESTS" => $deny_all, 
                "PRIVACY_LANGUAGES" => $deny_all, 
                "PRIVACY_CONTACTS" => $deny_all, 
                "PRIVACY_GROUPS" => $deny_all, 
                "PRIVACY_EMAIL" => $deny_all, 
                "PRIVACY_ADDRESS" => $deny_all, 
                "PRIVACY_TELEPHONE" => $deny_all, 
                "PRIVACY_PHONE_MOBILE" => $deny_all, 
                "PRIVACY_WEBSITE" => $deny_all, 
                "PRIVACY_ICQ_NUMBER" => $deny_all, 
                "PRIVACY_MSN_IDENTIFICATION" => $deny_all, 
                "PRIVACY_AIM_ALIAS" => $deny_all, 
                "PRIVACY_YAHOO_ID" => $deny_all, 
                "PRIVACY_SKYPE_NAME" => $deny_all 
            ));
        }
        
        // initialize options
        $options = array(
                    array("value" => 0, "name" => ""),  // 0 = all users
                    array("value" => PROFILE_DENY_ALLUSERS, "name" => ""),  // 1 = only contacts
                    array("value" => (PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS), "name" => "")   // 3 = no one
                );
        
        // show div table
        $tableFirstPart = new \Widgets\RawHtml();
        $contact_label = gettext("All Users");
        if (PLATFORM_ID == "bid") {
            $contact_label = "Meine Favoriten";
        }
        $tableFirstPart->setHtml(
                "<div class='grid' style='display:table; width:70%; margin-left:5px;'>
                    <div style='display:table-row;'>
                        <div style='display:table-cell; width:30%;'></div>
                        <div style='display:table-cell; width:23%;'><center><b>Alle Benutzer</b></center></div>
                        <div style='display:table-cell; width:23%;'><center><b>". $contact_label . "</b></center></div>
                        <div style='display:table-cell; width:23%;'><center><b>Niemand</b></center></div>
                    </div>");
        $frameResponseObject->addWidget($tableFirstPart);
        
        $general_title = new \Widgets\RawHtml();
        $general_title->setHtml("<br><b style='font-size:13px;'>" . gettext("General Information") . "</b><br><br>");
        $frameResponseObject->addWidget($general_title);
            
        if (ENABLED_STATUS || ENABLED_BID_DESCIPTION) {
            $radioButton = new \Widgets\RadioButton();
            if (PLATFORM_ID == "bid") {
                $radioButton->setLabel("Beschreibung");
            } else {
                $radioButton->setLabel(gettext("Status"));
            }
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_STATUS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_GENDER) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Gender"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_GENDER"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_FACULTY) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Origin"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_FACULTY"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_MAIN_FOCUS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Main focus"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_MAIN_FOCUS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_WANTS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Wants"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_WANTS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_HAVES) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Haves"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_HAVES"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_ORGANIZATIONS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Organizations"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ORGANIZATIONS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_HOMETOWN) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Hometown"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_HOMETOWN"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_OTHER_INTERESTS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Other interests"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_OTHER_INTERESTS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_LANGUAGES) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Language"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_LANGUAGES"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_CONTACTS_TITLE) {
            $contacts_title = new \Widgets\RawHtml();
            $contacts_title->setHtml("<br><b style='font-size:13px;'>" . gettext("Contact Data") . "</b><br><br>");
            $frameResponseObject->addWidget($contacts_title);
        }

        if (ENABLED_EMAIL || ENABLED_BID_EMAIL) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("E-Mail"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_EMAIL"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_ADDRESS || ENABLED_BID_ADRESS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Address"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ADDRESS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_TELEPHONE || ENABLED_BID_PHONE) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Phone"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_TELEPHONE"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_PHONE_MOBILE) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Phone, mobile"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_PHONE_MOBILE"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_WEBSITE) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Website"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_WEBSITE"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_ICQ_NUMBER || ENABLED_BID_IM) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("ICQ number"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ICQ_NUMBER"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("MSN identification"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_MSN_IDENTIFICATION"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_AIM_ALIAS || ENABLED_BID_IM) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("AIM-alias"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_AIM_ALIAS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_YAHOO_ID || ENABLED_BID_IM) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Yahoo-ID"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_YAHOO_ID"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_SKYPE_NAME || ENABLED_BID_IM) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Skype name"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_SKYPE_NAME"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_CONTACTS_GROUPS_TITLE) {
            $contacts_title = new \Widgets\RawHtml();
            $contacts_title->setHtml("<br><b style='font-size:13px;'>" . gettext("Contacts and Groups") . "</b><br><br>");
            $frameResponseObject->addWidget($contacts_title);
        }
         
        if (ENABLED_CONTACTS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel($contact_label);
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_CONTACTS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
        if (ENABLED_GROUPS) {
            $radioButton = new \Widgets\RadioButton();
            $radioButton->setLabel(gettext("Groups"));
            $radioButton->setType("horizontal");
            $radioButton->setOptions($options);
            $radioButton->setData($privacy_object);
            $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_GROUPS"));
            $frameResponseObject->addWidget($radioButton);
        }
        
       
         //save button at the end of the form
            $saveButton = new \Widgets\SaveButton();
            $frameResponseObject->addWidget($saveButton); 
        // close privacy div table
        $tableSecondPart = new \Widgets\RawHtml();
        $tableSecondPart->setHtml("</div>");
        $frameResponseObject->addWidget($tableSecondPart);
       
        
        return $frameResponseObject;
    }
}
?>