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
        return "Auflistung der Änderungen von abonnierten Objekten.";
    }

    public function getObjectIconUrl() {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
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
        if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE") == "0") {
            if ($portlet->check_access_write()) {
                $private = TRUE;
                $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                $filterHelp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                $filter = array();
                foreach ($filterHelp as $filterElement) {
                    $filter[] = $filterElement[1];
                }
            } else {
                $private = FALSE;
                $timestamp = "1209600";
                $filter = array();
            }
        } else {
            $private = FALSE;
            $timestamp = time() - intval($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE"));
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
        if ($depth > 1) return $updates;
        $type = getObjectType($subscriptionObject);
        if ($type === "forum") {
            $forumSubscription = new \PortletSubscription\Subscriptions\ForumSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $forumSubscription->getUpdates());
        } else if ($type === "wiki") {
            $wikiSubscription = new \PortletSubscription\Subscriptions\WikiSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $wikiSubscription->getUpdates());
        } else if ($type === "room") {
            $folderSubscription = new \PortletSubscription\Subscriptions\FolderSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $folderSubscription->getUpdates());
        } else if ($type === "gallery") {
            $gallerySubscription = new \PortletSubscription\Subscriptions\GallerySubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $gallerySubscription->getUpdates());
        } else if ($type === "portal") {
            $portalSubscription = new \PortletSubscription\Subscriptions\PortalSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $portalSubscription->getUpdates());
        } else if ($type === "rapidfeedback") { // TODO only admin
            $rapidfeedbackSubscription = new \PortletSubscription\Subscriptions\RapidfeedbackSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $rapidfeedbackSubscription->getUpdates());
        } else if ($type === "document" && strstr($subscriptionObject->get_attribute(DOC_MIME_TYPE), "text")) {
            $documentSubscription = new \PortletSubscription\Subscriptions\DocumentSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter, $depth);
            $updates = array_merge($updates, $documentSubscription->getUpdates());
        }
        return $updates;
    }
}
?>