<?php

class PortletSubscription extends AbstractExtension implements IObjectExtension {

    public function getName() {
        return "PortletSubscription";
    }

    public function getDesciption() {
        return "Extension for PortletSubscription.";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
        return $result;
    }

    public function getObjectReadableName() {
        return "Abonnement";
    }

    public function getObjectReadableDescription() {
        return "Auflistung der Änderungen von abonnierten Objekten";
    }

    public function getObjectIconUrl() {
        return Explorer::getInstance()->getAssetUrl() . "icons/unsubscribe.svg";
    }

    public function getHelpUrl() {
        return "https://bid.lspb.de/explorer/ViewDocument/1204999/";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment) {
        return new \PortletSubscription\Commands\CreateNewForm();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject) {
        $portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());

        $portletType = $portletObject->get_attribute("bid:portlet");
        if (!($portletType === "subscription"))
            return false;
        if ($idRequestObject->getMethod() == "view") {
            return new \PortletSubscription\Commands\Index();
        }
    }

    public function getPriority() {
        return 50;
    }

    public function calculateUpdates($subscriptionObject, $portlet, $filtering = true) {
        if ($portlet->check_access_write()) {
            $filterHelp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");

                $filter = array();
                foreach ($filterHelp as $filterElement) {
                    if (isset($filter[$filterElement[1]])) {
                        $timestamps = $filter[$filterElement[1]];
                        $timestamps[] = $filterElement[0];
                        sort($timestamps);
                        $filter[$filterElement[1]] = $timestamps;
                    } else {
                        $filter[$filterElement[1]] = array($filterElement[0]);
                    }
                }
            if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE") == "0") {

                // 'private' indicates whether the user can change the attributes of the object (to filter out an update)
                $private = TRUE;
                $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
            } else {
                $private = TRUE;
                $timestamp = time() - intval($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE"));

            }
        } else {
            $private = FALSE;
            $timestamp = time() - "1209600";
            $filter = array();
        }
        if (!$filtering) {
            $filter = array();
        }
        $updates = $this->collectUpdates(array(), $portlet, $subscriptionObject, $private, $timestamp, $filter);

        usort($updates, "sortSubscriptionElements");
        if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_ORDER") == "1") {
            $updates = array_reverse($updates);
        }
        return $updates;
    }

    public function collectUpdates($updates, $portlet, $subscriptionObject, $private, $timestamp, $filter, $depth = 0) {
        if ($depth > 1)
            return $updates;
        $type = getObjectType($subscriptionObject);
        if ($type === "forum") {
            $forumSubscription = new \PortletSubscription\Subscriptions\ForumSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $forumSubscription->getUpdates());
        } else if ($type === "wiki") {
            $wikiSubscription = new \PortletSubscription\Subscriptions\WikiSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $wikiSubscription->getUpdates());
        } else if ($type === "room" || $type === "userHome") {
            $folderSubscription = new \PortletSubscription\Subscriptions\FolderSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $folderSubscription->getUpdates());
        } else if ($type === "gallery") {
            $gallerySubscription = new \PortletSubscription\Subscriptions\GallerySubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $gallerySubscription->getUpdates());
        } else if ($type === "portal") {
            $portalSubscription = new \PortletSubscription\Subscriptions\PortalSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $portalSubscription->getUpdates());
        } else if ($type === "rapidfeedback") {
            $rapidfeedbackSubscription = new \PortletSubscription\Subscriptions\RapidfeedbackSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $rapidfeedbackSubscription->getUpdates());
        } else if ($type === "document" && strstr($subscriptionObject->get_attribute(DOC_MIME_TYPE), "text")) {
            $documentSubscription = new \PortletSubscription\Subscriptions\DocumentSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $documentSubscription->getUpdates());
        } else if ($type === "postbox") {
            $postboxSubscription = new \PortletSubscription\Subscriptions\PostboxSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $postboxSubscription->getUpdates());
        }
        return $updates;
    }

    /**
     * Method to get a standardised name for the subscription. Return the description if it exisits, if the description doesn't exisit, retuen at least the object name.
     *
     * @param steam_object $object the object to get tne name of
     * @param type $length the returned string length. Default is 30, -1 means: no cropping
     * @param type $nameAndDescription set to true, to get a name like OBJ_DESC (OBJ_NAME)
     * @return string returns the generated title
     */
    public static function getNameForSubscription($object, $length = 30, $nameAndDescription = false) {
        if (!($object instanceof steam_object)) {
            return "";
        }

        $objectName = $object->get_name();
        $objectDescription = $object->get_attribute(OBJ_DESC);

        if (($objectName !== 0 && trim($objectName) !== "")) {
            //name exists
            $title = $objectName;
        } else {
            //no name available
            $title = $objectDescription;
        }



        if ($nameAndDescription) {
            $title = $objectName . " (" . $objectDescription . ")";
        }
        //remove line breaks
        $title = str_replace(array("\r", "\n"), "", $title);

        //limit return length
        if ($length != -1 && $length < strlen($title)) {
            $title = mb_substr($title, 0, $length, "UTF-8") . "...";
        }

        return $title;
    }

    public static function getObjectTypeForSubscription($object) {
        if (!($object instanceof steam_object)) {
            return "s Objekt";
        }

        $rawObjectName = getObjectType($object);

        switch ($rawObjectName) {
            case "map":
                return " kml Datei:";
                break;
            case "document":
                return "s Dokument:";
                break;
            case "forum":
                return "s Forum:";
                break;
            case "referenceFolder":
                return " Referenz:";
                break;
            case "referenceFile":
                return " Referenz:";
                break;
            case "user":
                return "r Benutzer:";
                break;
            case "group":
                return " Gruppe:";
                break;
            case "trashbin":
                return "r Papierkorb:";
                break;
            case "docextern":
                return " Internet-Referenz:";
                break;
            case "portal_old":
                return "s altes Portal:";
                break;
            case "gallery":
                return "s Fotoalbum:";
                break;
            case "wiki":
                return "s Wiki:";
                break;
            case "portal":
                return "s Portal:";
                break;
            case "portalColumn":
                return " Portal-Spalte:";
                break;
            case "portalPortlet":
                return "s Portal-Portlet:";
                break;
            case "userHome":
                return "r Benutzerordner:";
                break;
            case "groupWorkroom":
                return "r Gruppen-Arbeitsraum:";
                break;
            case "rapidfeedback":
                return "r Fragebogen:";
                break;
            case "pyramiddiscussion":
                return " Pyramidendiskussion:";
                break;
            case "postbox":
                return "r Briefkasten:";
                break;
            case "ellenberg":
                return "s Ellenbergobjekt:";
                break;
            case "worksheet":
                return "s Arbeitsblatt:";
                break;
            case "webarena":
                return " Webarena:";
                break;
            case "mokodesk":
                return "r Mokodesk:";
                break;
            case "room":
                return "r Ordner:";
                break;
            case "container":
                return "r Ordner:";
                break;

            default:
                return "s Objekt";
                break;
        }
    }


    public static function getPortletTypeForSubscription($portlet) {
        if (!($portlet instanceof steam_object)) {
            return "s Objekt";
        }

        $portletType = $portlet->get_attribute("bid:portlet");

        switch ($portletType) {
            case "msg":
                return "Meldungs-";

            case "headline":
                return "Überschriften-";

            case "topic":
                return "Linkliste-";

            case "appointment":
                return "Terminkalender-";

            case "media":
                return "Medien-";

            case "rss":
                return "RSS-";

            case "poll":
                return "Abstimmungs-";

            case "termplan":
                return "Terminplaner-";

            case "subscription":
                return "Abonnement-";

            case "userpicture":
                return "Benutzerbild-";

            case "chronic":
                return "Verlaufs-";

            case "bookmarks":
                return "Lesezeichen-";

            default:
                return "Unknown-";

        }

    }

}

?>
