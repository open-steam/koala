<?php

namespace Profile\Commands;

include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

class Index extends \AbstractCommand implements \IFrameCommand {

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
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        //chronic
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentOther("profile");

        $current_user = \lms_steam::get_current_user();

        $name = $this->id;
        if (isset($name)) {
            $user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $name);
        } else {
            $user = $current_user;
        }

        if($user == 0){
          $rawHtml = new \Widgets\RawHtml();
          $rawHtml->setHtml("Der Nutzer kann leider nicht angezeigt werden. Bitte achten Sie auf die korrekte Schreibweise des Nutzernamens. Sollten weiterhin Probleme auftreten ist der angeforderte Nutzer eventuell nicht mehr vorhanden.");
        }
        else{
          $this->profileUser = $user;
          $login = $user->get_name();
          $frameResponseObject->setTitle($login);
          $frameResponseObject = $this->execute($frameResponseObject);
        }
        return $frameResponseObject;
    }

    public function display($block, $label, $value, $is_buddy = TRUE) {
        if (empty($value)) {
            return;
        }

        if ($is_buddy && $this->viewer_authorized($label)) {
            $GLOBALS["content"]->setCurrentBlock("BLOCK_" . $block);
            $GLOBALS["content"]->setVariable("LABEL_" . $block, secure_gettext($label));
            $GLOBALS["content"]->setVariable("VALUE_" . $block, $value);
            $GLOBALS["content"]->parse("BLOCK_" . $block);

            $this->{$block . '_displayed'} = true;
        }
    }

    public function viewer_authorized($label) {
        $current_user = $GLOBALS["current user"];
        $current_userId = $current_user->get_id();
        $profileUser = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $this->id);
        $authorizations = $GLOBALS["authorizations"];
        (isset($authorizations[$this->label_to_mapping($label)])) ? $current_authorization = $authorizations[$this->label_to_mapping($label)] : $current_authorization = "";

        if (!( $current_authorization & PROFILE_DENY_ALLUSERS ))
            return true;
        $is_contact = false;
        if (PLATFORM_ID == "bid") {
            if ($this->profileUser->get_id() == $current_userId) {
                $is_contact = true;
            } else {
                $buddies = $profileUser->get_buddies();
                foreach ($buddies as $buddy) {
                    if ($buddy->get_id() == $current_userId) {
                        $is_contact = true;
                    }
                }
            }
        } else {
            $is_contact = in_array($current_user->get_id(), $GLOBALS["contact_ids"]);
        }

        if ($is_contact && !($current_authorization & PROFILE_DENY_CONTACTS)) {
            return true;
        }

        return false;
    }

    public function label_to_mapping($label) {
        switch ($label) {
            case "Origin": return "PRIVACY_FACULTY";
                break;
            case "Language": return "PRIVACY_LANGUAGES";
                break;
            case "E-Mail": return "PRIVACY_EMAIL";
                break;
            case "E-Mail-Adresse": return "PRIVACY_EMAIL";
                break;
            case "AIM-alias": return "PRIVACY_AIM_ALIAS";
                break;
            case "Yahoo-ID": return "PRIVACY_YAHOO_ID";
                break;
            case "Phone, mobile": return "PRIVACY_PHONE_MOBILE";
                break;
            case "Beschreibung": return "PRIVACY_STATUS";
                break;
            case "Telefon": return "PRIVACY_TELEPHONE";
                break;
            default: return "PRIVACY_" . strtoupper(str_replace(" ", "_", $label));
                break;
        }
    }

    public function execute(\FrameResponseObject $frameResponseObject) {
        // get user object of the user which profile is to be displayed
        $current_user = \lms_steam::get_current_user();
        if ($this->id != "") {
            $user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $this->id);
        } else {
            $user = $current_user;
        }

        $login = $user->get_name();
        $cache = get_cache_function($login, 3600);
        $user_profile = $cache->call("lms_steam::user_get_profile", $login);

        //template
        $GLOBALS["content"] = \Profile::getInstance()->loadTemplate("index.template.html");

        // left side
        $GLOBALS["content"]->setCurrentBlock("BLOCK_LEFT_SIDE");

        $itemId = $user_profile["OBJ_ICON"];
        $icon_link = ( $user_profile["OBJ_ICON"] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "download/image/" . $itemId . "/140/185";

        $GLOBALS["content"]->setVariable("USER_IMAGE", $icon_link);
        $GLOBALS["content"]->setVariable("GIVEN_NAME", $user_profile["USER_FIRSTNAME"]);
        $GLOBALS["content"]->setVariable("FAMILY_NAME", $user_profile["USER_FULLNAME"]);
        if (!empty($user_profile["USER_ACADEMIC_TITLE"])) {
            $GLOBALS["content"]->setVariable("ACADEMIC_TITLE", $user_profile["USER_ACADEMIC_TITLE"]);
        }
        if (!empty($user_profile["USER_ACADEMIC_DEGREE"])) {
            $GLOBALS["content"]->setVariable("ACADEMIC_DEGREE", "(" . $user_profile["USER_ACADEMIC_DEGREE"] . ")");
        }
        if (\lms_steam::is_koala_admin($current_user)) {
            $GLOBALS["content"]->setVariable("LABEL_LAST_LOGIN", gettext("last login") . ":");
            $GLOBALS["content"]->setVariable("LAST_LOGIN", how_long_ago($user_profile["USER_LAST_LOGIN"]));
        }
        if (CHANGE_PROFILE_PICTURE && PROFILE_PICTURE && $current_user->get_id() == $user->get_id()) {
            $editImageURL = PATH_URL . "profile/image/";
            $GLOBALS["content"]->setVariable("EDIT_BUTTON", '<a href=' . $editImageURL . '><svg id="imageEditButton" title="Bearbeiten" style="width:20px; height:20px; color:#FFFFFF;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="' . PATH_URL . 'widgets/asset/edit.svg#edit" /></svg></a>');
        }
        $GLOBALS["content"]->parse("BLOCK_LEFT_SIDE");

        // get buddys of the user and put them into the $globals-array for authorization-query
        $confirmed = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
        $contacts = $cache->call("lms_steam::user_get_buddies", $login, $confirmed);
        $tmp = array();
        foreach ($contacts as $contact) {
            $tmp[] = $contact["OBJ_ID"];
        }
        $GLOBALS["contact_ids"] = $tmp;

        // get privacy settings and put them into the $globals-array for authorization-query
        $user_privacy = $cache->call("lms_steam::user_get_profile_privacy", $user->get_name());
        $GLOBALS["authorizations"] = $user_privacy;
        $GLOBALS["current user"] = $current_user;

        // display actionbar
        $profileActionBar = new \ProfileActionBar($user, $current_user);
        $actions = $profileActionBar->getActions();
        if (count($actions) > 1) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions($actions);
            $frameResponseObject->addWidget($actionBar);
        }

        if ($current_user->get_id() == $user->get_id()) {
            \Profile::getInstance()->addCSS();

            // infobar
            /*
              $infoBar = new \Widgets\InfoBar();
              if (PLATFORM_ID == "bid") {
              $infoBar->addParagraph("Hier können Sie Ihre persönlichen Kontaktdaten und Einstellungen einrichten. Bis auf Ihren Namen sind alle Angaben freiwillig und können von Ihnen geändert werden. Klicken Sie auf den Button <b><i>Profil-Privatsphäre</i></b> um festzulegen, wem welche Informationen angezeigt werden sollen.");
              } else {
              $infoBar->addParagraph(gettext("Please complete your profile. None of the fields are mandatory. Some of the fields can not be changed due to central identity management at the IMT.<br/><b>Note: With the button <i>Profile Privacy</i> you can control which information can be seen by other users.</b>"));
              }
              $frameResponseObject->addWidget($infoBar);
             */

            $clearer = new \Widgets\Clearer();

            if (CHANGE_PROFILE_PRIVACY && PROFILE_PRIVACY) {
                $script = '$("#PrivacyButton").show();'
                        . '$("#PrivacyButton").click(function() {'
                        . 'var that = this;'
                        . 'if ($(this).html() == "Sichtbarkeit ►") {'
                        . '$("#privacyShield").animate({"margin-left":"800px", "width": $("#sl-row").width - 180 + "px"},1000, function () {'
                        . '$(that).html("◄ Sichtbarkeit");'
                        . '});'
                        . '} else {'
                        . '$("#privacyShield").animate({"width":"100%", "margin-left":"460px"},1000, function () {'
                        . '$(that).html("Sichtbarkeit ►");'
                        . '});'
                        . '}'
                        . '});';
            } else {
                $script = '$("#PrivacyButton").hide()';
            }

            // table cell html
            $rawHtml = new \Widgets\RawHtml();
            $rawHtml->setHtml($GLOBALS["content"]->get() . '<td class="detail" valign="top" style="line-height: 13px;">'
                    . '<style type="text/css"> .widget.textarea textarea { margin-top: 5px; margin-bottom: 5px; }</style>'
                    . '<script>'
                    . $script
                    . '</script>');
            $frameResponseObject->addWidget($rawHtml);
            /*
              $contact_label = gettext("All Users");
              if (PLATFORM_ID == "bid") {
              $contact_label = "Meine Favoriten";
              }
             */
            // general information label
            $generalLabel = new \Widgets\RawHtml();
            $generalLabel->setHtml("<div class='grid' style='display:table; width:70%; margin-left:5px;'>
                    <div style='width:770px'>
                        <div style='display:table-cell; width:335px; font-size: 15px;'><b>Allgemeine Informationen</b></div>
                        <div style='display:table-cell; width:100px; font-size: 15px;'><center><b>Sichtbar für:</b></center></div>
                    </div>");
            $frameResponseObject->addWidget($generalLabel);

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
                array("value" => 0, "name" => "Alle Benutzer", "class" => "bidRadio left"), // 0 = all users
                array("value" => PROFILE_DENY_ALLUSERS, "name" => "Meine Favoriten", "class" => "bidRadio"), // 1 = only contacts
                array("value" => (PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS), "name" => "Niemand", "class" => "bidRadio right")   // 3 = no one
            );

            // first name
            if (ENABLED_FIRST_NAME) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("First name"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setReadOnly(TRUE);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_FIRSTNAME"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setReadOnly(true);
                $frameResponseObject->addWidget($radioButton);
            }

            // last name
            if (ENABLED_FULL_NAME) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Last name"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setReadOnly(TRUE);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_FULLNAME"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setReadOnly(true);
                $frameResponseObject->addWidget($radioButton);
            }

            // bid owl name
            if (ENABLED_BID_NAME) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("name"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setReadOnly(TRUE);
                $textWidget->setContentProvider(\Widgets\DataProvider::staticProvider($user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME")));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setReadOnly(true);
                $frameResponseObject->addWidget($radioButton);
            }

            // academic degree and title
            if (ENABLED_DEGREE) {
                $dropDownWidget = new \Widgets\ComboBox();
                $dropDownWidget->setLabel(gettext("Academic title"));
                $dropDownWidget->setData($user);
                $dropDownWidget->setOptions(array(
                    array("name" => "keiner", "value" => ""),
                    array("name" => "Dr.", "value" => "Dr."),
                    array("name" => "PD Dr.", "value" => "PD Dr."),
                    array("name" => "Prof.", "value" => "Prof."),
                    array("name" => "Prof. Dr.", "value" => "Prof. Dr.")));
                $dropDownWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_ACADEMIC_TITLE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($dropDownWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setReadOnly(true);
                $frameResponseObject->addWidget($radioButton);

                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Academic degree"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_ACADEMIC_DEGREE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton2 = new \Widgets\RadioButton();
                $radioButton2->setType("horizontal");
                $radioButton2->setOptions($options);
                $radioButton2->setReadOnly(true);
                $frameResponseObject->addWidget($radioButton2);
            }

            // bid description
            if (ENABLED_BID_DESCIPTION) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Description"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_STATUS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // status
            if (ENABLED_STATUS) {
                $comboBoxWidget = new \Widgets\ComboBox();
                $comboBoxWidget->setLabel(gettext("Status"));
                $comboBoxWidget->setData($user);
                $comboBoxWidget->setOptions(array(
                    array("name" => gettext("student"), "value" => "student"),
                    array("name" => gettext("staff member"), "value" => "staff member"),
                    array("name" => gettext("guest"), "value" => "guest"),
                    array("name" => gettext("alumni"), "value" => "alumni")));
                $comboBoxWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($comboBoxWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_STATUS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // user description
            // TODO: autosave?
            if (ENABLED_USER_DESC) {
                $textWidget = new \Widgets\Textarea();
                $textWidget->setLabel(gettext("Describe yourself"));
                $textWidget->setData($user);
                $textWidget->setWidth("128");
                $textWidget->setHeight("100");
                //$textWidget->setAutosave(TRUE);
                $textWidget->setLinebreaks("");
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_DSC"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_STATUS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // gender
            if (ENABLED_GENDER) {
                $comboBoxWidget = new \Widgets\ComboBox();
                $comboBoxWidget->setLabel(gettext("Gender"));
                $comboBoxWidget->setData($user);
                $comboBoxWidget->setOptions(array(
                    array("name" => gettext("female"), "value" => "F"),
                    array("name" => gettext("male"), "value" => "M"),
                    array("name" => gettext("rather not say"), "value" => "X")));
                $comboBoxWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_GENDER"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($comboBoxWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_GENDER"));
                $frameResponseObject->addWidget($radioButton);
            }

            // faculty
            if (ENABLED_FACULTY) {
                $dropDownWidget = new \Widgets\ComboBox();
                $dropDownWidget->setLabel(gettext("Origin"));
                $dropDownWidget->setData($user);
                $FacultyOptions = array();
                array_push($FacultyOptions, array("name" => gettext("miscellaneous"), "value" => "miscellaneous"));
                $faculties = $cache->call("lms_steam::get_faculties_asc");
                foreach ($faculties as $faculty) {
                    array_push($FacultyOptions, array("name" => $faculty["OBJ_NAME"], "value" => $faculty["OBJ_ID"]));
                }
                $dropDownWidget->setOptions($FacultyOptions);
                $dropDownWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_FACULTY"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($dropDownWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_FACULTY"));
                $frameResponseObject->addWidget($radioButton);
            }


            // wants
            if (ENABLED_WANTS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Wants"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_WANTS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_WANTS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // haves
            if (ENABLED_HAVES) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Haves"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_HAVES"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_HAVES"));
                $frameResponseObject->addWidget($radioButton);
            }

            // hometown
            if (ENABLED_HOMETOWN) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Hometown"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_HOMETOWN"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_HOMETOWN"));
                $frameResponseObject->addWidget($radioButton);
            }

            // main focus
            // TODO: was textarea before
            if (ENABLED_MAIN_FOCUS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Main focus"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_FOCUS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_MAIN_FOCUS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // other interests
            if (ENABLED_OTHER_INTERESTS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Other interests"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_OTHER_INTERESTS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_OTHER_INTERESTS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // organizations
            if (ENABLED_ORGANIZATIONS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Organizations"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_ORGANIZATIONS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ORGANIZATIONS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // contacts title
            if (ENABLED_CONTACTS_TITLE) {
                $contactLabel = new \Widgets\RawHtml();
                $contactLabel->setHtml("<div style='font-size:15px; padding-top:10px;'><b>" . gettext("Contact Data") . "</b></div>");
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($contactLabel);
            }

            // email
            if (ENABLED_EMAIL) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("E-mail"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setReadOnly("TRUE");
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_EMAIL"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);
                // Email Settings?

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_EMAIL"));
                $frameResponseObject->addWidget($radioButton);
            }

            // bid email
            if (ENABLED_BID_EMAIL) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("E-mail"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_EMAIL"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_EMAIL"));
                $frameResponseObject->addWidget($radioButton);
            }

            // address
            if (ENABLED_ADDRESS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Address"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_ADDRESS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ADDRESS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // bid address
            // TODO: was textarea before
            if (ENABLED_BID_ADRESS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Address"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_ADRESS"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ADDRESS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // telephone number
            if (ENABLED_TELEPHONE) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel("Telefon");
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_TELEPHONE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_TELEPHONE"));
                $frameResponseObject->addWidget($radioButton);
            }

            // bid telephone number
            if (ENABLED_BID_PHONE) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel("Telefon");
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:user_callto"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_TELEPHONE"));
                $frameResponseObject->addWidget($radioButton);
            }

            // mobile phone number
            if (ENABLED_PHONE_MOBILE) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Phone, mobile"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_PHONE_MOBILE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_PHONE_MOBILE"));
                $frameResponseObject->addWidget($radioButton);
            }

            // website
            if (ENABLED_WEBSITE) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Website"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_WEBSITE_URI"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_WEBSITE"));
                $frameResponseObject->addWidget($radioButton);
            }

            // icq number
            if (ENABLED_ICQ_NUMBER) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("ICQ number"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_IM_ICQ"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_ICQ_NUMBER"));
                $frameResponseObject->addWidget($radioButton);
            }

            // msn identification
            if (ENABLED_MSN_IDENTIFICATION) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("MSN identification"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_IM_MSN"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_MSN_IDENTIFICATION"));
                $frameResponseObject->addWidget($radioButton);
            }

            // aim alias
            if (ENABLED_AIM_ALIAS) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("AIM-alias"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_IM_AIM"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_AIM_ALIAS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // yahoo id
            if (ENABLED_YAHOO_ID) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Yahoo-ID"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_IM_YAHOO"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_YAHOO_ID"));
                $frameResponseObject->addWidget($radioButton);
            }

            // skype name
            if (ENABLED_SKYPE_NAME || ENABLED_BID_IM) {
                $textWidget = new \Widgets\TextInput();
                $textWidget->setLabel(gettext("Skype name"));
                $textWidget->setData($user);
                $textWidget->setInputWidth(130);
                $textWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_PROFILE_IM_SKYPE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($textWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_SKYPE_NAME"));
                $frameResponseObject->addWidget($radioButton);
            }
            /*
              if (ENABLED_CONTACTS_GROUPS_TITLE) {
              $contacts_title = new \Widgets\RawHtml();
              $contacts_title->setHtml("<br><b style='font-size:15px;'>" . gettext("Contacts and Groups") . "</b><br><br>");
              $frameResponseObject->addWidget($clearer);
              $frameResponseObject->addWidget($contacts_title);
              }
             */
            if (ENABLED_CONTACTS) {
                $raw = new \Widgets\RawHtml();
                $raw->setHtml('<div class="widgets_label">' . $contact_label . ':</div><div style="width:134px; height:19px; float: left;"></div>');
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($raw);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_CONTACTS"));
                $frameResponseObject->addWidget($radioButton);
            }

            if (ENABLED_GROUPS) {
                $raw = new \Widgets\RawHtml();

                $groupString = "";
                $groups = $current_user->get_groups();
                foreach ($groups as $group) {
                    $fullName = $group->get_groupname();

                    $matches = array();
                    $re = "/([a-z0-9\\s\\-\\_]+)+/iu";
                    preg_match_all($re, $fullName, $matches);
                    $groupName = $matches[0][sizeof($matches[0]) - 1];
                    $fromLinkExcludedGroups = array("sTeam");
                    if (!in_array($groupName, $fromLinkExcludedGroups)) {
                        $groupName = "<a href='/group/index/" . $group->get_id() . "'>" . $groupName . "</a>";
                    }
                    $groupDescription = $group->get_attribute("OBJ_DESC");
                    if ($groupDescription != "") {
                        $displaydGroupName = "<acronym title='" . $groupDescription . "'>" . $groupName . "</acronym>";
                        //$fullName = $fullName . " (" . $groupDescription . ")";
                    } else {
                        $displaydGroupName = $groupName;
                    }

                    $groupString = $groupString . $displaydGroupName . '</br>';
                }

                $raw->setHtml('<div class="widgets_label">Meine Gruppen:</div><div style="width:134px; padding-top:11px; float: left; overflow-y:hidden; white-space: nowrap;">' . $groupString . '</div>');
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($raw);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_GROUPS"));
                $frameResponseObject->addWidget($radioButton);
            }

            // settings label
            $settingsLabel = new \Widgets\RawHtml();
            $settingsLabel->setHtml("<div style='font-size:15px; padding-top:10px;'><b>" . "Einstellungen" . "</b></div>");
            $frameResponseObject->addWidget($clearer);
            $frameResponseObject->addWidget($settingsLabel);

            // languages
            if (ENABLED_LANGUAGES || ENABLED_BID_LANGUAGE) {
                $dropDownWidget = new \Widgets\ComboBox();
                $dropDownWidget->setLabel(gettext("Language"));
                $dropDownWidget->setData($user);
                $dropDownWidget->setOptions(array(
                    array("name" => "Deutsch", "value" => "german"),
                    array("name" => "Englisch", "value" => "english")
                ));
                $dropDownWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("USER_LANGUAGE"));
                $frameResponseObject->addWidget($clearer);
                $frameResponseObject->addWidget($dropDownWidget);

                $radioButton = new \Widgets\RadioButton();
                $radioButton->setType("horizontal");
                $radioButton->setOptions($options);
                $radioButton->setData($privacy_object);
                $radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("PRIVACY_LANGUAGES"));
                $frameResponseObject->addWidget($radioButton);
            }

            // hidden documents in explorer
            $checkboxWidget = new \Widgets\Checkbox();
            $checkboxWidget->setLabel("Versteckte Objekte<br>im Explorer anzeigen");
            $checkboxWidget->setCheckedValue("TRUE");
            $checkboxWidget->setUncheckedValue("FALSE");
            $checkboxWidget->setData($user);
            $checkboxWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("EXPLORER_SHOW_HIDDEN_DOCUMENTS"));
            $frameResponseObject->addWidget($clearer);
            $frameResponseObject->addWidget($checkboxWidget);
            $frameResponseObject->addWidget($clearer);

            // explorer view (list vs. gallery)
            $explorerViewWidget = new \Widgets\ComboBox();
            $explorerViewWidget->setLabel("Exploreransicht");
            $explorerViewWidget->setData($user);
            $explorerViewWidget->setOptions(array(
                //array("name" => "Intelligente Auswahl", "value" => "touch"),
                array("name" => "Liste", "value" => "list"),
                array("name" => "Galerie", "value" => "gallery"),
            ));
            $explorerViewWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("EXPLORER_VIEW"));
            $frameResponseObject->addWidget($explorerViewWidget);
            $frameResponseObject->addWidget($clearer);

            // number of items per row (gallery view)
            $galleryNumberWidget = new \Widgets\TextInput();
            $galleryNumberWidget->setLabel("Objekte pro Zeile");
            $galleryNumberWidget->setData($user);
            $galleryNumberWidget->setType("number");
            $galleryNumberWidget->setMin(1);
            $galleryNumberWidget->setMax(10);
            $galleryNumberWidget->setInputWidth(128);
            $galleryNumberWidget->setPlaceholder(5);
            $galleryNumberWidget->setContentProvider(\Widgets\DataProvider::attributeProvider("GALLERY_NUMBER"));
            $frameResponseObject->addWidget($galleryNumberWidget);

            $raw = new \Widgets\RawHtml();
            $raw->setHtml("<script>
            if($('select').val() == 'list'){
              $('.widgets_label').last().hide();
              $('.widgets_label').last().next().hide();
            }
            $('select').change(function(){
              if($(this).val() == 'list'){
                $('.widgets_label').last().hide();
                $('.widgets_label').last().next().hide();
              }else{
                $('.widgets_label').last().show();
                $('.widgets_label').last().next().show();
              }
            });</script>");

            $frameResponseObject->addWidget($raw);
            $frameResponseObject->addWidget($clearer);

            //save button at the end of the form
            $saveButton = new \Widgets\SaveButton();
            $frameResponseObject->addWidget($saveButton);

            // close table
            $rawClose = new \Widgets\RawHtml();
            $rawClose->setHtml("</td></tr></table>");
            $frameResponseObject->addWidget($rawClose);
        } else {
            //show profile of another user
            //\Profile::getInstance()->addCSS();
            $GLOBALS["content"]->setCurrentBlock("BLOCK_RIGHT_SIDE");
            // display profile
            if (!empty($user_profile["USER_PROFILE_DSC"])) {
                $GLOBALS["content"]->setVariable("HTML_CODE_DESCRIPTION", "<p>" . get_formatted_output($user_profile["USER_PROFILE_DSC"]) . "</p>");
            }

            if (!empty($user_profile["USER_PROFILE_WEBSITE_URI"])) {
                $website_name = h(( empty($user_profile["USER_PROFILE_WEBSITE_NAME"]) ) ? $user_profile["USER_PROFILE_WEBSITE_URI"] : $user_profile["USER_PROFILE_WEBSITE_NAME"]);
                $GLOBALS["content"]->setVariable("HTML_CODE_PERSONAL_WEBSITE", "<br/><b>" . gettext("Website") . ":</b> <a href=\"" . h($user_profile["USER_PROFILE_WEBSITE_URI"]) . "\" target=\"_blank\">$website_name</a>");
            }

            //////////////  GENERAL INFORMATION  //////////////
            // Status
            if (ENABLED_BID_DESCIPTION) {
                $user_profile_desc = $user_profile["OBJ_DESC"];
                $status = secure_gettext($user_profile_desc);
                if ($status != "" && !is_integer($status)) {
                    $this->display("GENERAL", "Beschreibung", $status);
                }
            }

            if (ENABLED_STATUS) {
                $user_profile_desc = ( empty($user_profile["OBJ_DESC"]) ) ? "student" : $user_profile["OBJ_DESC"];
                $status = secure_gettext($user_profile_desc);
                $this->display("GENERAL", "Status", $status);
            }

            if (ENABLED_EMAIL) {
                $user_email = (empty($user_profile["USER_EMAIL"])) ? "keine E-Mail-Adresse gesetzt" : $user_profile["USER_EMAIL"];
                $this->display("GENERAL", "E-Mail-Adresse", h($user_email));
            }

            if (ENABLED_BID_EMAIL) {
                $helper = (empty($user_profile["USER_EMAIL"])) ? true : false;
                $user_email = (empty($user_profile["USER_EMAIL"])) ? "keine E-Mail-Adresse gesetzt" : $user_profile["USER_EMAIL"];
                if ($helper) {
                    $this->display("GENERAL", "E-Mail", h($user_email));
                } else {
                    $mail = h($user_profile["USER_EMAIL"]);
                    $mail1 = '<a href="mailto:' . $mail . '">' . $mail . '</a>';
                    $this->display("GENERAL", "E-Mail", $mail1);
                }
            }

            // Gender
            if (ENABLED_GENDER) {
                switch (is_string($user_profile["USER_PROFILE_GENDER"]) ? $user_profile["USER_PROFILE_GENDER"] : "X") {
                    case( "F" ):
                        $gender = gettext("female");
                        break;

                    case( "M" ):
                        $gender = gettext("male");
                        break;

                    default:
                        $gender = gettext("rather not say");
                        break;
                }
                $this->display("GENERAL", "Gender", $gender);
            }

            // Origin - Faculty
            if (ENABLED_FACULTY) {
                $faculty = \lms_steam::get_faculty_name($user_profile["USER_PROFILE_FACULTY"]);
                $this->display("GENERAL", "Origin", $faculty);
            }

            if (ENABLED_WANTS) {
                $this->display("GENERAL", "Wants", h($user_profile["USER_PROFILE_WANTS"]));
            }

            if (ENABLED_HAVES) {
                $this->display("GENERAL", "Haves", h($user_profile["USER_PROFILE_HAVES"]));
            }

            if (ENABLED_ORGANIZATIONS) {
                $this->display("GENERAL", "Organizations", h($user_profile["USER_PROFILE_ORGANIZATIONS"]));
            }

            if (ENABLED_HOMETOWN) {
                $this->display("GENERAL", "Hometown", h($user_profile["USER_PROFILE_HOMETOWN"]));
            }

            if (ENABLED_MAIN_FOCUS) {
                $this->display("GENERAL", "Main focus", h($user_profile["USER_PROFILE_FOCUS"]));
            }

            if (ENABLED_OTHER_INTERESTS) {
                $this->display("GENERAL", "Other interests", h($user_profile["USER_PROFILE_OTHER_INTERESTS"]));
            }

            // LANGUAGE
            if (ENABLED_BID_LANGUAGE) {
                $this->display("GENERAL", "Language", $user_profile["USER_LANGUAGE"]);
            }

            if (ENABLED_LANGUAGES) {
                $languages = array(
                    "english" => array("name" => gettext("English"), "icon" => "flag_gb.gif", "lang_key" => "en_US"),
                    "german" => array("name" => gettext("German"), "icon" => "flag_de.gif", "lang_key" => "de_DE"));

                $ulang = $user_profile["USER_LANGUAGE"];

                if (!is_string($ulang) || $ulang === "0")
                    $ulang = LANGUAGE_DEFAULT_STEAM;
                if (!array_key_exists($ulang, $languages))
                    $ulang = LANGUAGE_DEFAULT_STEAM;

                $language_string = "";

                foreach ($languages as $key => $language) {
                    if ($ulang == $key) {
                        $language_string .= "<img class=\"flag\" src=\"" . PATH_EXTENSIONS . "/profile/asset/icons/images/" . $language["icon"] . "\" title=\"" . $language["name"] . "\" />";
                    }
                }

                $this->display("GENERAL", "Language", $language_string);
            }

            ///////////////  CONTACT INFORMATION  ///////////////
            //$is_buddy = ( $user->is_buddy($current_user) || $user->get_id() == $current_user->get_id() ) ? TRUE : FALSE;
            if (ENABLED_EMAIL) {
                $mail = h($user_profile["USER_EMAIL"]);
                $mail1 = '<a href="mailto:"' . $mail . '">' . $mail . '</a>';
                $this->display("CONTACT_DATA", "E-Mail", $mail1);
            }

            if (ENABLED_ADDRESS) {
                $adress = h($user_profile["USER_PROFILE_ADDRESS"]);
                $this->display("CONTACT_DATA", "Address", $adress);
            }

            if (ENABLED_BID_ADRESS) {
                $adress = h($user_profile["USER_PROFILE_ADDRESS"]);
                if (isset($adress) && !is_integer($adress) && trim($adress) != "") {
                    $this->display("GENERAL", "Address", h($user_profile["USER_ADRESS"]));
                }
            }

            if (ENABLED_TELEPHONE) {
                $this->display("CONTACT_DATA", "Telephone", h($user_profile["USER_PROFILE_TELEPHONE"]));
            }

            if (ENABLED_BID_PHONE) {
                $phone = h($user_profile["bid:user_callto"]);
                if (isset($phone) && $phone != 0 && $phone != "") {
                    $phone1 = '<a href="callto:' . $phone . '">' . $phone . '</a>';
                    $this->display("GENERAL", "Telefon", $phone1);
                }
            }

            if (ENABLED_PHONE_MOBILE) {
                $this->display("CONTACT_DATA", "Phone, mobile", h($user_profile["USER_PROFILE_PHONE_MOBILE"]));
            }

            // Website
            $website_name = $user_profile["USER_PROFILE_WEBSITE_NAME"];
            $website_uri = $user_profile["USER_PROFILE_WEBSITE_URI"];
            if (empty($website_name))
                $website_name = $website_uri;
            $website_link = ( empty($website_name) ) ? '' : '<a target="_blank" href="' . h($website_uri) . '">' . h($website_name) . '</a>';
            if (ENABLED_WEBSITE) {
                $this->display("CONTACT_DATA", gettext("Website"), $website_link);
            }

            if (ENABLED_ICQ_NUMBER) {
                $icq = h($user_profile["USER_PROFILE_IM_ICQ"]);
                if (isset($icq) && $icq != 0 && $icq != "") {
                    $this->display("CONTACT_DATA", "ICQ number", $icq);
                }
            }

            if (ENABLED_MSN_IDENTIFICATION) {
                $msn = h($user_profile["USER_PROFILE_IM_MSN"]);
                if (isset($msn) && $msn !== 0 && $msn != "") {
                    $msn1 = '<a href="http://members.msn.com/' . $msn . '">' . $msn . '</a>';
                    $this->display("CONTACT_DATA", "MSN identification", $msn1);
                }
            }

            // AIM
            if (ENABLED_AIM_ALIAS) {
                if (!empty($user_profile["USER_PROFILE_IM_AIM"])) {
                    $aim_alias = h($user_profile["USER_PROFILE_IM_AIM"]);
                    if (isset($aim_alias) && $aim_alias !== 0 && $aim_alias != "") {
                        $aim = "<a href=\"aim:" . $aim_alias . "\">" . $aim_alias . "</a>";
                        $this->display("CONTACT_DATA", "AIM-alias", $aim);
                    }
                }
            }

            if (ENABLED_YAHOO_ID) {
                $yahoo = (h($user_profile["USER_PROFILE_IM_YAHOO"]) !== 0) ? h($user_profile["USER_PROFILE_IM_YAHOO"]) : "";
                if (isset($yahoo) && $yahoo !== 0 && $yahoo != "") {
                    $yahooUrl = "<a href=\"ymsgr:sendIM?" . $yahoo . "\">" . $yahoo . "</a>";
                    $this->display("CONTACT_DATA", "Yahoo-ID", $yahooUrl);
                }
            }

            // Skype
            if (ENABLED_SKYPE_NAME || ENABLED_BID_IM) {
                if (!empty($user_profile["USER_PROFILE_IM_SKYPE"])) {
                    $skype_alias = h($user_profile["USER_PROFILE_IM_SKYPE"]);
                    if (isset($skype_alias) && $skype_alias !== 0 && $skype_alias != "") {
                        $skype = "<a href=\"skype:" . $skype_alias . "\">" . $skype_alias . "</a>";
                    }
                    $this->display("CONTACT_DATA", "Skype name", $skype);
                }
            }

            ///////////////  CONTACTS & GROUPS  ///////////////
            // CONTACTS
            if (ENABLED_CONTACTS) {
                $html_code_contacts = "";
                $max_contacts = $counter = 25;

                if (count($contacts) > 0) {
                    foreach ($contacts as $id => $contact) {
                        if ($counter > 0) {
                            $title = (!empty($contact["USER_ACADEMIC_TITLE"]) ) ? $contact["USER_ACADEMIC_TITLE"] . " " : "";
                            $html_code_contacts .= "<a href=\"" . PATH_URL . "profile/" . $contact["OBJ_NAME"] . "/\">" . $title . $contact["USER_FIRSTNAME"] . " " . $contact["USER_FULLNAME"] . "</a>";
                            $html_code_contacts .= ($id == count($contacts) - 1 || $counter == 1) ? "" : ", ";
                            $counter--;
                        } else {
                            $html_code_contacts .= " <a href=\"" . PATH_URL . "profile/$login/contacts/\">(" . gettext("more") . "...)</a>";
                            break;
                        }
                    }
                } else {
                    $html_code_contacts = gettext("No contacts yet.");
                }
                $this->display("CONTACTS_AND_GROUPS", "Contacts", $html_code_contacts);
            }

            if (ENABLED_GROUPS) {
                // GROUPS
                $groupString = "";
                $groups = $user->get_groups();
                foreach ($groups as $group) {
                    $name = $group->get_groupname();
                    $groupString = $groupString . $name;
                    $groupDescription = $group->get_attribute("OBJ_DESC");
                    if ($groupDescription != "") {
                        $groupString = $groupString . " (" . $groupDescription . ")";
                    }
                    $groupString = $groupString . '</br>';
                }

                $html_code_groups = '<div style="float: left;">' . $groupString . '</div>';

                $this->display("CONTACTS_AND_GROUPS", "Groups", $html_code_groups);
            }

            if ($this->GENERAL_displayed) {
                $GLOBALS["content"]->setVariable("HEADER_GENERAL_INFORMATION", gettext("General Information"));
            }
            /*
              if ($this->CONTACT_DATA_displayed) {
              $GLOBALS["content"]->setVariable("HEADER_CONTACT_DATA", gettext("Contact Data"));
              }
              if ($this->CONTACTS_AND_GROUPS_displayed) {
              $GLOBALS["content"]->setVariable("HEADER_CONTACTS_AND_GROUPS", gettext("Contacts and Groups"));
              }
             */
            $GLOBALS["content"]->setVariable("DISPLAY_RIGHT_SIDE", "");

            // this needed?
            $GLOBALS["content"]->setVariable("PATH_JAVASCRIPT", PATH_JAVASCRIPT);
            $GLOBALS["content"]->setVariable("KOALA_VERSION", KOALA_VERSION);
            $GLOBALS["content"]->setVariable("USER_LOGIN", $login);

            $GLOBALS["content"]->parse("BLOCK_RIGHT_SIDE");

            $rawEnd = new \Widgets\RawHtml();
            $rawEnd->setHtml($GLOBALS["content"]->get());
            $rawEnd->setCSS(".detail .widgets_label { width: 150px; }");
            $frameResponseObject->addWidget($rawEnd);
        }

        return $frameResponseObject;
    }

}

?>
