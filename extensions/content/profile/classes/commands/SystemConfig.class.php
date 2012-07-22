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
        $this->profileUser = $user;
        $login = $user->get_name();
        $frameResponseObject->setTitle($login);
        $frameResponseObject = $this->execute($frameResponseObject);
        return $frameResponseObject;
    }

    public function display($block, $label, $value, $is_buddy = TRUE) {
        if (empty($value)) {
            return;
        }

        if ($is_buddy && $this->viewer_authorized($label)) {
            //$c = $GLOBALS["content"];
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
        if ($is_contact && !( $current_authorization & PROFILE_DENY_CONTACTS ))
            return true;

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

        //var_dump($request);
        $current_user = \lms_steam::get_current_user();
        //var_dump($current_user);die;

        $name = $this->id;
        if ($name != "") {

            //$userName = $path[2];
            $user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $name);
        } else {
            $user = $current_user;
        }



        $login = $user->get_name();
        $cache = get_cache_function($login, 3600);
        //$portal = \lms_portal::get_instance();
        //$portal->set_page_title( $login );
        $user_profile = $cache->call("lms_steam::user_get_profile", $login);
        $html_handler_profile = new \koala_html_profile($user);
        $html_handler_profile->set_context("profile");

        $GLOBALS["content"] = \Profile::getInstance()->loadTemplate("profile_display.template.html");
        //$content = new HTML_TEMPLATE_IT();
        //$content->loadTemplateFile( PATH_TEMPLATES . "profile_display.template.html" );

        if (!empty($user_profile["USER_PROFILE_DSC"])) {
            $GLOBALS["content"]->setVariable("HTML_CODE_DESCRIPTION", "<p>" . get_formatted_output($user_profile["USER_PROFILE_DSC"]) . "</p>");
        }
        if (!empty($user_profile["USER_PROFILE_WEBSITE_URI"])) {
            $website_name = h(( empty($user_profile["USER_PROFILE_WEBSITE_NAME"]) ) ? $user_profile["USER_PROFILE_WEBSITE_URI"] : $user_profile["USER_PROFILE_WEBSITE_NAME"]);
            $GLOBALS["content"]->setVariable("HTML_CODE_PERSONAL_WEBSITE", "<br/><b>" . gettext("Website") . ":</b> <a href=\"" . h($user_profile["USER_PROFILE_WEBSITE_URI"]) . "\" target=\"_blank\">$website_name</a>");
        }

        //get Buddys from user and put them into the $globals-Array for authorization-query
        $confirmed = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
        $contacts = $cache->call("lms_steam::user_get_buddies", $login, $confirmed);

        $tmp = array();
        foreach ($contacts as $contact) {
            $tmp[] = $contact["OBJ_ID"];
        }
        $GLOBALS["contact_ids"] = $tmp;

        //get Viewer-Authorization and put them into the $globals-Array for authorization-query
        $user_privacy = $cache->call("lms_steam::user_get_profile_privacy", $user->get_name());
        $GLOBALS["authorizations"] = $user_privacy;
        $GLOBALS["current user"] = $current_user;

        //$GLOBALS["content"] = $content;
        ///////////////////////////////////////////////////
        //////////////  GENERAL INFORMATION  //////////////
        ///////////////////////////////////////////////////
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
                //var_dump($mail1);die;
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

        if ($this->GENERAL_displayed)
            $GLOBALS["content"]->setVariable("HEADER_GENERAL_INFORMATION", gettext("General Information"));

        ///////////////////////////////////////////////////
        ///////////////  CONTACTS & GROUPS  ///////////////
        ///////////////////////////////////////////////////
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
            $public = ( $user->get_id() != $current_user->get_id() ) ? TRUE : FALSE;
            $groups = $cache->call("lms_steam::user_get_groups", $login, $public);
            $html_code_groups = "";
            $max_groups = $counter = 25;

            if (count($groups) > 0) {
                usort($groups, "sort_objects");

                foreach ($groups as $id => $group) {
                    if ($counter > 0) {
                        $html_code_groups .= "<a href=\"" . PATH_URL . "groups/" . $group["OBJ_ID"] . "/\">" . h($group["OBJ_NAME"]) . "</a>";
                        $html_code_groups .= ($id == count($groups) - 1 || $counter == 1) ? "" : ", ";
                        $counter--;
                    } else {
                        $html_code_groups .= " <a href=\"" . PATH_URL . "profile/$login/groups/\">(" . gettext("more") . "...)</a>";
                        break;
                    }
                }
            } else {
                $html_code_groups = gettext("No memberships yet.");
            }
            $this->display("CONTACTS_AND_GROUPS", "Groups", $html_code_groups);
        }

        if ($this->CONTACTS_AND_GROUPS_displayed)
            $GLOBALS["content"]->setVariable("HEADER_CONTACTS_AND_GROUPS", gettext("Contacts and Groups"));

        /////////////////////////////////////////////////////
        ///////////////  CONTACT INFORMATION  ///////////////
        /////////////////////////////////////////////////////


        $is_buddy = ( $user->is_buddy($current_user) || $user->get_id() == $current_user->get_id() ) ? TRUE : FALSE;
        if (ENABLED_EMAIL) {
            $mail = h($user_profile["USER_EMAIL"]);
            $mail1 = '<a href="mailto:"' . $mail . '">' . $mail . '</a>';
            $this->display("CONTACT_DATA", "E-mail", $mail1, $is_buddy);
        }
        if (ENABLED_ADDRESS) {
            $adress = h($user_profile["USER_PROFILE_ADDRESS"]);

            $this->display("CONTACT_DATA", "Address", $adress, $is_buddy);
        }
        if (ENABLED_BID_ADRESS) {
            $adress = h($user_profile["USER_PROFILE_ADDRESS"]);
            if (isset($adress) && !is_integer($adress) && trim($adress) != "") {
                $this->display("GENERAL", "Address", h($user_profile["USER_ADRESS"]), $is_buddy);
            }
        }
        if (ENABLED_TELEPHONE) {
            $this->display("CONTACT_DATA", "Telephone", h($user_profile["USER_PROFILE_TELEPHONE"]), $is_buddy);
        }
        if (ENABLED_BID_PHONE) {
            $phone = h($user_profile["bid:user_callto"]);
            if (isset($phone) && $phone != 0 && $phone != "") {
                $phone1 = '<a href="callto:' . $phone . '">' . $phone . '</a>';
                $this->display("GENERAL", "Telefon", $phone1, $is_buddy);
            }
        }
        if (ENABLED_PHONE_MOBILE) {
            $this->display("CONTACT_DATA", "Phone, mobile", h($user_profile["USER_PROFILE_PHONE_MOBILE"]), $is_buddy);
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

        if (ENABLED_ICQ_NUMBER || ENABLED_BID_IM) {
            $icq = h($user_profile["USER_PROFILE_IM_ICQ"]);
            if (isset($icq) && $icq != 0 && $icq != "") {
                $this->display("CONTACT_DATA", "ICQ number", $icq);
            }
        }
        if (ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM) {
            $msn = h($user_profile["USER_PROFILE_IM_MSN"]);
            if (isset($msn) && $msn !== 0 && $msn != "") {
                $msn1 = '<a href="http://members.msn.com/' . $msn . '">' . $msn . '</a>';
                $this->display("CONTACT_DATA", "MSN identification", $msn1);
            }
        }

        // AIM
        if (ENABLED_AIM_ALIAS || ENABLED_BID_IM) {
            if (!empty($user_profile["USER_PROFILE_IM_AIM"])) {
                $aim_alias = h($user_profile["USER_PROFILE_IM_AIM"]);
                if (isset($aim_alias) && $aim_alias !== 0 && $aim_alias != "") {
                    $aim = "<a href=\"aim:" . $aim_alias . "\">" . $aim_alias . "</a>";
                    $this->display("CONTACT_DATA", "AIM-alias", $aim);
                }

                //$aim = "<span id=\"USER_PROFILE_IM_AIM\"><a href=\"{VALUE_AIM_LINK}\">{VALUE_AIM_ALIAS}</a></span>";
            }
        }

        if (ENABLED_YAHOO_ID || ENABLED_BID_IM) {
            $yahoo = (h($user_profile["USER_PROFILE_IM_YAHOO"]) !== 0) ? h($user_profile["USER_PROFILE_IM_YAHOO"]) : "";
            if (isset($yahoo) && $yahoo != 0 && $yahoo != "") {
                
                $yahooUrl = "<a href=\"ymsgr:sendIM?".$yahoo."\">".$yahoo."</a>";
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



        $GLOBALS["content"] = $GLOBALS["content"];
        if ($this->CONTACT_DATA_displayed)
            $GLOBALS["content"]->setVariable("HEADER_CONTACT_DATA", gettext("Contact Data"));


        $GLOBALS["content"]->setVariable("PATH_JAVASCRIPT", PATH_JAVASCRIPT);
        $GLOBALS["content"]->setVariable("KOALA_VERSION", KOALA_VERSION);
        $GLOBALS["content"]->setVariable("USER_LOGIN", $login);

        $html_handler_profile->set_html_left($GLOBALS["content"]->get());

        $frameResponseObject->setHeadline($html_handler_profile->get_headline());
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($html_handler_profile->get_html());
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>